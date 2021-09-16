<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle\Protocols;


interface VersionInterface {
    /**
     * @return string
     */
    public function getProtocolIdentifierString(): string;

    /**
     * @return int
     */
    public function getProtocolVersion(): int;

    /**
     * @return int
     */
    public function getNextPacketId(): int;

    /**
     * @param int|null $packetId
     * @return string
     */
    public function getPacketIdPayload(int $packetId): string;

    public function pushData(string $data): void;

    public function extractPackets(): iterable;

    public function reset(): void;
}