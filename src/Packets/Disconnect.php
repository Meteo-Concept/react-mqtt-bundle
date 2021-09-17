<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle\Packets;


/**
 * The DISCONNECT Packet is the final Control Packet sent from the Client
 * to the Server. It indicates that the Client is disconnecting cleanly.
 */
class Disconnect extends ControlPacket
{
    const EVENT = 'DISCONNECT';

    public function getControlPacketType(): int
    {
        return ControlPacketType::MQTT_DISCONNECT;
    }

    public function buildPayload()
    {
        // empty payload
    }
}