<?php
use PassByME\Methods\Management;

class ManagementTest extends \PHPUnit_Framework_TestCase
{
    private static $alias;
    private static $adminUserExists;
    public static function setUpBeforeClass()
    {
        self::$alias = 'Admin to User';
        self::$adminUserExists = false;
    }

    public function testCreateUser()
    {
        $mng = new Management();
        $email = 'phpunit.test@passbyme.com';
        $fullName = 'PHP Unit Test User';
        $phoneNumber = '123456';
        $result = $mng->createUser('PHP Unit Test Alias_' . mt_rand(), $email, $fullName, $phoneNumber);
        $this->assertObjectHasAttribute('oid', $result);
        $this->assertObjectHasAttribute('shortOID', $result);
        $this->assertObjectHasAttribute('fullName', $result);
        $this->assertObjectHasAttribute('email', $result);
        $this->assertObjectHasAttribute('phoneNumber', $result);
        $this->assertObjectHasAttribute('disabled', $result);
        $this->assertEquals($result->email, $email);
        $this->assertEquals($result->phoneNumber, $phoneNumber);
        $this->assertEquals($result->fullName, $fullName);

        return $result->oid;
    }

    /**
     * @depends testCreateUser
     */
    public function testGetListOfUsers()
    {
        $mng = new Management();
        $result = $mng->getListOfUsers();
        $this->assertObjectHasAttribute('recordsFiltered', $result);
        $this->assertObjectHasAttribute('recordsTotal', $result);
        $this->assertObjectHasAttribute('data', $result);
    }

    /**
     * @depends testCreateUser
     */
    public function testGetUsersNumber()
    {
        $mng = new Management();
        $result = $mng->getUsersNumber();
        $this->assertInternalType('integer', $result);
    }

    /**
     * @depends testCreateUser
     */
    public function testGetUser($oid)
    {
        $mng = new Management();
        $result = $mng->getUser($oid);
        $this->assertObjectHasAttribute('oid', $result);
        $this->assertObjectHasAttribute('shortOID', $result);
        $this->assertObjectHasAttribute('fullName', $result);
        $this->assertObjectHasAttribute('email', $result);
        $this->assertObjectHasAttribute('phoneNumber', $result);
        $this->assertObjectHasAttribute('disabled', $result);
        $this->assertEquals($result->oid, $oid);
    }

    /**
     * @depends testCreateUser
     */
    public function testModifyUser($oid)
    {
        $mng = new Management();
        $userObj = $mng->getUser($oid);
        $userObj->email = 'new@email.com';
        $result = $mng->modifyUser($oid, $userObj);
        $this->assertEquals($result, $oid);
    }

    /**
     * @depends testCreateUser
     */
    public function testCreateEnrollment($oid)
    {
        $mng = new Management();
        $result = $mng->createEnrollment($oid);
        $this->assertObjectHasAttribute('enrollmentId', $result);
        $this->assertObjectHasAttribute('expireDate', $result);
        $this->assertObjectHasAttribute('state', $result);
        $this->assertObjectHasAttribute('sent', $result);
        $this->assertObjectHasAttribute('deviceConfigUrl', $result);
    }

    /**
     * @depends testCreateUser
     */
    public function testGetListOfEnrollments($oid)
    {
        $mng = new Management();
        $result = $mng->getListOfEnrollments($oid);
        $this->assertObjectHasAttribute('data', $result);
    }

    /**
     * @depends testCreateUser
     * @depends testCreateEnrollment
     * @depends testGetListOfEnrollments
     */
    public function testDownloadEnrollmentPdf($oid)
    {
        $mng = new Management();
        $enrollments = $mng->getListOfEnrollments($oid);
        $mng->downloadEnrollmentPdf($oid, $enrollments->data[0]->enrollmentId);
        $this->assertEquals($mng->isError(), false);
    }

    /**
     * @depends testCreateUser
     * @depends testCreateEnrollment
     * @depends testGetListOfEnrollments
     */
    public function testSendEnrollmentInEmail($oid)
    {
        $mng = new Management();
        $enrollments = $mng->getListOfEnrollments($oid);
        $result = $mng->sendEnrollmentInEmail($oid, $enrollments->data[0]->enrollmentId);
        $this->assertEquals($mng->isError(), false);
        $this->assertInternalType('string', $result);
    }

    /**
     * @depends testCreateUser
     * @depends testCreateEnrollment
     * @depends testGetListOfEnrollments
     */
    public function testDeleteEnrollment($oid)
    {
        $mng = new Management();
        $enrollments = $mng->getListOfEnrollments($oid);
        $result = $mng->deleteEnrollment($oid, $enrollments->data[0]->enrollmentId);
        $this->assertEquals($mng->isError(), false);
        $this->assertInternalType('string', $result);
    }

    /**
     * @depends testCreateUser
     */
    public function testCreateAlias($oid)
    {
        $newAlias = 'new @alias';
        $mng = new Management();
        $result = $mng->createAlias($oid, $newAlias);
        $this->assertObjectHasAttribute('userId', $result);
        $this->assertObjectHasAttribute('oid', $result);
        $this->assertObjectHasAttribute('shortOID', $result);
        $this->assertEquals($newAlias, $result->userId);

        return $newAlias;
    }

    /**
     * @depends testCreateUser
     * @depends testCreateAlias
     */
    public function testListOfAliases($oid)
    {
        $mng = new Management();
        $result = $mng->getListOfAliases($oid);
        $this->assertObjectHasAttribute('data', $result);
    }

    /**
     * @depends testCreateAlias
     */
    public function testGetUserByAlias($alias)
    {
        $mng = new Management();
        $result = $mng->getUserByAlias($alias);
        $this->assertObjectHasAttribute('userId', $result);
        $this->assertObjectHasAttribute('oid', $result);
        $this->assertObjectHasAttribute('shortOID', $result);
        $this->assertEquals($alias, $result->userId);
    }

    /**
     * @depends testCreateUser
     * @depends testCreateAlias
     */
    public function testDeleteAlias($oid, $alias)
    {
        $mng = new Management();
        $result = $mng->deleteAlias($oid, $alias);
        $this->assertEquals($mng->isError(), false);
        $this->assertInternalType('string', $result);
    }

    public function testGetListOfAdministrators()
    {
        $mng = new Management();
        $result = $mng->getListOfAdministrators();
        $this->assertObjectHasAttribute('data', $result);
        return $result;
    }

    /**
     * @depends testGetListOfAdministrators
     */
    public function testCreateUserFromAdmin($admins)
    {
        $mng = new Management();
        $user = $mng->getUser($admins->data[0]->oid);
        if (isset($user->oid) and $user->oid == $admins->data[0]->oid) {
            self::$adminUserExists = true;
            $userObj = $user;
        } else {
            $result = $mng->createUserFromAdmin(self::$alias, $admins->data[0]->userId);
            $this->assertObjectHasAttribute('oid', $result);
            $this->assertObjectHasAttribute('shortOID', $result);
            $this->assertObjectHasAttribute('fullName', $result);
            $this->assertObjectHasAttribute('email', $result);
            $this->assertObjectHasAttribute('phoneNumber', $result);
            $this->assertObjectHasAttribute('disabled', $result);
            $userObj = $mng->getUserByAlias(self::$alias);
        }
        return $userObj;
    }

    public function testCreateInvitation()
    {
        $mng = new Management();
        $result = $mng->createInvitation();
        $this->assertEquals(false, $mng->isError());
        $this->assertObjectHasAttribute('expiryDate', $result);
        $this->assertObjectHasAttribute('code', $result);
    }

    public function testListInvitations()
    {
        $mng = new Management();
        $result = $mng->getListOfInvitations();
        $this->assertObjectHasAttribute('data', $result);
    }

    /**
     * @depends testGetListOfAdministrators
     */
    public function testCreateAdminEnrollment($admins)
    {
        $mng = new Management();
        $result = $mng->createAdminEnrollment($admins->data[0]->userId);
        $this->assertObjectHasAttribute('enrollmentId', $result);
        $this->assertObjectHasAttribute('expireDate', $result);
        $this->assertObjectHasAttribute('state', $result);
        $this->assertObjectHasAttribute('sent', $result);
        $this->assertObjectHasAttribute('deviceConfigUrl', $result);
    }

    /**
     * @depends testGetListOfAdministrators
     */
    public function testGetAdminEnrollments($admins)
    {
        $mng = new Management();
        $result = $mng->getAdminEnrollments($admins->data[0]->userId);
        $this->assertObjectHasAttribute('data', $result);
        return $result;
    }

    /**
     * @depends testGetListOfAdministrators
     * @depends testGetAdminEnrollments
     */
    public function testDownloadAdminEnrollmentPdf($admins, $enrollments)
    {
        $mng = new Management();
        $mng->downloadAdminEnrollmentPdf($admins->data[0]->userId, $enrollments->data[0]->enrollmentId);
        $this->assertEquals($mng->isError(), false);
    }

    /**
     * @depends testGetListOfAdministrators
     * @depends testGetAdminEnrollments
     */
    public function testSendAdminEnrollmentInEmail($admins, $enrollments)
    {
        $mng = new Management();
        $result = $mng->sendAdminEnrollmentInEmail($admins->data[0]->userId, $enrollments->data[0]->enrollmentId);
        $this->assertEquals($mng->isError(), false);
        $this->assertInternalType('string', $result);
    }

    /**
     * @depends testGetListOfAdministrators
     * @depends testGetAdminEnrollments
     */
    public function testDeleteAdminEnrollment($admins, $enrollments)
    {
        $mng = new Management();
        $result = $mng->deleteAdminEnrollment($admins->data[0]->userId, $enrollments->data[0]->enrollmentId);
        $this->assertEquals($mng->isError(), false);
        $this->assertInternalType('string', $result);
    }

    public function testCreateApplication()
    {
        $mng = new Management();
        $newAppName = 'I m a new Application';
        $result = $mng->createApplication($newAppName);
        $this->assertObjectHasAttribute('id', $result);
        $this->assertObjectHasAttribute('name', $result);
        $this->assertEquals($newAppName, $result->name);
        return $result;
    }

    public function testGetListOfApplication()
    {
        $mng = new Management();
        $result = $mng->getListOfApplication();
        $this->assertObjectHasAttribute('data', $result);
        return $result;
    }

    /**
     * @depends testGetListOfApplication
     */
    public function testGetApplication($app)
    {
        $mng = new Management();
        $result = $mng->getApplication($app->data[0]->id);
        $this->assertObjectHasAttribute('id', $result);
        $this->assertObjectHasAttribute('name', $result);
    }

    /**
     * @depends testGetListOfApplication
     */
    public function testModifyApplication($app)
    {
        $mng = new Management();
        $result = $mng->modifyApplication($app->data[0]->id, 'new App name');
        $this->assertEquals($mng->isError(), false);
        $this->assertInternalType('string', $result);
    }

    /**
     * @depends testGetListOfApplication
     */
    public function testDeleteApplication($app)
    {
        $mng = new Management();
        $result = $mng->deleteApplication($app->data[0]->id);
        $this->assertEquals($mng->isError(), false);
        $this->assertInternalType('string', $result);
    }

    public function testGetListOfUsersDevices()
    {
        $mng = new Management();
        $result = $mng->getListOfUsersDevices();
        $this->assertObjectHasAttribute('recordsFiltered', $result);
        $this->assertObjectHasAttribute('recordsTotal', $result);
        $this->assertObjectHasAttribute('data', $result);
    }

    /**
     * @depends testCreateUser
     */
    public function testGetUserDevices($oid)
    {
        $mng = new Management();
        $result = $mng->getUserDevices($oid);
        $this->assertObjectHasAttribute('data', $result);
    }

    /**
     * @depends testGetListOfAdministrators
     */
    public function testGetListOfAdminDevices($admins)
    {
        $mng = new Management();
        $result = $mng->getListOfAdminDevices($admins->data[0]->userId);
        $this->assertObjectHasAttribute('data', $result);
    }

    /**
     * @depends testCreateUserFromAdmin
     * @depends testGetUserDevices
     */
    public function testSendDeactivationPassword($user)
    {
        $mng = new Management();
        $devices = $mng->getUserDevices($user->oid);
        $result = $mng->sendDeactivationPassword($devices->data[0]->vendorId);
        $this->assertEquals("", $result);
    }

    public function testDeleteDevice()
    {
        $this->assertTrue(true, 'We can not test this without integration tests!');
    }

    /**
     * @depends testCreateUser
     */
    public function testDeleteUser($oid)
    {
        $mng = new Management();
        $result = $mng->deleteUser($oid);
        $this->assertEquals($result, $oid);
    }

    public function testGetOrganization()
    {
        $mng = new Management();
        $result = $mng->getOrganization();
        $this->assertObjectHasAttribute('organizationId', $result);
        $this->assertObjectHasAttribute('name', $result);
        $this->assertObjectHasAttribute('pricing', $result);
        $this->assertObjectHasAttribute('upgradable', $result);
        $this->assertObjectHasAttribute('downgradable', $result);
        $this->assertObjectHasAttribute('email', $result);
        $this->assertObjectHasAttribute('enrollmentExpirationHours', $result);
        $this->assertObjectHasAttribute('invitationExpirationHours', $result);
        $this->assertObjectHasAttribute('confirmationLetterEnabled', $result);
        $this->assertObjectHasAttribute('pricingExpiration', $result);
        $this->assertObjectHasAttribute('isCommercial', $result);
        return $result;
    }

    /**
     * @depends testGetOrganization
     */
    public function testUpdateOrganization($org)
    {
        $mng = new Management();
        $org->orgName = 'PHPApiTest';
        $result = $mng->updateOrganization($org);
        $this->assertEquals("", $result);
    }

    public function testGetAccountLimitations()
    {
        $mng = new Management();
        $result = $mng->getAccountLimitations();
        $this->assertObjectHasAttribute('maxNumberOfUsers', $result);
        $this->assertObjectHasAttribute('maxNumberOfAdmins', $result);
        $this->assertObjectHasAttribute('daysOfActivityLog', $result);
        $this->assertObjectHasAttribute('maxNumberOfDevicesPerUser', $result);
        $this->assertObjectHasAttribute('hasManagementAPI', $result);
        $this->assertObjectHasAttribute('hasUserDisabling', $result);
        $this->assertObjectHasAttribute('creditPerUser', $result);
    }

    public function testActivityLog()
    {
        $mng = new Management();
        $result = $mng->activityLog('');
        $this->assertObjectHasAttribute('recordsFiltered', $result);
        $this->assertObjectHasAttribute('recordsTotal', $result);
        $this->assertObjectHasAttribute('data', $result);
    }

    public static function tearDownAfterClass()
    {
        if (!self::$adminUserExists) {
            $mng = new Management();
            $user = $mng->getUserByAlias(self::$alias);
            if (isset($user->oid)) {
                $mng->deleteUser($user->oid);
            }
        }
    }
}
