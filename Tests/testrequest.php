<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Fetching HTML page using GET request
try {
	$purli = (new \Purli\Purli(\Purli\Purli::SOCKET))
			->get('http://localhost/Purli/Tests/testresponse.php')
			->close();

	$response = $purli->response();

	echo $response->asText();
} catch(\Exception $e) {
	echo $e->getMessage();
}

echo '<hr>';

// Fetching HTML page using POST request
try {
	$purli = (new \Purli\Purli(\Purli\Purli::SOCKET))
		->setParams(['foo' => 'bar'])
		->post('http://localhost/Purli/Tests/testresponsepost.php')
		->close();

	$response = $purli->response();

	echo $response->asText();
} catch(\Exception $e) {
	echo $e->getMessage();
}

echo '<hr>';

// Sending and receiving JSON data using PUT
try {
	$data = array('foo' => 'bar');
	$json = json_encode($data);

	$purli = (new \Purli\Purli(\Purli\Purli::SOCKET))
			->setConnectionTimeout(3)
			->setHeader('Content-Type', 'application/json')
			->setHeader('Connection', 'Close')
			->setHeader('Content-Length', strlen($json))
			->setParams($json)
			->post('http://localhost/Purli/Tests/testresponsejson.php')
			->close();

	$response = $purli->response();

	print_r($response->asObject());
} catch(\Exception $e) {
	echo $e->getMessage();
}

echo '<hr>';

// Sending and receiving XML data using POST
try {
	$data = '<root><foo>bar</foo></root>';

	$purli = (new \Purli\Purli(\Purli\Purli::SOCKET))
			->setUserAgent('curl 7.16.1 (i386-portbld-freebsd6.2) libcurl/7.16.1 OpenSSL/0.9.7m zlib/1.2.3')
			->setHeader('Content-Type', 'text/xml')
			->setHeader('Content-Length', strlen($data))
			->setParams($data)
			->post('http://localhost/Purli/Tests/testresponsexml.php')
			->close();

	$response = $purli->response();

	print_r($response->asArray());
} catch(\Exception $e) {
	echo $e->getMessage();
}

echo '<hr>';

// Fetch SSL page
try {
    $purli = (new \Purli\Purli(\Purli\Purli::SOCKET))
        ->get('https://www.youtube.com/')
        ->close();

    $response = $purli->response();

    echo htmlentities($response->asText());
} catch(\Exception $e) {
    echo $e->getMessage();
}