<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle\Packets;


use MeteoConcept\ReactMqttBundle\Protocols\VersionInterface;

/**
 * The UNSUBACK Packet is sent by the Server to the Client to confirm
 * receipt of an UNSUBSCRIBE Packet.
 */
class UnsubscribeAck extends ControlPacket
{
    const EVENT = 'UNSUBSCRIBE_ACK';

    /**
     * @var int
     */
    protected int $packetId;

    public function getControlPacketType(): int
    {
        return ControlPacketType::MQTT_UNSUBACK;
    }

    /**
     * @param VersionInterface $version
     * @param string $rawInput
     * @return UnsubscribeAck|ControlPacket
     */
    public static function parse(VersionInterface $version, string $rawInput)
    {
        $packet = new static($version);

        // if necessary: $length = ord($rawInput[1]);
        $message = substr($rawInput, 2);
        $data = unpack("n*", $message);
        $packet->setPacketId($data[1]);

        return $packet;
    }

    public function setPacketId(int $packetId)
    {
        $this->packetId = $packetId;
    }

    public function getPacketId(): int
    {
        return $this->packetId;
    }
}