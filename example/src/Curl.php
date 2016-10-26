<?php

/**
 * Abstracting class for cURL library.
 */
class Curl
{
    /**
     * Body returned by the last request.
     * 
     * @var string
     */
    protected $body;

    /**
     * Actual cURL connection handle.
     * 
     * @var resource
     */
    protected $ch;

    /**
     * Data to send to server.
     * 
     * @var mixed
     */
    protected $data;

    /**
     * Response code from the last request.
     * 
     * @var integer
     */
    protected $status;

    /**
     * Request type.
     * 
     * @var string
     */
    public $type;

    /**
     * Url for the connection.
     * 
     * @var string
     */
    protected $url;
    
    /**
     * Logger instance.
     * 
     * @var object
     */
    public $log;

    /**
     * cURL constructor.
     */
    public function __construct()
    {
        $this->body = null;
        $this->ch = curl_init();
        $this->data = null;
        $this->status = null;
        $this->type = 'GET';
        $this->url = null;
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
    }

    /**
     * cURL close event.
     */
    public function __destruct()
    {
        if (property_exists($this, 'ch')) {
            curl_close($this->ch);
        }
    }

    /**
     * Return the body returned by the last request.
     * 
     * @return string
     */
    public function getResponse()
    {
        return $this->body;
    }

    /**
     * Return the request content type
     * 
     * @return string
     */
    public function getContentType()
    {
        return curl_getinfo($this->ch, CURLINFO_CONTENT_TYPE);
    }

    /**
     * Set the payload for the request.
     * This can either by a string, formatted like a query string or a single-dimensional array.
     * 
     * @param mixed $data
     * @return self
     */
    public function setData($data, $buildQuery = true)
    {
        if (is_array($data)) {
            if ($buildQuery) {
                $data = http_build_query($data);
            }
        }
        $this->data = $data;
        return $this;
    }

    /**
     * Return the status code for the last request.
     * 
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * Set the type of request to make (GET, POST, DELETE)
     * 
     * @param string $type Request type to send.
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $type);
        return $this;
    }

    /**
     * Verifying the peer's certificate
     *
     * @param bool $bool
     * @return $this
     */
    public function setSslVerify($bool = false)
    {
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $bool);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $bool);
        return $this;
    }

    /**
     * Set the URL to make an HTTP connection to.
     * 
     * @param string $url URL to connect to.
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;
        curl_setopt($this->ch, CURLOPT_URL, $url);
        return $this;
    }

    /**
     * Sets the value of sslCert.
     *
     * @param mixed $sslCert The path of a file containing a PEM formatted certificate.
     * @param string $type
     * @return Curl
     */
    public function setSslCert($sslCert, $type = 'PEM')
    {
        curl_setopt($this->ch, CURLOPT_SSLCERTTYPE, $type);
        curl_setopt($this->ch, CURLOPT_SSLCERT, $sslCert);
        return $this;
    }

    /**
     * Sets the value of sslKey.
     *
     * @param mixed $sslKey The path of a file containing a private SSL key.
     * @param string $type
     * @return Curl
     */
    public function setSslKey($sslKey, $type = 'PEM')
    {
        curl_setopt($this->ch, CURLOPT_SSLKEYTYPE, $type);
        curl_setopt($this->ch, CURLOPT_SSLKEY, $sslKey);
        return $this;
    }

    /**
     * Sets the value of cainfo.
     *
     * @param mixed $cainfo the cainfo
     * @return self
     */
    public function setCainfo($cainfo)
    {
        curl_setopt($this->ch, CURLOPT_CAINFO, $cainfo);
        return $this;
    }

     /**
     * Sets the value of connectiontimeout.
     *
     * @param mixed $connectiontimeout the connectiontimeout
     * @return self
     */
    public function setConnectiontimeout($connectiontimeout)
    {
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $connectiontimeout);
        return $this;
    }

    /**
     * Sets the value of timeout.
     *
     * @param mixed $timeout the timeout
     * @return self
     */
    public function setTimeout($timeout)
    {
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);
        return $this;
    }

    /**
     * Sets the value of maxredirs.
     *
     * @param mixed $maxredirs the maxredirs
     * @return self
     */
    public function setMaxredirs($maxredirs)
    {
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, $maxredirs);
        return $this;
    }

    /**
     * The X-PBM-API-VERSION custom HTTP header value of the current using Authentication API version.
     *
     * @param mixed $httpheader the httpheader
     * @return self
     */
    public function setPbmApiVersionHeader($httpheader)
    {
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('X-PBM-API-VERSION: ' . $httpheader ));
        return $this;
    }

    /**
     * Sets the value of proxyport.
     *
     * @param mixed $proxyport the proxyport
     * @return self
     */
    public function setProxyport($proxyport)
    {
        curl_setopt($this->ch, CURLOPT_PROXYPORT, $proxyport);
        return $this;
    }

    /**
     * Sets the value of proxy.
     *
     * @param mixed $proxy the proxy
     * @return self
     */
    public function setProxy($proxy)
    {
        curl_setopt($this->ch, CURLOPT_PROXY, $proxy);
        return $this;
    }

    /**
     * Sets the value of proxytype.
     *
     * @param mixed $proxytype the proxytype
     * @return self
     */
    public function setProxytype($proxytype)
    {
        curl_setopt($this->ch, CURLOPT_PROXYTYPE, $proxytype);
        return $this;
    }

    /**
     * Sets the value of proxyuserpwd.
     *
     * @param mixed $proxyuserpwd the proxyuserpwd
     * @return self
     */
    public function setProxyuserpwd($proxyuserpwd)
    {
        curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $proxyuserpwd);
        return $this;
    }

    /**
     * Sets the value of useragent.
     *
     * @param mixed $useragent the useragent
     * @return self
     */
    public function setUseragent($useragent)
    {
        curl_setopt($this->ch, CURLOPT_USERAGENT, $useragent);
        return $this;
    }

    /**
     * Send the request.
     * @return Curl
     * @throws \Exception
     */
    public function send()
    {
        switch (strtolower($this->type)) {
            case 'get':
            case 'delete':
                if ($this->data) {
                    $this->url .= '?' . $this->data;
                }
                break;
            case 'post':
            case 'put':
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->data);
                break;
            default:
                throw new Exception('Unsupported request type: ' . $this->type);
        }

        $this->body = curl_exec($this->ch);
        if($error = curl_errno($this->ch)) {
            throw new Exception('cURL error code: ' . $error);
        }
        $this->status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        return $this;
    }
}
