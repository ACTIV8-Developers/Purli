<?php

class SocketTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $purli = new \Purli\Purli(\Purli\Purli::SOCKET);

        $purli
            ->get(SERVER_URL . '/get.php')
            ->close();

        $response = $purli->response();

        $this->assertEquals("foo", $response->asText());
    }

    public function testHttpsGet()
    {
        $purli = new \Purli\Purli(\Purli\Purli::SOCKET);

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

        $purli = new \Purli\Purli(\Purli\Purli::SOCKET);

        $purli
            ->get(SERVER_URL . '/get.php?foo='.$param)
            ->close();

        $response = $purli->response();

        $this->assertEquals("foo" . $param, $response->asText());

        $purli = new \Purli\Purli(\Purli\Purli::SOCKET);

        $purli
            ->get(SERVER_URL . '/get.php/?foo='.$param)
            ->close();

        $response = $purli->response();

        $this->assertEquals("foo" . $param, $response->asText());
    }

    public function testPost()
    {
        $param = 'bar';

        $purli = new \Purli\Purli(\Purli\Purli::SOCKET);
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

        $purli = new \Purli\Purli(\Purli\Purli::SOCKET);
        $purli
            ->setParams(['foo' => $param])
            ->put(SERVER_URL . '/put.php')
            ->close();

        $response = $purli->response();

        $this->assertEquals("1", $response->asText());
    }

    public function testPatch()
    {
        $param = 'bar';

        $purli = new \Purli\Purli(\Purli\Purli::SOCKET);
        $purli
            ->setParams(['foo' => $param])
            ->put(SERVER_URL . '/patch.php')
            ->close();

        $response = $purli->response();

        $this->assertEquals("1", $response->asText());
    }

    public function testDelete()
    {
        $param = 'bar';

        $purli = new \Purli\Purli(\Purli\Purli::SOCKET);
        $purli
            ->setParams(['foo' => $param])
            ->delete(SERVER_URL . '/delete.php')
            ->close();

        $response = $purli->response();

        $this->assertEquals("1", $response->asText());
    }

    public function testGetJson()
    {
        $purli = new \Purli\Purli(\Purli\Purli::SOCKET);
        $purli
            ->get(SERVER_URL . '/json.php')
            ->close();

        $response = $purli->response();

        $this->assertTrue($response->isJson());

        $this->assertEquals('', $response->asArray()['foo']);
    }

    public function testGetXml()
    {
        $purli = new \Purli\Purli(\Purli\Purli::SOCKET);
        $purli
            ->get(SERVER_URL . '/xml.php')
            ->close();

        $response = $purli->response();

        $this->assertTrue($response->isXml());

        $this->assertEquals('foo', $response->asObject()->user);
    }

    public function testPostJson()
    {
        $param = 'test';

        $purli = new \Purli\Purli(\Purli\Purli::SOCKET);
        $purli
            ->setHeader('Content-Type', 'application/json')
            ->setParams(json_encode(['foo' => $param]))
            ->post(SERVER_URL . '/json.php')
            ->close();

        $response = $purli->response();

        $this->assertEquals($param, $response->asArray()['foo']);
    }

    public function testPostXml()
    {
        $param = 'foo';

        $purli = new \Purli\Purli(\Purli\Purli::SOCKET);
        $purli
            ->setHeader('Content-Type', 'text/xml')
            ->setParams('<root><user>foo</user><pass>bar</pass></root>')
            ->post(SERVER_URL . '/xml.php')
            ->close();

        $response = $purli->response();

        $this->assertEquals($param, $response->asObject()->user);
    }

    public function testGetInfo()
    {
        $purli = new \Purli\Purli(\Purli\Purli::SOCKET);

        $purli->get(SERVER_URL . '/get.php');

        $info = $purli->getInfo();

        $purli->close();

        $this->assertContains("HTTP/1.1", $info['request_header']);
    }
}