<?php
namespace PassByME\TwoFactor;

/**
 * Sending 2FA request to PassBy[ME] API.
 * For further information on PassBy[ME] please visit: www.passbyme.com and sign up for a free account.
 * You can download our API documentation after login.
 * 
 * @author     Microsec Ltd. <development@passbyme.com>
 * @copyright  (c) 2015, Microsec Ltd.   
 * @version    1.0.0
 */

class Send2FaRequest extends Curl
{
    public function __construct($logger)
    {
        $this->log = $logger;
        parent::__construct($logger);
    }

    /**
     * Sending the request.
     *  
     * @param  string $url  Request URL
     * @param  string $type Request type (GET, POST, PUT, DELETE)
     * @param  string $data Json data
     * @return string       Json 
     */
    public function sendRequest($url, $type, $data = '')
    {
        $this->log->info('Connecting to : ' . $url);
        $caPath = dirname(__FILE__) . '/ca_bundle/cacert.cer';
        $caInfo = empty(Config::get('ca_cert')) ? $caPath : Config::get('ca_cert');
        
        /**
         * cURL
         */
        $this->setUrl($url)
        ->setType($type)
        ->setData($data)
        ->setProxy(Config::get('curl_proxy'))
        ->setProxyport(Config::get('curl_proxyport'))
        ->setSslCert(Config::get('cert_file'))
        ->setSslKey(Config::get('cert_key'))
        ->setCainfo($caInfo)
        ->setPbmApiVersionHeader('1')
        ->setDebug(Config::get('curl_debug'))
        ->send();

        $this->log->info('Request HTTP status code: ' . $this->getStatusCode());
        switch ($this->getStatusCode()) {
            case '200':
            case '201':
                $this->log->info('Request returning with OK.');
                break;
            case '401':
                throw new \Exception('Unauthorized authentication error occured while communicating with PassBy[ME] API.');
            case '403':
                throw new \Exception('You don\'t have permission to access the requested file or resource.');
            case '404':
                throw new \Exception('The ' . $url . ' url you are looking for does not exist.');
            case '420':
                $this->log->warning('Request returning with PassByMe2FA server error.');
                $this->log->warning('Request result: ' . $this->getResponse());
                break;
            case '500':
                throw new \Exception('Internal PassBy[ME] server error.');
            default:
                throw new \Exception('Unexpected http header code from PassByMe2FA server. ' . '(' . $this->getStatusCode() . ')');
        }

        $response = $this->responseContentTypeValidation($this->getResponse(), $this->getContentType());
        return $response;
    }

    /**
     * Validate cURL response by allowed PassBy[ME] Content-Types.
     * (Content-Type Header -> http://www.w3.org/Protocols/rfc1341/4_Content-Type.html)
     * 
     * @return mixed
     */
    private function responseContentTypeValidation($response, $contentType)
    {
        preg_match('/^([a-z\-]+)\/([a-z\-0-9]+)/', $contentType, $matches);
        list($full, $type, $subtype) = $matches;
        /**
         * We allow only "application" type
         */
        if ($type === 'application') {
            switch ($subtype) {
                /**
                 * Allowed subtypes
                 */
                case 'json':
                    $this->log->info('Request result: json string');
                    $this->log->debug('Json string: ' . $response);
                    $response = $this->decodeResponse($response);
                    break;
                case 'pdf':
                case 'x-pkcs12':
                    $this->log->info('Request result: binary (' . $subtype . ')');
                    break;
                case 'x-pem-file':
                    $this->log->info('Request result: .pem file');
                    break;
                default:
                    throw new \Exception('Unsupported Content-Type! Unknown subtype: ' . $subtype);
            }
        } else {
            throw new \Exception('Unsupported Content-Type header! (' . $full . ')');
        }

        return $response;
    }
    /**
     * Decode Json response
     * 
     * @param  json $resp
     * @return mixed      
     */
    private function decodeResponse($resp)
    {
        $this->log->info('Decoding PassBy[ME] json response: ' . $resp);
        $decode = json_decode($resp);
        if ($decode === false or $decode === null) {
            throw new \Exception('Failed to decode response as JSON data.');
        }
        $this->log->info('JSON decoding was successful!');
        
        return $decode;
    }
}
