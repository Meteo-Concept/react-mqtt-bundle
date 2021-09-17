<?php


use MeteoConcept\ReactMqttBundle\Packets\Connect;
use MeteoConcept\ReactMqttBundle\Protocols\VersionInterface;
use PHPUnit\Framework\TestCase;

class ConnectTest extends TestCase
{
    private VersionInterface $version;

    public function setUp(): void
    {
       $this->version = $this->createMock(VersionInterface::class);

       $this->version->expects($this->any())
           ->method('getProtocolIdentifierString')
           ->willReturn("MQTT");

       $this->version->expects($this->any())
           ->method('getProtocolVersion')
           ->willReturn(0x04);
    }

    public function test_The_packet_construction_works_with_all_options()
    {
        $this->version->expects($this->never())
            ->method('generateClientId');

        $packet = new Connect(
            $this->version,
            "username",
            "password",
            "clientId",
            true,
            [
                "active" => true,
                "message" => "will_message",
                "topic" => "will_topic",
                "qos" => 1,
                "retain" => "will_retain",
            ],
            42
        );

        $serialized = $packet->buildPacket();

        $this->assertEquals(
            pack("CC", 0x10, 66), // fixed header
            substr($serialized, 0, 2)
        );

        // variable header 1: protocol name
        $this->assertEquals(
            pack("CCC4", 0x00, 0x04, ord('M'), ord('Q'), ord('T'), ord('T')),
            substr($serialized, 2, 6)
        );

         // variable header 2: protocol level
        $this->assertEquals(
            pack("C", 0x04),
            substr($serialized, 8, 1)
        );

         // variable header 3: connect flags
        $this->assertEquals(
            pack("C", 0xee),
            substr($serialized, 9, 1)
        );

        // variable header 4: keep alive
        $this->assertEquals(
            pack("n", 42),
            substr($serialized, 10, 2)
        );

        // payload, field 1: client id
        $this->assertEquals(
            pack("na8", 8, "clientId"),
            substr($serialized, 12, 10)
        );

        // payload, field 2: will topic
        $this->assertEquals(
            pack("na10", 10, "will_topic"),
            substr($serialized, 22, 12)
        );

        // payload, field 3: will message
        $this->assertEquals(
            pack("na12", 12, "will_message"),
            substr($serialized, 34, 14)
        );

        // payload, field 4: username
        $this->assertEquals(
            pack("na8", 8, "username"),
            substr($serialized, 48, 10)
        );

        // payload, field 5: password
        $this->assertEquals(
            pack("na8", 8, "password"),
            substr($serialized, 58, 10)
        );
    }

    public function test_The_packet_construction_works_with_will()
    {
        $this->version->expects($this->never())
            ->method('generateClientId');

        $packet = new Connect(
            $this->version,
            null,
            null,
            "clientId",
            true,
            [
                "active" => true,
                "message" => "will_message",
                "topic" => "will_topic",
                "qos" => 1,
                "retain" => "will_retain",
            ],
            0
        );

        $serialized = $packet->buildPacket();

        $this->assertEquals(
            pack("CC", 0x10, 46), // fixed header
            substr($serialized, 0, 2)
        );

        // variable header 1: protocol name
        $this->assertEquals(
            pack("CCC4", 0x00, 0x04, ord('M'), ord('Q'), ord('T'), ord('T')),
            substr($serialized, 2, 6)
        );

        // variable header 2: protocol level
        $this->assertEquals(
            pack("C", 0x04),
            substr($serialized, 8, 1)
        );

        // variable header 3: connect flags
        $this->assertEquals(
            pack("C", 0x2e),
            substr($serialized, 9, 1)
        );

        // variable header 4: keep alive
        $this->assertEquals(
            pack("n", 0),
            substr($serialized, 10, 2)
        );

        // payload, field 1: client id
        $this->assertEquals(
            pack("na8", 8, "clientId"),
            substr($serialized, 12, 10)
        );

        // payload, field 2: will topic
        $this->assertEquals(
            pack("na10", 10, "will_topic"),
            substr($serialized, 22, 12)
        );

        // payload, field 3: will message
        $this->assertEquals(
            pack("na12", 12, "will_message"),
            substr($serialized, 34, 14)
        );
    }

    public function test_The_packet_construction_works_with_keepalive()
    {
        $this->version->expects($this->never())
            ->method('generateClientId');

        $packet = new Connect(
            $this->version,
            null,
            null,
            "clientId",
            false,
            [
                "active" => false,
            ],
            42
        );

        $serialized = $packet->buildPacket();

        $this->assertEquals(
            pack("CC", 0x10, 20), // fixed header
            substr($serialized, 0, 2)
        );

        // variable header 1: protocol name
        $this->assertEquals(
            pack("CCC4", 0x00, 0x04, ord('M'), ord('Q'), ord('T'), ord('T')),
            substr($serialized, 2, 6)
        );

        // variable header 2: protocol level
        $this->assertEquals(
            pack("C", 0x04),
            substr($serialized, 8, 1)
        );

        // variable header 3: connect flags
        $this->assertEquals(
            pack("C", 0x00),
            substr($serialized, 9, 1)
        );

        // variable header 4: keep alive
        $this->assertEquals(
            pack("n", 42),
            substr($serialized, 10, 2)
        );

        // payload, field 1: client id
        $this->assertEquals(
            pack("na8", 8, "clientId"),
            substr($serialized, 12, 10)
        );
    }

    public function test_The_packet_construction_works_with_cleansession()
    {
        $this->version->expects($this->never())
            ->method('generateClientId');

        $packet = new Connect(
            $this->version,
            null,
            null,
            "clientId",
            true,
            [
                "active" => false,
            ],
            0
        );

        $serialized = $packet->buildPacket();

        $this->assertEquals(
            pack("CC", 0x10, 20), // fixed header
            substr($serialized, 0, 2)
        );

        // variable header 1: protocol name
        $this->assertEquals(
            pack("CCC4", 0x00, 0x04, ord('M'), ord('Q'), ord('T'), ord('T')),
            substr($serialized, 2, 6)
        );

        // variable header 2: protocol level
        $this->assertEquals(
            pack("C", 0x04),
            substr($serialized, 8, 1)
        );

        // variable header 3: connect flags
        $this->assertEquals(
            pack("C", 0x02),
            substr($serialized, 9, 1)
        );

        // variable header 4: keep alive
        $this->assertEquals(
            pack("n", 0),
            substr($serialized, 10, 2)
        );

        // payload, field 1: client id
        $this->assertEquals(
            pack("na8", 8, "clientId"),
            substr($serialized, 12, 10)
        );
    }

    public function test_The_packet_construction_works_with_random_client_id()
    {
        $this->version->expects($this->once())
            ->method('generateClientId')
            ->willReturn("clientId");

        $packet = new Connect(
            $this->version,
            null,
            null,
            null,
            false,
            [
                "active" => false,
            ],
            0
        );

        $serialized = $packet->buildPacket();

        $this->assertEquals(
            pack("CC", 0x10, 20), // fixed header
            substr($serialized, 0, 2)
        );

        // variable header 1: protocol name
        $this->assertEquals(
            pack("CCC4", 0x00, 0x04, ord('M'), ord('Q'), ord('T'), ord('T')),
            substr($serialized, 2, 6)
        );

        // variable header 2: protocol level
        $this->assertEquals(
            pack("C", 0x04),
            substr($serialized, 8, 1)
        );

        // variable header 3: connect flags
        // cleanSession is forced to 1
        $this->assertEquals(
            pack("C", 0x02),
            substr($serialized, 9, 1)
        );

        // variable header 4: keep alive
        $this->assertEquals(
            pack("n", 0),
            substr($serialized, 10, 2)
        );

        // payload, field 1: client id
        $this->assertEquals(
            pack("na8", 8, "clientId"),
            substr($serialized, 12, 10)
        );
    }
}
