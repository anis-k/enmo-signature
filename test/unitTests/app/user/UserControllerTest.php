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
            'firstname' => 'Prénom',
            'lastname'  => 'Nom',
            'email'     => 'email@test.fr'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());
        
        $this->assertNotEmpty($responseBody->user);
        $this->assertInternalType('int', $responseBody->user->id);
        $this->assertSame('email@test.fr', $responseBody->user->email);
        $this->assertSame('Prénom', $responseBody->user->firstname);
        $this->assertSame('Nom', $responseBody->user->lastname);
        self::$userId = $responseBody->user->id;

        //Mail missing
        $aArgs = [
            'firstname' => 'Prénom',
            'lastname'  => 'Nom'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('L\'email est vide ou le format n\'est pas correct', $responseBody->errors);

        //Mail wrong format
        $aArgs = [
            'firstname' => 'Prénom',
            'lastname'  => 'Nom',
            'email'     => 'emailtest.fr'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('L\'email est vide ou le format n\'est pas correct', $responseBody->errors);
    }

    public function testUpdatePassword()
    {
        $userController = new \User\controllers\UserController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'currentPassword'          => 'maarch',
            'newPassword'              => 'maarch2',
            'passwordConfirmation'     => 'maarch2'
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->updatePassword($fullRequest, new \Slim\Http\Response(), ['id' => self::$userId]);
        $responseBody = json_decode((string)$response->getBody());
        
        $this->assertSame('success', $responseBody->success);

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
        
        $this->assertSame('New password does not match password confirmation', $responseBody->errors);
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

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'firstname'     => 'Jolly',
            'lastname'      => 'Jumper',
            'preferences'   => [
                'writingMode'   => 'stylus',
                'writingSize'   => 2,
                'writingColor'  => '#F1F1F1',
            ]
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
        $this->assertSame('stylus', $responseBody->user->preferences->writingMode);
        $this->assertSame(2, $responseBody->user->preferences->writingSize);
        $this->assertSame('#F1F1F1', $responseBody->user->preferences->writingColor);

        $aArgs = [
            'firstname'     => 'Jenny',
            'lastname'      => 'JANE',
            'preferences'   => [
                'writingMode'   => 'direct',
                'writingSize'   => 1,
                'writingColor'  => '#000000',
            ]
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $userController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$userId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('object', $responseBody->user);
        $this->assertNotEmpty($responseBody->user);

        $response     = $userController->getById($request, new \Slim\Http\Response(), ['id' => self::$userId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Jenny', $responseBody->user->firstname);
        $this->assertSame('JANE', $responseBody->user->lastname);
        $this->assertSame('direct', $responseBody->user->preferences->writingMode);
        $this->assertSame(1, $responseBody->user->preferences->writingSize);
        $this->assertSame('#000000', $responseBody->user->preferences->writingColor);
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
        $response     = $userController->createSignature($fullRequest, new \Slim\Http\Response(), ['id' => self::$userId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('int', $responseBody->signatureId);
        self::$signatureId = $responseBody->signatureId;
    }

    public function testGetSignatures()
    {
        $userController = new \User\controllers\UserController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $userController->getSignatures($request, new \Slim\Http\Response(), ['id' => self::$userId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->signatures);
        $this->assertNotEmpty($responseBody->signatures);
    }

    public function testDeleteSignature()
    {
        $userController = new \User\controllers\UserController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $userController->deleteSignature($request, new \Slim\Http\Response(), ['id' => self::$userId, 'signatureId' => self::$signatureId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        \SrcCore\models\DatabaseModel::delete([
            'table' => 'users',
            'where' => ['id = ?'],
            'data'  => [self::$userId]
        ]);
    }
}
