<?php
namespace PassByME\TwoFactor;

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
    protected $type;

    /**
     * Url for the connection.
     *
     * @var string
     */
    protected $url;

    /**
     * If true write cURL connection information's to log.
     *
     * @var boolean
     */
    protected $debug;

    /**
     * Logger instance.
     *
     * @var object
     */
    protected $log;

    /**
     * cURL constructor.
     *
     * @param object $logger
     */
    public function __construct($logger)
    {
        $this->body = null;
        $this->ch = curl_init();
        $this->data = null;
        $this->status = null;
        $this->type = 'GET';
        $this->url = null;
        $this->debug = false;
        $this->cainfo = '';
        $this->sslcert = '';
        $this->sslcertPwd = '';
        $this->log = $logger;
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($this->ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, Config::get('curl_follow_location'));
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
    }

    /**
     * Return the body returned by the last request.
     *
     * @return string
     */
    protected function getResponse()
    {
        return $this->body;
    }

    /**
     * Return the request content type
     *
     * @return string
     */
    protected function getContentType()
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
    protected function setData($data)
    {
        if (is_array($data)) {
            $data = http_build_query($data);
        }
        $this->data = $data;
        return $this;
    }

    /**
     * Return the status code for the last request.
     *
     * @return integer
     */
    protected function getStatusCode()
    {
        return $this->status;
    }

    /**
     * Set the type of request to make (GET, POST, DELETE)
     *
     * @param string $type Request type to send.
     * @return self
     */
    protected function setType($type)
    {
        $this->type = $type;
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $type);
        return $this;
    }

    /**
     * Set the URL to make an HTTP connection to.
     *
     * @param string $url URL to connect to.
     * @return self
     */
    protected function setUrl($url)
    {
        $this->url = $url;
        curl_setopt($this->ch, CURLOPT_URL, $url);
        return $this;
    }

    /**
     * Sets the value of sslCert.
     *
     * @param mixed $sslCert The path of a file containing a PEM formatted certificate.
     * @return self
     */
    protected function setSslCert($sslCert)
    {
        $this->sslcert = $sslCert;
        curl_setopt($this->ch, CURLOPT_SSLCERT, $sslCert);
        return $this;
    }

    /**
     * Sets certificate password.
     *
     * @param $pwd
     * @return static
     * @throws PBMErrorException
     */
    protected function setSslCertPwd($pwd)
    {
        $this->sslcertPwd = $pwd;
        curl_setopt($this->ch, CURLOPT_SSLCERTPASSWD, $pwd);
        return $this;
    }

    /**
     * Sets the path of CA certificate file.
     *
     * @param mixed $caInfo
     * @return self
     */
    protected function setCaInfo($caInfo)
    {
        $this->cainfo = $caInfo;
        curl_setopt($this->ch, CURLOPT_CAINFO, $caInfo);
        return $this;
    }

    /**
     * Sets the value of connection timeout.
     *
     * @param mixed $connectionTimeout
     * @return self
     */
    protected function setConnectionTimeout($connectionTimeout)
    {
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $connectionTimeout);
        return $this;
    }

    /**
     * Sets the value of timeout.
     *
     * @param mixed $timeout the timeout
     * @return self
     */
    protected function setTimeout($timeout)
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
    protected function setMaxredirs($maxredirs)
    {
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, $maxredirs);
        return $this;
    }

    /**
     * The X-PBM-API-VERSION custom HTTP header value of the current using Authentication API version.
     *
     * @param mixed $httpHeader
     * @return self
     */
    protected function setPbmApiVersionHeader($httpHeader)
    {
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('X-PBM-API-VERSION: ' . $httpHeader));
        return $this;
    }


    /**
     * Sets the value of proxyport.
     *
     * @param mixed $proxyport the proxyport
     * @return self
     */
    protected function setProxyport($proxyport)
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
    protected function setProxy($proxy)
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
    protected function setProxytype($proxytype)
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
    protected function setProxyuserpwd($proxyuserpwd)
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
    protected function setUseragent($useragent)
    {
        curl_setopt($this->ch, CURLOPT_USERAGENT, $useragent);
        return $this;
    }

    /**
     * Sets the value of debug.
     *
     * @param boolean $debug the debug
     * @return self
     */
    protected function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }


    /**
     * Check certificates
     *
     * @param $cert
     * @throws PBMErrorException
     */
    private function checkCert($cert)
    {
        $certData = $this->isCertAvailable($cert);
        $from = new \DateTime();
        $from = $from->setTimestamp($certData['validFrom_time_t']);
        $to = new \DateTime();
        $to = $to->setTimestamp($certData['validTo_time_t']);
        $now = new \DateTime('now');
        $this->log->debug('Certificate: ' . $cert);
        $this->log->debug('Certificate valid between: ' . $from->format('Y-m-d') . ' and ' . $to->format('Y-m-d'));
        if (!($from < $now and $to > $now)) {
            throw new PBMErrorException('Certificate has expired!');
        }
    }

    /**
     * Check certificate file configuration
     *
     * @param $certFile
     * @return array
     * @throws PBMErrorException
     */
    private function isCertAvailable($certFile)
    {
        if (!empty($certFile)) {
            $cert = file_get_contents($certFile);
            if ($cert === false) {
                throw new PBMErrorException('Can not read cert file! (' . $certFile . ')');
            } else {
                $certData = openssl_x509_parse($cert);
                if (is_array($certData)) {
                    return $certData;
                } else {
                    throw new PBMErrorException('Can not read certificate properties.');
                }
            }
        } else {
            throw new PBMErrorException('No certificate file found!');
        }
    }

    /**
     * Send the request.
     * @return Curl
     * @throws PBMErrorException()
     */
    protected function send()
    {
        $this->log->debug('Checking connection certificates.');
        $this->checkCert($this->cainfo);
        $this->checkCert($this->sslcert);
        if (!$this->sslcertPwd) {
            throw new PBMErrorException('Missing certificate password!');
        }
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
                throw new PBMErrorException('Unsupported request type: ' . $this->type);
        }

        $curl_file = tempnam('', 'res');
        $handle = fopen($curl_file, 'w');

        if ($this->debug) {
            curl_setopt($this->ch, CURLOPT_VERBOSE, true);
            curl_setopt($this->ch, CURLOPT_STDERR, $handle);
        }
        $this->body = curl_exec($this->ch);
        $this->status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if ($error = curl_errno($this->ch)) {
            $this->log->error('cURL error code: ' . $error);
            $this->log->error('cURL message: ' . curl_error($this->ch));
        }
        if ($this->debug) {
            $this->log->debug('Curl result: ' . file_get_contents($curl_file));
        }
        fclose($handle);
        unlink($curl_file);

        return $this;
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
}
