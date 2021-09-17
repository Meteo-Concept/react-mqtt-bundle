<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle\Protocols;

use MeteoConcept\ReactMqttBundle\Packets\ControlPacket;

/**
 * Describes an implementation of the MQTT protocol
 *
 * Objects implementing this interface are usable both as decoders (to parse
 * packets from the server) and encoders (to build packets to send to the
 * server).
 * @package MeteoConcept\ReactMqttBundle\Protocols
 */
interface VersionInterface {
    /**
     * Returns a specific string identifying the protocol, to build or parse packets
     * @return string The 4-byte string "MQTT", which is found in CONNECT packets
     */
    public function getProtocolIdentifierString(): string;

    /**
     * Returns the protocol version byte, to build or parse packets
     * @return int The protocol version number: 4
     */
    public function getProtocolVersion(): int;

    /**
     * Returns the protocol version byte, to build or parse packets
     * @return int The protocol version number: 4
     */
    public function getNextPacketId(): int;

    /**
     * Extracts the packet id as a big-endian string of two bytes
     * @param int $packetId The packet id as an integer (two bytes, little-endian on x86 platform)
     * @return string The packet id as a binary string of two bytes
     */
    public function getPacketIdPayload(int $packetId): string;

    /**
     * Generates a valid random client id for the user
     * @return string A random client id
     */
    public function generateClientId(): string;

    /**
     * Add data to the incoming buffer
     * @param string $data The raw data received from the MQTT server
     */
    public function pushData(string $data): void;

    /**
     * Resets the parser for use on a new connection
     */
    public function reset(): void;

    /**
     * Extracts all packets from the buffer, parses them and returns them one at a time
     * @return iterable An iterable sequence of packets (instances of subclasses of
     * {@link ControlPacket}), parsed and returned one by one
     * @throws VersionViolation If no valid packet could be parsed from the buffer
     */
    public function extractPackets(): iterable;
}