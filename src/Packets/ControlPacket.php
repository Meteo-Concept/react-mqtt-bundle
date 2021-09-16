<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle\Packets;


use MeteoConcept\ReactMqttBundle\Protocols\VersionInterface;
use RuntimeException;

abstract class ControlPacket
{
    /**
     * @var VersionInterface
     */
    protected VersionInterface $version;

    /**
     * @var string
     */
    protected string $payload;

    /**
     * @var int The packet type identifier (as an integer)
     */
    protected int $identifier;

    public function __construct(VersionInterface $version)
    {
        $this->version = $version;
        $this->payload = '';
    }

    /**
     * Create MQTT header from command and payload
     *
     * @param int $command The packet type identifier value
     * @param string $additionalPayload
     * @return string Header to send
     */
    protected function createHeader(int $command, string $additionalPayload = ''): string
    {
        $payload = $this->payload;
        if ($additionalPayload) {
            $payload .= $additionalPayload;
        }

        return chr($command) . $this->encodeLength(strlen($payload));
    }

    /**
     * Encode length to bytes to send in stream
     *
     * @param integer $len
     * @return string
     */
    protected function encodeLength(int $len)
    {
        if ($len < 0 || $len >= 128 * 128 * 128 * 128) {
            // illegal length
            return false;
        }

        $output = '';

        do {
            $byte = $len & 0x7f;  // keep lowest 7 bits
            $len = $len >> 7;     // shift away lowest 7 bits
            if ($len > 0) {
                $byte = $byte | 0x80; // set high bit to indicate continuation
            }
            $output .= chr($byte);
        } while ($len > 0);

        return $output;
    }

    /**
     * Append payload data
     *
     * @param string $stringToAdd
     */
    public function addRawToPayLoad(string $stringToAdd)
    {
        $this->payload .= $stringToAdd;
    }

    /**
     * Add payload length as bytes to beginning of string and return
     *
     * @param string $payload
     * @return string
     */
    protected function createPayload(string $payload): string
    {
        $fullLength = strlen($payload);
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $retval = chr($fullLength >> 8) . chr($fullLength & 0xff) . $payload;

        return $retval;
    }

    protected function buildPayload()
    {
        throw new RuntimeException('You must overwrite buildPayload() for this packet type');
    }

    /**
     * @return int
     */
    abstract public function getControlPacketType(): int;

    /**
     * @return string
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    public function get(string $additionalPayload = ''): string
    {
        $this->buildPayload();
        $header = $this->createHeader($this->getControlPacketType(), $additionalPayload);

        $payload = $this->payload;
        if ($additionalPayload) {
            $payload .= $additionalPayload;
        }

        if (strlen($payload)) {
            return $header . $payload;
        }

        return $header;
    }
}