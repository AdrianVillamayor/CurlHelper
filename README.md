# CurlHelper

*By [Adrii](https://github.com/AdrianVillamayor)*

[![Latest Stable Version](http://img.shields.io/packagist/v/adrii/curl-helper.svg)](https://packagist.org/packages/adrii/curl-helper)
[![Total Downloads](http://img.shields.io/packagist/dt/adrii/curl-helper.svg)](https://packagist.org/packages/adrii/curl-helper)
[![License](http://img.shields.io/packagist/l/adrii/curl-helper.svg)](https://packagist.org/packages/adrii/curl-helper)

CurlHelper is a lightweight PHP class to organize and have a standard in projects.

## Installation

Use [Composer](https://getcomposer.org/) to install the library.

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

$curl->execute();

$response   = $curl->response();
$code       = $curl->http_code();

list($error, $msg) = $curl->parseCode();

```

## Methods

### POST Request

```php
$curl->setPostParams([
    'client_id' => '{app-id}',
    'client_secret' => '{app-secret}',
    'grant_type' => 'authorization_code',
    'redirect_uri' => '{redirect-uri}',
    'code' => '{code}'
]);
```

```php
// Array
$curl->setPostRaw([
    'client_id' => '{app-id}',
    'client_secret' => '{app-secret}',
    'grant_type' => 'authorization_code',
    'redirect_uri' => '{redirect-uri}',
    'code' => '{code}'
]);

// String
$curl->setPostRaw("key1=value1&key2=value2");
```

### GET Request

```php
$curl->setGetParams([
    'param' => '{param}',
    'type' => '{type}'
]);
```

### PUT Request


```php
$curl->setPutParams([
    'param' => '{param}',
    'type' => '{type}'
]);
```

# Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

# License
[MIT](https://github.com/AdrianVillamayor/CurlHelper/blob/master/LICENSE)
