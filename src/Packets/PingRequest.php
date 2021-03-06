<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle\Packets;

/**
 * The PINGREQ Packet is sent from a Client to the Server. It can be used to:
 *   1. Indicate to the Server that the Client is alive in the absence of
 *      any other Control Packets being sent from the Client to the Server.
 *   2. Request that the Server responds to confirm that it is alive.
 *   3. Exercise the network to indicate that the Network Connection is active.
 */
class PingRequest extends ControlPacket
{
    public function getControlPacketType(): int
    {
        return ControlPacketType::MQTT_PINGREQ;
    }

    protected function buildPayload()
    {
        // mepty payload
    }
}