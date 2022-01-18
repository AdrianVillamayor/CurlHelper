<?php

declare(strict_types=1);

require './vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Adrii\CurlHelper;

final class CurlHelperTest extends TestCase
{
    // https://reqres.in/

    public function testGet(): void
    {
        $url    = 'https://reqres.in/api/users?page=1';

        $curl = new CurlHelper();

        $curl->setUrl($url);

        $curl->setGetParams([]);

        $curl->setOptions([
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_2_0,
            CURLOPT_CONNECTTIMEOUT => 30,
        ]);

        $curl->setMime("json");

        $curl->setUtf8();

        $curl->execute();

        $response   = $curl->response();
        $code       = $curl->http_code();

        $employee = count($response['data']);

        $this->assertSame(200, $code);
    }

    public function testPost(): void
    {
        $url  = 'https://reqres.in/api/users';

        $curl = new CurlHelper();

        $curl->setUrl($url);

        $curl->setGetParams([
            "name"  => "Morpheus",
            "job"   => "Leader"
        ]);

        $curl->setOptions([
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_2_0,
            CURLOPT_CONNECTTIMEOUT => 30,
        ]);

        $curl->setMime("form");

        $curl->setUtf8();

        $curl->execute();

        $code       = $curl->http_code();

        $this->assertSame(201, $code);
    }
   
    public function testPut(): void
    {
        $url  = 'https://reqres.in/api/users/2';

        $curl = new CurlHelper();

        $curl->setUrl($url);

        $curl->setPutParams([
            "name"  => "Morpheus",
            "job"   => "Zion Resident"
        ]);

        $curl->setOptions([
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_2_0,
            CURLOPT_CONNECTTIMEOUT => 30,
        ]);

        $curl->setMime("form");

        $curl->setUtf8();

        $curl->execute();

        $code       = $curl->http_code();

        $this->assertSame(200, $code);
    }
   
    public function testDelete(): void
    {
        $url  = 'https://reqres.in/api/users/2';

        $curl = new CurlHelper();

        $curl->setUrl($url);

        $curl->setDeleteParams([]);

        $curl->setOptions([
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_2_0,
            CURLOPT_CONNECTTIMEOUT => 30,
        ]);

        $curl->setMime("form");

        $curl->setUtf8();

        $curl->execute();

        $code       = $curl->http_code();

        $this->assertSame(200, $code);
    }
}
