<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle\Packets;


use MeteoConcept\ReactMqttBundle\Protocols\VersionInterface;

/**
 * A PUBACK Packet is the response to a PUBLISH Packet with QoS level 1.
 */
class PublishAck extends ControlPacket
{
    const EVENT = 'PUBLISH_ASC';

    /**
     * @var int
     */
    protected int $packetId;

    public function getControlPacketType(): int
    {
        return ControlPacketType::MQTT_PUBACK;
    }

    public static function parse(VersionInterface $version, $rawInput): PublishAck
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