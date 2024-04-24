<?php

/**
 *
 * CurlHelper class provides functionalities for making HTTP requests using cURL.
 * It supports setting custom options, headers, and handling different types of requests such as GET, POST, PUT, DELETE.
 * @category   Helper
 * @author     Original Author <adrian@socialdiabetes.com>
 * @link       https://github.com/AdrianVillamayor/CurlHelper
 *
 */

declare(strict_types=1);

namespace Adrii;

class CurlHelper
{
    /** @var string Default user agent string for cURL requests */
    public string $user_agent = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.132 Safari/537.36";

    /** @var int Default timeout for cURL requests */
    public int $timeout = 30;

    /** @var \CurlHandle cURL handle for the session */
    private $ch;

    /** @var string|null URL for the cURL request */
    private ?string $url;

    /** @var string|null MIME type for the request content */
    private ?string $mime = null;

    /** @var array Map of MIME types for convenience */
    private array $mime_type = [
        'MIME_X_WWW_FORM' => 'application/x-www-form-urlencoded',
        'MIME_FORM_DATA'  => 'multipart/form-data',
        'MIME_JSON'       => 'application/json',
        'MIME_XML'        => 'application/xml',
        'MIME_BINARY'     => 'application/binary'
    ];

    /** @var bool Flag to set encoding to UTF-8 */
    private bool $utf8 = FALSE;

    /** @var array Data for GET requests */
    private array $get_data = [];

    /** @var array Data for POST requests */
    private array $post_data = [];

    /** @var array Data for PUT requests */
    private array $put_data = [];

    /** @var array Data for DELETE requests */
    private array $delete_data = [];

    /** @var string|null Raw data for POST requests */
    private ?string $post_raw;

    /** @var array Custom headers for the cURL request */
    private array $headers = [];

    /** @var mixed The response from the cURL request */
    private $response;

    /** @var int The size of the response header */
    private int $header_size;

    /** @var mixed Information about the request that was sent */
    private $sent;

    /** @var string Error message from the last cURL operation */
    private string $error;

    /** @var int Error number from the last cURL operation */
    private int $errno;

    /** @var int HTTP status code from the last cURL response */
    public int $http_code;

    /** @var mixed Debug information for the cURL request */
    public $debug;

    /** @var bool Flag to enable debug mode */
    public bool $is_debug = FALSE;

    /**
     * Initializes the cURL session.
     * 
     * @throws \Exception if cURL session fails to initialize.
     */
    public function __construct()
    {
        $this->initCurl();
    }

    /**
     * Sets the URL for the cURL request.
     *
     * @param string $url The URL to request.
     */
    private function initCurl(): void
    {
        $this->ch = curl_init();
        if ($this->ch === false) {
            throw new \Exception('Failed to initialize cURL session.');
        }
    }

    /**
     * Sets the URL for the cURL request.
     *
     * @param string $url The URL to request.
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * Sets the MIME type for the request content.
     *
     * @param string|null $mime The MIME type to set. Defaults to JSON if not specified.
     */
    public function setMime(string $mime = null): void
    {
        // Mapping of shortcut keys to their corresponding MIME types
        $mimeShortcuts = [
            'form'                      => 'MIME_X_WWW_FORM',
            'x-www-form-urlencoded'     => 'MIME_X_WWW_FORM',
            'multipart'                 => 'MIME_FORM_DATA',
            'multipart/form-data'       => 'MIME_FORM_DATA',
            'json'                      => 'MIME_JSON',
            'xml'                       => 'MIME_XML',
            'binary'                    => 'MIME_BINARY'
        ];

        // Use the shortcut if available, or default to the provided $mime
        $key = $mimeShortcuts[$mime] ?? $mime;

        // Set the MIME type if the key exists, default to MIME_JSON otherwise
        $this->mime = $this->mime_type[$key] ?? $this->mime_type['MIME_JSON'];
    }

    /**
     * Enables UTF-8 encoding for the request content.
     */
    public function setUtf8(): void
    {
        $this->utf8 = TRUE;
    }

    /**
     * Sets custom headers for the cURL request.
     *
     * @param array $data Array of headers to set.
     * @param bool  $parse If TRUE, headers will be parsed to ensure proper capitalization.
     */
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

    /**
     * Sets a single cURL option.
     *
     * @param mixed $option The CURLOPT_XXX option to set.
     * @param mixed $value The value to set for the option.
     */
    public function setOption($option, $value): void
    {
        curl_setopt($this->ch, $option, $value);
    }

    /**
     * Sets multiple cURL options at once.
     *
     * @param array $options An associative array of CURLOPT_XXX keys and their values.
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $key => $value) {
            $this->setOption($key, $value);
        }
    }

    /**
     * Sets raw data for a POST request.
     *
     * @param mixed $raw The raw data to send in the POST request. Can be an array or a string.
     */
    public function setPostRaw($raw): void
    {
        $this->post_raw = (is_array($raw)) ? http_build_query($raw) : $raw;
    }

    /**
     * Sets parameters for a POST request.
     *
     * @param array $data The data to send in the POST request.
     */
    public function setPostParams(array $data): void
    {
        $this->post_data = array_merge($this->post_data, $data);
    }

    /**
     * Sets files to upload in a POST request.
     *
     * @param array $files The files to upload.
     * @param bool  $form If TRUE, uses multipart/form-data encoding.
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


    /**
     * Sets parameters for a GET request.
     *
     * @param array $data The data to append to the URL query string.
     */
    public function setGetParams(array $data): void
    {
        $this->get_data = array_merge($this->get_data, $data);
    }

    /**
     * Sets parameters for a PUT request.
     *
     * @param array $data The data to send in the PUT request.
     */
    public function setPutParams(array $data): void
    {
        $this->put_data = array_merge($this->put_data, $data);
    }

    /**
     * Sets parameters for a DELETE request.
     *
     * @param array $data The data to send in the DELETE request.
     */
    public function setDeleteParams(array $data): void
    {
        $this->delete_data = array_merge($this->delete_data, $data);
    }

    /**
     * Enables debug mode, which will capture additional information about the cURL request.
     */
    public function setDebug(): void
    {
        $this->is_debug = TRUE;
    }

    /**
     * Executes the cURL request with the previously set options and parameters.
     * This method handles different types of requests like GET, POST, PUT, DELETE, and includes error handling.
     * 
     * @return void
     * @throws \Exception If the cURL request fails.
     */
    public function execute(): void
    {
        try {
            $this->prepareRequest();
            $this->setCurlOptions();
            $this->response = curl_exec($this->ch);
            
            $this->error = curl_error($this->ch);
            $this->errno = curl_errno($this->ch);

            $this->postExecution();
        } finally {
            curl_close($this->ch);
            $this->ch = null; // Ensure the handle is closed and reset
        }
    }

    /**
     * Prepares the cURL request based on the specified parameters and data.
     * 
     * @return void
     */
    private function prepareRequest(): void
    {
        $this->generateUrl();
        $this->prepareData();
    }

    /**
     * Sets the cURL options for the request.
     * 
     * @return void
     */
    private function setCurlOptions(): void
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->formatHeaders());
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, TRUE);
    }

    /**
     * Formats headers for the cURL request.
     * 
     * @return array Formatted headers.
     */
    private function formatHeaders(): array
    {
        $formattedHeaders = [];
        if (!empty($this->headers)) {

            foreach ($this->headers as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $val) {
                        $formattedHeaders[] = "$k: $val";
                    }
                } else {
                    $formattedHeaders[] = "$k: $v";
                }
            }
        }

        return $formattedHeaders;
    }

    /**
     * Handles operations after executing the cURL request, such as setting response codes and debugging information.
     * 
     * @return void
     */
    private function postExecution(): void
    {
        $this->http_code    = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $this->header_size  = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $this->sent         = curl_getinfo($this->ch, CURLINFO_HEADER_OUT);

        if ($this->is_debug) {
            $this->debug    = curl_getinfo($this->ch);
        }
    }

    /**
     * Prepares the URL and data for the cURL request based on the request type (GET, POST, PUT, DELETE).
     * 
     * @return void
     */
    private function prepareData(): void
    {
        $method = $this->determineRequestMethod();

        switch ($method) {
            case 'POST':
                $this->preparePostData();
                break;

            case 'PUT':
                $this->preparePutData();
                break;

            case 'DELETE':
                $this->prepareDeleteData();
                break;

            case 'GET':
            default:
                $this->prepareGetData();
                break;
        }
    }

    /**
     * Determines the HTTP request method to use based on the data set in the class.
     * 
     * The method defaults to GET if no data is set for POST, PUT, or DELETE requests.
     * If data is set for both POST and PUT/DELETE, POST takes precedence.
     *
     * @return string The determined HTTP method (GET, POST, PUT, DELETE).
     */
    private function determineRequestMethod(): string
    {
        // If raw POST data or POST data array is provided, use POST
        if (isset($this->post_raw) || !empty($this->post_data)) {
            return 'POST';
        }

        // If PUT data is provided, use PUT
        if (!empty($this->put_data)) {
            return 'PUT';
        }

        // If DELETE data is provided, use DELETE
        if (!empty($this->delete_data)) {
            return 'DELETE';
        }

        // Default to GET if no other method is determined
        return 'GET';
    }

    /**
     * Prepares the data for a POST request.
     * Sets the request method to POST and formats the data based on its type (raw or structured).
     * It also sets the appropriate Content-Type header based on the MIME type.
     * 
     * @throws \Exception If there's a JSON encoding error.
     */
    private function preparePostData(): void
    {
        curl_setopt($this->ch, CURLOPT_POST, TRUE);

        if (isset($this->post_raw)) {
            $data = $this->post_raw;
        } else {
            $data = $this->mime === $this->mime_type['MIME_JSON'] ? json_encode($this->post_data) : http_build_query($this->post_data);
            $this->handleJsonError();
        }

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        $this->headers['Content-Type'] = $this->parseMimeType();
    }

    /**
     * Prepares the data for a PUT request.
     * Sets the request method to PUT and formats the data for sending.
     * It also sets the Content-Type header based on the MIME type.
     * 
     * @throws \Exception If there's a JSON encoding error.
     */
    private function preparePutData(): void
    {
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $data = $this->mime === $this->mime_type['MIME_JSON'] ? json_encode($this->put_data) : http_build_query($this->put_data);
        $this->handleJsonError();

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        $this->headers['Content-Type'] = $this->parseMimeType();
    }

    /**
     * Prepares the data for a DELETE request.
     * Sets the request method to DELETE and formats the data for sending.
     * It also sets the Content-Type header based on the MIME type.
     * 
     * @throws \Exception If there's a JSON encoding error.
     */
    private function prepareDeleteData(): void
    {
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        $data = $this->mime === $this->mime_type['MIME_JSON'] ? json_encode($this->delete_data) : http_build_query($this->delete_data);
        $this->handleJsonError();

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        $this->headers['Content-Type'] = $this->parseMimeType();
    }

    /**
     * Prepares the URL for a GET request.
     * Appends the query string with the GET data to the URL.
     * It also sets the cURL option to make a GET request.
     */
    private function prepareGetData(): void
    {
        if (!empty($this->get_data)) {
            $this->prepareUrl(); // Ensure this method appends the query parameters to the URL
        }
        curl_setopt($this->ch, CURLOPT_HTTPGET, true);
    }

    /**
     * Handles potential JSON encoding errors by throwing an exception.
     * 
     * @throws \Exception If there's a JSON encoding error.
     */
    private function handleJsonError(): void
    {
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON encoding error: ' . json_last_error_msg(), json_last_error());
        }
    }


    /**
     * Appends query parameters to the URL if necessary, especially for GET requests.
     * 
     * @return void
     */
    private function prepareUrl(): void
    {
        if (!empty($this->get_data)) {
            $query = http_build_query($this->get_data);
            $this->url = strpos($this->url, '?') ? $this->url . '&' . $query : $this->url . '?' . $query;
        }
    }

    /**
     * Returns the HTTP status code of the last cURL request.
     *
     * @return int The HTTP status code.
     */
    public function http_code(): int
    {
        return $this->http_code;
    }

    /**
     * Returns debug information for the last cURL request.
     *
     * @return array An array containing debug information.
     */
    public function debug(): array
    {
        return array("Debug" => $this->debug, "Error" => $this->error, "Errno" => $this->errno, "Out" => $this->sent, "Code" =>  $this->http_code, "Size" =>  $this->header_size);
    }

    /**
     * Returns the response from the last cURL request in the specified format.
     *
     * @param string $format The desired format of the response ('array', 'obj', or 'xml').
     * @return array|null The response in the specified format or NULL if there was an error.
     */

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
     * Parses the HTTP status code to determine if there was an error and provides a corresponding message.
     *
     * @return array An array containing a boolean indicating an error and a message.
     */
    public function parseCode(): array
    {
        // Map of HTTP status codes to messages
        $statusMessages = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Large',
            415 => 'Unsupported Media Type',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Time-out',
            505 => 'HTTP Version not supported',
        ];

        // Determine if the response is considered an error
        $error = !($this->http_code >= 200 && $this->http_code < 300);

        // Lookup the message or use a default message for unknown codes
        $msg = $statusMessages[$this->http_code] ?? "Unknown http status code " . $this->http_code;

        return [$error, $msg];
    }


    /**
     * Generates the full URL with query parameters for a GET request.
     *
     * @return string The full URL with query parameters.
     */

    private function generateUrl(): void
    {
        $parsed_string = '';
        $url = parse_url($this->url);

        if (!empty($url['query'])) {
            parse_str($url['query'], $get_data);
            $url['query'] = http_build_query(array_merge($get_data, $this->get_data));
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

        $this->url = $parsed_string;
    }

    /**
     * Parses the MIME type to include UTF-8 encoding if necessary.
     *
     * @return string The MIME type string with encoding if applicable.
     */
    private function parseMimeType(): string
    {
        $mime = ($this->utf8) ? $this->mime . "; charset=utf-8 ;" : $this->mime;

        return $mime;
    }

    /**
     * Converts header names to Proper-Case.
     *
     * @param string $str The header name.
     * @return string The header name in Proper-Case format.
     */
    private function parseStringHeader(string $str): string
    {
        $str = explode('-', $str);

        foreach ($str as &$word) {
            $word = ucfirst($word);
        }

        return implode('-', $str);
    }

    /**
     * Prepares local files for uploading via cURL.
     *
     * @param array $files Array of file paths to upload.
     * @param array $c_files_array The array to populate with CURLFile objects.
     * @return array The array populated with CURLFile objects.
     */
    private function prefabFiles(array $files, array $c_files_array): array
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

    /**
     * Prepares files from the $_FILES array for uploading via cURL.
     *
     * @param array $files The $_FILES array containing file upload information.
     * @param array $c_files_array The array to populate with CURLFile objects.
     * @return array The array populated with CURLFile objects.
     */
    private function prefabFromFiles(array $files, array $c_files_array)
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
