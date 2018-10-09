<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class ListTemplateControllerTest extends TestCase
{
    private static $id = null;

    public function testCreate()
    {
        $listTemplateController = new \Entity\controllers\ListTemplateController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'object_type'       => 'VISA_CIRCUIT',
            'title'             => 'TEST-LISTTEMPLATE123-TITLE',
            'description'       => 'TEST LISTTEMPLATE123 DESCRIPTION',
            'items'             => [
                [
                    'sequence'  => 0,
                    'item_id'   => 'bbain',
                    'item_type' => 'user_id',
                    'item_mode' => 'visa'
                ],
                [
                    'sequence'  => 1,
                    'item_id'   => 'ssissoko',
                    'item_type' => 'user_id',
                    'item_mode' => 'visa'
                ],
                [
                    'sequence'  => 0,
                    'item_id'   => 'bboule',
                    'item_type' => 'user_id',
                    'item_mode' => 'sign'
                ]
            ],
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $listTemplateController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('int', $responseBody->id);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $listTemplateController->get($request, new \Slim\Http\Response());
        $responseBody   = json_decode((string)$response->getBody());

        foreach ($responseBody->listTemplates as $listTemplate) {
            if ($listTemplate->title == 'TEST-LISTTEMPLATE123-TITLE') {
                self::$id = $listTemplate->id;
                $this->assertSame('VISA_CIRCUIT', $listTemplate->object_type);
                $this->assertSame('VISA_CIRCUIT_', substr($listTemplate->object_id, 0, 13));
                $this->assertSame('TEST LISTTEMPLATE123 DESCRIPTION', $listTemplate->description);
            }
        }

        $this->assertNotEmpty(self::$id);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $listTemplateController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('VISA_CIRCUIT', $responseBody->listTemplate->diffusionList[0]->object_type);
        $this->assertSame('TEST-LISTTEMPLATE123-TITLE', $responseBody->listTemplate->diffusionList[0]->title);
        $this->assertSame('TEST LISTTEMPLATE123 DESCRIPTION', $responseBody->listTemplate->diffusionList[0]->description);
        $this->assertSame(0, $responseBody->listTemplate->diffusionList[0]->sequence);
        $this->assertSame('bbain', $responseBody->listTemplate->diffusionList[0]->item_id);
        $this->assertSame('user_id', $responseBody->listTemplate->diffusionList[0]->item_type);
        $this->assertSame('visa', $responseBody->listTemplate->diffusionList[0]->item_mode);
        $this->assertSame('Y', $responseBody->listTemplate->diffusionList[0]->visible);

        $this->assertSame('VISA_CIRCUIT', $responseBody->listTemplate->diffusionList[1]->object_type);
        $this->assertSame('TEST-LISTTEMPLATE123-TITLE', $responseBody->listTemplate->diffusionList[1]->title);
        $this->assertSame('TEST LISTTEMPLATE123 DESCRIPTION', $responseBody->listTemplate->diffusionList[1]->description);
        $this->assertSame(1, $responseBody->listTemplate->diffusionList[1]->sequence);
        $this->assertSame('ssissoko', $responseBody->listTemplate->diffusionList[1]->item_id);
        $this->assertSame('user_id', $responseBody->listTemplate->diffusionList[1]->item_type);
        $this->assertSame('visa', $responseBody->listTemplate->diffusionList[1]->item_mode);
        $this->assertSame('Y', $responseBody->listTemplate->diffusionList[1]->visible);

        $this->assertSame('VISA_CIRCUIT', $responseBody->listTemplate->diffusionList[2]->object_type);
        $this->assertSame('TEST-LISTTEMPLATE123-TITLE', $responseBody->listTemplate->diffusionList[2]->title);
        $this->assertSame('TEST LISTTEMPLATE123 DESCRIPTION', $responseBody->listTemplate->diffusionList[2]->description);
        $this->assertSame(0, $responseBody->listTemplate->diffusionList[2]->sequence);
        $this->assertSame('bboule', $responseBody->listTemplate->diffusionList[2]->item_id);
        $this->assertSame('user_id', $responseBody->listTemplate->diffusionList[2]->item_type);
        $this->assertSame('sign', $responseBody->listTemplate->diffusionList[2]->item_mode);
        $this->assertSame('Y', $responseBody->listTemplate->diffusionList[2]->visible);
    }

    public function testUpdate()
    {
        $listTemplateController = new \Entity\controllers\ListTemplateController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'title'             => 'TEST-LISTTEMPLATE123-TITLE-UPDATED',
            'description'       => 'TEST LISTTEMPLATE123 DESCRIPTION UPDATED',
            'items'             => [
                [
                    'sequence'  => 0,
                    'item_id'   => 'kkaar',
                    'item_type' => 'user_id',
                    'item_mode' => 'visa'
                ],
                [
                    'sequence'  => 0,
                    'item_id'   => 'ppetit',
                    'item_type' => 'user_id',
                    'item_mode' => 'sign'
                ]
            ],
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $listTemplateController->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('int', $responseBody->id);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $listTemplateController->get($request, new \Slim\Http\Response());
        $responseBody   = json_decode((string)$response->getBody());

        self::$id = null;
        foreach ($responseBody->listTemplates as $listTemplate) {
            if ($listTemplate->title == 'TEST-LISTTEMPLATE123-TITLE-UPDATED') {
                self::$id = $listTemplate->id;
                $this->assertSame('VISA_CIRCUIT', $listTemplate->object_type);
                $this->assertSame('VISA_CIRCUIT_', substr($listTemplate->object_id, 0, 13));
                $this->assertSame('TEST LISTTEMPLATE123 DESCRIPTION UPDATED', $listTemplate->description);
            }
        }
        $this->assertNotEmpty(self::$id);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $listTemplateController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('VISA_CIRCUIT', $responseBody->listTemplate->diffusionList[0]->object_type);
        $this->assertSame('TEST-LISTTEMPLATE123-TITLE-UPDATED', $responseBody->listTemplate->diffusionList[0]->title);
        $this->assertSame('TEST LISTTEMPLATE123 DESCRIPTION UPDATED', $responseBody->listTemplate->diffusionList[0]->description);
        $this->assertSame(0, $responseBody->listTemplate->diffusionList[0]->sequence);
        $this->assertSame('kkaar', $responseBody->listTemplate->diffusionList[0]->item_id);
        $this->assertSame('user_id', $responseBody->listTemplate->diffusionList[0]->item_type);
        $this->assertSame('visa', $responseBody->listTemplate->diffusionList[0]->item_mode);
        $this->assertSame('Y', $responseBody->listTemplate->diffusionList[0]->visible);

        $this->assertSame('VISA_CIRCUIT', $responseBody->listTemplate->diffusionList[1]->object_type);
        $this->assertSame('TEST-LISTTEMPLATE123-TITLE-UPDATED', $responseBody->listTemplate->diffusionList[1]->title);
        $this->assertSame('TEST LISTTEMPLATE123 DESCRIPTION UPDATED', $responseBody->listTemplate->diffusionList[1]->description);
        $this->assertSame(0, $responseBody->listTemplate->diffusionList[1]->sequence);
        $this->assertSame('ppetit', $responseBody->listTemplate->diffusionList[1]->item_id);
        $this->assertSame('user_id', $responseBody->listTemplate->diffusionList[1]->item_type);
        $this->assertSame('sign', $responseBody->listTemplate->diffusionList[1]->item_mode);
        $this->assertSame('Y', $responseBody->listTemplate->diffusionList[1]->visible);

        $this->assertSame(null, $responseBody->listTemplate->diffusionList[2]);
    }

    public function testDelete()
    {
        $listTemplateController = new \Entity\controllers\ListTemplateController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $listTemplateController->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $listTemplateController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('List template not found', $responseBody->errors);
    }
}
