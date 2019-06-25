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
    private static $attachmentId = null;

    public function testCreate()
    {
        $documentController = new \Document\controllers\DocumentController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'title'                 => 'Mon Courrier',
            'reference'             => '2018/CR/7',
            'description'           => 'Mon premier courrier parapheur',
            'encodedDocument'       => base64_encode(file_get_contents('test/unitTests/samples/testPdf.zip')),
            'sender'                => 'Oliver Queen',
            'deadline'              => '2018-12-25',
            'workflow'              => [[
                "processingUser" => "jjane@maarch.com",
                "mode" => "visa"
            ], [
                "processingUser" => "ccornillac@maarch.com",
                "mode" => "visa"
            ]],
            'metadata'              => ['Entité' => 'QE', 'Destinataire' => 'Barry Allen', 'priorité' => 'Urgent'],
            'attachments'           => [[
                'encodedDocument'       => base64_encode(file_get_contents('test/unitTests/samples/testPdf.zip')),
                'title'                 => 'Ma pj de mon courrier',
                'reference'             => '2018/ZZ/10',
            ]]
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);
        $response     = $documentController->create($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('int', $responseBody->id);
        self::$id = $responseBody->id;
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
        $this->assertInternalType('int', $responseBody->count->visa);
        $this->assertInternalType('int', $responseBody->count->sign);
        $this->assertInternalType('int', $responseBody->count->note);
        $this->assertInternalType('int', $responseBody->count->current);
        $this->assertNotEmpty($responseBody->count);

        $fullRequest = $request->withQueryParams(['mode' => 'SIGN']);
        $response     = $documentController->get($fullRequest, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->documents);
        $this->assertNotEmpty($responseBody->documents);
    }

    public function testGetById()
    {
        // Wait thumbnail generation
        sleep(10);

        $documentController = new \Document\controllers\DocumentController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $documentController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('2018/CR/7', $responseBody->document->reference);
        $this->assertSame('Mon Courrier', $responseBody->document->title);
        $this->assertSame('Oliver Queen', $responseBody->document->sender);
        $this->assertInternalType('array', $responseBody->document->metadata);
        $this->assertNotEmpty($responseBody->document->metadata);
        $this->assertInternalType('array', $responseBody->document->workflow);
        $this->assertInternalType('int', $responseBody->document->workflow[0]->userId);
        $this->assertSame(1, $responseBody->document->workflow[0]->userId);
        $this->assertSame('visa', $responseBody->document->workflow[0]->mode);
        $this->assertNotEmpty($responseBody->document->workflow);
        $this->assertInternalType('array', $responseBody->document->attachments);
        $this->assertNotEmpty($responseBody->document->attachments);
        $this->assertNotEmpty($responseBody->document->attachments[0]->id);
        self::$attachmentId = $responseBody->document->attachments[0]->id;

        $response     = $documentController->getById($request, new \Slim\Http\Response(), ['id' => -1]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Document does not exist', $responseBody->errors);
    }

    // AttachmentController
    public function testGetById_AttachmentController()
    {
        $attachmentController = new \Attachment\controllers\AttachmentController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $attachmentController->getById($request, new \Slim\Http\Response(), ['id' => self::$attachmentId]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('2018/ZZ/10', $responseBody->attachment->reference);
        $this->assertSame('Ma pj de mon courrier', $responseBody->attachment->title);
        $this->assertInternalType('string', $responseBody->attachment->encodedDocument);

        $response     = $attachmentController->getById($request, new \Slim\Http\Response(), ['id' => -1]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Attachment does not exist', $responseBody->errors);
    }

    public function testSetAction()
    {
        $documentController = new \Document\controllers\DocumentController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'POST']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $aArgs = [
            'signatures' => [
                [
                    'encodedImage' => base64_encode(file_get_contents('test/unitTests/samples/signature.jpg')),
                    'width' => 120,
                    'positionX' => 0,
                    'positionY' => 0,
                    'page' => 1,
                    'type' => 'jpg',
                ],
                [
                    'encodedImage' => base64_encode(file_get_contents('test/unitTests/samples/signature.jpg')),
                    'width' => 300,
                    'positionX' => 140,
                    'positionY' => 120,
                    'page' => 2,
                    'type' => 'jpg',
                ]
            ]
        ];

        $fullRequest = \httpRequestCustom::addContentInBody($aArgs, $request);

        // Error action
        $response     = $documentController->setAction($fullRequest, new \Slim\Http\Response(), ['id' => self::$id, 'actionId' => 203]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Action does not exist', $responseBody->errors);

        // Success action
        $response     = $documentController->setAction($fullRequest, new \Slim\Http\Response(), ['id' => self::$id, 'actionId' => 1]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('success', $responseBody->success);
    }

    public function testGetContent()
    {
        $documentController = new \Document\controllers\DocumentController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $documentController->getContent($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertNotEmpty($responseBody->encodedDocument);
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
        \Attachment\models\AttachmentModel::delete([
            'where' => ['id = ?'],
            'data'  => [self::$attachmentId]
        ]);
        \Docserver\models\AdrModel::deleteAttachmentAdr([
            'where' => ['attachment_id = ?'],
            'data'  => [self::$attachmentId]
        ]);

        \Workflow\models\WorkflowModel::delete([
            'where' => ['main_document_id = ?'],
            'data'  => [self::$id]
        ]);

        $documentController = new \Document\controllers\DocumentController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $documentController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Document does not exist', $responseBody->errors);
    }
}
