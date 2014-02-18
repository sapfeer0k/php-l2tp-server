<?php

namespace L2tpServer\General;

abstract class Packet {

    const TYPE_CONTROL = 128,
        TYPE_DATA = 0;

	protected $isLengthPresent;
	protected $isSequencePresent;
	protected $isOffsetPresent;
	protected $isPrioritized;

	protected $protoVersion = 2; // always must be 2
	protected $packetType;

	protected $length;
	protected $tunnelId;
	protected $sessionId;
	protected $Ns;
	protected $Nr;
	protected $error;

	public function __construct($rawPacket=false) {
		if ($rawPacket) {
			if (!$this->parse($rawPacket)) {
				throw new \Exception("Can't parse packet");
			}
		}
	}
	// Return packet properties encoded as raw string:
    public abstract function encode();


    /**
     * @param $name name of property to return
     * @return mixed return any property data
     * @throws Exception
     */
    public function __get($name) {
		if (method_exists($this, ($method = 'get'.ucfirst($name)))) {
			return $this->$method;
		} else {
			if (property_exists($this, $name)) {
				return $this->$name;
			} else {
				throw new Exception("You're trying to read property '$name' which doesn't exist");
			}
		}
	}

    /**
     * @return Packet instance with this class used as parent
     */
    abstract public static function create();

    /**
     * @param $packet
     * @return mixed
     */
    protected abstract function parse($packet);

    /**
     * @param $packet Packet
     * @return NULL
     */
    protected abstract function parseHeader($packet);

    /**
     * @return string - binary representation of the header
     */
    protected abstract function formatHeader($payloadSize);

}


?>