<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Note Controller
 * @author dev@maarch.org
 * @ingroup core
 */

namespace Note\controllers;

use Note\models\NoteModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;

class NoteController
{
    public function getByResId(Request $request, Response $response, $aArgs)
    {
        $check = Validator::intVal()->validate($aArgs['resId']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $aNotes = NoteModel::getByResId(['select' => ['notes.id', 'firstname', 'lastname', 'entity_label', 'note_text', 'date_note'], 'resId' => $aArgs['resId'], 'orderBy' => ['date_note DESC']]);

        return $response->withJson($aNotes);
    }
}
