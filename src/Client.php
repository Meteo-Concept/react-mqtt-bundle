<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle;


use Exception;
use MeteoConcept\ReactMqttBundle\Packets;
use MeteoConcept\ReactMqttBundle\Protocols\Version311;
use MeteoConcept\ReactMqttBundle\Protocols\VersionViolation;
use Psr\Log;
use React\EventLoop;
use React\EventLoop\Loop;
use React\Promise;
use React\Socket;

/**
 * The main MQTT client class, used as a wrapper to the React connection
 * @package MeteoConcept\ReactMqttBundle
 */
class Client
{
    /**
     * @var Socket\ConnectorInterface The React socket used for communication with the server
     */
    protected Socket\ConnectorInterface $connector;

    /**
     * @var EventLoop\LoopInterface The React event loop driving the client asynchronous behaviour
     */
    protected EventLoop\LoopInterface $loop;

    /**
     * @var Log\LoggerInterface A place where logging messages can be sent
     */
    protected $logger;

    /**
     * @var EventLoop\TimerInterface The timer reminding this client to send keep-alive packets
     */
    protected EventLoop\TimerInterface $keepAliveTimer;

    /**
     * @var string The state of the connection to the server
     */
    protected string $state;

    /**
     * @var Version311 A service able to extract MQTT control packets out of the
     * data stream and to build packets for sending
     */
    protected Version311 $encoderDecoder;

    /**
     * @var string State enumeration: the state of the connection when the
     * client is ready to connect
     */
    const STATE_INITIATED = 'initiated';
    /**
     * @var string State enumeration: the state of the connection when the
     * connection request has been sent
     */
    const STATE_CONNECTING = 'connecting';
    /**
     * @var string State enumeration: the state of the connection when the
     * connection has been handled and acknowledged by the server
     */
    const STATE_CONNECTED = 'connected';
    /**
     * @var string State enumeration: the state of the connection when the
     * client is not yet or no longer connected
     */
    const STATE_DISCONNECTED = 'disconnected';

    /**
     * Construct
     */
    public function __construct(
        ?EventLoop\LoopInterface $loop = null,
        ?Log\LoggerInterface $logger = null,
        ?Socket\ConnectorInterface $connector = null
    ) {
        if ($loop === null)
            $this->loop = Loop::get();
        else
            $this->loop = $loop;

        if ($connector === null)
            $this->connector = new Socket\Connector([], $this->loop);
        else
            $this->connector = $connector;

        $this->logger = $logger;
        $this->encoderDecoder = new Version311(new StrictClientIdGenerator());
        $this->state = self::STATE_INITIATED;

        if ($this->logger === null) {
            $this->logger = new Log\NullLogger();
        }
    }

    public function connect(string $uri, ConnectionOptions $options = null)
    {
        $this->logger->debug(sprintf('Initiating connection to %s', $uri));
        $this->state = self::STATE_CONNECTING;

        // Set default connection options, if none provided
        if ($options == null) {
            $options = $this->getDefaultConnectionOptions();
        }

        $promise = $this->connector->connect($uri);

        $promise->then(function (Socket\ConnectionInterface $stream) {
            $this->startHandlingIncomingPackets($stream);
        });

        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $connection = $promise
            ->then(function (Socket\ConnectionInterface $stream) use ($options) {
                return $this->sendConnectPacket($stream, $options);
            })
            ->then(function (Socket\ConnectionInterface $stream) use ($options) {
                $this->state = self::STATE_CONNECTED;
                return $this->setupKeepAlive($stream, $options->keepAlive);
            })
            ->otherwise(function (Exception $e) {
                if ($e instanceof ConnectionException) {
                    $this->logger->critical('Connection error', [$e->getMessage()]);
                }
                throw $e;
            });

        return $connection;
    }

    protected function startHandlingIncomingPackets(Socket\ConnectionInterface $stream)
    {
        $this->encoderDecoder->reset();

        $stream->on('data', function ($raw) use ($stream) {
            try {
                $this->encoderDecoder->pushData($raw);
                foreach ($this->encoderDecoder->extractPackets() as $packet) {
                    $this->logger->debug('Received packet: ' . get_class($packet));
                    $stream->emit($packet::EVENT, [$packet]);
                }
            } catch (VersionViolation $e) {
                $stream->emit('INVALID', [$e]);
            }
        });

        $stream->on('close', function () {
            $this->state = self::STATE_DISCONNECTED;
            $this->logger->debug('Stream was closed');
        });

        $this->logger->debug('Incoming packet handling initiated');
    }

    protected function sendConnectPacket(
        Socket\ConnectionInterface $stream,
        ConnectionOptions $options): Promise\PromiseInterface
    {
        $packet = new Packets\Connect(
            $this->encoderDecoder,
            $options->username,
            $options->password,
            $options->clientId,
            $options->cleanSession,
            $options->will,
            $options->keepAlive
        );

        $deferred = new Promise\Deferred();
        $stream->on(Packets\ConnectionAck::EVENT, function (Packets\ConnectionAck $ack) use ($stream, $deferred) {
            $this->logger->debug('Received ' . Packets\ConnectionAck::EVENT . ' event', ['statusCode' => $ack->getStatusCode()]);
            if ($ack->getConnected()) {
                $deferred->resolve($stream);
            }
            $deferred->reject(
                new ConnectionException("Unable to establish connection, statusCode is {$ack->getStatusCode()}: {$ack->getReason()}")
            );
        });

        $this->sendPacketToStream($stream, $packet);

        return $deferred->promise();
    }

    protected function setupKeepAlive(Socket\ConnectionInterface $stream, int $interval)
    {
        if ($interval > 0) {
            $this->logger->debug('KeepAlive interval is ' . $interval);
            $this->loop->addPeriodicTimer($interval, function (EventLoop\TimerInterface $timer) use ($stream) {
                if ($this->state === self::STATE_CONNECTED) {
                    $packet = new Packets\PingRequest($this->encoderDecoder);
                    $this->sendPacketToStream($stream, $packet);
                }
                $this->keepAliveTimer = $timer;
            });
        }

        return Promise\resolve($stream);
    }

    public function subscribe(Socket\ConnectionInterface $stream, $topic, $qos = 0): Promise\PromiseInterface
    {
        if ($this->state !== self::STATE_CONNECTED) {
            return Promise\reject('Connection unavailable');
        }

        $subscribePacket = new Packets\Subscribe($this->encoderDecoder);
        $subscribePacket->addSubscription($topic, $qos);
        $this->sendPacketToStream($stream, $subscribePacket);
        $this->logger->debug('Sending subscription, packetId: '.$subscribePacket->getPacketId());

        $deferred = new Promise\Deferred();
        $stream->on(Packets\SubscribeAck::EVENT, function (Packets\SubscribeAck $ackPacket) use ($stream, $deferred, $subscribePacket) {
            if ($subscribePacket->getPacketId() === $ackPacket->getPacketId()) {
                $this->logger->debug('Subscription successful', [
                    'topic' => $subscribePacket->getTopic(),
                    'qos' => $subscribePacket->getQoS()
                ]);
                $deferred->resolve($stream);
            } else {
                $deferred->reject('Subscription ack has wrong packetId');
            }
        });

        return $deferred->promise();
    }

    public function unsubscribe(Socket\ConnectionInterface $stream, $topic): Promise\PromiseInterface
    {
        if ($this->state !== self::STATE_CONNECTED) {
            return Promise\reject('Connection unavailable');
        }

        $unsubscribePacket = new Packets\Unsubscribe($this->encoderDecoder);
        $unsubscribePacket->removeSubscription($topic);
        $this->sendPacketToStream($stream, $unsubscribePacket);

        $deferred = new Promise\Deferred();

        $stream->on(Packets\UnsubscribeAck::EVENT, function (Packets\UnsubscribeAck $ackPacket) use ($stream, $deferred, $unsubscribePacket) {
            if ($unsubscribePacket->getPacketId() === $ackPacket->getPacketId()) {
                $this->logger->debug('Unsubscription successful', [
                    'topic' => $unsubscribePacket->getTopic()
                ]);
                $deferred->resolve($stream);
            } else {
                $deferred->reject('Subscription ack has wrong packetId');
            }
        });

        return $deferred->promise();
    }

    public function publish(
        Socket\ConnectionInterface $stream,
        string $topic,
        string $message,
        int $qos = 0,
        bool $dup = false,
        bool $retain = false
    ): Promise\PromiseInterface
    {
        if ($this->state !== self::STATE_CONNECTED) {
            return Promise\reject('Connection unavailable');
        }

        $publishPacket = new Packets\Publish($this->encoderDecoder);
        $publishPacket->setTopic($topic);
        $publishPacket->setQos($qos);
        $publishPacket->setDup($dup);
        $publishPacket->setRetain($retain);

        $success = $this->sendPacketToStream($stream, $publishPacket, $message);

        $deferred = new Promise\Deferred();
        if ($success) {
            if ($qos === Packets\QoS\Levels::AT_LEAST_ONCE_DELIVERY) {
                $stream->on(Packets\PublishAck::EVENT, function (Packets\PublishAck $message) use ($deferred, $stream) {
                    $this->logger->debug('QoS: '.Packets\QoS\Levels::AT_LEAST_ONCE_DELIVERY.', packetId: '.$message->getPacketId());
                    $deferred->resolve($stream);
                });
            } elseif ($qos === Packets\QoS\Levels::EXACTLY_ONCE_DELIVERY) {
                $stream->on(Packets\PublishReceived::EVENT, function (Packets\PublishReceived $receivedPacket) use ($stream, $deferred, $publishPacket) {
                    if ($publishPacket->getPacketId() === $receivedPacket->getPacketId()) {
                        $this->logger->debug('QoS: '.Packets\QoS\Levels::EXACTLY_ONCE_DELIVERY.', packetId: '.$receivedPacket->getPacketId());

                        $releasePacket = new Packets\PublishRelease($this->encoderDecoder);
                        $releasePacket->setPacketId($receivedPacket->getPacketId());
                        $stream->write($releasePacket->buildPacket());

                        $deferred->resolve($stream);
                    } else {
                        $deferred->reject('PublishReceived ack has wrong packetId');
                    }
                });
            } else {
                $deferred->resolve($stream);
            }
        } else {
            $deferred->reject();
        }

        return $deferred->promise();
    }

    public function disconnect(Socket\ConnectionInterface $stream): Promise\PromiseInterface
    {
        $packet = new Packets\Disconnect($this->encoderDecoder);
        $this->sendPacketToStream($stream, $packet);

        $stream->close();
        return Promise\resolve($stream);
    }

    protected function sendPacketToStream(
        Socket\ConnectionInterface $stream,
        Packets\ControlPacket $controlPacket,
        string $additionalPayload = ''
    ): bool
    {
        $this->logger->debug('Sending packet to stream', ['packet' => get_class($controlPacket)]);
        $message = $controlPacket->buildPacket($additionalPayload);

        return $stream->write($message);
    }


    /**
     * Returns default connection options
     *
     * @return ConnectionOptions
     */
    private function getDefaultConnectionOptions(): ConnectionOptions
    {
        return new ConnectionOptions();
    }
}