<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle\Packets;

use MeteoConcept\ReactMqttBundle\Protocols\VersionInterface;

/**
 * The CONNACK Packet is the packet sent by the Server in response to
 * a CONNECT Packet received from a Client.
 */
class ConnectionAck extends ControlPacket
{
    const EVENT = 'CONNECTION_ACK';

    const CONNECTION_SUCCESS = 0;
    const CONNECTION_UNACCEPTABLE_PROTOCOL_VERSION = 1;
    const CONNECTION_IDENTIFIER_REJECTED = 2;
    const CONNECTION_SERVER_UNAVAILABLE = 3;
    const CONNECTION_BAD_CREDENTIALS = 4;
    const CONNECTION_AUTH_ERROR = 5;

    /**
     * @var bool
     */
    protected bool $connected;

    /**
     * @var int
     */
    protected int $statusCode;

    /**
     * @var string $reason The reason why the connection failed
     */
    protected string $reason;

    public function getControlPacketType(): int
    {
        return ControlPacketType::MQTT_CONNACK;
    }

    public function setConnected(bool $status)
    {
        $this->connected = $status;
    }

    public function getConnected(): bool
    {
        return $this->connected;
    }

    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setReason(string $reason)
    {
        $this->reason = $reason;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public static function parse(VersionInterface $version, $rawInput): ConnectionAck
    {
        $packet = new static($version);

        $statusCode = ord(substr($rawInput, 3));
        $packet->setStatusCode($statusCode);
        switch ($statusCode) {
            case self::CONNECTION_SUCCESS:
                $packet->setConnected(true);
                $packet->setReason("");
                break;
            case self::CONNECTION_UNACCEPTABLE_PROTOCOL_VERSION:
                $packet->setConnected(false);
                $packet->setReason("Unacceptable protocol version");
                break;
            case self::CONNECTION_IDENTIFIER_REJECTED:
                $packet->setConnected(false);
                $packet->setReason("Identifier rejected");
                break;
            case self::CONNECTION_SERVER_UNAVAILABLE:
                $packet->setConnected(false);
                $packet->setReason("The server is currently unavailable");
                break;
            case self::CONNECTION_BAD_CREDENTIALS:
                $packet->setConnected(false);
                $packet->setReason("Bad credentials, authentication failed");
                break;
            case self::CONNECTION_AUTH_ERROR:
                $packet->setConnected(false);
                $packet->setReason("Authentication failed");
                break;
            default:
                $packet->setConnected(false);
                $packet->setReason("Unknown error");
        }

        return $packet;
    }
}