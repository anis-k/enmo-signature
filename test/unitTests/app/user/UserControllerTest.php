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
    private static $userId = null;

    public function testCreateUser()
    {
        $userController = new \User\controllers\UserController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'login'     => 'emailLogin',
            'firstname' => 'Prénom',
            'lastname'  => 'Nom',
            'email'     => 'email@test.fr'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());
        
        $this->assertInternalType('int', $responseBody->id);
        self::$userId = $responseBody->id;

        //Mail missing
        $aArgs = [
            'login'     => 'failLogin',
            'firstname' => 'Prénom',
            'lastname'  => 'Nom'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Body email is empty or not a valid email', $responseBody->errors);

        //Mail wrong format
        $aArgs = [
            'login'     => 'failLogin',
            'firstname' => 'Prénom',
            'lastname'  => 'Nom',
            'email'     => 'emailtest.fr'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Body email is empty or not a valid email', $responseBody->errors);
    }

    public function testUpdatePassword()
    {
        $userController = new \User\controllers\UserController();

        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'currentPassword'          => 'maarch',
            'newPassword'              => 'maarch2',
            'passwordConfirmation'     => 'maarch2'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->updatePassword($fullRequest, new \Slim\Http\Response(), ['id' => self::$userId]);
        $responseBody = json_decode((string)$response->getBody());
        
        $this->assertEmpty($responseBody);

        //Error
        $aArgs = [
            'currentPassword'          => 'maarch3',
            'newPassword'              => 'maarch',
            'passwordConfirmation'     => 'maarch'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->updatePassword($fullRequest, new \Slim\Http\Response(), ['id' => self::$userId]);
        $responseBody = json_decode((string)$response->getBody());
        
        $this->assertSame('Wrong Password', $responseBody->errors);

        //Error
        $aArgs = [
            'currentPassword'          => 'maarch2',
            'newPassword'              => 'maa',
            'passwordConfirmation'     => 'maa'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->updatePassword($fullRequest, new \Slim\Http\Response(), ['id' => self::$userId]);
        $responseBody = json_decode((string)$response->getBody());
        
        $this->assertSame('Password does not match security criteria', $responseBody->errors);

        //Error
        $aArgs = [
            'currentPassword'          => 'maarch2',
            'newPassword'              => 'maarch1',
            'passwordConfirmation'     => 'maarch'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->updatePassword($fullRequest, new \Slim\Http\Response(), ['id' => self::$userId]);
        $responseBody = json_decode((string)$response->getBody());
        
        $this->assertSame('Body newPassword and passwordConfirmation must be identical', $responseBody->errors);
    }

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

        $response     = $userController->getById($request, new \Slim\Http\Response(), ['id' => self::$userId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame(self::$userId, $responseBody->user->id);
        $this->assertSame('email@test.fr', $responseBody->user->email);
        $this->assertSame('Prénom', $responseBody->user->firstname);
        $this->assertSame('Nom', $responseBody->user->lastname);

        $response     = $userController->getById($request, new \Slim\Http\Response(), ['id' => -1]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertEmpty($responseBody->users);
    }

    public function testUpdate()
    {
        $userController = new \User\controllers\UserController();

        // UPDATE PREFERENCE

        $previousUserId = $GLOBALS['id'];
        $GLOBALS['id']  = self::$userId;
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'writingMode'   => 'stylus',
            'writingSize'   => 2,
            'writingColor'  => '#F1F1F1',
            'lang'          => 'fr',
            'notifications' => false
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->updatePreferences($fullRequest, new \Slim\Http\Response(), ['id' => self::$userId]);
        $responseBodyPreferences = json_decode((string)$response->getBody());
        $GLOBALS['id'] = $previousUserId;
        $this->assertEmpty($responseBodyPreferences);

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'firstname'     => 'Jolly',
            'lastname'      => 'Jumper',
            'email'         => 'email@test.fr'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$userId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('object', $responseBody->user);
        $this->assertNotEmpty($responseBody->user);

        $response     = $userController->getById($request, new \Slim\Http\Response(), ['id' => self::$userId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Jolly', $responseBody->user->firstname);
        $this->assertSame('Jumper', $responseBody->user->lastname);

        $preferences = json_decode($responseBody->user->preferences, true);
        $this->assertSame('stylus', $preferences['writingMode']);
        $this->assertSame(2, $preferences['writingSize']);
        $this->assertSame('#F1F1F1', $preferences['writingColor']);
        $this->assertSame('fr', $preferences['lang']);
        $this->assertSame(false, $preferences['notifications']);
    }

    public function testCreateSignature()
    {
        $previousUserId = $GLOBALS['id'];
        $GLOBALS['id']  = self::$userId;
        $signatureController = new \User\controllers\SignatureController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'encodedSignature'  => base64_encode(file_get_contents('test/unitTests/samples/signature.jpg')),
            'format'            => 'jpg'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $signatureController->create($fullRequest, new \Slim\Http\Response(), ['id' => self::$userId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('int', $responseBody->signatureId);
        self::$signatureId = $responseBody->signatureId;
        $GLOBALS['id'] = $previousUserId;
    }

    public function testGetSignatures()
    {
        $previousUserId = $GLOBALS['id'];
        $GLOBALS['id']  = self::$userId;
        $signatureController = new \User\controllers\SignatureController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $signatureController->get($request, new \Slim\Http\Response(), ['id' => self::$userId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->signatures);
        $this->assertNotEmpty($responseBody->signatures);
        $GLOBALS['id'] = $previousUserId;
    }

    public function testDeleteSignature()
    {
        $previousUserId = $GLOBALS['id'];
        $GLOBALS['id']  = self::$userId;
        $signatureController = new \User\controllers\SignatureController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $signatureController->delete($request, new \Slim\Http\Response(), ['id' => self::$userId, 'signatureId' => self::$signatureId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        \SrcCore\models\DatabaseModel::delete([
            'table' => 'users',
            'where' => ['id = ?'],
            'data'  => [self::$userId]
        ]);
        $GLOBALS['id'] = $previousUserId;
    }
}
