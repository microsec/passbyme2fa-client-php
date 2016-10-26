<?php
/**
 * @package passbyme2fa-client-auth-php
 * @author Microsec Ltd. <development@passbyme.com>
 * @since 2016.08.03.
 * @copyright (c) 2016, Microsec Ltd.
 */
use PassByME\Methods\Messaging;
use PassByME\TwoFactor\PBMErrorException;

require_once __DIR__ . '/../../PassByME/Autoloader.php';
require_once __DIR__ . '/../conf/config.php';

abstract class Client
{
    private $messageId;
    private $userId;
    private $action;
    private $body;
    private $subject;
    private $cancel;
    private $pbmResponse;
    private $polling;
    private $frontend;
    public $jsonResponse;

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return mixed
     */
    public function getPolling()
    {
        return $this->polling;
    }

    /**
     * @return mixed
     */
    public function getPbmResponse()
    {
        return $this->pbmResponse;
    }

    /**
     * @param mixed $pbmResponse
     */
    public function setPbmResponse($pbmResponse)
    {
        $this->pbmResponse = $pbmResponse;
    }

    /**
     * @return Messaging
     */
    public function getFrontend()
    {
        return $this->frontend;
    }

    /**
     * @return mixed
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return mixed
     */
    public function getCancel()
    {
        return $this->cancel;
    }

    /**
     * Client constructor.
     */
    public function __construct()
    {
        //This is an example. Feel free to implement input validation if you want to reuse this code.
        $this->userId = $this->validatePOST('userId', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $this->body = $this->validatePOST('body', FILTER_SANITIZE_STRING);
        $this->action = $this->validatePOST('action', FILTER_SANITIZE_STRING);
        $this->subject = $this->validatePOST('subject', FILTER_SANITIZE_STRING);
        $this->cancel = $this->validatePOST('cancel', FILTER_VALIDATE_BOOLEAN);
        //Extra params for polling
        $this->messageId = $this->validatePOST('messageId', FILTER_SANITIZE_STRING);
        $this->polling = $this->validatePOST('polling', FILTER_VALIDATE_BOOLEAN);

        $this->jsonResponse = array();
        $this->frontend = new Messaging();
    }

    private function validatePOST($param, $filter = FILTER_DEFAULT, $options = array())
    {
        return filter_input(INPUT_POST, $param, $filter, $options);
    }

    abstract protected function work();

    public function run()
    {
        ob_start();
        try {
            $this->work();
        } catch (PBMErrorException $exc) {
            $this->jsonResponse = array('errormsg' => 'PassByME error: ' . $exc->getMessage());
        } catch (Exception $exc) {
            $this->jsonResponse = array('errormsg' => 'Client application error: ' . $exc->getMessage());
        }
        $this->jsonResponse = new ArrayObject($this->jsonResponse);
        $this->jsonResponse->offsetSet('stdout', ob_get_contents());
        ob_clean();
        print json_encode($this->jsonResponse);
    }
}