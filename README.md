Purli
=
[![DUB](https://img.shields.io/dub/l/vibe-d.svg)](http://opensource.org/licenses/MIT)
[![Version](https://img.shields.io/badge/version-1.0.0-green.svg)](https://github.com/Kajna/Purli/releases)
[![Build Status](https://travis-ci.org/Kajna/Purli.svg)](https://travis-ci.org/Kajna/Purli)

Purli (PHP Url Interface) is lightweight library with object-oriented interface for sending HTTP requests. 

Installing
=
This package is available via Composer:

```json
{
  "require": {
    "kajna/purli": "dev-master"
  }
}
```

Usage examples
=

#### Fetching data using GET method and CURL handler
Minimal example, Purli by default uses CURL handler if available otherwise fallback to socket.
```php
try {
    $purli = (new \Purli\Purli())
        ->get('http://www.example.com')
        ->close();

    $response = $purli->response();

    echo $response->asText();
} catch(\Exception $e) {
    echo $e->getMessage();
}
```

#### Fetching data using GET method and socket handler
If explicitly set Purli will use PHP sockets to make request regardless if CURL is installed or not
```php
try {
    $purli = (new \Purli\Purli(\Purli\Purli::SOCKET))
            ->get('http://example.com')
            ->close();
    
    $response = $purli->response();
    
    echo $response->asText();
} catch(\Exception $e) {
	echo $e->getMessage();
}
```

#### Fetching data using POST method
```php
try {
    $data = array('foo' => 'bar');

    $purli = (new \Purli\Purli())
        ->setParams($data)
        ->post('http://www.example.com')
        ->close();

    $response = $purli->response();

    print_r($response->asText());
} catch(\Exception $e) {
    echo $e->getMessage();
}
```

#### Sending and receiving XML data using POST method

```php
try {
    $data = '<root><foo>bar</foo></root>';

    $purli = (new \Purli\Purli())
        ->setUserAgent('curl 7.16.1 (i386-portbld-freebsd6.2) libcurl/7.16.1 OpenSSL/0.9.7m zlib/1.2.3')
        ->setHeader('Content-Type', 'text/xml')
        ->setHeader('Content-Length', strlen($data))
        ->setParams($data)
        ->post('http://www.example.com')
        ->close();

    $response = $purli->response();

    print_r($response->asArray());
} catch(\Exception $e) {
    echo $e->getMessage();
}
```

#### Sending and receiving JSON data using PUT method
```php
try {
    $data = array('foo' => 'bar');
    $json = json_encode($data);
    
    $purli = (new \Purli\Purli(\Purli\Purli::SOCKET))
            ->setConnectionTimeout(3)
            ->setHeader('Content-Type', 'application/json')
            ->setParams($json)
            ->put('http://www.example.com')
            ->close();
    
    $response = $purli->response();
    
    print_r($response->asObject());
} catch(\Exception $e) {
	echo $e->getMessage();
}
```

#### Using proxy server to make request
```php
try {
    $purli = (new \Purli\Purli());
    
    $purli
        ->setProxy(PROXY_ADDRESS, PROXY_PORT)
        ->get('http://www.example.com')
        ->close();

    $response = $purli->response();

    echo $response->asText();
} catch(\Exception $e) {
    echo $e->getMessage();
}
```

#### Setting custom CURL option
If CURL extension is installed by default Purli will use it, 
you can always get CURL handler object and set custom option if more flexibility is needed
```php
try {
    $purli = (new \Purli\Purli());
    
    if ($purli->getHandlerType() === \Purli\Purli::CURL) {
        curl_setopt($purli->getHandler(), CURLOPT_TIMEOUT, 10);
    }
    
    $purli
        ->get('http://www.example.com')
        ->close();

    $response = $purli->response();

    echo $response->asText();
} catch(\Exception $e) {
    echo $e->getMessage();
}
```

Running tests
=
Purli uses [PHPUnit](https://phpunit.de/) for testing
```$xslt
cd tests
phpunit
```

Author
=
Milos Kajnaco 
milos@caenazzo.com

Licence
=
Purli is released under the [MIT](http://opensource.org/licenses/MIT) public license.
