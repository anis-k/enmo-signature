<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class TemplateControllerTest extends TestCase
{
    private static $id = null;
    private static $idDuplicated = null;

    public function testCreate()
    {
        $templates   = new \Template\controllers\TemplateController();

        //  CREATE
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'template_label'            => 'TEST TEMPLATE',
            'template_comment'          => 'DESCRIPTION OF THIS TEMPLATE',
            'template_target'           => 'sendmail',
            'template_attachment_type'  => 'all',
            'template_type'             => 'HTML',
            'template_content'          => 'Content of this template',
            'template_datasource'       => 'letterbox_attachment',
            'entities'                  => ['DGS', 'COU']
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $templates->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        self::$id = $responseBody->template;
        $this->assertInternalType("int", self::$id);

        // CREATE FAIL
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'template_label'            => '',
            'template_comment'          => '',
            'template_target'           => 'sendmail',
            'template_attachment_type'  => 'all',
            'template_type'             => 'HTML',
            'template_content'          => 'Content of this template',
            'template_datasource'       => 'letterbox_attachment'
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $templates->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Bad Request', $responseBody->errors);
    }

    public function testRead()
    {
        $templates   = new \Template\controllers\TemplateController();

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $templates->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('int', $responseBody->template->template_id);
        $this->assertSame('TEST TEMPLATE', $responseBody->template->template_label);
        $this->assertSame('DESCRIPTION OF THIS TEMPLATE', $responseBody->template->template_comment);
        $this->assertSame('sendmail', $responseBody->template->template_target);
        $this->assertSame('all', $responseBody->template->template_attachment_type);
        $this->assertSame('HTML', $responseBody->template->template_type);
        $this->assertSame('Content of this template', $responseBody->template->template_content);
        $this->assertSame('letterbox_attachment', $responseBody->template->template_datasource);
        $this->assertNotNull($responseBody->templatesModels);
        $this->assertNotNull($responseBody->templatesModels[0]->fileName);
        $this->assertNotNull($responseBody->templatesModels[0]->fileExt);
        $this->assertNotNull($responseBody->templatesModels[0]->filePath);
        $this->assertNotNull($responseBody->attachmentTypes);
        $this->assertNotNull($responseBody->attachmentTypes[0]->label);
        $this->assertNotNull($responseBody->attachmentTypes[0]->id);
        $this->assertNotNull($responseBody->datasources);
        $this->assertNotNull($responseBody->datasources[0]->id);
        $this->assertNotNull($responseBody->datasources[0]->label);
        $this->assertNotNull($responseBody->datasources[0]->script);
        $this->assertNotNull($responseBody->datasources[0]->target);
        $this->assertNotNull($responseBody->entities);
        $this->assertNotNull($responseBody->entities[0]->entity_id);
        $this->assertNotNull($responseBody->entities[0]->entity_label);

        //  READ FAIL
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $templates->getDetailledById($request, new \Slim\Http\Response(), ['id' => '11119999']);
        $responseBody   = json_decode((string)$response->getBody());
        $this->assertSame('Template does not exist', $responseBody->errors);
    }

    public function testUpdate()
    {
        $templates   = new \Template\controllers\TemplateController();

        ########## UPDATE ##########
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'template_label'            => 'TEST TEMPLATE UPDATE',
            'template_comment'          => 'DESCRIPTION OF THIS TEMPLATE UPDATE',
            'template_target'           => 'sendmail',
            'template_attachment_type'  => 'all',
            'template_type'             => 'HTML',
            'template_content'          => 'Content of this template',
            'template_datasource'       => 'letterbox_attachment',
            'entities'                  => []
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $templates->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame("success", $responseBody->success);

        //  READ
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $templates->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertInternalType('int', $responseBody->template->template_id);
        $this->assertSame('TEST TEMPLATE UPDATE', $responseBody->template->template_label);
        $this->assertSame('DESCRIPTION OF THIS TEMPLATE UPDATE', $responseBody->template->template_comment);
        $this->assertSame('sendmail', $responseBody->template->template_target);
        $this->assertSame('all', $responseBody->template->template_attachment_type);
        $this->assertSame('HTML', $responseBody->template->template_type);
        $this->assertSame('Content of this template', $responseBody->template->template_content);
        $this->assertSame('letterbox_attachment', $responseBody->template->template_datasource);

        ########## UPDATE FAIL MISSING PARAMETERS ##########
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'template_label'            => '',
            'template_comment'          => '',
            'template_target'           => 'sendmail',
            'template_attachment_type'  => 'all',
            'template_type'             => 'HTML',
            'template_content'          => 'Content of this template',
            'template_datasource'       => 'letterbox_attachment',
            'entities'                  => []
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $templates->update($fullRequest, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame("Bad Request", $responseBody->errors);

        ########## UPDATE FAIL WRONG ID ##########
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'template_label'            => '',
            'template_comment'          => '',
            'template_target'           => 'sendmail',
            'template_attachment_type'  => 'all',
            'template_type'             => 'HTML',
            'template_content'          => 'Content of this template',
            'template_datasource'       => 'letterbox_attachment',
            'entities'                  => []
        ];
        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        $response     = $templates->update($fullRequest, new \Slim\Http\Response(), ['id' => '1235789']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Template does not exist', $responseBody->errors);
    }

    public function testDuplicate()
    {
        $templates   = new \Template\controllers\TemplateController();

        ########## DUPLICATE ##########
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $templates->duplicate($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        self::$idDuplicated = $responseBody->id;
        $this->assertInternalType("int", self::$idDuplicated);

        //  READ
        $environment  = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request      = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $templates->getDetailledById($request, new \Slim\Http\Response(), ['id' => self::$idDuplicated]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('int', $responseBody->template->template_id);
        $this->assertSame('Copie de TEST TEMPLATE UPDATE', $responseBody->template->template_label);
        $this->assertSame('DESCRIPTION OF THIS TEMPLATE UPDATE', $responseBody->template->template_comment);
        $this->assertSame('sendmail', $responseBody->template->template_target);
        $this->assertSame('all', $responseBody->template->template_attachment_type);
        $this->assertSame('HTML', $responseBody->template->template_type);
        $this->assertSame('Content of this template', $responseBody->template->template_content);
        $this->assertSame('letterbox_attachment', $responseBody->template->template_datasource);

        ########## DUPLICATE FAIL ##########
        $environment  = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request      = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $templates->duplicate($request, new \Slim\Http\Response(), ['id' => 139875323456]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Template not found', $responseBody->errors);
    }

    public function testGetList()
    {
        $templates   = new \Template\controllers\TemplateController();

        //  READ
        $environment  = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request      = \Slim\Http\Request::createFromEnvironment($environment);
        $response     = $templates->get($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $idFound = false;
        $idDuplicatedFound = false;
        foreach ($responseBody->templates as $template) {
            $this->assertInternalType('int', $template->template_id);
            $this->assertNotNull($template->template_label);
            $this->assertNotNull($template->template_comment);
            $this->assertNotNull($template->template_type);

            if ($template->template_id == self::$id) {
                $idFound = true;
            }
            if ($template->template_id == self::$idDuplicated) {
                $idDuplicatedFound = true;
            }
        }

        $this->assertSame(true, $idFound);
        $this->assertSame(true, $idDuplicatedFound);
    }

    public function testDelete()
    {
        $templates   = new \Template\controllers\TemplateController();

        ########## DELETE ##########
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $templates->delete($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame("success", $responseBody->success);

        ########## DELETE DUPLICATED ##########
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $templates->delete($request, new \Slim\Http\Response(), ['id' => self::$idDuplicated]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame("success", $responseBody->success);

        ########## DELETE FAIL ##########
        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'DELETE']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $templates->delete($request, new \Slim\Http\Response(), ['id' => '8928191923']);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Template does not exist', $responseBody->errors);
    }

    public function testInitTemplate()
    {
        $templates   = new \Template\controllers\TemplateController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);
        $response       = $templates->initTemplates($request, new \Slim\Http\Response());
        $responseBody   = json_decode((string)$response->getBody());

        $this->assertNotNull($responseBody->templatesModels);
        $this->assertNotNull($responseBody->templatesModels[0]->fileName);
        $this->assertNotNull($responseBody->templatesModels[0]->fileExt);
        $this->assertNotNull($responseBody->templatesModels[0]->filePath);
        $this->assertNotNull($responseBody->attachmentTypes);
        $this->assertNotNull($responseBody->attachmentTypes[0]->label);
        $this->assertNotNull($responseBody->attachmentTypes[0]->id);
        $this->assertNotNull($responseBody->datasources);
        $this->assertNotNull($responseBody->datasources[0]->id);
        $this->assertNotNull($responseBody->datasources[0]->label);
        $this->assertNotNull($responseBody->datasources[0]->script);
        $this->assertNotNull($responseBody->datasources[0]->target);
        $this->assertNotNull($responseBody->entities);
        $this->assertNotNull($responseBody->entities[0]->entity_id);
        $this->assertNotNull($responseBody->entities[0]->entity_label);
    }
}
