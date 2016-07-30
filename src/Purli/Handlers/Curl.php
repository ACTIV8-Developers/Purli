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
	 * @var string
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
	 * @param $params
	 * @param $body
	 * @return void
	 */
	protected function setCurlOptions($params = null, $body = null) {
		// Request parameters
		if ($params) {
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $params);
		}

		if ($body) {
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
		}

		// Set some default CURL options
		curl_setopt($this->curl, CURLOPT_HEADER, true);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

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

		$this->fillRequest($method, $this->body);

		// Set headers/options
		$this->setCurlHeaders();
		$this->setCurlRequestMethod($method);
		$this->setCurlOptions($this->parameters, $this->body);

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
			$this->headers['Connection'] = 'application/x-www-form-urlencoded';
		}

		if (!isset($this->headers['Content-Length'])) {
			$this->headers['Content-Length'] = strlen($data);
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
