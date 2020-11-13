<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class WorkflowTemplateControllerTest extends TestCase
{
    private static $workflowTemplateId = null;

    public function testCreate()
    {
        $wtController = new \Workflow\controllers\WorkflowTemplateController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $args = [
            'title'     => 'mon workflow',
            'items'     => [
                [
                    'userId'        => 1,
                    'mode'          => 'visa',
                    'signatureMode' => 'stamp'
                ],
                [
                    'userId'        => 3,
                    'mode'          => 'sign',
                    'signatureMode' => 'stamp'
                ]
            ]
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $wtController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(200, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertIsNumeric($responseBody['id']);
        self::$workflowTemplateId = $responseBody['id'];


        //ERRORS
        unset($args['items'][0]['userId']);
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $wtController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(400, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Item[0] userId is empty', $responseBody['errors']);

        unset($args['title']);
        $fullRequest = \httpRequestCustom::addContentInBody($args, $request);
        $response     = $wtController->create($fullRequest, new \Slim\Http\Response());
        $this->assertSame(400, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Body title is empty or not a string', $responseBody['errors']);
    }

    public function testGet()
    {
        $wtController = new \Workflow\controllers\WorkflowTemplateController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $wtController->get($request, new \Slim\Http\Response());
        $this->assertSame(200, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($responseBody['workflowTemplates']);
        $this->assertNotEmpty($responseBody['workflowTemplates']);
    }

    public function testGetById()
    {
        $wtController = new \Workflow\controllers\WorkflowTemplateController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $wtController->getById($request, new \Slim\Http\Response(), ['id' => self::$workflowTemplateId]);
        $this->assertSame(200, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('mon workflow', $responseBody['workflowTemplate']['title']);
        $this->assertSame(1, $responseBody['workflowTemplate']['items'][0]['userId']);
        $this->assertNotEmpty($responseBody['workflowTemplate']['items'][0]['userLabel']);
        $this->assertSame('visa', $responseBody['workflowTemplate']['items'][0]['mode']);
        $this->assertSame('stamp', $responseBody['workflowTemplate']['items'][0]['signatureMode']);
        $this->assertSame(3, $responseBody['workflowTemplate']['items'][1]['userId']);
        $this->assertNotEmpty($responseBody['workflowTemplate']['items'][1]['userLabel']);
        $this->assertSame('sign', $responseBody['workflowTemplate']['items'][1]['mode']);
        $this->assertSame('stamp', $responseBody['workflowTemplate']['items'][1]['signatureMode']);

        //ERRORS
        $previousUserId = $GLOBALS['id'];
        $GLOBALS['id']  = 2;

        $response     = $wtController->getById($request, new \Slim\Http\Response(), ['id' => self::$workflowTemplateId]);
        $this->assertSame(403, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Workflow template out of perimeter', $responseBody['errors']);

        $GLOBALS['id']  = $previousUserId;
    }

    public function testDelete()
    {
        $wtController = new \Workflow\controllers\WorkflowTemplateController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $wtController->delete($request, new \Slim\Http\Response(), ['id' => self::$workflowTemplateId]);
        $this->assertSame(204, $response->getStatusCode());

        //ERRORS
        $response     = $wtController->delete($request, new \Slim\Http\Response(), ['id' => 9999999]);
        $this->assertSame(400, $response->getStatusCode());

        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertSame('Workflow template does not exist', $responseBody['errors']);
    }
}
