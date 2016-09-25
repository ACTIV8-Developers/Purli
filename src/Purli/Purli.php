<?php
namespace Purli;

use Purli\Handlers\Curl;
use Purli\Handlers\Socket;
use Purli\Interfaces\HandlerInterface;

/**
 * Class Purli
 * 
 * @method get($uri)
 * @method post($uri)
 * @method put($uri)
 * @method delete($uri)
 * @method request($uri, $method)
 * @method setParams(array $params)
 * @method setParam($key, $value)
 * @method setBody($body)
 * @method setHeader($key, $value)
 * @method setUserAgent($agent)
 * @method setConnectionTimeout($timeout)
 * @method setKeepAlive($keepAlive)
 * @method response()
 * @method close()
 * @method getHandler()
 * @method getHandlerType()
 */
class Purli
{
	/**
	 * Version
	 */
	const VERSION = '1.0.0rc';

	/**
	 * Use CURL handler
	 */
	const CURL = 1;

	/**
	 * Use Socket handler
	 */
	const SOCKET = 2;

	/**
	 * @var $handler HandlerInterface
	 */ 
	protected $handler = null;

	/**
	 * Class construct
	 * @param $use
	 */
	public function __construct($use = self::CURL)
	{
		if ($this->isCurlInstalled() && $use === self::CURL) {
			$this->handler = new Curl();
		} else {
			$this->handler = new Socket();
		}
	}

	/**
	 * Proxy all method calls to handler
	 * @param string $method
	 * @param $args
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		return call_user_func_array([$this->handler, $method], $args);
	}
	
	/**
	 * @return bool
	 */ 
	protected function isCurlInstalled()
	{
		return function_exists('curl_version');
	}
}
