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
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;
use Workflow\models\WorkflowModel;

class SearchController
{
    public function getDocuments(Request $request, Response $response)
    {
        $body = $request->getParsedBody();
        $queryParams = $request->getQueryParams();

        $queryParams['offset'] = empty($queryParams['offset']) ? 0 : (int)$queryParams['offset'];
        $queryParams['limit'] = empty($queryParams['limit']) ? 0 : (int)$queryParams['limit'];

        $where = [];
        $data = [];
        $hasFullRights = PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents']);
        if (!$hasFullRights) {
            $substitutedUsers = UserModel::get(['select' => ['id'], 'where' => ['substitute = ?'], 'data' => [$GLOBALS['id']]]);
            $users = [$GLOBALS['id']];
            foreach ($substitutedUsers as $value) {
                $users[] = $value['id'];
            }

            $workflowSelect = "SELECT id FROM workflows ws WHERE workflows.main_document_id = main_document_id AND process_date IS NULL AND status IS NULL ORDER BY \"order\" LIMIT 1";
            $workflowSelect = "SELECT main_document_id FROM workflows WHERE user_id in (?) AND id in ({$workflowSelect})";
            $where = ["id in ({$workflowSelect}) OR typist = ?"];
            $data = [$users, $GLOBALS['id']];
        }

        $whereWorkflow = [];
        $dataWorkflow = [];
        if (Validator::arrayType()->notEmpty()->validate($body['workflowStates'])) {
            $whereStatuses = [];
            if (in_array('VAL', $body['workflowStates'])) {
                $whereStatuses[] = "main_document_id not in (SELECT DISTINCT main_document_id FROM workflows WHERE status is null OR status in ('REF', 'END', 'STOP'))";
            }
            if (in_array('REF', $body['workflowStates'])) {
                $whereStatuses[] = "main_document_id in (SELECT DISTINCT main_document_id FROM workflows WHERE status = 'REF')";
            }
            if (in_array('STOP', $body['workflowStates'])) {
                $whereStatuses[] = "main_document_id in (SELECT DISTINCT main_document_id FROM workflows WHERE status = 'STOP')";
            }
            if (in_array('PROG', $body['workflowStates'])) {
                $whereStatuses[] = "main_document_id in (SELECT DISTINCT main_document_id FROM workflows WHERE status is null)";
            }
            if (!empty($whereStatuses)) {
                $whereStatuses = implode(' OR ', $whereStatuses);
                $whereWorkflow[] = "({$whereStatuses})";
            }
        }
        if (!empty($body['workflowUsers'])) {
            $whereWorkflow[] = 'user_id in (?)';
            $dataWorkflow[] = $body['workflowUsers'];
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

        if (Validator::stringType()->notEmpty()->validate($body['title'])) {
            $requestData = SearchController::getDataForRequest([
                'search'        => $body['title'],
                'fields'        => 'unaccent(title) ilike unaccent(?::text)',
                'where'         => [],
                'data'          => [],
                'fieldsNumber'  => 1,
                'longField'     => true
            ]);
            $where[] = implode(' AND ', $requestData['where']);
            $data = array_merge($data, $requestData['data']);

        }
        if (Validator::stringType()->notEmpty()->validate($body['reference'])) {
            $where[] = 'reference ilike ?';
            $data[] = "%{$body['reference']}%";
        }

        $documents = DocumentModel::get([
            'select'    => ['id', 'title', 'reference', 'typist', 'count(1) OVER()'],
            'where'     => $where,
            'data'      => $data,
            'limit'     => $queryParams['limit'],
            'offset'    => $queryParams['offset'],
            'orderBy'   => ['creation_date desc']
        ]);

        $count = empty($documents[0]['count']) ? 0 : $documents[0]['count'];
        $hasIndexation = PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'indexation']);
        foreach ($documents as $key => $document) {
            $workflow = WorkflowModel::getByDocumentId(['select' => ['user_id', 'mode', 'process_date', 'signature_mode', 'note', 'status'], 'documentId' => $document['id'], 'orderBy' => ['"order"']]);
            $currentFound = false;
            $formattedWorkflow = [];
            $state = null;
            foreach ($workflow as $value) {
                if (!empty($value['process_date'])) {
                    $date = new \DateTime($value['process_date']);
                    $value['process_date'] = $date->format('d-m-Y H:i');
                }

                $formattedWorkflow[] = [
                    'userId'                => $value['user_id'],
                    'userDisplay'           => UserModel::getLabelledUserById(['id' => $value['user_id']]),
                    'mode'                  => $value['mode'],
                    'processDate'           => $value['process_date'],
                    'current'               => !$currentFound && empty($value['status']),
                    'signatureMode'         => $value['signature_mode'],
                    'status'                => $value['status'],
                    'reason'                => $value['note']
                ];
                if (empty($value['status'])) {
                    $currentFound = true;
                }

                $state = str_replace(['END'], ['REF'], $value['status']);
                if (empty($value['status'])) {
                    $state = 'PROG';
                }
            }
            $documents[$key]['canInterrupt'] = ($document['typist'] == $GLOBALS['id'] || $hasFullRights);
            $documents[$key]['canReaffect'] = ($document['typist'] == $GLOBALS['id'] || $hasFullRights) && $hasIndexation;
            $documents[$key]['workflow'] = $formattedWorkflow;
            $documents[$key]['state'] = $state;
            unset($documents[$key]['count']);
        }

        return $response->withJson(['documents' => $documents, 'count' => $count]);
    }

    public static function getDataForRequest(array $args)
    {
        ValidatorModel::notEmpty($args, ['search', 'fields', 'fieldsNumber']);
        ValidatorModel::stringType($args, ['search', 'fields']);
        ValidatorModel::arrayType($args, ['where', 'data']);
        ValidatorModel::intType($args, ['fieldsNumber']);

        $searchItems = explode(' ', $args['search']);

        foreach ($searchItems as $keyItem => $item) {
            if (strlen($item) >= 2) {
                $args['where'][] = $args['fields'];

                $isIncluded = false;
                foreach ($searchItems as $key => $value) {
                    if ($keyItem == $key) {
                        continue;
                    }
                    if (strpos($value, $item) === 0) {
                        $isIncluded = true;
                    }
                }
                for ($i = 0; $i < $args['fieldsNumber']; $i++) {
                    if (!empty($args['longField'])) {
                        $args['data'][] = ($isIncluded ? "%{$item} %" : "%{$item}%");
                    } else {
                        $args['data'][] = ($isIncluded ? "%{$item}" : "%{$item}%");
                    }
                }
            }
        }

        return ['where' => $args['where'], 'data' => $args['data']];
    }
}
