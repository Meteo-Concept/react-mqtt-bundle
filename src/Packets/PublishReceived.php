<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle\Packets;


use MeteoConcept\ReactMqttBundle\Protocols\VersionInterface;

/**
 * A PUBREC Packet is the response to a PUBLISH Packet with QoS 2.
 * It is the second packet of the QoS 2 protocol exchange.
 */
class PublishReceived extends ControlPacket
{
    const EVENT = 'PUBLISH_RECEIVED';

    /**
     * @var int
     */
    protected int $packetId;

    public function getControlPacketType(): int
    {
        return ControlPacketType::MQTT_PUBREC;
    }

    public static function parse(VersionInterface $version, $rawInput): PublishReceived
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