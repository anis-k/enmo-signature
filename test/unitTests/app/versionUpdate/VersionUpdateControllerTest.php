<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

use PHPUnit\Framework\TestCase;

class VersionUpdateControllerTest extends TestCase
{
    public function testGet()
    {
        $versionUpdateController = new \VersionUpdate\controllers\VersionUpdateController();

        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $versionUpdateController->get($request, new \Slim\Http\Response());
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->currentMinorVersions);
        $this->assertNotNull($responseBody->currentMinorVersions);
        $this->assertInternalType('array', $responseBody->availableMajorVersions);
        $this->assertInternalType('string', $responseBody->currentVersion);
        $this->assertNotNull($responseBody->currentVersion);
    }
}
