<?php
/**
 * @package passbyme2fa-client-auth-php
 * @author Microsec Ltd. <development@passbyme.com>
 * @since 2016.08.03.
 * @copyright (c) 2016, Microsec Ltd.
 */

require_once __DIR__ . '/../src/Client.php';

class Messaging extends Client
{
    protected function work()
    {
        $frontend = $this->getFrontend();

        //Check request progression by the request PBMSession
        if ($this->getPolling() == true) {
            if ($frontend->isError()) {
                throw new Exception($frontend->getErrorMsg());
            }
            $this->jsonResponse = $frontend->trackMessage(urldecode($this->getMessageId()));
        } else {
            //Send request
            if ($this->getUserId()) {
                $this->setPbmResponse($frontend->generalMessage(
                    $this->getUserId(),
                    $this->getSubject(),
                    $this->getBody()
                ));
                if ($frontend->isError()) {
                    throw new Exception($frontend->getErrorMsg());
                } else {
                    $this->jsonResponse = $this->getPbmResponse();
                }
            } else {
                throw new Exception("Missing PassBy[ME] user identifier!");
            }
        }
    }
}

$message = new Messaging();
$message->run();