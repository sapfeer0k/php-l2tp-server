<?php

/**
 * Description of server
 *
 * @author "Sergei Lomakov <sergei@lomakov.net>"
 */

namespace L2tpServer\General;

use L2tpServer\AVPs\AssignedTunnelIdAVP;
use L2tpServer\Constants\AvpType;
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
		if (socket_bind($this->socket, $this->addr, $this->port) == false) {
			throw new ServerException("Can't bind on address.");
		}
        if (is_file($this->logger->file())) {
            unlink($this->logger->file());
        }
        $this->logger->info("Starting listen on the {$this->addr}:{$this->port}");
        $i=1;
		while(1) {
			$buf = NULL;
			$ip = NULL;
			$port = NULL;
            $len = socket_recvfrom($this->socket, $buf, 65535, 0, $ip, $port);
			if ($len > 0) {
				$client_hash = md5($ip .':'. $port);
				// Is it new client ?
				if (!isset($this->clients[$client_hash]) || is_object($this->clients[$client_hash])) {
                    $this->logger->info("New connection: $ip:$port");
					$this->clients[$client_hash] = new Client($ip, $port);
				}
                /* @var $packet Packet */
                $packet = Factory::createPacket($buf);
                /* @var $this->clients[] Client */
                /* @var $response CtrlPacket */
				$response = $this->clients[$client_hash]->processRequest($packet);
                //file_put_contents('test_response_0.dat', $response->encode());
                //die;
                $rawData = $response->encode();
                $this->logger->info("Send response to: $ip:$port, with " . strlen($rawData) . ' bytes');
                socket_sendto($this->socket, $rawData, strlen($rawData), 0, $ip, $port);
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
