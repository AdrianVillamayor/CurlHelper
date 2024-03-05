# CurlHelper

*Original author: [Adrii](https://github.com/AdrianVillamayor)*

[![Test](https://github.com/AdrianVillamayor/CurlHelper/actions/workflows/php.yml/badge.svg)](https://github.com/AdrianVillamayor/CurlHelper/actions/workflows/php.yml)
[![Latest Stable Version](http://img.shields.io/packagist/v/adrii/curl-helper.svg)](https://packagist.org/packages/adrii/curl-helper)
[![Total Downloads](http://img.shields.io/packagist/dt/adrii/curl-helper.svg)](https://packagist.org/packages/adrii/curl-helper)
[![License](http://img.shields.io/packagist/l/adrii/curl-helper.svg)](https://packagist.org/packages/adrii/curl-helper)

`CurlHelper` is a streamlined PHP utility for easy cURL usage, supporting GET, POST, PUT, and DELETE methods. It simplifies HTTP requests by offering straightforward options and header settings, making web API interactions more accessible and efficient.

## Features

- **Versatile HTTP Support**: GET, POST, PUT, and DELETE methods are all supported, catering to a wide range of API interactions.
- **Header & Option Customization**: Effortlessly configure request headers and cURL options to meet the precise needs of every API call.
- **File Uploads**: Simplified multipart/form-data handling makes file uploads a breeze.
- **Data Flexibility**: Easily switch between raw and array data inputs, with automatic content type handling for JSON and form-urlencoded data.
- **Debug Mode**: Activate detailed logging for troubleshooting and optimization, gaining insight into request and response cycles.


## Requirements

- PHP 7.4 or higher
- cURL extension enabled in PHP

## Installation

You can copy the `CurlHelper` class into your project, or preferably, autoload it using [Composer](https://getcomposer.org/).

```bash
composer require adrii/curl-helper
```

### Composer
```php
use Adrii\CurlHelper;
```

### Manual

```php
require_once ROOT . 'CurlHelper.php';
```

## Usage

```php
$curl = new CurlHelper();
```

## Methods

This section provides a quick start for users to understand how to apply the `CurlHelper` class in their projects for various HTTP requests, setting custom headers, and enabling debug mode for detailed request and response insights.


### GET Request

```php
$curl->setUrl('http://example.com/api/data');
$curl->setGetParams(['key' => 'value']);
$curl->execute();
$response = $curl->response();

```

### POST Request

```php
$curl->setUrl('http://example.com/api/data');
$curl->setPostParams(['key' => 'value']);
$curl->execute();
$response = $curl->response();
```

### PUT Request

```php
$curl = new CurlHelper();
$curl->setUrl('https://example.com/api/data/1');
$curl->setPutParams(['key' => 'updatedValue']);
$curl->execute();
$response = $curl->response();
```

### DELETE Request

```php
$curl = new CurlHelper();
$curl->setUrl('https://example.com/api/data/1');
$curl->execute();
$response = $curl->response();
```

### Setting Custom Headers
```php
$curl->setUrl('http://example.com/api/data');
$curl->setPostParams(['key' => 'value']);
$curl->execute();
$response = $curl->response();
```

### Enabling Debug Mode
```php
$curl->setDebug();
$curl->execute();
$debugInfo = $curl->debug();
```

### Handling Errors
The `execute` method throws an exception if the cURL request fails. Make sure to catch these exceptions and handle them accordingly.

```php
try {
    $curl->execute();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```


# Example
These examples cover basic usage scenarios for CurlHelper. Modify parameters and URLs as needed to fit your specific use case.


```php
$url    = '{url}';
$basic  = '{basic}';

$curl = new CurlHelper();

$curl->setUrl($url);

$curl->setPostParams([
    'param' => '{param}',
    'type' => '{type}'
]);

$curl->setHeaders([
    "Authorization" => "Basic {$basic}"
], false); // Disable parse (dafault Enabled:true) 

$curl->setOptions([
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_2_0,
    CURLOPT_CONNECTTIMEOUT => 30,
]);

$curl->setMime("json");

$curl->setUtf8();

$curl->execute();

$response   = $curl->response();
$code       = $curl->http_code();

list($error, $msg) = $curl->parseCode();

```


# Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

# License
CurlHelper is open-sourced software licensed under the [MIT](https://github.com/AdrianVillamayor/CurlHelper/blob/master/LICENSE)
