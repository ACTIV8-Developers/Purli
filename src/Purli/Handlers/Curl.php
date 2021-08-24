<?php
namespace Purli\Handlers;

use Purli\Purli;
use Purli\PurliException;
use Purli\PurliResponse;
use Purli\Interfaces\HandlerInterface;

/**
 * Class Curl
 *
 * @author <milos@caenazzo.com>
 */
class Curl implements HandlerInterface
{
    /**
     * Parameters to be sent along with request
     *
     * @var array|string
     */
    protected $parameters = null;

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
     * @var PurliResponse
     */
    protected $response = null;

    /**
     * An associative array of CURLOPT options to send along with requests
     *
     * @var array
     */
    protected $options = [];

    /**
     * Stores resource handle for the current CURL request
     *
     * @var resource
     */
    protected $curl = null;

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
     * Set request headers
     *
     * @return void
     */
    protected function setCurlHeaders() {
        $headers = [];

        foreach ($this->headers as $key => $value) {
            $headers[] = $key.': '.$value;
        }
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);

        $this->headers = [];
    }

    /**
     * Sets the CURLOPT options for the current curl
     *
     * @param $data
     * @return void
     */
    protected function setCurlOptions($data = null) {
        // Request parameters
        if ($data) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
        }

        // Set some default CURL options
        curl_setopt($this->curl, CURLOPT_HEADER, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLINFO_HEADER_OUT, true);

        // Set any custom CURL options
        foreach ($this->options as $option => $value) {
            curl_setopt($this->curl, $option, $value);
        }

        $this->options = [];
    }

    /**
     * Set the associated CURL options for a request method
     *
     * @param string $method
     * @return void
     */
    protected function setCurlRequestMethod($method)
    {
        switch (strtoupper($method)) {
            case 'HEAD':
                curl_setopt($this->curl, CURLOPT_NOBODY, true);
                break;
            case 'GET':
                curl_setopt($this->curl, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($this->curl, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
        }
    }

    /**
     * Set header
     *
     * @param string $key
     * @param string $value
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
    public function getInfo()
    {
        return curl_getinfo($this->curl);
    }

    /**
     * @param string $key
     * @param string $value
     * @return self
     */
    public function setCookie($key, $value)
    {
        $this->cookies[$key] = $value;
        $this->setOption(CURLOPT_COOKIE, str_replace('+', '%20', http_build_query($this->cookies, '', '; ')));
        return $this;
    }

    /**
     * @param string $cookieFile
     * @return self
     */
    public function setCookieFile($cookieFile)
    {
        $this->setOption(CURLOPT_COOKIEFILE, $cookieFile);
        return $this;
    }

    /**
     * @param $cookieJar
     * @return self
     */
    public function setCookieJar($cookieJar)
    {
        $this->setOption(CURLOPT_COOKIEJAR, $cookieJar);
        return $this;
    }

    /**
     * @param string $referrer
     * @return self
     */
    public function setReferrer($referrer)
    {
        $this->setOption(CURLOPT_REFERER, $referrer);
        return $this;
    }

    /**
     * @param int $seconds
     * @return self
     */
    public function setConnectionTimeout($seconds)
    {
        $this->setOption(CURLOPT_CONNECTTIMEOUT, $seconds);
        return $this;
    }

    /**
     * @param int $seconds
     * @return self
     */
    public function setTimeout($seconds)
    {
        $this->setOption(CURLOPT_TIMEOUT, $seconds);
        return $this;
    }

    /**
     * @param string $userAgent
     * @return self
     */
    public function setUserAgent($userAgent)
    {
        $this->setOption(CURLOPT_USERAGENT, $userAgent);
        return $this;
    }

    /**
     * Set cUrl option
     *
     * @param string $key
     * @param string $value
     * @return self
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
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
     * @param $uri
     * @return $this
     * @throws PurliException
     */
    public function get($uri)
    {
        $this->request($uri, 'GET');
        return $this;
    }

    /**
     * @param $uri
     * @return $this
     * @throws PurliException
     */
    public function post($uri)
    {
        $this->request($uri, 'POST');
        return $this;
    }

    /**
     * @param $uri
     * @return $this
     * @throws PurliException
     */
    public function put($uri)
    {
        $this->request($uri, 'PUT');
        return $this;
    }

    /**
     * @param $uri
     * @return $this
     * @throws PurliException
     */
    public function patch($uri)
    {
        $this->request($uri, 'PATCH');
        return $this;
    }

    /**
     * @param $uri
     * @return $this
     * @throws PurliException
     */
    public function delete($uri)
    {
        $this->request($uri, 'DELETE');
        return $this;
    }

    /**
     * @param string $uri
     * @param string $method
     * @return self
     * @throws PurliException
     */
    public function request($uri, $method)
    {
        // Init cUrl
        if (!$this->curl) {
            $this->curl = curl_init();
        }

        // Set uri
        curl_setopt($this->curl, CURLOPT_URL, $uri);

        // Set proxy
        if ($this->proxyIp)
            curl_setopt($this->curl, CURLOPT_PROXY, sprintf('%s:%s', $this->proxyIp, $this->proxyPort));

        if ($this->noWaitResponse)
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, false);

        $data = '';

        if (is_array($this->parameters) && $this->headers['Content-Type'] !== 'multipart/form-data') {
            foreach ($this->parameters as $key => $value) {
                $data .= ($data ? '&' : '') . urlencode($key) . '=' . urlencode($value);
            }
        } else {
            $data = $this->parameters;
        }

        $this->fillRequest($method, $data);

        // Set headers/options
        $this->setCurlHeaders();
        $this->setCurlRequestMethod($method);
        $this->setCurlOptions($data);

        // Execute cUrl and get response
        $response = curl_exec($this->curl);

        // Check response
        if ($response === false) {
            throw new PurliException(curl_errno($this->curl) . ' - ' . curl_error($this->curl));
        } else {
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
    }

    /**
     * Close stream
     *
     * @return self
     */
    public function close()
    {
        if ($this->curl) {
            curl_close($this->curl);
            $this->curl = null;
        }
        return $this;
    }

    /**
     * @return PurliResponse
     */
    public function response()
    {
        return $this->response;
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
        return $this->curl;
    }

    /**
     * @return mixed
     */
    public function getHandlerType()
    {
        return Purli::CURL;
    }
}
