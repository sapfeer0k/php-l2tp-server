<?php
/**
 * Created by PhpStorm.
 * User: Sergei
 * Date: 16.02.14
 * Time: 17:33
 */

use L2tpServer\Factory,
    L2tpServer\General\CtrlPacket,
    L2tpServer\General\InfoPacket,
    L2tpServer\General\Packet as Packet;

class PacketFactoryTest extends PHPUnit_Framework_TestCase
{
    /* @var $importedPacket CtrlPacket */
    protected $importedPacket = NULL;
    /* @var $createdPacket CtrlPacket */
    protected $createdPacket = NULL;

    public function testCreateRawControlPacket()
    {
        $this->createdPacket = CtrlPacket::create(Packet::TYPE_CONTROL, 0, 0, 0, 0);
    }

    /*
     * @depends testCreateRawControlPacket
     */
    public function testEncodeCreatedControlPacket()
    {

    }

    /*
     * @depends testEncodeCreatedControlPacket
     */
    public function testImportControlPacket()
    {
        $rawData = file_get_contents(dirname(__FILE__) . '/1.raw');
        $packet = Factory::createPacket($rawData);
        $this->importedPacket = $packet;
        $this->assertTrue($packet instanceof Packet, "Type mismatch. Factory should return Packet instance");
        $this->assertTrue($packet instanceof CtrlPacket, "Type mismatch. Factory should return CtrlPacket instance");

        //var_dump($packet);
        //die();
    }

    /*
     * @depends testImportControlPacket
     */
    public function testEncodeImportedControlPacket()
    {
        /*
        $rawCustomData = $this->importedPacket->encode();
        $rawData = file_get_contents(dirname(__FILE__) . '/1.raw');
        $this->assertEquals(md5($rawData), md5($rawCustomData), "Encoding process doesn't work well!");
        */
    }


} 