<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Lang Model
* @author dev@maarch.org
*/

namespace SrcCore\models;

class LangModel
{
    public static $lang = [];

    public function __construct(array $args)
    {
        ValidatorModel::notEmpty($args, ['language']);
        ValidatorModel::stringType($args, ['language']);

        require_once("src/core/lang/lang-{$args['language']}.php");

        if (!empty($lang)) {
            self::$lang = $lang;
        }
    }
}
