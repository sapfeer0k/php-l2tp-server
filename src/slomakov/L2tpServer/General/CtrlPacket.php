<?php

namespace L2tpServer\General;

// Control connection managment
// 0 is reserved
define('MT_SCCRQ', 1);
define('MT_SCCRP', 2);
define('MT_SCCCN', 3);
define('MT_STOP_CCN', 4);
// 5 is reserved
define('MT_HELLO', 6);

// Call managment:
define('MT_OCRQ', 7);
define('MT_OCRP', 8);
define('MT_OCCN', 9);
define('MT_ICRQ', 10);
define('MT_ICRP', 11);
define('MT_ICCN', 12);
// 13 is reserved
define('MT_CDN', 14);
// Error Reporting
define('MT_WEN', 15);
// PPP Session Control
define('MT_SLI', 16);

use L2tpServer\AVPs\AVPFactory;
use L2tpServer\Constants\AvpType;
use L2tpServer\Exceptions\TunnelException;
use L2tpServer\Exceptions\IgnoreAVPException;
use L2tpServer\AVPs\BaseAVP;
use L2tpServer\Tools\TLogger;
use Packfire\Logger\File as Logger;

class CtrlPacket extends Packet
{
    use TLogger;

    protected $avps = array();

    protected function __construct()
    {
        parent::__construct();
        $this->packetType = self::TYPE_CONTROL;
        $this->isLengthPresent = self::TRUE;
        $this->isSequencePresent = self::TRUE;
        $this->isOffsetPresent = self::FALSE;
        $this->isPrioritized = self::FALSE;
        $this->numberSent = 0;
        $this->numberReceived = 0;
        $this->tunnelId = 0;
        $this->sessionId = 0;
        return true;
    }

    public function addAVP(BaseAVP $avp)
    {
        array_push($this->avps, $avp);
        return true;
    }

    public function parse($rawData)
    {
        $this->parseHeader($rawData);
        // Further we'll work with $packet_data property:
        $payload = substr($rawData, 12);
        if (strlen($payload)) {
            $this->parseAVPs($payload);
        }
        return $this;
    }

    protected function parseAVPs($payload)
    {
        // let's parse packet's AVPs:
        while (strlen($payload)) {
            // Get AVPs length:
            list( , $avp_bytes) = unpack('n', $payload[0].$payload[1]);
            $avp_len = $avp_bytes & 1023;
            $avp_raw_data = substr($payload, 0, $avp_len);
            try {
                $this->avps[] = AVPFactory::create(array('avp_raw_data' => $avp_raw_data));
            } catch (IgnoreAVPException $e) {
                trigger_error($e->getMessage());
            }
            $payload = substr($payload, $avp_len);

            unset($avp_len, $avp_bytes);
        }
        if ($this->avps[0]->getType() != AvpType::MESSAGE_TYPE_AVP) {
            throw new TunnelException("Message type AVP not found in the packet");
            //$this->message_type = $this->avps[0]->value;
        }
    }

    // Return packet properties encoded as raw string:
    public function encode()
    {
        $packetData = '';
        foreach ($this->avps as $avp) { // encode AVP
            /* @var $avp BaseAVP */
            $packetData .= $avp->encode();
        }
        $header = $this->encodeHeader(strlen($packetData)); // encode header
        $this->length = strlen($header . $packetData);
        return $header . $packetData;
    }

    public function setNumberSent($numberSent)
    {
        if (!is_numeric($numberSent) && $numberSent < 0) {
            throw new \Exception("Nr must be greater or euqual than 0");
        }
        $this->numberSent = $numberSent % 65536;
    }

    public function setNumberReceived($nR)
    {
        if (!is_numeric($nR) && $nR < 0) {
            throw new \Exception("Nr must be greater or euqual than 0");
        }
        $this->numberReceived = $nR % 65536;
    }

    public static function create()
    {
        return new self();
    }

    /**
     * @param $type
     * @return BaseAVP
     */
    public function getAVP($type)
    {
        foreach ($this->avps as $avp) {
            /* @var $avp BaseAVP */
            if ($avp->getType() == $type) {
                return $avp;
            }
        }
        return null;
    }

    public function getAvpCount()
    {
        return count($this->avps);
    }

    /**
     * @return BaseAVP[]
     */
    public function getAvps()
    {
        // DEBUG METHOD ONLY
        return $this->avps;
    }

    public function __toString()
    {
        $vars = get_object_vars($this);
        unset($vars['avps']);
        foreach($this->getAvps() as $avp) {
            $vars['avps'][] = (string)$avp;
        }
        return (new \ReflectionClass($this))->getShortName() . " " . json_encode($vars);
    }
}
