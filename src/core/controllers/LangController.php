<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Url Controller
 * @author dev@maarch.org
 */

namespace SrcCore\controllers;

use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class LangController
{
    public static function get(array $args = [])
    {
        ValidatorModel::stringType($args, ['lang']);

        if (empty($args['lang'])) {
            $user = UserModel::getById(['select' => ['preferences'], 'id' => $GLOBALS['id']]);
            $user['preferences'] = json_decode($user['preferences'], true);
            $args['lang'] = $user['preferences']['lang'];
        }

        $lang = [];
        if ($args['lang'] == 'fr') {
            require_once "src/core/lang/lang-fr.php";
        } elseif ($args['lang'] == 'en') {
            require_once "src/core/lang/lang-en.php";
        }

        return $lang;
    }
}
