<?php

/**
 * Description of server
 *
 * @author "Sergei Lomakov <sergei@lomakov.net>"
 */

namespace L2tpServer\General;

use L2tpServer\Exceptions\ClientException;
use L2tpServer\Exceptions\CloseConnectionException;
use L2tpServer\Exceptions\ServerException;
use L2tpServer\Factory;
use Packfire\Logger\File as Logger;

class Server
{
    protected $logger;
    protected $frameParser;
    private $addr;
    private $port;
    private $socket;
    private $clients;

    /**
     * Server constructor.
     * @param string $addr
     * @param int $port
     */
    public function __construct($addr = "0.0.0.0", $port = 1701)
    {
        $this->logger = new Logger('server.log');
        $this->clients = array();
        $this->addr = $addr;
        $this->port = $port;
        # create socket:
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($this->socket < 0) {
            throw new ServerException("Can't create socket.");
        }
        return;
    }

    /**
     * @throws ServerException
     */
    public function run()
    {
        if (socket_bind($this->socket, $this->addr, $this->port) == false) {
            throw new ServerException("Can't bind on address.");
        }
        if (is_file($this->logger->file())) {
            unlink($this->logger->file());
        }
        $this->logger->info("Starting listen on the {$this->addr}:{$this->port}");
        while (1) {
            if ($this->receive() && $this->deliver()) {
                usleep(1000);
            }
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function receive()
    {
        $idle = true;
        $ip = $port = $buf = null;
        $len = socket_recvfrom($this->socket, $buf, 65535, MSG_DONTWAIT, $ip, $port);
        if ($len > 0) {
            $client_hash = md5($ip); // xl2tp sents PINGs from different ports
            // Is it new client ?
            if (!isset($this->clients[$client_hash]) || !is_object($this->clients[$client_hash])) {
                $this->logger->info("New connection: $ip:$port");
                $client = new Client($ip, $port);
                $client->setSocket($this->socket);
                $this->clients[$client_hash] = $client;
            }
            //$this->logger->info("Got data. Client: {$ip}, data: " . bin2hex($buf));
            $packet = Factory::createPacket($buf);
            /* @var $packet Packet */
            /* @var $response CtrlPacket */
            try {
                $response = $this->getClient($client_hash)->processRequest($packet);
                if ($response) {
                    $rawData = $response->encode();
                    $this->getClient($client_hash)->send($rawData);
                    $idle = false;
                }
            } catch (CloseConnectionException $e) {
                $this->logger->info("Closing connection for client: $ip:$port");
                unset($this->clients[$client_hash]);
            } catch (ClientException $e) {
                $this->logger->error($client_hash . ' error: ' . $e->getMessage());
                $this->logger->error('Drop packet from ' . $client_hash);
                unset($this->clients[$client_hash]);
            }
        }
        return $idle;
    }

    /**
     * @param $hash
     * @return Client
     */
    protected function getClient($hash)
    {
        // Todo: check existance!
        return $this->clients[$hash];
    }

    /**
     * @return bool
     */
    protected function deliver()
    {
        $idle = true;
        foreach ($this->clients as $id => $client) {
            if ($client->getTimeout() < time()) {
                /* @var $client Client */
                // TODO: send keep alive packet
                $this->logger->info("Client: $id disconnected by timeout");
                unset($this->clients[$id]);
            }
            foreach ($client->getTunnels() as $tunnelId => $tunnel) {
                foreach ($tunnel->getSessions() as $sessionId => $session) {
                    unset($string);
                    if (!$session->getOutputPipe()) {
                        continue;
                    }
                    $string = stream_get_contents($session->getOutputPipe());
                    if ($string) {
                        $frames = $this->getFrameParser()->split($string);
                        foreach ($frames as $frame) {
                            $string = $this->getFrameParser()->decode($frame);
                            $responsePacket = new DataPacket();
                            $responsePacket->setTunnelId($tunnel->getId());
                            $responsePacket->setSessionId($session->getId());
                            $responsePacket->setPayload($string);
                            $client->send($responsePacket->encode());
                            $idle = false;
                        }
                    }
                }
            }
        }
        return $idle;
    }

    /**
     * @return PppFrameParser
     */
    protected function getFrameParser()
    {
        if (!isset($this->frameParser) || !$this->frameParser instanceof PppFrameParser) {
            $this->frameParser = new PppFrameParser();
        }
        return $this->frameParser;
    }
}
