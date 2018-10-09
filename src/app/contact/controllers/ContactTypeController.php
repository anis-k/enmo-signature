<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Contact Type Controller
 * @author dev@maarch.org
 */

namespace Contact\controllers;

use Contact\models\ContactTypeModel;
use Slim\Http\Request;
use Slim\Http\Response;

class ContactTypeController
{
    public function get(Request $request, Response $response)
    {
        $contactsTypes = ContactTypeModel::get();

        return $response->withJson(['contactsTypes' => $contactsTypes]);
    }
}
