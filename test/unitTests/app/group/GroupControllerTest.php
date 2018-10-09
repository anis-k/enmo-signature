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
    private static $id = null;


    public function testCreate()
    {
        $groupController = new \Group\controllers\GroupController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'group_id'      => 'TEST-JusticeLeague',
            'group_desc'    => 'Beyond the darkness',
            'security'      => [
                'where_clause'      => '1=2',
                'maarch_comment'    => 'commentateur du dimanche'
            ]
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $groupController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        self::$id = $responseBody->group . '';

        $this->assertInternalType('int', $responseBody->group);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $groupController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('TEST-JusticeLeague', $responseBody->group->group_id);
        $this->assertSame('Beyond the darkness', $responseBody->group->group_desc);
        $this->assertSame('Y', $responseBody->group->enabled);
        $this->assertSame('1=2', $responseBody->group->security->where_clause);
        $this->assertSame('commentateur du dimanche', $responseBody->group->security->maarch_comment);
        $this->assertInternalType('array', $responseBody->group->users);
        $this->assertInternalType('array', $responseBody->group->baskets);
        $this->assertEmpty($responseBody->group->users);
        $this->assertEmpty($responseBody->group->baskets);
        $this->assertSame(true, $responseBody->group->canAdminUsers);
        $this->assertSame(true, $responseBody->group->canAdminBaskets);
    }

    public function testUpdate()
    {
        $groupController = new \Group\controllers\GroupController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'description' => 'Beyond the darkness #2',
            'security'  => [
                'where_clause'  => '1=3',
                'maarch_comment'    => 'commentateur du dimanche #2'
            ]
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $groupController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $groupController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('TEST-JusticeLeague', $responseBody->group->group_id);
        $this->assertSame('Beyond the darkness #2', $responseBody->group->group_desc);
        $this->assertSame('Y', $responseBody->group->enabled);
        $this->assertSame('1=3', $responseBody->group->security->where_clause);
        $this->assertSame('commentateur du dimanche #2', $responseBody->group->security->maarch_comment);
        $this->assertInternalType('array', $responseBody->group->users);
        $this->assertInternalType('array', $responseBody->group->baskets);
        $this->assertEmpty($responseBody->group->users);
        $this->assertEmpty($responseBody->group->baskets);
        $this->assertSame(true, $responseBody->group->canAdminUsers);
        $this->assertSame(true, $responseBody->group->canAdminBaskets);
    }

    public function testDelete()
    {
        $groupController = new \Group\controllers\GroupController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $groupController->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->groups);
        $this->assertNotEmpty($responseBody->groups);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $groupController->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('Group not found', $responseBody->errors);
    }

}
