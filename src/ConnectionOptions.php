<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle;


use MeteoConcept\ReactMqttBundle\Packets\QoS\Levels;

/**
 * Class ConnectionOptions
 * @package MeteoConcept\ReactMqttBundle
 *
 * @property-read array $will
 */
class ConnectionOptions
{
    /**
     * Username
     *
     * Can be used by broker for authentication
     * and authorisation
     *
     * @var string|null
     */
    public ?string $username = null;

    /**
     * Password
     *
     * Can be used by broker for authentication
     * and authorisation
     *
     * @var string|null
     */
    public ?string $password = null;

    /**
     * Client Identifier
     *
     * Used by Clients and by Brokers to identify
     * state that they hold relating to this MQTT
     * Session between the Client and the Server
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Toc398718031
     *
     * @var string|null
     */
    public ?string $clientId = null;

    /**
     * Clean session (flag)
     *
     * If false, broker will resume communication
     * based on previous state, which it remembers.
     *
     * If true, broker will not keep track of any
     * previous state.
     *
     * @var bool
     */
    public bool $cleanSession = true;

    /**
     * Will Topic
     *
     * In case a client disconnects ungracefully,
     * the broker will inform other clients
     * about this, by sending a message to
     * the provided topic.
     *
     * @var string|null
     */
    public ?string $willTopic = null;

    /**
     * Will Message
     *
     * In case a client disconnects ungracefully,
     * the broker will inform other clients
     * about this, by sending a message to
     * the provided topic.
     *
     * @var string|null
     */
    public ?string $willMessage = null;

    /**
     * Will Quality of Service
     *
     * @var int
     */
    public int $willQos = Levels::AT_MOST_ONCE_DELIVERY;

    /**
     * Will Retain (flag)
     *
     * If false, the Broker MUST publish the Will Message as a non-retained message.
     * If true, the Broker MUST publish the Will Message as a retained message.
     *
     * @var bool
     */
    public bool $willRetain = false;

    /**
     * The Keep Alive is a time interval measured in seconds.
     *
     * @see http://docs.oasis-open.org/mqtt/mqtt/v3.1.1/os/mqtt-v3.1.1-os.html#_Keep_Alive
     *
     * @var int
     */
    public int $keepAlive = 0;

    /**
     * ConnectionOptions constructor.
     *
     * @param array $options [optional]
     */
    public function __construct(array $options = [])
    {
        $this->populate($options);
    }

    /**
     * Populate these options from an array
     *
     * @param array $options [optional]
     */
    public function populate(array $options = [])
    {
        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function __get($name)
    {
        if ($name === 'will') {
            $active = $this->willMessage || $this->willTopic || $this->willQos || $this->willRetain;

            return [
                'active' => $active,
                'message' => $this->willMessage,
                'topic' => $this->willTopic,
                'qos' => $this->willQos,
                'retain' => $this->willRetain,
            ];
        }

        return $this->{$name};
    }
}