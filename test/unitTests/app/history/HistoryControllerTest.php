<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class HistoryControllerTest extends TestCase
{
    public function testGetHistoryByUserId()
    {
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);
        $history     = new \History\controllers\HistoryController();

        $currentUser = \User\models\UserModel::getByUserId(['userId' => $GLOBALS['userId'], 'select' => ['id']]);
        $response = $history->getByUserId($request, new \Slim\Http\Response(), ['userSerialId' => $currentUser['id']]);

        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody->histories);
    }

    public function testGetHistory()
    {
        $history     = new \History\controllers\HistoryController();

        //  GET
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'startDate' => '1521100000',
            'endDate'   => time()
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response = $history->get($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->histories);
        $this->assertInternalType('bool', $responseBody->limitExceeded);
        $this->assertNotEmpty($responseBody->histories);
    }

    public function testGetBatchHistory()
    {
        $batchHistory     = new \History\controllers\BatchHistoryController();

        //  GET
        $environment = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request     = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'startDate' => '1521100000',
            'endDate'   => time()
        ];
        $fullRequest = $request->withQueryParams($aArgs);

        $response = $batchHistory->get($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->batchHistories);
        $this->assertInternalType('bool', $responseBody->limitExceeded);
        $this->assertNotNull($responseBody->batchHistories);
    }
}
