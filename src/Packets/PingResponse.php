<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle\Packets;


use MeteoConcept\ReactMqttBundle\Protocols\VersionInterface;

/**
 * A PINGRESP Packet is sent by the Server to the Client in response to a PINGREQ Packet. It indicates that the Server is alive.
 */
class PingResponse extends ControlPacket
{
    const EVENT = 'PING_RESPONSE';

    public function getControlPacketType(): int
    {
        return ControlPacketType::MQTT_PINGRESP;
    }

    /** @noinspection PhpUnusedParameterInspection */
    public static function parse(VersionInterface $version, string $rawInput): PingResponse
    {
        return new static($version);
    }
}