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
 * Copyright (c) 2016 Microsec Ltd. <development@passbyme.com>
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
 * @copyright  (c) 2016, Microsec Ltd.
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version    1.0.0
 */

/**
 * This library provides you with functionality to handle PassBy[ME] management API.
 * For further information on PassBy[ME] please visit: www.passbyme.com
 */
class Management extends Send2FaRequest
{
    private $mng_url;

    public function __construct($logger = null)
    {
        $log = $logger ? $logger : new Logger();
        $sslCert = Config::get('mng_cert');
        $sslCertPwd = Config::get('mng_pwd');
        parent::__construct($log);
        parent::setSslCert($sslCert);
        parent::setSslCertPwd($sslCertPwd);
        parent::setPbmApiVersionHeader('2');
        $this->mng_url = Config::get('mng_url');
    }

    /*************************** USER MANAGEMENT ********************************/

    /**
     * Creates a new PassBy[ME] user.
     *
     * @param string $userId [required] The PassBy[ME] ID of a user.
     * @param string $email [required] Email address of the user.
     * @param string $fullName [required] Full name of the user.
     * @param string $phoneNumber [optional] The phone number of the user.
     * @return mixed
     * @throws PBMErrorException
     */
    public function createUser($userId, $email, $fullName = '', $phoneNumber = '')
    {
        $this->log->debug('Sending create new user request to PassBy[ME] management API.');
        $ret = parent::prepareAndSend(
            $this->mng_url . '/rest/users',
            'POST',
            array_filter(array(
                'userId' => $userId,
                'email' => $email,
                'fullName' => $fullName,
                'phoneNumber' => $phoneNumber
            ))
        );
        $this->log->debug('New user ' . $fullName . '[' . $email . '] created.');
        return $ret;
    }

    /**
     * Returns the list of PassBy[ME] users.
     *
     * @return object|string
     * @throws PBMErrorException
     */
    public function getListOfUsers()
    {
        $this->log->debug('Sending get list of users request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/users',
            'GET'
        );
    }

    /**
     * Get the number of users in the account.
     *
     * @return string
     * @throws PBMErrorException
     */
    public function getUsersNumber()
    {
        $this->log->debug('Sending get number of users request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/users/count',
            'GET'
        );
    }

    /**
     * Find the user with the given OID.
     *
     * @param string $oid The OID of the user.
     * @return object|string
     * @throws PBMErrorException
     */
    public function getUser($oid)
    {
        $this->log->debug('Sending get user request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/users/' . $oid,
            'GET'
        );
    }

    /**
     * Deletes the user with the given OID.
     *
     * @param string $oid The OID of the user to be deleted.
     * @return mixed
     * @throws PBMErrorException
     */
    public function deleteUser($oid)
    {
        $this->log->debug('Deleting user with PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/users/' . $oid,
            'DELETE'
        );
    }

    /**
     * Modify the user with the given OID.
     *
     * @param string $oid The OID of the selected user.
     * @param object $modifiedUserObj A UserData object contains the following properties with the modified data:
     * - email          => 'user01@email.com',  The email address of the user.
     * - fullName       => 'User 01',           The email address of the user.
     * - phoneNumber    => '12345678'           The phone number of the user.
     * - disabled       => true                 True if the user is disabled, false otherwise.
     * @return mixed
     * @throws PBMErrorException
     */
    public function modifyUser($oid, $modifiedUserObj)
    {
        $this->log->debug('Sending modify user data request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/users/' . $oid,
            'PUT',
            (array) $modifiedUserObj
        );
    }

    /**
     * Create a new enrollment for the given user.
     *
     * @param string $oid The OID of the user.
     * @return object|string
     * @throws PBMErrorException
     */
    public function createEnrollment($oid)
    {
        $this->log->debug('Sending create new enrollment request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/users/' . $oid . '/enrollments',
            'POST'
        );
    }

    /**
     * Returns the active enrollments of the given user.
     *
     * @param string $oid The OID of the user.
     * @return object|string
     * @throws PBMErrorException
     */
    public function getListOfEnrollments($oid)
    {
        $this->log->debug('Sending list enrollment request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/users/' . $oid . '/enrollments',
            'GET'
        );
    }

    /**
     * Downloads the enrollment in pdf format.
     *
     * @param string $oid The OID of the user.
     * @param string $enrollmentId The ID of the enrollment.
     * @return mixed
     * @throws PBMErrorException
     */
    public function downloadEnrollmentPdf($oid, $enrollmentId)
    {
        $this->log->debug('Downloading enrollment in PDF format from PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/download/users/' . $oid . '/enrollments/' . $enrollmentId . '/pdf',
            'GET'
        );
    }

    /**
     * Send the specified enrollment pdf document to the user via e-mail.
     *
     * @param string $oid The OID of the owner user.
     * @param string $enrollmentId The ID of the enrollment.
     * @return mixed
     * @throws PBMErrorException
     */
    public function sendEnrollmentInEmail($oid, $enrollmentId)
    {
        $this->log->debug('Sending enrollment in email request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/users/' . $oid . '/enrollments/' . $enrollmentId . '/pdfviaemail',
            'POST'
        );
    }

    /**
     * Deletes the enrollment specified by the enrollmentId of the user specified by the OID.
     *
     * @param string $oid The OID of the owner of the enrollment.
     * @param string $enrollmentId The enrollmentId of the enrollment to be deleted.
     * @return mixed
     * @throws PBMErrorException
     */
    public function deleteEnrollment($oid, $enrollmentId)
    {
        $this->log->debug('Deleting user enrollment with PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/users/' . $oid . '/enrollments/' . $enrollmentId,
            'DELETE'
        );
    }

    /**
     * Adds a new userId (alias) for the specified PassBy[ME] user.
     *
     * @param string $oid The OID of the chosen user, who receives the alias.
     * @param string $alias The PassBy[ME] ID of a user.
     * @return object|string
     * @throws PBMErrorException
     */
    public function createAlias($oid, $alias)
    {
        $this->log->debug('Sending create LoginName of user request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/loginNames/' . $oid,
            'POST',
            array('userId' => $alias)
        );
    }

    /**
     * Returns the list of userIds (aliases) of the specified PassBy[ME] user.
     *
     * @param string $oid The OID of the user.
     * @return object|string
     * @throws PBMErrorException
     */
    public function getListOfAliases($oid)
    {
        $this->log->debug('Sending get LoginNames of user request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/loginNames/' . $oid,
            'GET'
        );
    }

    /**
     * Find the loginName with the given userId.
     *
     * @param string $userId One of the aliases of the user.
     * @return object|string
     * @throws PBMErrorException
     */
    public function getUserByAlias($userId)
    {
        $this->log->debug('Sending find LoginName by userId request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/loginNames?userId=' . urlencode($userId),
            'GET'
        );
    }

    /**
     * Deletes the specified userId (alias) of the specified PassBy[ME] user.
     *
     * @param string $oid The oid of the PassBy[ME] user who owns the userId to be deleted.
     * @param string $userId The userId to be deleted.
     * @return object|string
     * @throws PBMErrorException
     */
    public function deleteAlias($oid, $userId)
    {
        $this->log->debug('Sending delete LoginName request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/loginNames?oid=' . urlencode($oid) . '&userId=' . urlencode($userId),
            'DELETE'
        );
    }

    /*************************** ADMINISTRATOR MANAGEMENT ********************************/

    /**
     * Returns the list of administrators.
     *
     * @return object|string
     * @throws PBMErrorException
     */
    public function getListOfAdministrators()
    {
        $this->log->debug('Sending list administrators request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/administrators',
            'GET'
        );
    }

    /**
     * Create a new invitation. Finishing the invitation process a new administrator will be created.
     *
     * @return object|string
     * @throws PBMErrorException
     */
    public function createInvitation()
    {
        $this->log->debug('Sending create new invitation request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/invitations',
            'POST'
        );
    }

    /**
     * Returns the list of active invitations.
     *
     * @return object|string
     * @throws PBMErrorException
     */
    public function getListOfInvitations()
    {
        $this->log->debug('Sending list active invitations request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/invitations',
            'GET'
        );
    }

    /**
     * Create a new enrollment for the given administrator.
     *
     * @param string $userId The userId of the administrator.
     * @return object|string
     * @throws PBMErrorException
     */
    public function createAdminEnrollment($userId)
    {
        $this->log->debug('Sending create new admin enrollment request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/administrators/' . $userId . '/enrollments',
            'POST'
        );
    }

    /**
     * Returns the active enrollments of the given administrator.
     *
     * @param string $userId The userId of the administrator.
     * @return object|string
     * @throws PBMErrorException
     */
    public function getAdminEnrollments($userId)
    {
        $this->log->debug('Sending get enrollments of administrator request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/administrators/' . $userId . '/enrollments',
            'GET'
        );
    }

    /**
     * Downloads the enrollment in pdf format.
     *
     * @param string $userId The userId of the administrator.
     * @param string $enrollmentId The id of the enrollment.
     * @return mixed The PDF file or json object.
     * @throws PBMErrorException
     */
    public function downloadAdminEnrollmentPdf($userId, $enrollmentId)
    {
        $this->log->debug('Sending download admin enrollment PDF request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/download/administrators/' . $userId . '/enrollments/' . $enrollmentId . '/pdf',
            'GET'
        );
    }

    /**
     * Send the specified enrollment pdf document to the owner administrator via e-mail.
     *
     * @param string $userId The userId of the owner administrator.
     * @param string $enrollmentId Id of the enrollment.
     * @return mixed
     * @throws PBMErrorException
     */
    public function sendAdminEnrollmentInEmail($userId, $enrollmentId)
    {
        $this->log->debug('Sending enrollment to admin in email request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/administrators/' . $userId . '/enrollments/' . $enrollmentId . '/pdfviaemail',
            'POST'
        );
    }

    /**
     * Deletes the enrollment specified by the enrollmentId of the administrator specified by the userId.
     *
     * @param string $userId The userId of the owner of the enrollment.
     * @param string $enrollmentId The enrollmentId of the enrollment to be deleted.
     * @return mixed
     * @throws PBMErrorException
     */
    public function deleteAdminEnrollment($userId, $enrollmentId)
    {
        $this->log->debug('Sending delete admin enrollment request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/administrators/' . $userId . '/enrollments/' . $enrollmentId,
            'DELETE'
        );
    }

    /*************************** APPLICATION MANAGEMENT ********************************/

    /**
     * Creates a new Application registration.
     *
     * @param string $name Name of the application.
     * @return object|string
     * @throws PBMErrorException
     */
    public function createApplication($name)
    {
        $this->log->debug('Sending create application request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/applications',
            'POST',
            array('name' => $name)
        );
    }

    /**
     * Returns the list of applications registered in the PassBy[ME] system.
     *
     * @return object|string
     * @throws PBMErrorException
     */
    public function getListOfApplication()
    {
        $this->log->debug('Sending list applications request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/applications',
            'GET'
        );
    }

    /**
     * Find the application with the given application identifier.
     *
     * @param string $appId The id of the application in the PassBy[ME] system.
     * @return object|string
     * @throws PBMErrorException
     */
    public function getApplication($appId)
    {
        $this->log->debug('Sending get application request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/applications/' . $appId,
            'GET'
        );
    }

    /**
     * Deletes the application from the PassBy[ME] system with the given application identifier.
     *
     * @param string $appId The application identifier of the application to be deleted.
     * @return mixed
     * @throws PBMErrorException
     */
    public function deleteApplication($appId)
    {
        $this->log->debug('Sending delete application request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/applications/' . $appId,
            'DELETE'
        );
    }

    /**
     * Modify the application with the given application identifier.
     *
     * @param string $appId The application identifier of the application to be modified.
     * @param string $name The new name of the application.
     * @return mixed
     * @throws PBMErrorException
     */
    public function modifyApplication($appId, $name)
    {
        $this->log->debug('Sending modify application request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/applications/' . $appId,
            'PUT',
            array('name' => $name)
        );
    }

    /*************************** DEVICE MANAGEMENT ********************************/

    /**
     * Returns all devices.
     *
     * @return object|string
     * @throws PBMErrorException
     */
    public function getListOfUsersDevices()
    {
        $this->log->debug('Sending list devices request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/devices',
            'GET'
        );
    }

    /**
     * Returns the devices of the given user.
     *
     * @param string $oid The OID of the user.
     * @return object|string
     * @throws PBMErrorException
     */
    public function getUserDevices($oid)
    {
        $this->log->debug('Sending get devices of user request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/users/' . $oid . '/devices',
            'GET'
        );
    }

    /**
     * Returns the devices of the given administrator.
     *
     * @param string $adminId The id of the administrator.
     * @return object|string
     * @throws PBMErrorException
     */
    public function getListOfAdminDevices($adminId)
    {
        $this->log->debug('Sending get admin devices request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/administrators/' . $adminId . '/devices',
            'GET'
        );
    }

    /**
     * Re-sends deactivation password via email.
     *
     * @param string $vendorId The vendorId of the device, which belongs to the deactivation password to resend.
     * @return mixed
     * @throws PBMErrorException
     */
    public function sendDeactivationPassword($vendorId)
    {
        $this->log->debug('Sending deactivation password request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/devices/deactivationpass',
            'POST',
            array('vendorId' => $vendorId)
        );
    }

    /**
     * Delete user device.
     *
     * @param string $deactivationPassword The suspension password of the owner of the device.
     * @return mixed
     * @throws PBMErrorException
     */
    public function deleteDevice($deactivationPassword)
    {
        $this->log->debug('Deleting device with PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/devices/deactivations',
            'POST',
            array('deactivationPassword' => $deactivationPassword)
        );
    }

    /*************************** ORGANISATION MANAGEMENT ********************************/

    /**
     * Returns organization details.
     *
     * @return object|string
     * @throws PBMErrorException
     */
    public function getOrganization()
    {
        $this->log->debug('Sending get organization request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/organization',
            'GET'
        );
    }

    /**
     * Updates organization details.
     *
     * @param object $modifiedOrgObj Mocked object from getOrganization function request.
     * @return mixed
     * @throws PBMErrorException
     */
    public function updateOrganization($modifiedOrgObj)
    {
        $this->log->debug('Sending update organization request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/organization',
            'PUT',
            (array) $modifiedOrgObj
        );
    }

    /**
     * Get the current account limitations (pricing) of the organization.
     *
     * @return object|string
     * @throws PBMErrorException
     */
    public function getAccountLimitations()
    {
        $this->log->debug('Sending get account limitations request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/organization/pricing',
            'GET'
        );
    }

    /*************************** ACTIVITY MANAGEMENT ********************************/

    /**
     * Returns list of second factor authentication log entries.
     *
     * @param string $search The value to be used for filtering records.
     * @param int $start The number of records to skip from the result.
     * @param int $length The maximum number of records to be returned.
     * @return object|string
     * @throws PBMErrorException
     */
    public function activityLog($search = '', $start = 0, $length = 1000)
    {
        $this->log->debug('Sending activity log request to PassBy[ME] management API.');
        return parent::prepareAndSend(
            $this->mng_url . '/rest/activity?' . http_build_query(array(
                'start' => $start,
                'length' => $length,
                'search' => array(
                    'value' => $search
                )
            )),
            'GET'
        );
    }
}