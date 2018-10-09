<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class ReportControllerTest extends TestCase
{
    private static $id = null;

    public function testGetGroups()
    {
        $reportController = new \Report\controllers\ReportController();

        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $reportController->getGroups($request, new \Slim\Http\Response());
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->groups);

        self::$id = $responseBody->groups[0]->group_id;
    }

    public function testUpdateForGroupId()
    {
        $reportController = new \Report\controllers\ReportController();

        //  GET
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $reportController->getByGroupId($request, new \Slim\Http\Response(), ['groupId' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->reports);

        foreach ($responseBody->reports as $key => $report) {
            $responseBody->reports[$key]->checked = true;
        }

        //  PUT
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $fullRequest = \httpRequestCustom::addContentInBody($responseBody->reports, $request);

        $response       = $reportController->updateForGroupId($fullRequest, new \Slim\Http\Response(), ['groupId' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $reportController->getByGroupId($request, new \Slim\Http\Response(), ['groupId' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->reports);

        foreach ($responseBody->reports as $key => $report) {
            $this->assertSame(true, $report->checked);
            if ($key % 2) {
                $responseBody->reports[$key]->checked = false;
            }
        }

        //  PUT
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $fullRequest = \httpRequestCustom::addContentInBody($responseBody->reports, $request);

        $response       = $reportController->updateForGroupId($fullRequest, new \Slim\Http\Response(), ['groupId' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $reportController->getByGroupId($request, new \Slim\Http\Response(), ['groupId' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->reports);

        foreach ($responseBody->reports as $key => $report) {
            if ($key % 2) {
                $this->assertSame(false, $report->checked);
            } else {
                $this->assertSame(true, $report->checked);
            }
        }
    }
}
