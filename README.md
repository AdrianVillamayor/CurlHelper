# CurlHelper

A simple class that helps to organize and have a standard when creating new projects.

## Installation


```php
 require_once ROOT . 'CurlHelper.php';
```

## Usage

```php
$curl = new CurlHelper();

$fields = array(
    'client_id' => '{app-id}',
    'client_secret' => '{app-secret}',
    'grant_type' => 'authorization_code',
    'redirect_uri' => '{redirect-uri}',
    'code' => '{code}'
);

$curl->perform_http_request("POST", $url, "json", $fields, true);

$response   = $curl->response();
$code       = $curl->http_code();

list($error, $msg)       = $curl->parseCode();
```

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://github.com/AdrianVillamayor/CurlHelper/blob/master/LICENSE)
