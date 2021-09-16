<?php

declare(strict_types=1);

namespace MeteoConcept\ReactMqttBundle\Packets;

/**
 * An enumeration for the various types of packets described in protocol MQTT 3.1.1
 * @package MeteoConcept\ReactMqttBundle\Packets
 */
class ControlPacketType
{
    // MQTT control packet types (here left shifted 4 bits)
    /** @var int Client request to connect to the server */
    const MQTT_CONNECT     = 0x10;
    /** @var int Connection acknowledgement */
    const MQTT_CONNACK     = 0x20;
    /** @var int Message publication */
    const MQTT_PUBLISH     = 0x30;
    /** @var int Publication acknowledgement */
    const MQTT_PUBACK      = 0x40;
    /** @var int Publication received (assurance of delivery part 1) */
    const MQTT_PUBREC      = 0x50;
    /** @var int Publication release (assurance of delivery part 2) */
    const MQTT_PUBREL      = 0x62;
    /** @var int Publication complete (assurance of delivery part 3) */
    const MQTT_PUBCOMP     = 0x70;
    /** @var int Client subscription request */
    const MQTT_SUBSCRIBE   = 0x80;
    /** @var int Client subscription acknowledgement */
    const MQTT_SUBACK      = 0x90;
    /** @var int Client Unsubscription request */
    const MQTT_UNSUBSCRIBE = 0xa0;
    /** @var int Client unsubscription acknowledgement */
    const MQTT_UNSUBACK    = 0xb0;
    /** @var int Ping/keep-alive request */
    const MQTT_PINGREQ     = 0xc0;
    /** @var int Ping/keep-alive response */
    const MQTT_PINGRESP    = 0xd0;
    /** @var int Client graceful disconnection */
    const MQTT_DISCONNECT  = 0xe0;

    /** @var int Filler value */
    const MOST_SIGNIFICANT_BYTE = 0x00;
}