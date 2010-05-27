<?php

/**
 * Fari Framework
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link      http://radekstepan.com
 * @category  Fari Framework
 */



/**
 * Connection to APIs.
 *
 * @copyright Copyright (c) 2008, 2010 Radek Stepan
 * @package   Fari Framework\Api
 */
class Fari_Api {

    /** @var cURL session */
    private $cURL;

    /** @var URL to prepend before each request */
    private $baseURL;

    /** @var when set, cURL will authenticate */
    private $credentials;

    /**
     * Setup cURL session.
     */
    public function __construct() {
        $this->cURL = curl_init();
    }

    /**
     * Set base URL to use.
     * @param string $URL
     */
    public function setBaseURL($URL) {
        // prepend http if needed
        if (substr(strtolower($URL), 0, 4) != 'http') $URL = "http://";
        // validate
        assert('Fari_Filter::isURL($URL); // malformed url "' . $URL . '"');
        $this->baseURL = $URL;
    }

    /**
     * Set credentials to autenticate with.
     * @param string $username
     * @param string $password
     */
    public function setCredentials($username, $password) {
        assert('is_string($username) && is_string($password); // username & password need to be strings');
        $this->credentials = "{$username}:{$password}";
    }

    /**
     * cURL POST.
     * @param string $URL
     * @param string $format return JSON/XML
     * @param mixed $data to POST
     * @return mixed
     */
    private function post($URL, $format, $data) {
        assert('!empty($format) && is_string($format); // pass format of the data as a string');

        // array
        if (is_array($data)) {
            curl_setopt($this->cURL, CURLOPT_POST, count($data));

            // urlify & encode
            foreach ($data as $key => &$value) $key = $key . '=' . urlencode($value);
            $data = implode('&', $data);

            curl_setopt($this->cURL, CURLOPT_POSTFIELDS, $data);
        // string... hopefully
        } else {
            assert('is_string($data); // malformed data, pass string or an array');
            curl_setopt($this->cURL, CURLOPT_POST, 1);

            // replace newlines if present
            $data = str_replace("\n", "", $data);
            
            curl_setopt($this->cURL, CURLOPT_POSTFIELDS, $data);
        }

        // setup
        if (count($formats = splitCamelCase($format)) > 1) {
            assert('$formats[1] == "Get" && count($formats) == 3; // malformed format query');
            $this->setupSession($URL, $formats[2], $formats[0]);
        } else {
            $this->setupSession($URL, $format);
        }

        // execute
        $data = curl_exec($this->cURL);
        curl_close($this->cURL);

        // decode JSON?
        return $data;
    }

    /**
     * cURL GET.
     * @param string $URL
     * @param string $format return JSON/XML
     * @return mixed
     */
    private function get($URL, $format) {
        assert('!empty($format) && is_string($format); // pass format of the data as a string');

        // setup
        $this->setupSession($URL, $format);
        
        // execute
        $data = curl_exec($this->cURL);
        curl_close($this->cURL);

        // decode JSON?
        return $data;
    }

    /**
     * Setup cURL session.
     * @param string $URL
     * @param string $replyFormat JSON, XML to retrieve
     * @param string $sendFormat JSON, XML to send
     */
    private function setupSession($URL, $replyFormat, $sendFormat=NULL) {
        // do we have a base URL set?
        if (isset($this->baseURL)) {
            $URL = $this->baseURL . $URL;
        }

        assert('Fari_Filter::isURL($URL); // malformed url "' . $URL . '"');

        // set the URL
        curl_setopt($this->cURL, CURLOPT_URL, $URL);

        // authenticate?
        if (isset($this->credentials)) {
            curl_setopt($this->cURL, CURLOPT_USERPWD, $this->credentials);
            curl_setopt($this->cURL, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($this->cURL, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($this->cURL, CURLOPT_RETURNTRANSFER, 1);
        }

        // format
        $headers[] = 'Accept: ' . $this->whatContentType($replyFormat);
        if (isset($sendFormat)) $headers[] = 'Content-type: ' . $this->whatContentType($sendFormat);
        curl_setopt($this->cURL, CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * Content types.
     * @param string $format
     * @return string
     */
    private function whatContentType($format) {
        try {
            switch (strtolower($format)) {
                case 'json':
                    return 'application/json';
                    break;
                case 'xml':
                    return 'application/xml';
                    break;
                default:
                    throw new Fari_Exception("Unknown format '{$format}'");
                    break;
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }
    }


    /**
     * Method overloader.
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function __call($method, $params) {
        try {
            // determine the method called
            if (!preg_match('/^(get|post)(\w+)$/', $method, $matches)) {
                throw new Fari_Exception("Call to undefined method {$method}");
            }
            
            // what do you want?
            switch ($matches[1]) {
                // GET resource
                case 'get':
                    return $this->get(current($params), $matches[2]);
                    break;
                // POST resource
                case 'post':
                    assert('count($params) == 2; // you need to pass 2 parameters');
                    return $this->post($params[0], $matches[2], $params[1]);
                    break;
            }

        } catch (Fari_Exception $exception) { $exception->fire(); }
    }

}