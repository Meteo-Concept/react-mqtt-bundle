<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle\Packets;


/**
 * A PUBREL Packet is the response to a PUBREC Packet.
 * It is the third packet of the QoS 2 protocol exchange.
 */
class PublishRelease extends ControlPacket
{
    const EVENT = 'PUBLISH_RELEASE';

    /**
     * @var int
     */
    protected int $packetId;

    public function getControlPacketType(): int
    {
        return ControlPacketType::MQTT_PUBREL;
    }

    public function buildPayload()
    {
        $this->addRawToPayLoad(
            chr(($this->packetId & 0xff00)>>8) . chr($this->packetId & 0xff)
        );
    }

    public function setPacketId(int $packetId)
    {
        $this->packetId = $packetId;
    }
}