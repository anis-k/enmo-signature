<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Search Controller
* @author dev@maarch.org
*/

namespace Search\controllers;

use Group\controllers\PrivilegeController;
use Document\models\DocumentModel;
use Slim\Http\Request;
use Slim\Http\Response;
use Workflow\models\WorkflowModel;

class SearchController
{
    public function getDocuments(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();

        $queryParams['offset'] = empty($queryParams['offset']) ? 0 : (int)$queryParams['offset'];
        $queryParams['limit'] = empty($queryParams['limit']) ? 0 : (int)$queryParams['limit'];

        $where = [];
        $data = [];
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
            $where[] = 'typist = ?';
            $data[] = $GLOBALS['id'];
        }

        $whereWorkflow = [];
        $dataWorkflow = [];
        if (!empty($queryParams['statuses'])) {
            $whereWorkflow[] = 'status in (?)';
            $dataWorkflow[] = $queryParams['statuses'];
        }
        if (!empty($queryParams['users'])) {
            $whereWorkflow[] = 'user_id in (?)';
            $dataWorkflow[] = $queryParams['statuses'];
        }

        if (!empty($whereWorkflow)) {
            $workflows = WorkflowModel::get([
                'select'    => ['main_document_id'],
                'where'     => $whereWorkflow,
                'data'      => $dataWorkflow,
                'groupBy'   => ['main_document_id']
            ]);
            $documentIds = array_column($workflows, 'main_document_id');
            if (empty($documentIds)) {
                $documentIds = [0];
            }
            $where[] = 'id in (?)';
            $data[] = $documentIds;
        }

        if (!empty($queryParams['search'])) {
            $where[] = '(reference ilike ? OR unaccent(title) ilike unaccent(?::text))';
            $data[] = "%{$queryParams['search']}%";
            $data[] = "%{$queryParams['search']}%";
        }

        $documents = DocumentModel::get([
            'select'    => ['id', 'title', 'reference', 'count(1) OVER()'],
            'where'     => $where,
            'data'      => $data,
            'limit'     => $queryParams['limit'],
            'offset'    => $queryParams['offset'],
            'orderBy'   => ['creation_date desc']
        ]);

        $count = empty($documents[0]['count']) ? 0 : $documents[0]['count'];
        foreach ($documents as $key => $document) {
            unset($documents[$key]['count']);
        }

        return $response->withJson(['documents' => $documents, 'count' => $count]);
    }
}
