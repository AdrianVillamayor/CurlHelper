<?php

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
     * @var null|string
     */
    protected $post_raw;

    /**
     * @var array
     */
    protected $cookies = [];

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $xpath = [];

    /**
     * @var array
     */
    protected $files = [];

    /**
     * @var array
     */
    protected $response = [];

    /**
     * @var string
     */
    public $http_code;

    /**
     * @var mixed
     */
    public $debug;

    /**
     * @var mixed
     */
    public $MIME_X_WWW_FORM   = 'application/x-www-form-urlencoded';
    public $MIME_FORM_DATA    = 'multipart/form-data';
    public $MIME_JSON         = 'application/json';

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

    public function perform_http_request($method, $url, $mime, $data, $debug = false): void
    {
        $this->ch  = curl_init();
        $this->url = $url;

        switch ($mime) {
            case 'form':
                $this->content_type =  $this->MIME_X_WWW_FORM;
                break;

            case 'multipart':
                $this->content_type =  $this->MIME_FORM_DATA;
                break;

            case 'json':
                $this->content_type =  $this->MIME_JSON;
                $data = json_encode($data);
                break;

            default:
                $this->content_type =  $this->MIME_JSON;
                break;
        }

        switch ($method) {
            case "POST":
                curl_setopt($this->ch, CURLOPT_POST, true);

                if (!empty($data)) {
                    curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
                }
                break;

            case "PUT":
                curl_setopt($this->ch, CURLOPT_PUT, true);
                break;

            default:
                if (!empty($data)) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
                break;
        }

        curl_setopt($this->ch, CURLOPT_URL, $this->url);

        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->user_agent);

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: {$this->content_type}; charset=utf-8 ;",
        ));

        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);

        $this->response = curl_exec($this->ch);

        $this->http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if ($debug) {
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
        return $this->debug;
    }

    public function response($format = 'array'): ?array
    {
        switch ($format) {
            case 'obj':
                $response = json_decode($this->response);
                break;

            case 'array':
                $response = json_decode($this->response, true);
                break;
        }

        if (json_last_error() == JSON_ERROR_NONE) {
            return (array) $response;
        }

        if ($this->response == "" || empty($this->response)) {
            return null;
        }

        return $this->response;
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
}
