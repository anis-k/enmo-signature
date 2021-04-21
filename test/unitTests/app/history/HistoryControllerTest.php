<?php

/**
* Copyright Maarch since 2008 under license.
* See LICENSE.txt file at the root folder for more details.
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

        $this->assertIsArray($responseBody->history);
        $this->assertNotEmpty($responseBody->history);

        $this->assertIsString($responseBody->history[0]->code);
        $this->assertIsString($responseBody->history[0]->type);
        $this->assertIsString($responseBody->history[0]->user);
        $this->assertIsString($responseBody->history[0]->date);
        $this->assertIsString($responseBody->history[0]->message);
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
