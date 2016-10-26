<?php
namespace PassByME\Methods;

use PassByME\Log\Logger;
use PassByME\TwoFactor\Config;
use PassByME\TwoFactor\PBMErrorException;
use PassByME\TwoFactor\Send2FaRequest;

/**
 * MIT License
 * ===========
 *
 * Copyright (c) 2015 Microsec Ltd. <development@passbyme.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author     Microsec Ltd. <development@passbyme.com>
 * @copyright  (c) 2015, Microsec Ltd.
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version    2.0.0
 */

/**
 * This library provides you with functionality to handle PassBy[ME] messaging.
 * For further information on PassBy[ME] please visit: www.passbyme.com
 */

class Messaging extends Send2FaRequest
{
    private $aut_url;
    public function __construct($logger = null)
    {
        $log = $logger ? $logger : new Logger();
        $sslCert = Config::get('auth_cert');
        $sslCertPwd = Config::get('auth_pwd');
        parent::__construct($log);
        parent::setSslCert($sslCert);
        parent::setSslCertPwd($sslCertPwd);
        parent::setPbmApiVersionHeader('1');
        $this->aut_url = Config::get('auth_url');
    }

    /*************************** MESSAGING ********************************/

    /**
     * Set message.
     *
     * @param array $userIdentifier A JSON Array of PassBy[ME] IDs.
     * @param string $subject A JSON String that contains the subject. Maximum size is 254 characters.
     * @param string $body A JSON String that contains the message body. Maximum size is 4094 characters.
     * @param integer $availability A JSON integer that denotes the availability of the message in seconds.
     * @param string $type Type of the message: "message", "esign" or "authorization"
     * @return object|string
     * @throws PBMErrorException
     */
    private function message($userIdentifier, $subject, $body, $availability, $type)
    {
        return parent::prepareAndSend(
            $this->aut_url . '/messages',
            'POST',
            array(
                'recipients' => $userIdentifier,
                'subject' => $subject,
                'body' => $body,
                'availability' => $availability,
                'type' => $type
            )
        );
    }

    /**
     * Sends general message for a given user by sending a notification to the user's device.
     *
     * @param array $userIdentifier
     * @param string $subject
     * @param string $body
     * @param int $availability
     * @return object|string
     */
    public function generalMessage($userIdentifier = array(), $subject = '', $body = '', $availability = 300)
    {
        $this->log->debug('Sending general message request to PassBy[ME] API.');
        return $this->message($userIdentifier, $subject, $body, $availability, 'message');
    }

    /**
     * Sends an e-sign message for a given user by sending a notification to the user's device.
     *
     * @param array $userIdentifier
     * @param string $subject
     * @param string $body
     * @param int $availability
     * @return object|string
     */
    public function eSignMessage($userIdentifier = array(), $subject = '', $body = '', $availability = 300)
    {
        $this->log->debug('Sending eSign message request to PassBy[ME] API.');
        return $this->message($userIdentifier, $subject, $body, $availability, 'esign');
    }

    /**
     * Sends authorization message for a given user by sending a notification to the user's device.
     *
     * @param array $userIdentifier
     * @param string $subject
     * @param string $body
     * @param int $availability
     * @return object|string
     */
    public function authorizationMessage($userIdentifier = array(), $subject = '', $body = '', $availability = 300)
    {
        $this->log->debug('Sending authorization message request to PassBy[ME] API.');
        return $this->message($userIdentifier, $subject, $body, $availability, 'authorization');
    }

    /**
     * This returns the status of a message, identified by the given messageId.
     *
     * @param $messageId
     * @return object|string
     */
    public function trackMessage($messageId)
    {
        $this->log->debug('Sending tracking message request to PassBy[ME] API.');
        return parent::prepareAndSend(
            $this->aut_url . '/messages/' . $messageId,
            'GET'
        );
    }

    /**
     * This cancels a message, identified by the given messageId.
     *
     * @param  string $messageId The ID of the authentication session
     * @return object|string
     */
    public function cancelMessage($messageId)
    {
        $this->log->debug('Sending cancel message request to PassBy[ME] API.');
        return parent::prepareAndSend(
            $this->aut_url . '/messages/' . $messageId,
            'DELETE'
        );
    }
}
