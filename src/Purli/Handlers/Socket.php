<?php
namespace Purli\Handlers;

use Purli\Interfaces\ResponseInterface;
use Purli\Purli;
use Purli\PurliException;
use Purli\PurliResponse;
use Purli\Interfaces\HandlerInterface;

/**
 * Class Socket
 *
 * @author <milos@caenazzo.com>
 */
class Socket implements HandlerInterface
{
    /**
     * URI string
     *
     * @var string
     */
    protected $uri = '';

    /**
     * Parameters to be sent along with request
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * An associative array of headers to send along with requests
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Cookies to be sent
     * @var array
     */
    protected $cookies = [];

    /**
     * Response
     *
     * @var PurliResponse|null
     */
    protected $response = null;

    /**
     * @var null
     */
    protected $socket = null;

    /**
     * @var int
     */
    protected $connectionTimeout = 5;

    /**
     * @var bool
     */
    protected $keepAlive = false;

    /**
     * Proxy IP address
     *
     * @var string
     */
    private $proxyIp = null;

    /**
     * Proxy port
     *
     * @var string|int
     */
    private $proxyPort = null;

    /**
     * Not waiting response
     *
     * @var bool
     */
    private $noWaitResponse = false;

    /**
     * @var string
     */
    private $lastRequest = '';

    /**
     * @param $uri
     * @return self
     */
    public function get($uri)
    {
        return $this->request($uri, "GET");
    }

    /**
     * @param $uri
     * @return self
     */
    public function post($uri)
    {
        return $this->request($uri, "POST");
    }

    /**
     * @param $uri
     * @return self
     */
    public function put($uri)
    {
        return $this->request($uri, "PUT");
    }

    /**
     * @param $uri
     * @return self
     */
    public function delete($uri)
    {
        return $this->request($uri, "DELETE");
    }

    /**
     * @param $uri
     * @param $method
     * @return self
     * @throws PurliException
     */
    public function request($uri, $method)
    {
        $parsedUri = parse_url($uri);

        $data = '';

        if (is_array($this->parameters)) {
            foreach ($this->parameters as $key => $value) {
                $data .= ($data ? '&' : '') . urlencode($key) . '=' . urlencode($value);
            }
        } else {
            $data = $this->parameters;
        }

        switch ($parsedUri['scheme']) {
            case 'https':
                $scheme = 'ssl://';
                $port = 443;
                break;
            case 'http':
            default:
                $scheme = '';
                $port = 80;
        }

        $this->socket = fsockopen(
            $this->proxyIp
                ? $this->proxyIp
                : $scheme . $parsedUri['host'],
            $this->proxyPort
                ? $this->proxyPort
                : (isset($parsedUri['port'])?$parsedUri['port']:$port),
            $errno,
            $errstr,
            $this->connectionTimeout
        );

        if (!$this->socket) {
            throw new PurliException(sprintf("Connection failed: %s, %s", $errno, $errstr));
        } else {
            $this->fillRequest($method, $data);

            // added scheme + host, needed when using proxy
            $http  = $method . " " .
                $parsedUri['scheme'] . "://" .
                $parsedUri['host'] .
                (isset($parsedUri['path'])?$parsedUri['path']:'') .
                (isset($parsedUri['query'])?"?".$parsedUri['query']:'') .
                " HTTP/1.1\r\n";
            $http .= "Host: " . $parsedUri['host'] . "\r\n";

            foreach ($this->headers as $key => $value) {
                $http .= $key . ":" . $value . "\r\n";
            }

            switch ($method) {
                case "POST":
                case "PUT":
                case "DELETE":
                    $http .= "\r\n";
                    $http .= $data;
                    break;
                case "GET":
                default:
                    $http .= "\r\n";
                    break;
            }

            // Write HTTP header and body to stream
            fwrite($this->socket, $http);

            // Remember last request
            $this->lastRequest = $http;

            // Return if no wait options is checked
            if ($this->noWaitResponse)
                return $this;

            // Gather response
            $response = "";
            while (!feof($this->socket)) {
                $response .= fgets($this->socket, 4096);
            }

            $this->response = new PurliResponse($response);
        }
        return $this;
    }

    /**
     * Add some common headers if not already present
     * @param $method
     * @param $data
     */
    protected function fillRequest($method, $data)
    {
        if (!$this->keepAlive && !isset($this->headers['Connection'])) {
            $this->headers['Connection'] = 'Close';
        }

        if (!isset($this->headers['Content-Type']) && in_array($method, ['POST', 'PUT', 'DELETE'])) {
            $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        if (!isset($this->headers['Content-Length']) && in_array($method, ['POST', 'PUT', 'DELETE'])) {
            $this->headers['Content-Length'] = strlen($data);
        }
    }

    /**
     * @param array|string $params
     * @return self
     */
    public function setParams($params)
    {
        $this->parameters = $params;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return self
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getInfo() {
        return $this->lastRequest;
    }

    /**
     * @param $agent
     * @return self
     */
    public function setUserAgent($agent)
    {
        $this->headers['User-Agent'] = $agent;
        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * @return self
     */
    public function close()
    {
        if ($this->socket) {
            fclose($this->socket);
        }
        return $this;
    }

    /**
     * @param $timeout
     * @return self
     */
    public function setConnectionTimeout($timeout)
    {
        $this->connectionTimeout = (int)$timeout;
        return $this;
    }

    /**
     * @param boolean $keepAlive
     * @return $this
     */
    public function setKeepAlive($keepAlive)
    {
        $this->keepAlive = $keepAlive;
        return $this;
    }

    /**
     * Sets proxy parameters
     *
     * @param string $ip
     * @param string|int $port
     * @return $this
     */
    public function setProxy($ip, $port) {
        $this->proxyIp = $ip;
        $this->proxyPort = $port;
        return $this;
    }

    /**
     * Not waiting for response
     *
     * @param bool $flag
     * @return $this
     */
    public function setNoWaitResponse($flag=true) {
        $this->noWaitResponse = $flag;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHandler()
    {
        return $this->socket;
    }

    /**
     * @return mixed
     */
    public function getHandlerType()
    {
        return Purli::SOCKET;
    }
}
