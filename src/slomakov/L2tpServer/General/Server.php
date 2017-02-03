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
use L2tpServer\PacketFactory;
use L2tpServer\Tools\TLogger;
use Packfire\Logger\File as Logger;

class Server
{
    use TLogger;

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
        $this->getLogger()->info("Starting listen on the {$this->addr}:{$this->port}");
        while (1) {
            if ($this->receive() && $this->deliver()) {
                usleep(10);
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
            $clientId = md5($ip); // xl2tp sents PINGs from different ports
            // Is it new client ?
            if (!isset($this->clients[$clientId]) || !is_object($this->clients[$clientId])) {
                $this->getLogger()->info("New connection: $ip:$port");
                $client = new Client($ip, $port);
                $client->setLogger($this->getLogger());
                $client->setSocket($this->socket);
                $this->clients[$clientId] = $client;
            }
            $packet = PacketFactory::parse($buf);
            /* @var $packet Packet */
            /* @var $response CtrlPacket */
            try {
                $this->getLogger()->info('Received packet: ' . (string)$packet);
                $response = $this->getClient($clientId)->processRequest($packet);
                if ($response) {
                    $this->getLogger()->info('Sent packet: ' . (string)$packet);
                    $rawData = $response->encode();
                    $this->getClient($clientId)->send($rawData);
                    $idle = false;
                }
            } catch (CloseConnectionException $e) {
                $this->getLogger()->info("Closing connection for client: $ip:$port");
                unset($this->clients[$clientId]);
            } catch (ClientException $e) {
                $this->getLogger()->error($clientId . ' error: ' . $e->getMessage());
                $this->getLogger()->error('Drop packet from ' . $clientId);
                unset($this->clients[$clientId]);
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
                $this->getLogger()->info("Client: $id disconnected by timeout");
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
                            $responsePacket = DataPacket::factory()->create($tunnel, $session, $string);
                            $client->send($responsePacket->encode());
                            $this->getLogger()->info('Sent packet: ' . (string)$responsePacket);
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
