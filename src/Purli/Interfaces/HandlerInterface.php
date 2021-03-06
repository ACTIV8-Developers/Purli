<?php
namespace Purli\Interfaces;

/**
 * Class HandlerInterface
 *
 * @author <milos@caenazzo.com>
 */
interface HandlerInterface
{
    /**
     * @param $uri
     * @return self
     */
    public function get($uri);

    /**
     * @param $uri
     * @return self
     */
    public function post($uri);

    /**
     * @param $uri
     * @return self
     */
    public function put($uri);

    /**
     * @param $uri
     * @return self
     */
    public function patch($uri);

    /**
     * @param $uri
     * @return self
     */
    public function delete($uri);

    /**
     * @param $uri
     * @param $method
     * @return self
     */
    public function request($uri, $method);

    /**
     * @param array|string $params
     * @return self
     */
    public function setParams($params);

    /**
     * @param $key
     * @param $value
     * @return self
     */
    public function setHeader($key, $value);

    /**
     * @param $agent
     * @return self
     */
    public function setUserAgent($agent);

    /**
     * @param $timeout
     * @return self
     */
    public function setConnectionTimeout($timeout);

    /**
     * @param boolean $keepAlive
     */
    public function setKeepAlive($keepAlive);

    /**
     * @return ResponseInterface
     */
    public function response();

    /**
     * @return self
     */
    public function close();

    /**
     * @return mixed
     */
    public function getHandler();

    /**
     * @return mixed
     */
    public function getHandlerType();
}
