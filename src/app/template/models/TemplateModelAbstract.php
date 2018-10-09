<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Template Model Abstract
 * @author dev@maarch.org
 */

namespace Template\models;

use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

abstract class TemplateModelAbstract
{
    public static function get(array $aArgs = [])
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($aArgs, ['limit']);

        $aTemplates = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['templates'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'order_by'  => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $aTemplates;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aTemplate = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['templates'],
            'where'     => ['template_id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        if (empty($aTemplate[0])) {
            return [];
        }

        return $aTemplate[0];
    }

    public static function getByTarget(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['template_target']);

        $aTemplate = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['templates'],
            'where'     => ['template_target = ?'],
            'data'      => [$aArgs['template_target']],
        ]);

        return $aTemplate;
    }

    public static function getByEntity(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['entities']);
        ValidatorModel::arrayType($aArgs, ['select', 'entities']);

        $aTemplate = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['templates t, templates_association ta'],
            'where'     => ['t.template_id = ta.template_id', 'ta.value_field in (?)'],
            'data'      => [$aArgs['entities']],
        ]);

        return $aTemplate;
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['template_label']);
        ValidatorModel::stringType($aArgs, ['template_label']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'templates_seq']);

        DatabaseModel::insert([
            'table'         => 'templates',
            'columnsValues' => [
                'template_id'               => $nextSequenceId,
                'template_label'            => $aArgs['template_label'],
                'template_comment'          => $aArgs['template_comment'],
                'template_content'          => $aArgs['template_content'],
                'template_type'             => $aArgs['template_type'],
                'template_style'            => $aArgs['template_style'],
                'template_datasource'       => $aArgs['template_datasource'],
                'template_target'           => $aArgs['template_target'],
                'template_attachment_type'  => $aArgs['template_attachment_type'],
                'template_path'             => $aArgs['template_path'],
                'template_file_name'        => $aArgs['template_file_name'],
            ]
        ]);

        return $nextSequenceId;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['set', 'where', 'data']);
        ValidatorModel::arrayType($aArgs, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'templates',
            'set'   => $aArgs['set'],
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['where', 'data']);
        ValidatorModel::arrayType($aArgs, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'templates',
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }

    public static function getDatasources()
    {
        $datasources = [];

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'modules/templates/xml/datasources.xml']);
        if ($loadedXml) {
            foreach ($loadedXml->datasource as $value) {
                $value = (array)$value;
                $datasources[] = [
                    'id'        => (string)$value['id'],
                    'label'     => (string)$value['label'],
                    'script'    => (string)$value['script'],
                    'target'    => (string)$value['target'],
                ];
            }
        }

        return $datasources;
    }

    public static function getDatasourceById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);

        $datasources = [];

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'modules/templates/xml/datasources.xml']);
        if ($loadedXml) {
            foreach ($loadedXml->datasource as $value) {
                $value = (array)$value;
                if ($value['id'] == $aArgs['id']) {
                    $datasource = [
                        'id'        => (string)$value['id'],
                        'label'     => (string)$value['label'],
                        'script'    => (string)$value['script'],
                        'target'    => (string)$value['target'],
                    ];
                }
            }
        }

        return $datasource;
    }

    public static function getModels()
    {
        $customId = CoreConfigModel::getCustomId();

        if (is_dir("custom/{$customId}/modules/templates/templates/styles/")) {
            $path = "custom/{$customId}/modules/templates/templates/styles/";
        } else {
            $path = 'modules/templates/templates/styles/';
        }

        $templateModels = scandir($path);
        $models = [];
        foreach ($templateModels as $value) {
            if ($value != '.' && $value != '..') {
                $file = implode('.', explode('.', $value, -1));
                $ext = explode('.', $value);
                $models[] = [
                    'fileName'  => $file,
                    'fileExt'   => strtoupper($ext[count($ext) - 1]),
                    'filePath'  => $path . $value,
                ];
            }
        }

        return $models;
    }
}
