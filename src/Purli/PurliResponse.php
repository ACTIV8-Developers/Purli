<?php
namespace Purli;

use Purli\Interfaces\ResponseInterface;

/**
 * Class PurliResponse
 *
 * @author <milos@caenazzo.com>
 */
class PurliResponse implements ResponseInterface
{
    /**
     * An associative array containing the response's headers
     *
     * @var array
    **/
    protected $headers = [];

    /**
     * The body of the response without the headers block
     *
     * @var string
     */
    protected $body = '';

    /**
     * Accepts the result of a request as a string
	 *
     * @param string $response
     */
    public function __construct($response)
    {
        // Headers regex
        $pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';
        
        // Extract headers from response
        preg_match_all($pattern, $response, $matches);
        $headersString = array_pop($matches[0]);
        $headers = explode("\r\n", str_replace("\r\n\r\n", '', $headersString));

        // Remove headers from the response body
        $this->body = str_replace($headersString, '', $response);
        
        // Extract the version and status from the first header
        $versionAndStatus = array_shift($headers);
        preg_match('#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $versionAndStatus, $matches);
        $this->headers['Http-Version'] = $matches[1];
        $this->headers['Status-Code'] = $matches[2];
        $this->headers['Status'] = $matches[2].' '.$matches[3];
        
        // Convert headers into an associative array
        foreach ($headers as $header) {
            preg_match('#(.*?)\:\s(.*)#', $header, $matches);
            $this->headers[$matches[1]] = $matches[2];
        }
    }
    
    /**
     * @return string
     */
    public function asText()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        if ($this->isJson()) {
            return json_decode($this->body, true);
        } else if ($this->isXml()) {
            return json_decode(json_encode(simplexml_load_string($this->body)), true);
        } else {
            return array($this->asText());
        }
    }

    /**
     * @return \stdClass
     */
    public function asObject($LIBXML_NOCDATA = false)
    {
        if ($this->isJson()) {
            return json_decode($this->body);
        } else if ($this->isXml()) {
            if (!$LIBXML_NOCDATA)
                return simplexml_load_string($this->body);
            else
                return simplexml_load_string($this->body, null, LIBXML_NOCDATA);
        } else {
            $class = new \stdClass();
            $class->body = $this->body;
            return $class;
        }
    }

    /**
     * @param string $key
     * @return string|array
     */
	public function headers($key = null)
	{
		if (isset($this->headers[$key])) {
			return $this->headers[$key];
		}
		return $this->headers;
	}

    /**
     * @return bool
     */
    public function isJson()
    {
        if (strpos($this->headers('Content-Type'), 'json') !== false) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isXml()
    {
        if (strpos($this->headers('Content-Type'), 'xml') !== false) {
            return true;
        }
        return false;
    }
}
