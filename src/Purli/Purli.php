<?php
namespace Purli;

use Purli\Handlers\Curl;
use Purli\Handlers\Socket;
use Purli\Interfaces\HandlerInterface;

/**
 * Class Purli
 */
class Purli
{
	/**
	 * Version
	 */
	const VERSION = '2.0.0';

	/**
	 * Use CURL handler
	 */
	const CURL = 1;

	/**
	 * Use Socket handler
	 */
	const SOCKET = 2;
	
	/**
	 * @var HandlerInterface
	 */ 
	protected $client = null;

	/**
	 * Class construct
	 * @param $use
	 */
	public function __construct($use = self::CURL)
	{
		if ($this->isCurlnstalled() && $use === self::CURL) {
			$this->client = new Curl();
		} else {
			$this->client = new Socket();
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
		return call_user_func_array([$this->client, $method], $args);
	}
	
	/**
	 * @return bool
	 */ 
	protected function isCurlnstalled()
	{
		return function_exists('curl_version');
	}
}
