<?php

class CurlTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $purli = new \Purli\Purli();

        $purli
            ->get(SERVER_URL . '/get.php')
            ->close();

        $response = $purli->response();

        $this->assertEquals("foo", $response->asText());
    }

    public function testHttpsGet()
    {
        $purli = new \Purli\Purli();

        $purli
            ->get('https://google.com')
            ->setConnectionTimeout(5)
            ->close();

        $response = $purli->response();

        $code = (int)$response->headers("Status-Code");
        $this->assertTrue($code > 200 && $code < 400 );
    }

    public function testGetWithParams()
    {
        $param = 'test_param';

        $purli = new \Purli\Purli();

        $purli
            ->get(SERVER_URL . '/get.php?foo='.$param)
            ->close();

        $response = $purli->response();

        $this->assertEquals("foo" . $param, $response->asText());

        $purli = new \Purli\Purli();

        $purli
            ->get(SERVER_URL . '/get.php/?foo='.$param)
            ->close();

        $response = $purli->response();

        $this->assertEquals("foo" . $param, $response->asText());
    }

    public function testPost()
    {
        $param = 'bar';

        $purli = new \Purli\Purli();
        $purli
            ->setParams(['foo' => $param])
            ->post(SERVER_URL . '/post.php')
            ->close();

        $response = $purli->response();

        $this->assertEquals($param, $response->asText());
    }

    public function testPut()
    {
        $param = 'bar';

        $purli = new \Purli\Purli();
        $purli
            ->setParams(['foo' => $param])
            ->put(SERVER_URL . '/put.php')
            ->close();

        $response = $purli->response();

        $this->assertEquals("1", $response->asText());
    }

    public function testJson()
    {
        $param = 'test';

        $purli = new \Purli\Purli();
        $purli
            ->setHeader('Content-Type', 'application/json')
            ->setParams(json_encode(['foo' => $param]))
            ->post(SERVER_URL . '/json.php')
            ->close();

        $response = $purli->response();

        $this->assertEquals($param, $response->asArray()['foo']);
    }

    /**
     * @expectedException \Purli\PurliException
     */
    public function testException() {

        $purli = new \Purli\Purli();

        $purli
            ->get('uknown')
            ->close();
    }
}