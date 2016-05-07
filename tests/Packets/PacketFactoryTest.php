<?php
/**
 * Created by PhpStorm.
 * User: Sergei
 * Date: 16.02.14
 * Time: 17:33
 */

use L2tpServer\Factory,
    L2tpServer\General\CtrlPacket,
    L2tpServer\General\Packet as Packet;

class PacketFactoryTest extends PHPUnit_Framework_TestCase
{

    public function testCreateRawControlPacket()
    {
        $packet = $this->getPacket();
        return $packet->encode();
    }

    /**
     * @depends testCreateRawControlPacket
     */
    public function testParseCreatedControlPacket($binaryPacket)
    {
        $packet = new CtrlPacket($binaryPacket);
        $originalPacket = $this->getPacket();
        $originalPacket->encode(); // Hack! We need to calculate length property
        $this->assertEquals($originalPacket, $packet, "Packet before creation and after are mismatch!");
    }

    public function testImportControlPacket()
    {
        $rawData = file_get_contents(dirname(__FILE__) . '/1.raw');

        $packet = Factory::createPacket($rawData);
        $this->assertTrue($packet instanceof Packet, "Type mismatch. Factory should return Packet instance");
        $this->assertTrue($packet instanceof CtrlPacket, "Type mismatch. Factory should return CtrlPacket instance");
        $this->assertEquals($packet->length, strlen($rawData), "Packet lentgth mismatch error");
        //$this->markTestIncomplete("Please, add more check here!");
        return $packet;
    }

    /**
     * @depends testImportControlPacket
     */
    public function testEncodeImportedControlPacket(CtrlPacket $packet)
    {
        //$this->markTestIncomplete("Please, add more checks here!");
        $rawCustomData = $packet->encode();
        $this->assertEquals(strlen(file_get_contents(dirname(__FILE__) . '/1.raw')), strlen($rawCustomData), "Packets length mismatching");
        $new_packet = Factory::createPacket($rawCustomData);
        $this->assertEquals($packet, $new_packet, "Packets mistmaching!");
    }

    protected function getPacket()
    {
        $packet = CtrlPacket::create();
        $packet->setNs(1000);
        $packet->setNr(1000);
        return $packet;
    }
} 
