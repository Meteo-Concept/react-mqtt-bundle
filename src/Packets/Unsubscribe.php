<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle\Packets;


/**
 * An UNSUBSCRIBE Packet is sent by the Client to the Server, to
 * unsubscribe from topics.
 */
class Unsubscribe extends ControlPacket
{
    const EVENT = 'UNSUBSCRIBE';

    /**
     * @var int
     */
    protected int $packetId;

    /**
     * @var string
     */
    protected string $topic;

    public function getControlPacketType(): int
    {
        return ControlPacketType::MQTT_UNSUBSCRIBE + 0x02;
    }

    public function buildPayload()
    {
        $this->packetId = $this->version->getNextPacketId();
        $this->addRawToPayLoad(
            $this->version->getPacketIdPayload($this->packetId)
        );

        // Topic
        $this->addRawToPayLoad(
            $this->createLengthEncodedString($this->topic)
        );

        return $this->payload;
    }

    /**
     * @param string $topic
     */
    public function removeSubscription(string $topic)
    {
        $this->topic = $topic;
    }

    /**
     * @return string
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * @return int
     */
    public function getPacketId(): int
    {
        return $this->packetId;
    }
}