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
    public function testGetByDocumentId()
    {
        $historyController = new \History\controllers\HistoryController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);


        //Errors
        $response     = $historyController->getByDocumentId($request, new \Slim\Http\Response(), ['id' => 'test']);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Route id is not an integer', $responseBody->errors);

        $response     = $historyController->getByDocumentId($request, new \Slim\Http\Response(), ['id' => -1]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Document does not exist', $responseBody->errors);
    }

    public function testGetByUserId()
    {
        $historyController = new \History\controllers\HistoryController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $historyController->getByUserId($request, new \Slim\Http\Response(), ['id' => 1]);
        $this->assertSame(200, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->history);
        $this->assertNotEmpty($responseBody->history);

        $this->assertInternalType('string', $responseBody->history[0]->code);
        $this->assertInternalType('string', $responseBody->history[0]->type);
        $this->assertInternalType('string', $responseBody->history[0]->user);
        $this->assertInternalType('string', $responseBody->history[0]->date);
        $this->assertInternalType('string', $responseBody->history[0]->message);
        $this->assertSame('OK', $responseBody->history[0]->code);


        //Errors
        $response     = $historyController->getByUserId($request, new \Slim\Http\Response(), ['id' => 'test']);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('Route id is not an integer', $responseBody->errors);

        $response     = $historyController->getByUserId($request, new \Slim\Http\Response(), ['id' => 99999]);
        $this->assertSame(400, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody());
        $this->assertSame('User does not exist', $responseBody->errors);
    }
}
