<?php

/**
 *
 * This class has all the necessary code for making API calls thru curl library
 * @category   Helper
 * @author     Original Author <adrian@socialdiabetes.com>
 * @link       https://github.com/AdrianVillamayor/CurlHelper
 *
 */

declare(strict_types=1);

namespace Adrii;

class CurlHelper
{
    public string $user_agent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36';

    public int $timeout = 30;

    protected \CurlHandle $ch;

    protected ?string $url;

    protected ?string $mime = null;

    protected array $mime_type = array(
        'MIME_X_WWW_FORM'   => 'application/x-www-form-urlencoded',
        'MIME_FORM_DATA'    => 'multipart/form-data',
        'MIME_JSON'         => 'application/json',
        'MIME_XML'          => 'application/xml',
        'MIME_BINARY'       => 'application/binary'
    );

    protected bool $utf8 = FALSE;

    protected string $content_type;

    protected array $get_data = [];

    protected array $post_data = [];

    protected array $put_data = [];

    protected ?string $post_raw;

    protected array $headers = [];

    protected mixed $response;

    protected string $header_size;

    protected string $error;

    protected int $errno;

    public string $http_code;

    public mixed $debug;

    public bool $is_debug = FALSE;

    public function __construct()
    {
        $this->ch = curl_init();
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function setMime(string $mime = null): void
    {
        switch ($mime) {
            case 'form':
            case 'x-www-form-urlencoded':
                $this->mime =  $this->mime_type['MIME_X_WWW_FORM'];
                break;

            case 'multipart':
            case 'multipart/form-data':
                $this->mime =  $this->mime_type['MIME_FORM_DATA'];
                break;

            case 'json':
                $this->mime =  $this->mime_type['MIME_JSON'];
                break;

            case 'xml':
                $this->mime =  $this->mime_type['MIME_XML'];
                break;

            case 'binary':
                $this->mime =  $this->mime_type['MIME_BINARY'];
                break;

            default:
                $this->mime =  $this->mime_type['MIME_JSON'];
                break;
        }
    }

    public function setUtf8(): void
    {
        $this->uf8 = TRUE;
    }

    public function setHeaders(array $data, bool $parse = TRUE): void
    {
        foreach ($data as $key => $val) {
            if ($parse) {
                $this->headers[$this->parseStringHeader($key)] = $val;
            } else {
                $this->headers[$key] = $val;
            }
        }
    }

    public function setOptions(array $options): void
    {
        foreach ($options as $key => $value) {
            curl_setopt($this->ch, $key, $value);
        }
    }

    public function setPostRaw(mixed $raw): void
    {
        $this->post_raw = (is_array($raw)) ? http_build_query($raw) : $raw;
    }

    public function setPostParams(array $data): void
    {
        $this->post_data = array_merge($this->post_data, $data);
    }

    /**
     * @param array $files
     * @param bool $form
     * @return $this
     */
    public function setPostFiles(array $files, bool $form = FALSE): void
    {
        $c_files_array = array();

        if ($form) {
            $c_files_array = $this->prefabFromFiles($files, $c_files_array);
        } else {
            $c_files_array = $this->prefabFiles($files, $c_files_array);
        }

        $this->setPostParams($c_files_array);
    }


    public function setGetParams(array $data): void
    {
        $this->get_data = array_merge($this->get_data, $data);
    }


    public function setPutParams(array $data): void
    {
        $this->put_data = array_merge($this->put_data, $data);
    }

    public function setDebug(): void
    {
        $this->is_debug = TRUE;
    }

    /**
     * This method will perform an action/method thru HTTP/API calls
     *
     * @param string $method POST, PUT, GET etc
     * @param string $url
     * @param string $mime Type of data (form, multipart, json)
     * @param array $data Array
     * @param bool $debug Default False
     * 

     * @return void
     */
    public function execute(): void
    {
        //- POST 
        if (!empty($this->post_data)) {
            curl_setopt($this->ch, CURLOPT_POST, TRUE);

            if ($this->mime === $this->mime_type['MIME_JSON']) {
                $this->post_data = json_encode($this->post_data);
            }

            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->post_data);
            $this->headers['Content-Type'] = $this->parseMimeType();
        }

        //- POST RAW 
        elseif (isset($this->post_raw)) {
            curl_setopt($this->ch, CURLOPT_POST, TRUE);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->post_raw);
            $this->headers['Content-Type'] = $this->parseMimeType();
            $this->headers['Content-Length'] = strlen($this->post_raw);
        }

        // - PUT
        elseif (!empty($this->put_data)) {
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->put_raw);
            $this->headers['Content-Type'] = $this->parseMimeType();
        }

        // - GET
        elseif (!empty($this->get_data)) {
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "GET");
            $this->url = sprintf("%s?%s", $this->url, http_build_query($this->get_data));
        }

        $url = $this->generateUrl();

        curl_setopt($this->ch, CURLOPT_URL, $url);

        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->user_agent);

        if (!empty($this->headers)) {
            $data = [];
            foreach ($this->headers as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $val) {
                        $data[] = "$k: $val";
                    }
                } else {
                    $data[] = "$k: $v";
                }
            }
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $data);
        }

        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $this->response     = curl_exec($this->ch);

        $this->http_code    = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $this->header_size  = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $this->sent         = curl_getinfo($this->ch, CURLINFO_HEADER_OUT);
        $this->error        = curl_error($this->ch);
        $this->errno        = curl_errno($this->ch);

        if ($this->is_debug) {
            $this->debug = curl_getinfo($this->ch);
        }

        curl_close($this->ch);
    }

    public function http_code(): string
    {
        return $this->http_code;
    }

    public function debug(): array
    {
        return array("Debug" => $this->debug, "Error" => $this->error, "Errno" => $this->errno, "Out" => $this->sent, "Code" =>  $this->http_code, "Size" =>  $this->header_size);
    }

    public function response(string $format = 'array'): ?array
    {
        switch ($format) {
            case 'obj':
                $response = json_decode($this->response, FALSE, 512, JSON_BIGINT_AS_STRING);
                break;

            case 'array':
                $response = json_decode($this->response, TRUE, 512, JSON_BIGINT_AS_STRING);
                break;


            case 'xml':
                $xml      = simplexml_load_string($this->response);
                $json     = json_encode($xml);
                $response = json_decode($json, TRUE, 512, JSON_BIGINT_AS_STRING);
                break;
        }

        if (json_last_error() == JSON_ERROR_NONE) {
            return (array) $response;
        }

        if ($this->response == "" || empty($this->response)) {
            return null;
        }

        if (is_string($this->response)) {
            return null;
        }

        return $response;
    }

    /**
     * @return array $error, $msg
     */
    public function parseCode(): array
    {
        $error = TRUE;

        switch ($this->http_code) {
            case "100":
                $msg = 'Continue';
                break;
            case "101":
                $msg = 'Switching Protocols';
                break;
            case "200":
                $msg = 'OK';
                $error  = FALSE;
                break;
            case "201":
                $msg = 'Created';
                $error  = FALSE;
                break;
            case "202":
                $msg = 'Accepted';
                $error  = FALSE;
                break;
            case "203":
                $msg = 'Non-Authoritative Information';
                $error  = FALSE;
                break;
            case "204":
                $msg = 'No Content';
                $error  = FALSE;
                break;
            case "205":
                $msg = 'Reset Content';
                $error  = FALSE;
                break;
            case "206":
                $msg = 'Partial Content';
                $error  = FALSE;
                break;
            case "300":
                $msg = 'Multiple Choices';
                break;
            case "301":
                $msg = 'Moved Permanently';
                break;
            case "302":
                $msg = 'Moved Temporarily';
                break;
            case "303":
                $msg = 'See Other';
                break;
            case "304":
                $msg = 'Not Modified';
                break;
            case "305":
                $msg = 'Use Proxy';
                break;
            case "400":
                $msg = 'Bad Request';
                break;
            case "401":
                $msg = 'Unauthorized';
                break;
            case "402":
                $msg = 'Payment Required';
                break;
            case "403":
                $msg = 'Forbidden';
                break;
            case "404":
                $msg = 'Not Found';
                break;
            case "405":
                $msg = 'Method Not Allowed';
                break;
            case "406":
                $msg = 'Not Acceptable';
                break;
            case "407":
                $msg = 'Proxy Authentication Required';
                break;
            case "408":
                $msg = 'Request Time-out';
                break;
            case "409":
                $msg = 'Conflict';
                break;
            case "410":
                $msg = 'Gone';
                break;
            case "411":
                $msg = 'Length Required';
                break;
            case "412":
                $msg = 'Precondition Failed';
                break;
            case "413":
                $msg = 'Request Entity Too Large';
                break;
            case "414":
                $msg = 'Request-URI Too Large';
                break;
            case "415":
                $msg = 'Unsupported Media Type';
                break;
            case "500":
                $msg = 'Internal Server Error';
                break;
            case "501":
                $msg = 'Not Implemented';
                break;
            case "502":
                $msg = 'Bad Gateway';
                break;
            case "503":
                $msg = 'Service Unavailable';
                break;
            case "504":
                $msg = 'Gateway Time-out';
                break;
            case "505":
                $msg = 'HTTP Version not supported';
                break;
            default:
                $msg = "Unknown http status code " . $this->http_code;
                break;
        }

        return array($error, $msg);
    }

    protected function generateUrl(): string
    {
        $parsed_string = '';
        $url = parse_url($this->url);

        if (!empty($url['query'])) {
            parse_str($url['query'], $get_data);
            $url['query'] = http_build_query(array_merge($get_data, $this->get_data));
        } else {
            $url['query'] = http_build_query($this->get_data);
        }

        if (isset($url['scheme'])) {
            $parsed_string .= $url['scheme'] . '://';
        }

        if (isset($url['user'])) {
            $parsed_string .= $url['user'];
            if (isset($url['pass'])) {
                $parsed_string .= ':' . $url['pass'];
            }
            $parsed_string .= '@';
        }

        if (isset($url['host'])) {
            $parsed_string .= $url['host'];
        }

        if (isset($url['port'])) {
            $parsed_string .= ':' . $url['port'];
        }

        if (!empty($url['path'])) {
            $parsed_string .= $url['path'];
        } else {
            $parsed_string .= '/';
        }

        if (!empty($url['query'])) {
            $parsed_string .= '?' . $url['query'];
        }

        if (isset($url['fragment'])) {
            $parsed_string .= '#' . $url['fragment'];
        }

        return $parsed_string;
    }

    // - Fix strings to Mime-Type
    protected function parseMimeType(): string
    {
        $mime = ($this->utf8) ? $this->mime . "; charset=utf-8 ;" : $this->mime;

        return $mime;
    }

    // - Fix strings to Proper-Case
    protected function parseStringHeader(string $str): string
    {
        $str = explode('-', $str);

        foreach ($str as &$word) {
            $word = ucfirst($word);
        }

        return implode('-', $str);
    }


    // - Prepare local file data to the CURLFile model
    protected function prefabFiles(array $files, array $c_files_array): array
    {
        if (!empty($files)) {
            foreach ($files as $key) {
                $mime = mime_content_type($key);
                $info = pathinfo($key);
                $name = $info['basename'];

                $c_files_array[] = new CURLFile($key, $mime, $name);
            }
        }

        return $c_files_array;
    }

    // - Prepare data from $_FILES to the CURLFile model
    protected function prefabFromFiles(array $files, array $c_files_array)
    {
        if (!empty($files)) {
            for ($i = 0; $i < count($files['tmp_name']); $i++) {
                $filename = $files['tmp_name'][$i];
                $mime     = $files['type'][$i];
                $name     = $files['name'][$i];

                $c_files_array[] = new CURLFile($filename, $mime, $name);
            }
        }

        return $c_files_array;
    }
}
