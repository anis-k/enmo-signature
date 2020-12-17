<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Text Format Model
* @author dev@maarch.org
*/

namespace SrcCore\models;

class TextFormatModel
{
    public static function createFilename($args = [])
    {
        $search = array(
            utf8_decode('@[éèêë]@i'), utf8_decode('@[ÊË]@i'), utf8_decode('@[àâä]@i'), utf8_decode('@[ÂÄ]@i'),
            utf8_decode('@[îï]@i'), utf8_decode('@[ÎÏ]@i'), utf8_decode('@[ûùü]@i'), utf8_decode('@[ÛÜ]@i'),
            utf8_decode('@[ôö]@i'), utf8_decode('@[ÔÖ]@i'), utf8_decode('@[ç]@i'), utf8_decode('@[^a-zA-Z0-9_-s.]@i'));

        $replace = array('e', 'E','a', 'A','i', 'I', 'u', 'U', 'o', 'O','c','_');

        $filename = preg_replace($search, $replace, utf8_decode(substr($args['label'], 0, 30).".".$args['extension']));

        return $filename;
    }

    public static function getEndDayDate(array $args)
    {
        ValidatorModel::notEmpty($args, ['date']);
        ValidatorModel::stringType($args, ['date']);

        $date = new \DateTime($args['date']);
        $date->setTime(23, 59, 59);

        return $date->format('d-m-Y H:i:s');
    }
}
