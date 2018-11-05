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
        $nextSequenceId = \SrcCore\models\DatabaseModel::getNextSequenceValue(['sequenceId' => 'main_documents_id_seq']);

        \SrcCore\models\DatabaseModel::insert([
            'table'         => 'main_documents',
            'columnsValues' => [
                'id'                => $nextSequenceId,
                'reference'         => '2018/A/1',
                'subject'           => 'Mon Courrier',
                'doc_date'          => 'CURRENT_TIMESTAMP',
                'status'            => 2,
                'priority'          => 'Urgent',
                'sender'            => 'Oliver Queen',
                'sender_entity'     => 'QE',
                'processing_user'   => 1,
                'recipient'         => 'Barry Allen',
                'creation_date'     => 'CURRENT_TIMESTAMP'
            ]
        ]);

        self::$id = $nextSequenceId;
        $this->assertInternalType('int', self::$id);
    }

    public function testGet()
    {
        $documentController = new \Document\controllers\DocumentController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $documentController->get($request, new \Slim\Http\Response());
        $responseBody = json_decode((string)$response->getBody());

        $this->assertInternalType('array', $responseBody->documents);
    }

    public function testGetById()
    {
        $documentController = new \Document\controllers\DocumentController();

        $environment    = \Slim\Http\Environment::mock(['REQUEST_METHOD' => 'GET']);
        $request        = \Slim\Http\Request::createFromEnvironment($environment);

        $response     = $documentController->getById($request, new \Slim\Http\Response(), ['id' => self::$id]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('2018/A/1', $responseBody->document->reference);
        $this->assertSame('Mon Courrier', $responseBody->document->subject);
        $this->assertSame(2, $responseBody->document->status);
        $this->assertSame('Urgent', $responseBody->document->priority);
        $this->assertSame('Oliver Queen', $responseBody->document->sender);
        $this->assertSame('QE', $responseBody->document->sender_entity);
        $this->assertSame(1, $responseBody->document->processing_user);
        $this->assertSame('Barry Allen', $responseBody->document->recipient);

        $response     = $documentController->getById($request, new \Slim\Http\Response(), ['id' => -1]);
        $responseBody = json_decode((string)$response->getBody());

        $this->assertSame('Document out of perimeter', $responseBody->errors);
    }

    public function testDelete()
    {
        \Document\models\DocumentModel::delete([
            'where' => ['id = ?'],
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
