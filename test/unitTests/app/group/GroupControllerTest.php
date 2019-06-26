<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class GroupControllerTest extends TestCase
{
    private static $groupId = null;

    public function testCreateGroup()
    {
        $groupController = new \Group\controllers\GroupController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'label' => 'Test TU'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $groupController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());
        
        $this->assertNotEmpty($responseBody->id);
        $this->assertInternalType('int', $responseBody->id);
        self::$groupId = $responseBody->id;

        //Label missing
        $aArgs = [
            'label' => ''
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $groupController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Body label is empty or not a string or longer than 128 caracteres', $responseBody->errors);
    }

    public function testUpdateGroup()
    {
        $groupController = new \Group\controllers\GroupController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'label' => 'Test TU 2'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $groupController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$groupId]);
        $responseBody = json_decode((string)$response->getBody());
        
        $this->assertEmpty($responseBody);

        //Label missing
        $aArgs = [
            'label' => ''
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $groupController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$groupId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Body label is empty or not a string or longer than 128 caracteres', $responseBody->errors);
    }

    public function testAddUser()
    {
        $groupController = new \Group\controllers\GroupController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'userId' => 1
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $groupController->addUser($fullRequest, new \Slim\Http\Response(), ['id' => self::$groupId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertEmpty($responseBody);

        //Fail

        $aArgs = [
            'userId' => 12456789
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $groupController->addUser($fullRequest, new \Slim\Http\Response(), ['id' => self::$groupId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('User not found', $responseBody->errors);
    }

    public function testGetById()
    {
        $groupController = new \Group\controllers\GroupController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $groupController->getById($request, new \Slim\Http\Response(), ['id' => self::$groupId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody->group);
        $this->assertSame(self::$groupId, $responseBody->group->id);
        $this->assertSame('Test TU 2', $responseBody->group->label);
        $this->assertInternalType('array', $responseBody->group->users);
        $this->assertNotEmpty($responseBody->group->users);
        $this->assertSame(1, $responseBody->group->users[0]->id);
        $this->assertNotEmpty($responseBody->group->users[0]->firstname);
        $this->assertNotEmpty($responseBody->group->users[0]->lastname);
    }

    public function testRemoveUser()
    {
        $groupController = new \Group\controllers\GroupController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $groupController->removeUser($request, new \Slim\Http\Response(), ['id' => self::$groupId, 'userId' => 1]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertEmpty($responseBody);

        //Fail
        $response     = $groupController->removeUser($request, new \Slim\Http\Response(), ['id' => self::$groupId, 'userId' => 12456789]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('User not found', $responseBody->errors);
    }

    public function testGet()
    {
        $groupController = new \Group\controllers\GroupController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $groupController->get($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->groups);
        $this->assertNotEmpty($responseBody->groups);
        $this->assertInternalType('int', $responseBody->groups[0]->id);
        $this->assertNotEmpty($responseBody->groups[0]->label);
    }

    public function testDeleteGroup()
    {
        $groupController = new \Group\controllers\GroupController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $groupController->delete($request, new \Slim\Http\Response(), ['id' => self::$groupId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertEmpty($responseBody);

        //FAIL
        $response     = $groupController->delete($request, new \Slim\Http\Response(), ['id' => '12356789']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Group not found', $responseBody->errors);
    }
}
