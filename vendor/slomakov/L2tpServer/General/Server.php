<?php

/**
 * Description of server
 *
 * @author "Sergei Lomakov <sergei@lomakov.net>"
 */

namespace L2tpServer\General;

use L2tpServer\Exceptions\ServerException,
    L2tpServer\Factory,
    Packfire\Logger\File as Logger;

class Server {
    //put your code here
	private $addr;
	private $port;
	private $socket;
	private $clients;
    protected $logger;

	function __construct($addr="0.0.0.0", $port=1701) {
        $this->logger = new Logger('server.log');
		$this->clients = array();
		$this->addr = $addr;
		$this->port = $port;
		# create socket:
		$this->socket = socket_create(AF_INET,SOCK_DGRAM, SOL_UDP);
		if ($this->socket < 0)
		{
			throw new ServerException("Can't create socket.");
		}

		return ;
	}

	function run() {
		if (socket_bind($this->socket, $this->addr, $this->port) == false)
		{
			throw new ServerException("Can't bind on address.");
		}
        $this->logger->info("Starting listen on the {$this->addr}:{$this->port}");
		while(1) {
			$buf = NULL;
			$ip = NULL;
			$port = NULL;
			$len = socket_recvfrom($this->socket, $buf, 65535, 0, $ip, $port);
			if ($len > 0) {
				$client_hash = md5($ip .':'. $port);
                /* @var $packet Packet */
				$packet = Factory::createPacket($buf);
				// Is it new client ?
				if (!isset($this->clients[$client_hash]) || is_object($this->clients[$client_hash])) {
                    $this->logger->info("New connection: $ip:$port");
					$this->clients[$client_hash] = new Client($ip, $port);
				}
                /* @var $this->clients[] L2tpServer\General\Client */
                /* @var $answer CtrlPacket */
				$answer = $this->clients[$client_hash]->processRequest($packet);
                var_dump($answer);
                $rawData = $answer->encode();
                //socket_sendto($this->socket, $rawData, 65535, 0, $ip, $port);
			} else {
                usleep(50000);
            }
            foreach($this->clients as $id => $client) {
                if ($client->getTimeout() < time()) {
                    $this->logger->info("Client: $id disconnected by timeout");
                    unset($this->clients[$id]);
                }
            }
		}
	}
}

?>
