<?php
namespace Purli\Interfaces;

/**
 * Class ResponseInterface
 *
 * @author <milos@caenazzo.com>
 */
interface ResponseInterface 
{
    /**
     * @return string
     */
    public function asText();

    /**
     * @return array
     */
    public function asArray();

    /**
     * @return \stdClass
     */
    public function asObject();

    /**
     * @param string $key
     * @return string|array
     */
    public function headers($key = null);
}
