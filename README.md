# CurlHelper

A simple class that helps to organize and have a standard when creating new projects.

<br/>

## Installation

### Manual

```php
require_once ROOT . 'CurlHelper.php';
```

### Composer
```
composer require adrii/curl-helper
```

<br/>

## Usage

```php
$url    = '{url}';
$basic  = '{basic}';

$curl = new CurlHelper();

$curl->setUrl($url);

$curl->setGetParams([
    'param' => '{param}',
    'type' => '{type}'
]);

$curl->setHeaders([
    "Authorization" => "Basic {$basic}"
]);

$curl->setMime("json");

$curl->execute();

$response   = $curl->response();
$code       = $curl->http_code();

list($error, $msg) = $curl->parseCode();

```
<br/>

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
// RAW
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

<br/>

# Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

<br/>

# License
[MIT](https://github.com/AdrianVillamayor/CurlHelper/blob/master/LICENSE)
