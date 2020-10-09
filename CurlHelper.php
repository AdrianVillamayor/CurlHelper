<?php


namespace Adrii;

/**
 *
 * This class has all the necessary code for making API calls thru curl library
 * @category   Helper
 * @author     Original Author <adrian@socialdiabetes.com>
 * @link       https://github.com/AdrianVillamayor/CurlHelper
 *
 */

class CurlHelper
{
    /**
     * @var string
     */
    public $user_agent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36';

    /**
     * @var int
     */
    public $timeout = 30;

    /**
     * @var resource
     */
    protected $ch;

    /**
     * @var null|string
     */
    protected $url;

    /**
     * @var null|string
     */
    protected $mime;

    /**
     * @var string
     */
    protected $content_type;

    /**
     * @var array
     */
    protected $get_data = [];

    /**
     * @var array
     */
    protected $post_data = [];

    /**
     * @var array
     */
    protected $put_data = [];

    /**
     * @var null|string
     */
    protected $post_raw;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var mixed
     */
    protected $response = [];

    /**
     * @var string
     */
    protected $header_size;

    /**
     * @var string
     */
    protected $error;
    /**
     * @var int
     */
    protected $errno;

    /**
     * @var string
     */
    public $http_code;

    /**
     * @var mixed
     */
    public $debug;

    /**
     * @var bool
     */
    public $is_debug = false;

    /**
     * @var mixed
     */
    const MIME_X_WWW_FORM   = 'application/x-www-form-urlencoded';
    const MIME_FORM_DATA    = 'multipart/form-data';
    const MIME_JSON         = 'application/json';




    
    public function __construct()
    {
        $this->ch = curl_init();
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url): object
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param string $mime
     * @return $this
     */

    public function setMime($mime = null): object
    {
        switch ($mime) {
            case 'form':
                $this->mime =  self::MIME_X_WWW_FORM;
                break;

            case 'multipart':
                $this->mime =  self::MIME_FORM_DATA;
                break;

            case 'json':
                $this->mime =  self::MIME_JSON;
                break;

            default:
                $this->mime =  self::MIME_JSON;
                break;
        }

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */

    public function setHeaders($data): object
    {
        foreach ($data as $key => $val) {
            $this->headers[$this->parseStringHeader($key)] = $val;
        }
        return $this;
    }

    /**
     * @param string $raw
     * @return $this
     */
    public function setPostRaw($raw): object
    {
        $this->post_raw = $raw;
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setPostParams($data): object
    {
        $this->post_data = array_merge($this->post_data, $data);
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setGetParams($data): object
    {
        $this->get_data = array_merge($this->get_data, $data);
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setPutParams($data): object
    {
        $this->put_data = array_merge($this->put_data, $data);
        return $this;
    }

    /**
     * @return $this
     */
    public function setDebug(): object
    {
        $this->is_debug = true;
        return $this;
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
            curl_setopt($this->ch, CURLOPT_POST, true);
            if ($this->mime == self::MIME_JSON) {
                $this->post_data = json_encode($this->post_data);
            }
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->post_data);
            $this->headers['Content-Type'] = $this->mime . " ; charset=utf-8 ;";
        }

        //- POST RAW 
        elseif (isset($this->post_raw)) {
            curl_setopt($this->ch, CURLOPT_POST, true);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->post_raw);
            $this->headers['Content-Type'] = $this->mime . " ; charset=utf-8 ;";
            $this->headers['Content-Length'] = strlen($this->post_raw);
        }

        // - PUT
        elseif (!empty($this->put_data)) {
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->put_raw);
            $this->headers['Content-Type'] = $this->mime . " ; charset=utf-8 ;";
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

        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);

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

    public function debug(): ?array
    {
        return array("Debug" => $this->debug, "Error" => $this->error, "Errno" => $this->errno, "Out" => $this->sent, "Code" =>  $this->http_code, "Size" =>  $this->header_size);
    }

    public function response($format = 'array'): ?array
    {
        switch ($format) {
            case 'obj':
                $response = json_decode($this->response, false, 512, JSON_BIGINT_AS_STRING);
                break;

            case 'array':
                $response = json_decode($this->response, true, 512, JSON_BIGINT_AS_STRING);
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

    public function parseCode(): array
    {
        $error = true;

        switch ($this->http_code) {
            case '200':
            case '201':
                $msg = _("Success");
                $error = false;
                break;

            case '409':
                $msg = _("This email is already registered.");
                break;

            case '403':
                $msg = _("Forbidden, denied access to the requested action.");
                break;

            case '404':
                $msg = _("Nothing has been found.");
                break;

            case '401':
                $msg = _("Unauthorized");
                break;

            case '405':
                $msg = _("Method Not Allowed");
                break;
        }

        return array($error, $msg);
    }


    /**
     * @return string
     */
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


    /**
     * Fix strings to Proper-Case
     * @param string $str
     * @return string
     */
    protected function parseStringHeader($str): string
    {
        $str = explode('-', $str);

        foreach ($str as $word) {
            $word = ucfirst($word);
        }

        return implode('-', $str);
    }
}
