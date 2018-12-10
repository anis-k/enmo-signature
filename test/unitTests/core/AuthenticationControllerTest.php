<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class AuthenticationControllerTest extends TestCase
{
    public function testLog()
    {
        date_default_timezone_set(\SrcCore\models\CoreConfigModel::getTimezone());

        $authenticationController = new \SrcCore\controllers\AuthenticationController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'email'     => 'jjane@maarch.com',
            'password'  => 'maarch'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $authenticationController->log($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody->user);
        $this->assertNotEmpty($responseBody->user->id);
        $this->assertNotEmpty($responseBody->user->firstname);
        $this->assertNotEmpty($responseBody->user->lastname);
        $this->assertNotEmpty($responseBody->user->email);
        $GLOBALS['id'] = 2;

        //  ERRORS
        $aArgs = [
            'email'     => 'jjane@maarch.com',
            'password'  => 'maarche'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $authenticationController->log($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Authentication Failed', $responseBody->errors);

        //  ERRORS
        $aArgs = [
            'logi'     => 'jjane@maarch.com',
            'password'  => 'maarche'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $authenticationController->log($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Bad Request', $responseBody->errors);
    }

    public function testAuthentication()
    {
        $_SERVER['PHP_AUTH_USER'] = 'jjane@maarch.com';
        $_SERVER['PHP_AUTH_PW'] = 'maarch';

        $response = \SrcCore\controllers\AuthenticationController::authentication();

        $this->assertNotEmpty($response);
        $this->assertInternalType('int', $response);
    }

    public function testLogout()
    {
        $authenticationController = new \SrcCore\controllers\AuthenticationController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $authenticationController->logout($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);
    }
}
