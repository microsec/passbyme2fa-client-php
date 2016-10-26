<?php
/**
 * Sending 2FA request to PassBy[ME] API.
 * For further information on PassBy[ME] please visit: www.passbyme.com and sign up for a free account.
 */

namespace PassByME\TwoFactor;

class Send2FaRequest extends Curl
{
    private $error;
    private $errorCode;
    private $errorMsg;

    /**
     * @return mixed
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    /**
     * @param mixed $errorMsg
     */
    private function setErrorMsg($errorMsg)
    {
        $this->errorMsg = $errorMsg;
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param mixed $errorCode
     */
    private function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
    }

    public function isError()
    {
        return $this->error;
    }

    public function __construct($logger)
    {
        $this->log = $logger;
        parent::__construct($logger);
        $this->log->debug('-------------------------------------- NEW REQUEST --------------------------------------');
    }

    /**
     * Sending the request itself using cURL.
     *
     * @param  string $url Request URL
     * @param  string $type Request type (GET, POST, PUT, DELETE)
     * @param  string $data Json data
     * @return string Json
     * @throws PBMErrorException()
     */
    private function sendRequest($url, $type, $data = '')
    {
        $this->error = false;
        $this->setErrorCode('');
        $this->setErrorMsg('');
        $this->log->info('Sending ' . $type . ' data to: ' . $url);
        $this->log->debug('Proxy settings: ' . Config::get('curl_proxy') . ':' . Config::get('curl_proxyport'));
        $this->log->debug('Data is sent: ' . print_r($data, true));
        $caBundle = __DIR__ . '/ca_bundle/cacert.cer';
        $caCert = Config::get('ca_cert');
        $caInfo = empty($caCert) ? $caBundle : $caCert;

        /**
         * cURL
         */
        $this->setUrl($url)
            ->setType($type)
            ->setData($data)
            ->setProxy(Config::get('curl_proxy'))
            ->setProxyport(Config::get('curl_proxyport'))
            ->setCaInfo($caInfo)
            ->setDebug(Config::get('curl_debug'))
            ->send();

        $this->log->info('Request HTTP status code: ' . $this->getStatusCode());
        switch ($this->getStatusCode()) {
            case '200':
                $this->log->info('Request returning with OK.');
                break;
            case '401':
                throw new PBMErrorException('Unauthorized authentication error occurred while communicating with PassBy[ME] API.');
            case '403':
                throw new PBMErrorException('You don\'t have permission to access the requested file or resource while communicating with PassBy[ME] API.');
            case '404':
                throw new PBMErrorException('The ' . $url . ' url you are looking for does not exist.');
            case '420':
                $this->log->error('Request returning with PassByMe2FA server error.');
                $this->log->error('Request result: ' . $this->getResponse());
                $this->error = true;
                $this->errorCodes($this->decodeResponse($this->getResponse()));
                break;
            case '500':
                throw new PBMErrorException('Internal PassBy[ME] server error.');
            default:
                throw new PBMErrorException('Unexpected http header code found ' . '(' . $this->getStatusCode() . ') when trying to connect to ' . $url);
        }

        $response = $this->responseContentTypeValidation($this->getResponse(), $this->getContentType());
        return $response;
    }

    /**
     * Validate cURL response by allowed PassBy[ME] Content-Types.
     * (Content-Type Header -> http://www.w3.org/Protocols/rfc1341/4_Content-Type.html)
     *
     * @param $response
     * @param $contentType
     * @return mixed
     * @throws PBMErrorException()
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
                    throw new PBMErrorException('Unsupported Content-Type! Unknown subtype: ' . $subtype);
            }
        } else {
            throw new PBMErrorException('Unsupported Content-Type header! (' . $full . ')');
        }

        return $response;
    }

    /**
     * Decode Json response
     *
     * @param  $resp
     * @return mixed
     * @throws PBMErrorException()
     */
    private function decodeResponse($resp)
    {
        $this->log->info('Decoding PassBy[ME] json response: ' . $resp);
        $decode = json_decode($resp);
        if ($decode === null) {
            throw new PBMErrorException('Failed to decode response as JSON data.');
        }
        $this->log->info('JSON decoding was successful!');

        return $decode;
    }

    /**
     * Prepare and send the request to PassBy[ME] API
     *
     * @param $url
     * @param $type
     * @param array $requestData
     * @return string
     * @throws PBMErrorException()
     */
    protected function prepareAndSend($url, $type, $requestData = array())
    {
        switch ($type) {
            case 'GET':
                $parsedUrl = parse_url($url);
                if(isset($parsedUrl['query'])) {
                    $this->log->debug('Request query parameters: ' . $parsedUrl['query']);
                } else {
                    $this->log->debug('Request has no query parameters');
                }
                $result = $this->sendRequest($url, 'GET');
                $this->log->debug('Received result from PassBy[ME] API: ' . json_encode($result));
                break;
            case 'POST':
            case 'PUT':
                $jsonString = json_encode($requestData);
                if ($jsonString) {
                    if (empty($requestData)) {
                        $this->log->debug('Request has no parameters!');
                    } else {
                        $this->log->debug('Request json parameters: ' . $jsonString);
                    }
                    $result = $this->sendRequest($url, $type, $jsonString);
                } else {
                    throw new PBMErrorException('Json encode failed of given string: ' . $requestData);
                }
                $this->log->info('Received result from PassBy[ME] API: ' . json_encode($result));
                break;
            case 'DELETE':
                $result = $this->sendRequest($url, 'DELETE');
                $this->log->info('Received result from PassBy[ME] API: ' . json_encode($result));
                break;
            default:
                throw new PBMErrorException("Unsupported request type:" . $type, 1);
                break;
        }
        return $result;
    }

    private function errorCodes($errorResult)
    {
        $this->log->info('PassBy[ME] response code: ' . $errorResult->code);
        if (isset($errorResult->message) and !empty($errorResult->message)) {
            $this->log->info('PassBy[ME] API response message: ' . $errorResult->message);
        }

        switch ($errorResult->code) {
            case 'INVALID_SESSION_ID':
            case 'TIMEOUT':
            case 'ALREADY_COMPLETED':
            case 'CERTIFICATE_REVOKED':
            case 'NO_DEVICE':
            case 'PRICING_LIMIT':
            case 'PRICING_LIMIT_ADMIN':
            case 'PRICING_LIMIT_USER':
            case 'PRICING_LIMIT_DEVICE':
            case 'PRICING_LIMIT_INVITATION':
            case 'PRICING_LIMIT_UPGRADE':
            case 'INSUFFICIENT_BALANCE':
            case 'NOT_SUBSCRIBED_TO_SERVICE':
            case 'ALREADY_EXISTS':
            case 'MALFORMED_INPUT':
            case 'NOT_FOUND':
            case 'FORBIDDEN':
            case 'SUBSCRIPTION_EXPIRED':
            case 'USER_DISABLED':
            case 'UNSUPPORTED_API_VERSION':
            case 'SERVICE_UNAVAILABLE':
            case 'PAYMENT_FAILURE':
            case 'INVALID_VAT_NUMBER':
                $this->setErrorCode($errorResult->code);
                $this->setErrorMsg($errorResult->message);
                break;
            case 'ERROR':
                throw new PBMErrorException('PassBy[ME] error occurred.');
            default:
                throw new PBMErrorException('Unknown PassBy[ME] API response: ' . $errorResult->code . '.');
        }
    }
}
