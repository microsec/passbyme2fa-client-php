<?php

/**
 * @package passbyme2fa-client-auth-php
 * @author Microsec Ltd. <development@passbyme.com>
 * @since 2016.08.04.
 * @copyright (c) 2016, Microsec Ltd.
 */
require_once __DIR__ . '/../src/Client.php';

class Authorization extends Client
{
    protected function work()
    {
        $frontend = $this->getFrontend();
        if ($this->getCancel()) {
            $this->jsonResponse = $frontend->cancelMessage($this->getMessageId());
        } else {
            if ($this->getPolling() == true) {
                $this->jsonResponse = $frontend->trackMessage($this->getMessageId());
            } else {
                //Send request
                if ($this->getUserId() and $this->getSubject() and $this->getBody()) {
                    $this->jsonResponse = $frontend->authorizationMessage(
                        $this->getUserId(),
                        $this->getSubject(),
                        $this->getBody()
                    );
                } else {
                    throw new Exception("Please fill all fields!");
                }
            }
        }
    }
}

$authorization = new Authorization();
$authorization->run();