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
    private static $signatureId = null;

    public function testGet()
    {
        $userController = new \User\controllers\UserController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $userController->get($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->users);
        $this->assertNotEmpty($responseBody->users);
    }

    public function testGetById()
    {
        $userController = new \User\controllers\UserController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $userController->getById($request, new \Slim\Http\Response(), ['id' => 1]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(1, $responseBody->user->id);
        $this->assertSame('jjane@maarch.com', $responseBody->user->email);
        $this->assertSame('Jenny', $responseBody->user->firstname);
        $this->assertSame('JANE', $responseBody->user->lastname);

        $response     = $userController->getById($request, new \Slim\Http\Response(), ['id' => -1]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('User out of perimeter', $responseBody->errors);
    }

    public function testUpdate()
    {
        $userController = new \User\controllers\UserController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'firstname' => 'Jolly',
            'lastname'  => 'Jumper'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->update($fullRequest, new \Slim\Http\Response(), ['id' => 1]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->user);
        $this->assertNotEmpty($responseBody->user);

        $response     = $userController->getById($request, new \Slim\Http\Response(), ['id' => 1]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Jolly', $responseBody->user->firstname);
        $this->assertSame('Jumper', $responseBody->user->lastname);

        $aArgs = [
            'firstname' => 'Jenny',
            'lastname'  => 'JANE'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->update($fullRequest, new \Slim\Http\Response(), ['id' => 1]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->user);
        $this->assertNotEmpty($responseBody->user);

        $response     = $userController->getById($request, new \Slim\Http\Response(), ['id' => 1]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Jenny', $responseBody->user->firstname);
        $this->assertSame('JANE', $responseBody->user->lastname);
    }

    public function testCreateSignature()
    {
        $userController = new \User\controllers\UserController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'encodedSignature'  => base64_encode(file_get_contents('test/unitTests/samples/signature.jpg')),
            'format'            => 'jpg'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->createSignature($fullRequest, new \Slim\Http\Response(), ['id' => 1]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('int', $responseBody->signatureId);
        self::$signatureId = $responseBody->signatureId;
    }

    public function testGetSignatures()
    {
        $userController = new \User\controllers\UserController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $userController->getSignatures($request, new \Slim\Http\Response(), ['id' => 1]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->signatures);
        $this->assertNotEmpty($responseBody->signatures);
    }

    public function testDeleteSignature()
    {
        $userController = new \User\controllers\UserController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $userController->deleteSignature($request, new \Slim\Http\Response(), ['id' => 1, 'signatureId' => self::$signatureId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);
    }
}
