<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ActionsControllerTest
* @author  dev <dev@maarch.org>
* @ingroup core
*/

use PHPUnit\Framework\TestCase;

class CoreControllerTest extends TestCase
{
    public function testInitialize()
    {
        $coreController = new \SrcCore\controllers\CoreController();

        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $coreController->getAdministration($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody->administrations->organisation);
        $this->assertNotEmpty($responseBody->administrations->classement);
        $this->assertNotEmpty($responseBody->administrations->production);
        $this->assertNotEmpty($responseBody->administrations->supervision);
    }

    public function testrenderJnlp()
    {
        // ERROR FILE NAME
        $coreController = new \ContentManagement\controllers\JnlpController();

        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'fileName' => 'superadmin_maarch_12345.jnlp'
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response     = $coreController->renderJnlp($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('File name forbidden', $responseBody->errors);

        // ERROR EXTENSION
        $request = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'fileName' => 'superadmin_maarchCM_12345.js'
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response     = $coreController->renderJnlp($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('File extension forbidden', $responseBody->errors);
    }
}
