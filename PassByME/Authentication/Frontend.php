<?php
namespace PassByME\Authentication;

use PassByME\TwoFactor\Send2FaRequest;
use PassByME\TwoFactor\Config;

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
 * @version    1.0.1
 */

/**
 * This library provides you with functionality to handle PassBy[ME] authentications.
 * For further information on PassBy[ME] please visit: www.passbyme.com
 *
 * Usage:
 * $pbm = new PassByME\Authentication\Frontend($logger);
 * $pbm->authenticationRequest($userID, $message);
 * $pbm->authenticationProgress($session);
 * 
 */

class Frontend extends Send2FaRequest
{
    public $log;

    /**
     * Gets the value of log.
     *
     * @return mixed
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Sets the value of log.
     *
     * @param mixed $logger the log
     * @return self
     */
    public function setLog($logger)
    {
        $this->log = $logger ? $logger : new Log\Logger();
        return $this;
    }

    public function __construct($logger = null)
    {
        $this->setLog($logger);
        parent::__construct($this->getLog());
    }

    /**
     * Starts a new 2nd factor authentication for a given user by sending a notification to the user's device.
     *
     * @param  string $userIdentifier PassBy[ME] user identifier
     * @param  string $msg An arbitrary message to the user. Maximum size is 255 characters.
     * @param  integer $timeout Length of the second factor authentication session in seconds.
     * @return string The request returns the identifier of the created session in json form.
     * @throws \Exception
     */
    public function authenticationRequest($userIdentifier = '', $msg = '', $timeout = 0)
    {
        $this->log->info('Sending authentication request to PassBy[ME] API.');
        $json_array = array(
            'userId' => $userIdentifier,
            'message' => $msg
        );

        if (is_int($timeout) and $timeout > 0) {
            $json_array['timeout'] = $timeout;
        }

        $result = $this->sendRequest(
            Config::get('aut_api_url') . '/authenticationRequest',
            'POST',
            json_encode($json_array)
        );
        /**
         * If any error occured
         */
        if (isset($result->code)) {
            $this->log->info('PBM server response code: ' . $result->code);
            switch ($result->code) {
                case 'NO_DEVICE':
                    throw new \Exception(
                        'User has no registered PassByMe2FA device. PassByMe2FA authentication is not possible.'
                    );
                case 'CERTIFICATE_REVOKED':
                    throw new \Exception('Client certificate has been revoked.');
                case 'SUBSCRIPTION_EXPIRED':
                    throw new \Exception('Your subscription has been expired.');
                case 'USER_DISABLED':
                    throw new \Exception('This user has been disabled.');
                case 'FORBIDDEN':
                    throw new \Exception('Account is not authorized for this operation.');
                case 'UNSUPPORTED_API_VERSION':
                    throw new \Exception('Unsupported API version! ' . $result->message . '.');
                case 'MALFORMED_INPUT':
                    throw new \Exception($result->message);
                default:
                    throw new \Exception('Unknown PassByMe2FA authentication error: ' . $result->code);
            }
        }
        /**
         * If there is no error we return the sessionId
         */
        $this->log->info('Received session from PBM server: ' . $result);
        return $result;
    }

    /**
     * This returns the status of an authentication, identified by the given sessionId.
     * The sessionId is obtained by calling the authenticationRequest method.
     *
     * @param  string $session The ID of the authentication session
     * @return string On success it returns the status of the authentication transaction in json form.
     * @throws \Exception
     */
    public function authenticationProgress($session)
    {
        $this->log->info('Sending progression check request to PassBy[ME] API.');
        $result = $this->sendRequest(
            Config::get('aut_api_url') . '/authenticationProgress/' . $session,
            'GET'
        );
        if (isset($result->code)) {
            switch ($result->code) {
                case 'FORBIDDEN':
                    throw new \Exception('Account is not authorized for this operation.');
                case 'MALFORMED_INPUT':
                    throw new \Exception('Input is not well formed.');
                case 'INVALID_SESSION_ID':
                    throw new \Exception('The given PassByMe2FA session id is invalid.');
                case 'CERTIFICATE_REVOKED':
                    throw new \Exception('Client certificate has been revoked.');
                default:
                    throw new \Exception('Unknown PassByMe2FA progression error: ' . $result->code);
            }
        }
        $this->log->info('Received message from server: ' . $result);
        return $result;
    }

    /**
     * This function cancels an existing authentication session, identified by the given sessionId.
     *
     * @param  string $session The ID of the authentication session
     * @return string
     * @throws \Exception
     */
    public function authenticationCancel($session)
    {
        $this->log->info('Sending cancel request to PassBy[ME] API.');
        $result = $this->sendRequest(
            Config::get('aut_api_url') . '/authenticationCancel/' . $session,
            'DELETE'
        );
        if (isset($result->code)) {
            switch ($result->code) {
                case 'FORBIDDEN':
                    throw new \Exception('Account is not authorized for this operation.');
                case 'CERTIFICATE_REVOKED':
                    throw new \Exception('Client certificate has been revoked.');
                case 'MALFORMED_INPUT':
                    throw new \Exception($result->message);
                default:
                    throw new \Exception('Unknown PassByMe2FA error while canceling request: ' . $result->code);
            }
        }
        return $result;
    }
}
