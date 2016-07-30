Purli
=
[![DUB](https://img.shields.io/dub/l/vibe-d.svg)](http://opensource.org/licenses/MIT)
[![Version](https://img.shields.io/badge/version-0.9.0-orange.svg)](https://packagist.org/packages/kajna/Purli)

Lightweight library with object-oriented interface for sending HTTP requests

### Installing

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
### Fetching HTML page using GET

```php
try {
	$Purli = (new \Purli\Purli())
			->get('http://example.com')
			->close();

	$response = $Purli->response();

	echo $response->asText();
} catch(\Exception $e) {
	echo $e->getMessage();
}
```
### Sending and receiving JSON data using PUT with connection timeout

```php
try {
	$data = array('foo' => 'bar');
	$json = json_encode($data);

	$Purli = (new \Purli\Purli())
			->setConnectionTimeout(3)
			->setHeader('Content-Type', 'application/json')
			->setHeader('Content-Length', strlen($json))
			->setParams($json)
			->put('http://example.com')
			->close();

	$response = $Purli->response();

	print_r($response->asObject());
} catch(\Exception $e) {
	echo $e->getMessage();
}
```
### Sending and receiving XML data using POST

```php
try {
	$data = '<root><foo>bar</foo></root>';

	$Purli = (new \Purli\Purli())
			->setUserAgent('curl 7.16.1 (i386-portbld-freebsd6.2) libcurl/7.16.1 OpenSSL/0.9.7m zlib/1.2.3')
			->setHeader('Content-Type', 'text/xml')
			->setHeader('Content-Length', strlen($data))
			->setParams($data)
			->post('http://example.com')
			->close();

	$response = $Purli->response();

	print_r($response->asArray());
} catch(\Exception $e) {
	echo $e->getMessage();
}
```

Author
=
Author of library is Milos Kajnaco 
milos@caenazzo.com

Licence
=
Purli is released under the [MIT](http://opensource.org/licenses/MIT) public license.
