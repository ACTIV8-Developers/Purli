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
     * Raw body to send request with
     *
     * @var string
     */
    protected $body = '';

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
     * @return void|static
     * @throws PurliException
     */
    public function request($uri, $method)
    {
        $parsedUri = parse_url($uri);

        $data = '';
        foreach($this->parameters as $key => $value) {
            $data .= ($data ? '&' : '') . urlencode($key) . '=' . urlencode($value);
        }

        if ($this->body) {
            $data .= $this->body;
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

        $this->socket = fsockopen($scheme . $parsedUri['host'], isset($parsedUri['port'])?$parsedUri['port']:$port, $errno, $errstr, $this->connectionTimeout);

        if (!$this->socket) {
            throw new PurliException(sprintf("Connection failed: %s, %s", $errno, $errstr));
        } else {
            $this->fillRequest($method, $data);

            $http  = $method . " " . $parsedUri['path'] . " HTTP/1.1\r\n";
            $http .= "Host: " . $parsedUri['host'] . "\r\n";

            foreach ($this->headers as $key => $value) {
                $http .= $key . ": " . $value . "\r\n";
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
            
            fwrite($this->socket, $http);

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
     * @param array $params
     * @return self
     */
    public function setParams(array $params)
    {
        $this->parameters = $params;
        return $this;
    }


    /**
     * @param $key
     * @param $value
     * @return self
     */
    public function setParam($key, $value)
    {
        $this->parameters[$key] = $value;
        return $this;
    }

    /**
     * @param string $body
     * @return self
     */
    public function setBody($body)
    {
        $this->body = $body;
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
     */
    public function setKeepAlive($keepAlive)
    {
        $this->keepAlive = $keepAlive;
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
