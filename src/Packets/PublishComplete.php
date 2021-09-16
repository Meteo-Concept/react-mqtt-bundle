<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle\Packets;


use MeteoConcept\ReactMqttBundle\Protocols\VersionInterface;

/**
 * The PUBCOMP Packet is the response to a PUBREL Packet.
 * It is the fourth and final packet of the QoS 2 protocol exchange.
 */
class PublishComplete extends ControlPacket
{
    const EVENT = 'PUBLISH_COMPLETE';

    /**
     * @var int
     */
    protected int $packetId;

    public function getControlPacketType(): int
    {
        return ControlPacketType::MQTT_PUBCOMP;
    }

    public static function parse(VersionInterface $version, $rawInput): PublishComplete
    {
        $packet = new static($version);

        $data = unpack('n', substr($rawInput, 2));
        $packet->setPacketId($data[1]);

        return $packet;
    }

    /**
     * @param $messageId
     */
    public function setPacketId($messageId)
    {
        $this->packetId = $messageId;
    }

    /**
     * @return int
     */
    public function getPacketId(): int
    {
        return $this->packetId;
    }
}