<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;


class UserControllerTest extends TestCase
{
    private static $id = null;


    public function testGet()
    {
        $userController = new \User\controllers\UserController();

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $userController->get($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());


        $this->assertInternalType('array', $responseBody->users);
        $this->assertNotEmpty($responseBody->users);
    }

    public function testCreate()
    {
        $userController = new \User\controllers\UserController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'userId'    => 'TEST-CKENT',
            'firstname' => 'TEST-CLARK',
            'lastname'  => 'TEST-KENT'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        self::$id = $responseBody->user->id;

        $this->assertInternalType('int', self::$id);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->id);
        $this->assertSame('TEST-CKENT', $responseBody->user_id);
        $this->assertSame('TEST-CLARK', $responseBody->firstname);
        $this->assertSame('TEST-KENT', $responseBody->lastname);
        $this->assertSame('OK', $responseBody->status);
        $this->assertSame('Y', $responseBody->enabled);
        $this->assertSame(null, $responseBody->phone);
        $this->assertSame(null, $responseBody->mail);
        $this->assertSame(null, $responseBody->initials);
        $this->assertSame(null, $responseBody->thumbprint);
    }

    public function testUpdate()
    {
        $userController = new \User\controllers\UserController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'user_id'    => 'TEST-CKENT',
            'firstname' => 'TEST-CLARK2',
            'lastname'  => 'TEST-KENT2',
            'mail'      => 'ck@dailyP.com',
            'phone'     => '0122334455',
            'initials'  => 'CK',
            'enabled'   => 'N',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->id);
        $this->assertSame('TEST-CKENT', $responseBody->user_id);
        $this->assertSame('TEST-CLARK2', $responseBody->firstname);
        $this->assertSame('TEST-KENT2', $responseBody->lastname);
        $this->assertSame('OK', $responseBody->status);
        $this->assertSame('N', $responseBody->enabled);
        $this->assertSame('0122334455', $responseBody->phone);
        $this->assertSame('ck@dailyP.com', $responseBody->mail);
        $this->assertSame('CK', $responseBody->initials);
        $this->assertSame(null, $responseBody->thumbprint);
    }

    public function testAddGroup()
    {
        $userController = new \User\controllers\UserController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'groupId'   => 'AGENT',
            'role'      => 'Douche'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->addGroup($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->groups);
        $this->assertInternalType('array', $responseBody->baskets);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->id);
        $this->assertInternalType('array', $responseBody->groups);
        $this->assertSame('AGENT', $responseBody->groups[0]->group_id);
        $this->assertSame('Douche', $responseBody->groups[0]->role);
    }

    public function testUpdateGroup()
    {
        $userController = new \User\controllers\UserController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'role'      => 'role updated'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->updateGroup($fullRequest, new \Slim\Http\Response(), ['id' => self::$id, 'groupId' => 'AGENT']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->id);
        $this->assertInternalType('array', $responseBody->groups);
        $this->assertSame('AGENT', $responseBody->groups[0]->group_id);
        $this->assertSame('role updated', $responseBody->groups[0]->role);
    }

    public function testDeleteGroup()
    {
        $userController = new \User\controllers\UserController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->deleteGroup($request, new \Slim\Http\Response(), ['id' => self::$id, 'groupId' => 'AGENT']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->groups);
        $this->assertEmpty($responseBody->groups);
        $this->assertInternalType('array', $responseBody->baskets);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->id);
        $this->assertInternalType('array', $responseBody->groups);
        $this->assertEmpty($responseBody->groups);
    }

    public function testAddEntity()
    {
        $userController = new \User\controllers\UserController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'entityId'  => 'DGS',
            'role'      => 'Warrior'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->addEntity($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->entities);
        $this->assertInternalType('array', $responseBody->allEntities);

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'entityId'  => 'FIN',
            'role'      => 'Hunter'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->addEntity($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->entities);
        $this->assertInternalType('array', $responseBody->allEntities);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->id);
        $this->assertInternalType('array', $responseBody->entities);
        $this->assertSame('DGS', $responseBody->entities[0]->entity_id);
        $this->assertSame('Warrior', $responseBody->entities[0]->user_role);
        $this->assertSame('Y', $responseBody->entities[0]->primary_entity);
        $this->assertSame('FIN', $responseBody->entities[1]->entity_id);
        $this->assertSame('Hunter', $responseBody->entities[1]->user_role);
        $this->assertSame('N', $responseBody->entities[1]->primary_entity);
    }

    public function testUpdateEntity()
    {
        $userController = new \User\controllers\UserController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'user_role'      => 'Rogue'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->updateEntity($fullRequest, new \Slim\Http\Response(), ['id' => self::$id, 'entityId' => 'DGS']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->id);
        $this->assertInternalType('array', $responseBody->entities);
        $this->assertSame('DGS', $responseBody->entities[0]->entity_id);
        $this->assertSame('Rogue', $responseBody->entities[0]->user_role);
        $this->assertSame('Y', $responseBody->entities[0]->primary_entity);
    }

    public function testUpdatePrimaryEntity()
    {
        $userController = new \User\controllers\UserController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $userController->updatePrimaryEntity($request, new \Slim\Http\Response(), ['id' => self::$id, 'entityId' => 'FIN']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->entities);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->id);
        $this->assertInternalType('array', $responseBody->entities);
        $this->assertSame('FIN', $responseBody->entities[0]->entity_id);
        $this->assertSame('Hunter', $responseBody->entities[0]->user_role);
        $this->assertSame('Y', $responseBody->entities[0]->primary_entity);
        $this->assertSame('DGS', $responseBody->entities[1]->entity_id);
        $this->assertSame('Rogue', $responseBody->entities[1]->user_role);
        $this->assertSame('N', $responseBody->entities[1]->primary_entity);
    }

    public function testDeleteEntity()
    {
        $userController = new \User\controllers\UserController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->deleteEntity($request, new \Slim\Http\Response(), ['id' => self::$id, 'entityId' => 'FIN']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->entities);
        $this->assertInternalType('array', $responseBody->allEntities);

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->deleteEntity($request, new \Slim\Http\Response(), ['id' => self::$id, 'entityId' => 'DGS']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->entities);
        $this->assertEmpty($responseBody->entities);
        $this->assertInternalType('array', $responseBody->allEntities);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->id);
        $this->assertInternalType('array', $responseBody->entities);
        $this->assertEmpty($responseBody->entities);
    }

    public function testUpdateStatus()
    {
        $userController = new \User\controllers\UserController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'status'    => 'ABS'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->updateStatus($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('ABS', $responseBody->user->status);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->id);
        $this->assertSame('ABS', $responseBody->status);
    }

    public function testRead()
    {
        $userController = new \User\controllers\UserController();
        $parameterController = new \Parameter\controllers\ParameterController();
        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'description'           => 'User quota',
            'param_value_int'       => 0
        ];
        $fullRequest    = \httpRequestCustom::addContentInBody($aArgs, $request);
        $parameterController->update($fullRequest, new \Slim\Http\Response(), ['id' => 'user_quota']);

        // READ in case of deactivated user_quota
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $userController->get($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertNotNull($responseBody->users);
        $this->assertNull($responseBody->quota->userQuota);

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'description'           => 'User quota',
            'param_value_int'       => 20
        ];
        $fullRequest    = \httpRequestCustom::addContentInBody($aArgs, $request);
        $parameterController->update($fullRequest, new \Slim\Http\Response(), ['id' => 'user_quota']);

        // READ in case of enabled user_quotat
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $userController->get($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertNotNull($responseBody->users);
        $this->assertNotNull($responseBody->quota);
        $this->assertSame(20, $responseBody->quota->userQuota);
        $this->assertNotNull($responseBody->quota->actives);
        $this->assertInternalType('int', $responseBody->quota->inactives);

    }

    public function testUserQuota()
    {
        $userController = new \User\controllers\UserController();
        $parameterController = new \Parameter\controllers\ParameterController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'userId'    => 'TEST-CKENTquota',
            'firstname' => 'TEST-CLARKquota',
            'lastname'  => 'TEST-KENTquota'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());
        $userId = $responseBody->user->id;

        $this->assertInternalType('int', $userId);

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'user_id'    => 'TEST-CKENTquota',
            'firstname' => 'TEST-CLARKquota2',
            'lastname'  => 'TEST-KENTquota2',
            'mail'      => 'ck@dailyP.com',
            'phone'     => '0122334455',
            'initials'  => 'CK',
            'enabled'   => 'N',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->update($fullRequest, new \Slim\Http\Response(), ['id' =>$userId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  UPDATE disabled user for user_quota (avoid notification sending)
        $aArgs = [
            'user_id'    => 'TEST-CKENTquota',
            'firstname' => 'TEST-CLARKquota2',
            'lastname'  => 'TEST-KENTquota2',
            'mail'      => 'ck@dailyP.com',
            'phone'     => '0122334455',
            'initials'  => 'CK',
            'enabled'   => 'Y',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->update($fullRequest, new \Slim\Http\Response(), ['id' =>$userId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  DELETE
        //  REAL DELETE
        \SrcCore\models\DatabaseModel::delete([
            'table' => 'users',
            'where' => ['id = ?'],
            'data'  => [$userId]
        ]);

        //  UPDATE
        $aArgs = [
            'description'           => 'User quota',
            'param_value_int'       => 0
        ];
        $fullRequest    = \httpRequestCustom::addContentInBody($aArgs, $request);
        $parameterController->update($fullRequest, new \Slim\Http\Response(), ['id' => 'user_quota']);
    }

    public function testDelete()
    {
        $userController = new \User\controllers\UserController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $userController->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $userController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame(self::$id, $responseBody->id);
        $this->assertSame('TEST-CKENT', $responseBody->user_id);
        $this->assertSame('TEST-CLARK2', $responseBody->firstname);
        $this->assertSame('TEST-KENT2', $responseBody->lastname);
        $this->assertSame('DEL', $responseBody->status);
        $this->assertSame('N', $responseBody->enabled);
        $this->assertSame('0122334455', $responseBody->phone);
        $this->assertSame('ck@dailyP.com', $responseBody->mail);
        $this->assertSame('CK', $responseBody->initials);
        $this->assertSame(null, $responseBody->thumbprint);

        //  REAL DELETE
        \SrcCore\models\DatabaseModel::delete([
            'table' => 'users',
            'where' => ['id = ?'],
            'data'  => [self::$id]
        ]);
    }

    public function testPasswordManagement()
    {
        $userController = new \User\controllers\UserController();

        //  UPDATE PASSWORD
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'currentPassword'   => 'superadmin',
            'newPassword'       => 'hcraam',
            'reNewPassword'     => 'hcraam'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->updateCurrentUserPassword($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        $checkPassword = \SrcCore\models\AuthenticationModel::authentication(['userId' => $GLOBALS['userId'], 'password' => 'hcraam']);

        $this->assertSame(true, $checkPassword);

        //  RESET PASSWORD
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $user = \User\models\UserModel::getByUserId(['userId' => $GLOBALS['userId'], 'select' => ['id']]);
        $response     = $userController->resetPassword($fullRequest, new \Slim\Http\Response(), ['id' => $user['id']]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        $checkPassword = \SrcCore\models\AuthenticationModel::authentication(['userId' => $GLOBALS['userId'], 'password' => 'maarch']);

        $this->assertSame(true, $checkPassword);

        //  UPDATE PASSWORD
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'currentPassword'   => 'maarch',
            'newPassword'       => 'superadmin',
            'reNewPassword'     => 'superadmin'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->updateCurrentUserPassword($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        $checkPassword = \SrcCore\models\AuthenticationModel::authentication(['userId' => $GLOBALS['userId'], 'password' => 'superadmin']);

        $this->assertSame(true, $checkPassword);

        \SrcCore\models\DatabaseModel::update([
            'table'     => 'users',
            'set'       => [
                'change_password'   => 'N'
            ],
            'where'     => ['user_id = ?'],
            'data'      => [$GLOBALS['userId']]
        ]);
    }

    public function testUpdateProfile()
    {
        $userController = new \User\controllers\UserController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'firstname' => 'Wonder',
            'lastname'  => 'User',
            'mail'      => 'dev@maarch.org',
            'initials'  => 'SU'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->updateProfile($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->getProfile($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('superadmin', $responseBody->user_id);
        $this->assertSame('Wonder', $responseBody->firstname);
        $this->assertSame('User', $responseBody->lastname);
        $this->assertSame('dev@maarch.org', $responseBody->mail);
        $this->assertSame('SU', $responseBody->initials);

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'firstname' => 'Super',
            'lastname'  => 'Admin',
            'mail'      => 'dev@maarch.org',
            'initials'  => 'SU'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $userController->updateProfile($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $userController->getProfile($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('superadmin', $responseBody->user_id);
        $this->assertSame('Super', $responseBody->firstname);
        $this->assertSame('Admin', $responseBody->lastname);
        $this->assertSame('dev@maarch.org', $responseBody->mail);
        $this->assertSame('SU', $responseBody->initials);
    }

    public function testSetRedirectedBasket()
    {
        $userController = new \User\controllers\UserController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            [
                'newUser'       =>  'bblier',
                'basketId'      =>  'MyBasket',
                'basketOwner'   =>  'bbain',
                'virtual'       =>  'Y'
            ],
            [
                'newUser'       =>  'bblier',
                'basketId'      =>  'EenvBasket',
                'basketOwner'   =>  'bbain',
                'virtual'       =>  'Y'
            ]
        ];

        $user_id = \User\models\UserModel::getByUserId(['userId' => 'bbain', 'select' => ['id']]);
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->setRedirectedBaskets($fullRequest, new \Slim\Http\Response(), ['id' => $user_id['id']]);
        $responseBody = json_decode((string)$response->getBody());
        
        $this->assertNotNull($responseBody->baskets);

        $aArgs = [
            [
                'newUser'       =>  null,
                'basketId'      =>  'MyBasket',
                'basketOwner'   =>  'bbain',
                'virtual'       =>  'Y'
            ],
            [
                'newUser'       =>  'bblier',
                'basketId'      =>  'EenvBasket',
                'basketOwner'   =>  'bbain',
                'virtual'       =>  'Y'
            ]
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->setRedirectedBaskets($fullRequest, new \Slim\Http\Response(), ['id' => $user_id['id']]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Bad Request', $responseBody->errors);

        $aArgs = [
            [
                'newUser'       =>  'notExist',
                'basketId'      =>  'MyBasket',
                'basketOwner'   =>  'bbain',
                'virtual'       =>  'Y'
            ],
            [
                'newUser'       =>  'existNot',
                'basketId'      =>  'EenvBasket',
                'basketOwner'   =>  'bbain',
                'virtual'       =>  'Y'
            ]
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->setRedirectedBaskets($fullRequest, new \Slim\Http\Response(), ['id' => $user_id['id']]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('User not found', $responseBody->errors);
    }

    public function testDeleteRedirectedBaskets()
    {
        $userController = new \User\controllers\UserController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
                'basketOwner'   =>  'bbain',
        ];

        $user_id = \User\models\UserModel::getByUserId(['userId' => 'bbain', 'select' => ['id']]);
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->deleteRedirectedBaskets($fullRequest, new \Slim\Http\Response(), ['id' => $user_id['id'], 'basketId' => 'MyBasket']);
        $response     = $userController->deleteRedirectedBaskets($fullRequest, new \Slim\Http\Response(), ['id' => $user_id['id'], 'basketId' => 'EenvBasket']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotNull($responseBody->baskets);

        $aArgs = [
            'basketOwner'   =>  null,
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->deleteRedirectedBaskets($fullRequest, new \Slim\Http\Response(), ['id' => $user_id['id'], 'basketId' => 'MyBasket']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Bad Request', $responseBody->errors);
    }
}
