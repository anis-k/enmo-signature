<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class EntityControllerTest extends TestCase
{
    private static $id = null;

    public function testCreate()
    {
        $entityController = new \Entity\controllers\EntityController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'entity_id'         => 'TEST-ENTITY123',
            'entity_label'      => 'TEST-ENTITY123-LABEL',
            'short_label'       => 'TEST-ENTITY123-SHORTLABEL',
            'entity_type'       => 'Service',
            'email'             => 'paris@isMagic.fr',
            'adrs_1'            => '1 rue du parc des princes',
            'zipcode'           => '75016',
            'city'              => 'PARIS',
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $entityController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->entities);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $entityController->getById($request, new \Slim\Http\Response(), ['id' => 'TEST-ENTITY123']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('TEST-ENTITY123', $responseBody->entity->entity_id);
        $this->assertSame('TEST-ENTITY123-LABEL', $responseBody->entity->entity_label);
        $this->assertSame('TEST-ENTITY123-SHORTLABEL', $responseBody->entity->short_label);
        $this->assertSame('Service', $responseBody->entity->entity_type);
        $this->assertSame('Y', $responseBody->entity->enabled);
        $this->assertSame('paris@isMagic.fr', $responseBody->entity->email);
        $this->assertSame('1 rue du parc des princes', $responseBody->entity->adrs_1);
        $this->assertSame(null, $responseBody->entity->adrs_2);
        $this->assertSame(null, $responseBody->entity->adrs_3);
        $this->assertSame('75016', $responseBody->entity->zipcode);
        $this->assertSame('PARIS', $responseBody->entity->city);
        $this->assertSame(null, $responseBody->entity->parent_entity_id);
    }

    public function testUpdate()
    {
        $entityController = new \Entity\controllers\EntityController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'entity_label'      => 'TEST-ENTITY123-LABEL',
            'short_label'       => 'TEST-ENTITY123-SHORTLABEL-UP',
            'entity_type'       => 'Direction',
            'email'             => 'paris@isMagic2.fr',
            'adrs_2'            => '2 rue des princes'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $entityController->update($fullRequest, new \Slim\Http\Response(), ['id' => 'TEST-ENTITY123']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->entities);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $entityController->getById($request, new \Slim\Http\Response(), ['id' => 'TEST-ENTITY123']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('TEST-ENTITY123', $responseBody->entity->entity_id);
        $this->assertSame('TEST-ENTITY123-LABEL', $responseBody->entity->entity_label);
        $this->assertSame('TEST-ENTITY123-SHORTLABEL-UP', $responseBody->entity->short_label);
        $this->assertSame('Direction', $responseBody->entity->entity_type);
        $this->assertSame('Y', $responseBody->entity->enabled);
        $this->assertSame('paris@isMagic2.fr', $responseBody->entity->email);
        $this->assertSame('1 rue du parc des princes', $responseBody->entity->adrs_1);
        $this->assertSame('2 rue des princes', $responseBody->entity->adrs_2);
        $this->assertSame(null, $responseBody->entity->adrs_3);
        $this->assertSame('75016', $responseBody->entity->zipcode);
        $this->assertSame('PARIS', $responseBody->entity->city);
        $this->assertSame(null, $responseBody->entity->parent_entity_id);
    }

    public function testUpdateStatus()
    {
        $entityController = new \Entity\controllers\EntityController();

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'method'            => 'disable'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $entityController->updateStatus($fullRequest, new \Slim\Http\Response(), ['id' => 'TEST-ENTITY123']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $entityController->getById($request, new \Slim\Http\Response(), ['id' => 'TEST-ENTITY123']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('TEST-ENTITY123', $responseBody->entity->entity_id);
        $this->assertSame('N', $responseBody->entity->enabled);

        //  UPDATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'PUT']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $aArgs = [
            'method'            => 'enable'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $entityController->updateStatus($fullRequest, new \Slim\Http\Response(), ['id' => 'TEST-ENTITY123']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $entityController->getById($request, new \Slim\Http\Response(), ['id' => 'TEST-ENTITY123']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('TEST-ENTITY123', $responseBody->entity->entity_id);
        $this->assertSame('Y', $responseBody->entity->enabled);
    }

    public function testGet()
    {
        $entityController = new \Entity\controllers\EntityController();

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $entityController->get($request, new \Slim\Http\Response());
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->entities);
        $this->assertNotNull($responseBody->entities);
    }

    public function testGetDetailledById()
    {
        $entityController = new \Entity\controllers\EntityController();

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $entityController->getDetailledById($request, new \Slim\Http\Response(), ['id' => 'TEST-ENTITY123']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('TEST-ENTITY123', $responseBody->entity->entity_id);
        $this->assertSame('TEST-ENTITY123-LABEL', $responseBody->entity->entity_label);
        $this->assertSame('TEST-ENTITY123-SHORTLABEL-UP', $responseBody->entity->short_label);
        $this->assertSame('Direction', $responseBody->entity->entity_type);
        $this->assertSame('Y', $responseBody->entity->enabled);
        $this->assertSame('paris@isMagic2.fr', $responseBody->entity->email);
        $this->assertSame('1 rue du parc des princes', $responseBody->entity->adrs_1);
        $this->assertSame('2 rue des princes', $responseBody->entity->adrs_2);
        $this->assertSame(null, $responseBody->entity->adrs_3);
        $this->assertSame('75016', $responseBody->entity->zipcode);
        $this->assertSame('PARIS', $responseBody->entity->city);
        $this->assertSame(null, $responseBody->entity->parent_entity_id);
        $this->assertInternalType('array', (array) $responseBody->entity->listTemplate);
        $this->assertInternalType('array', $responseBody->entity->visaTemplate);
        $this->assertSame(false, $responseBody->entity->hasChildren);
        $this->assertSame(0, $responseBody->entity->documents);
        $this->assertInternalType('array', $responseBody->entity->users);
        $this->assertSame(0, $responseBody->entity->templates);
        $this->assertSame(0, $responseBody->entity->instances);
        $this->assertSame(0, $responseBody->entity->redirects);
    }

    public function testDelete()
    {
        $entityController = new \Entity\controllers\EntityController();

        //  DELETE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $entityController->delete($request, new \Slim\Http\Response(), ['id' => 'TEST-ENTITY123']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->entities);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $entityController->getById($request, new \Slim\Http\Response(), ['id' => 'TEST-ENTITY123']);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertSame('Entity not found', $responseBody->errors);
    }
}
