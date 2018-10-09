<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   DoctypeIndexesModelAbstract
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Doctype\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;

class DoctypeIndexesModelAbstract
{
    public static function getById(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $aReturn = DatabaseModel::select(
            [
            'select' => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'  => ['doctypes_indexes'],
            'where'  => ['type_id = ?'],
            'data'   => [$aArgs['id']]
            ]
        );

        if (empty($aReturn[0])) {
            return [];
        }
       
        return $aReturn;
    }


    public static function getAllIndexes()
    {
        $indexes = [];

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'apps/maarch_entreprise/xml/index_letterbox.xml']);
        if ($loadedXml) {
            foreach ($loadedXml->INDEX as $item) {
                $label = (string) $item->label;
                if (!empty($label) && defined($label) && constant($label) <> null) {
                    $label = constant($label);
                }
                $img = (string) $item->img;
                if (!empty($item->default_value)) {
                    $default = (string) $item->default_value;
                    if (!empty($default) && defined($default) && constant($default) <> null) {
                        $default = constant($default);
                    }
                } else {
                    $default = false;
                }
                if (isset($item->values_list)) {
                    $values = [];
                    $list   = $item->values_list;
                    foreach ($list->value as $val) {
                        $labelVal = (string) $val->label;
                        if (!empty($labelVal) && defined($labelVal) && constant($labelVal) <> null) {
                            $labelVal = constant($labelVal);
                        }

                        $values[] = [
                            'id'    => (string) $val->id,
                            'label' => $labelVal
                        ];
                    }
                    $tmpArr = [
                        'column'        => (string) $item->column,
                        'label'         => $label,
                        'type'          => (string) $item->type,
                        'img'           => $img,
                        'type_field'    => 'select',
                        'values'        => $values,
                        'default_value' => $default
                    ];
                } elseif (isset($item->table)) {
                    $values       = [];
                    $tableXml     = $item->table;
                    $tableName    = (string) $tableXml->table_name;
                    $foreignKey   = (string) $tableXml->foreign_key;
                    $foreignLabel = (string) $tableXml->foreign_label;
                    $whereClause  = (string) $tableXml->where_clause;
                    $order        = (string) $tableXml->order;


                    $res = DatabaseModel::select([
                        'select'   => [$foreignKey, $foreignLabel],
                        'table'    => [$tableName],
                        'where'    => [$whereClause],
                        'order_by' => [str_ireplace("order by", "", $order)]
                    ]);

                    foreach ($res as $value) {
                        $values[] = [
                            'id'    => (string) $value[0],
                            'label' => (string) $value[1]
                        ];
                    }

                    $tmpArr = [
                        'column'        => (string) $item->column,
                        'label'         => $label,
                        'type'          => (string) $item->type,
                        'img'           => $img,
                        'type_field'    => 'select',
                        'values'        => $values,
                        'default_value' => $default
                    ];
                } else {
                    $tmpArr = [
                        'column'        => (string) $item->column,
                        'label'         => $label,
                        'type'          => (string) $item->type,
                        'img'           => $img,
                        'type_field'    => 'input',
                        'default_value' => $default
                    ];
                }
                array_push($indexes, $tmpArr);
            }
        }

        return $indexes;
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['type_id', 'field_name', 'mandatory', 'coll_id']);
        ValidatorModel::intVal($aArgs, ['type_id']);

        DatabaseModel::insert([
            'table'         => 'doctypes_indexes',
            'columnsValues' => $aArgs
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['type_id']);
        ValidatorModel::intVal($aArgs, ['type_id']);

        DatabaseModel::delete([
            'table' => 'doctypes_indexes',
            'where' => ['type_id = ?'],
            'data'  => [$aArgs['type_id']]
        ]);

        return true;
    }
}
