<?php

/**
 * Description of server
 *
 * @author "Sergei Lomakov <sergei@lomakov.net>"
 */

namespace L2tpServer\General;

use L2tpServer\AVPs\AssignedTunnelIdAVP;
use L2tpServer\Constants\AvpType;
use L2tpServer\Exceptions\ClientException;
use L2tpServer\Exceptions\CloseConnectionException;
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
        $ppp = new Ppp();
		while(1) {
			$ip = $port = $buf = NULL;
            $len = socket_recvfrom($this->socket, $buf, 65535, MSG_DONTWAIT, $ip, $port);
			if ($len > 0) {
				//$client_hash = md5($ip .':'. $port);
				$client_hash = md5($ip); // xl2tp sents PINGs from different ports
				// Is it new client ?
				if (!isset($this->clients[$client_hash]) || !is_object($this->clients[$client_hash])) {
                    $this->logger->info("New connection: $ip:$port");
					$this->clients[$client_hash] = new Client($ip, $port);
				}
                /* @var $packet Packet */
                $packet = Factory::createPacket($buf);
                /* @var $this->clients[] Client */
                /* @var $response CtrlPacket */
                try {
                    $response = $this->clients[$client_hash]->processRequest($packet);
                    if (!$response) {
                        continue;
                    }
                    $rawData = $response->encode();
                    $this->logger->info("Send response to: $ip:$port, with " . strlen($rawData) . ' bytes');
                    socket_sendto($this->socket, $rawData, strlen($rawData), 0, $ip, $port);
                } catch (CloseConnectionException $e) {
                    $this->logger->info("Closing connection for client: $ip:$port");
                    unset($this->clients[$client_hash]);
                } catch (ClientException $e) {
                    $this->logger->error($client_hash . ' error: ' . $e->getMessage());
                    $this->logger->error('Drop packet from ' . $client_hash);
                    unset($this->clients[$client_hash]);
                }
            }
            foreach($this->clients as $id => $client) {
                if ($client->getTimeout() < time()) {
                    // TODO: send keep alive packet
                    $this->logger->info("Client: $id disconnected by timeout");
                    unset($this->clients[$id]);
                }
                foreach($client->getTunnels() as $tunnelId => $tunnel) {
                    foreach($tunnel->getSessions() as $sessionId => $session) {
			unset($string);
			$string = fread($this->pipes[1], 8192);
			//$string = stream_get_contents($this->pipes[1]);
			if ($string) {
				//var_dump("Frame to response: '" . bin2hex($string). "'");
				$frame = '';
				$string = str_split($string);
				$previousByte = null;
				foreach($string as $i => $byte) {
					if($i == 0 && $byte != chr(0x7e)) {
						$frame.=chr(0x7e);	
					} 
					$frame.= $byte;
					if ($byte == chr(0x7e) && !is_null($previousByte) && $previousByte != chr(0x7d)) {
						break;
					}
					$previousByte = $byte;
				}
				$string = $frame;
				//var_dump("Server: '" . bin2hex($string). "'");
				$string = $ppp->parse($string);
				$responsePacket = new DataPacket();
				$responsePacket->setPayload($string);
			}

                        $this->logger->info("Client: $ip, Tunnel: $tunnelId session: $sessionId");
                    }
                }
            }
            usleep(100);
		}
	}
}

