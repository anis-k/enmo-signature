<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

use PHPUnit\Framework\TestCase;

class DocumentControllerTest extends TestCase
{
    private static $id = null;

    public function testCreate()
    {
        $documentController = new \Document\controllers\DocumentController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'reference'             => '2018/CR/7',
            'subject'               => 'Mon Courrier',
            'mode'                  => 'SIGN',
            'processing_user'       => 1,
            'sender'                => 'Oliver Queen',
            'sender_entity'         => 'QE',
            'recipient'             => 'Barry Allen',
            'priority'              => 'Urgent',
            'limit_date'            => '2018-12-25',
            'encodedZipDocument'    => base64_encode(file_get_contents('test/unitTests/samples/testPdf.zip'))
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $documentController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('int', $responseBody->documentId);
        self::$id = $responseBody->documentId;
    }

    public function testGet()
    {
        $documentController = new \Document\controllers\DocumentController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $documentController->get($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->documents);
        $this->assertNotEmpty($responseBody->documents);
    }

    public function testGetById()
    {
        $documentController = new \Document\controllers\DocumentController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $documentController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('2018/CR/7', $responseBody->document->reference);
        $this->assertSame('Mon Courrier', $responseBody->document->subject);
        $this->assertSame('SIGN', $responseBody->document->mode);
        $this->assertSame(1, $responseBody->document->status);
        $this->assertSame('Urgent', $responseBody->document->priority);
        $this->assertSame('Oliver Queen', $responseBody->document->sender);
        $this->assertSame('QE', $responseBody->document->sender_entity);
        $this->assertSame(1, $responseBody->document->processing_user);
        $this->assertSame('Barry Allen', $responseBody->document->recipient);
        $this->assertInternalType('array', $responseBody->document->attachments);
        $this->assertEmpty($responseBody->document->attachments);

        $response     = $documentController->getById($request, new \Slim\Http\Response(), ['id' => -1]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Document out of perimeter', $responseBody->errors);
    }

    public function testGetStatusById()
    {
        $documentController = new \Document\controllers\DocumentController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $documentController->getStatusById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('NEW', $responseBody->status->reference);
        $this->assertSame('SIGN', $responseBody->status->mode);
    }

    public function testGetHandwrittenDocumentById()
    {
        $documentController = new \Document\controllers\DocumentController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $documentController->getHandwrittenDocumentById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertEmpty($responseBody->encodedDocument);
    }

    public function testDelete()
    {
        \Document\models\DocumentModel::delete([
            'where' => ['id = ?'],
            'data'  => [self::$id]
        ]);

        \Docserver\models\AdrModel::deleteDocumentAdr([
            'where' => ['main_document_id = ?'],
            'data'  => [self::$id]
        ]);

        $documentController = new \Document\controllers\DocumentController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $documentController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Document out of perimeter', $responseBody->errors);
    }
}
