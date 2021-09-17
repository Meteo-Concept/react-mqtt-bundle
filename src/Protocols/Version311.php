<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle\Protocols;

use Generator;
use MeteoConcept\ReactMqttBundle\Packets\ConnectionAck;
use MeteoConcept\ReactMqttBundle\Packets\ControlPacket;
use MeteoConcept\ReactMqttBundle\Packets\ControlPacketType;
use MeteoConcept\ReactMqttBundle\Packets\PingResponse;
use MeteoConcept\ReactMqttBundle\Packets\Publish;
use MeteoConcept\ReactMqttBundle\Packets\PublishAck;
use MeteoConcept\ReactMqttBundle\Packets\PublishComplete;
use MeteoConcept\ReactMqttBundle\Packets\PublishReceived;
use MeteoConcept\ReactMqttBundle\Packets\SubscribeAck;
use MeteoConcept\ReactMqttBundle\Packets\UnsubscribeAck;

/**
 * Implements version 3.1.1 of the MQTT protocol, this class acts both as a
 * parser (to read packets from the server) and also as a packet factory
 * (to write packets to the server)
 * @package MeteoConcept\ReactMqttBundle\Protocols
 */
final class Version311 implements VersionInterface
{
    /**
     * @var string The incoming raw data from the server
     */
    private string $buffer;

    /**
     * @var int The current packet id, to build packets that need one
     */
    private $packetId;

    /**
     * Constructs an instance of the parser/packet factory
     */
    public function __construct()
    {
        $this->buffer = "";
        // Reduce risk of creating duplicate ids in sequential sessions
        $this->packetId = rand(1, 100) * 100;
    }

    /**
     * @inheritdoc
     */
    public function getProtocolIdentifierString(): string
    {
        return 'MQTT';
    }

    /**
     * @inheritdoc
     */
    public function getProtocolVersion(): int
    {
        return 0x04;
    }

    /**
     * @inheritdoc
     */
    public function getNextPacketId(): int
    {
        return ($this->packetId = ($this->packetId + 1) & 0xffff);
    }

    /**
     * @inheritdoc
     */
    public function getPacketIdPayload(int $packetId): string
    {
        return chr(($packetId & 0xff00)>>8) . chr($packetId & 0xff);
    }

    /**
     * @inheritdoc
     */
    public function pushData(string $data): void
    {
        $this->buffer .= $data;
    }

    /**
     * @inheritdoc
     */
    public function reset(): void
    {
        $this->buffer = "";
    }

    /**
     * @inheritdoc
     */
    public function extractPackets(): Generator
    {
        while (strlen($this->buffer) > 2) { // 2 = fixed header length
            $bytesRead = 1;
            $remainingLength = 0;
            $multiplier = 1;
            do {
                if ($bytesRead > 4) {
                    return false;
                }
                if (!isset($this->buffer[$bytesRead])) {
                    return false;
                }
                $byte = ord($this->buffer[$bytesRead]);
                $remainingLength += ($byte & 0x7f) * $multiplier;
                $isContinued = ($byte & 0x80);
                if ($isContinued) {
                    $multiplier *= 128;
                }
                $bytesRead++;
            } while ($isContinued);

            $packetLength = 2 + $remainingLength;
            if (strlen($this->buffer) < $packetLength) {
                // not enough data in the stream for now, retry when we receive more
                return false;
            }
            $nextPacketData = substr($this->buffer, 0, $packetLength);
            $this->buffer = substr($this->buffer, $packetLength);

            yield $this->constructPacket($bytesRead, $nextPacketData);
        }
    }

    /**
     * Parses a single packet from the server
     * @param int $offsetToPayload The variable number of bytes taken by the header
     * @param string $input The raw data string containing the packet to parse
     * @returns ControlPacket A parsed MQTT control packet
     * @throws VersionViolation If no valid packet could be parsed from the input string
     */
    private function constructPacket(int $offsetToPayload, string $input): ControlPacket
    {
        $controlPacketType = ord($input[0]);

        switch ($controlPacketType) {
            case ControlPacketType::MQTT_CONNACK:
                return ConnectionAck::parse($this, $input);
            case ControlPacketType::MQTT_PINGRESP:
                return PingResponse::parse($this, $input);
            case ControlPacketType::MQTT_SUBACK:
                return SubscribeAck::parse($this, $input);
            case ControlPacketType::MQTT_UNSUBACK:
                return UnsubscribeAck::parse($this, $input);
            case ControlPacketType::MQTT_PUBLISH:        // QoS - 0
            case ControlPacketType::MQTT_PUBLISH + 0x02: // QoS - 1
            case ControlPacketType::MQTT_PUBLISH + 0x04: // QoS - 2
                return Publish::parse($this, $input, $offsetToPayload);
            case ControlPacketType::MQTT_PUBACK:
                return PublishAck::parse($this, $input);
            case ControlPacketType::MQTT_PUBREC:
                return PublishReceived::parse($this, $input);
            case ControlPacketType::MQTT_PUBCOMP:
                return PublishComplete::parse($this, $input);
        }

        throw new VersionViolation('Unexpected packet type: ' . $controlPacketType);
    }
}