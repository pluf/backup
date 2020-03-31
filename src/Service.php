<?php

/*
 * This file is part of Pluf Framework, a simple PHP Application Framework.
 * Copyright (C) 2010-2020 Phoinex Scholars Co. (http://dpq.co.ir)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Pluf\Backup;

use Pluf\Exception;
use Pluf;
use Pluf_FileUtil;
use Pluf_Service;
use Pluf_Tenant;
use Pluf\ModelUtils;
use Pluf\Db\Engine;

/**
 * !! You need also to backup Pluf if you want the full backup.
 * !!
 *
 * @param
 *            string Path to the folder where to store the backup
 * @return int The backup was correctly written
 *        
 */
class Service extends Pluf_Service
{

    /**
     * Loads all data from the zipfile into the current tenant
     *
     * To start a service online, you have to load initialization data or create them by your self. For
     * example, there are many pages, groups, templates you need in the first step.
     *
     * This function load basic data from a zip file.
     *
     * @param string $zipFilePath
     */
    public static function loadData($zipFilePath)
    {
        try {
            Pluf::db()->begin();
            // Temp folder
            $folder = Pluf_FileUtil::createTempFolder('restor-');

            // Unzip to temp folder
            Pluf_FileUtil::unzipToFolder($folder, $zipFilePath);

            $storagePath = Pluf_Tenant::storagePath();
            // Load data
            $apps = Pluf::f('installed_apps');
            foreach ($apps as $app) {
                // Note: hadi, 98-08: We could remove this check in loading data and use this only while exporting or storing data.
                // if (! self::isSuportedApp($app)) {
                // continue;
                // }
                if (false == ($file = Pluf::fileExists($app . '/module.json'))) {
                    continue;
                }
                $myfile = fopen($file, "r") or die("Unable to open module.json!");
                $json = fread($myfile, filesize($file));
                fclose($myfile);
                $moduel = json_decode($json, true);
                if (! array_key_exists('model', $moduel)) {
                    continue;
                }
                $models = $moduel['model'];
                if (sizeof($models) == 0) {
                    continue;
                }
                $dataFile = sprintf('%s/%s.json', $folder, $app);
                if (! file_exists($dataFile)) {
                    continue;
                }
                $full_data = json_decode(file_get_contents($dataFile), true);
                $objectMap = array();
                foreach ($full_data as $model => $data) {
                    $loadModel = self::load($data, false);
                    foreach ($loadModel as $item) {
                        if (! array_key_exists($item['model'], $objectMap)) {
                            $objectMap[$item['model']] = array();
                        }
                        $objectMap[$item['model']][$item['src_id']] = $item;
                    }
                }

                // load relation and files
                foreach ($objectMap as /* $type =>  */$objects) {
                    foreach ($objects as $object) {
                        $model = $object['object'];
                        foreach ($model->_a['cols'] as $col => $val) {
                            $field = new $val['type']();
                            if ($field->type == 'manytomany') {
                                foreach ($object['data']['fields'][$col] as $itemId) {
                                    $relatedModel = $objectMap[$val['model']][$itemId];
                                    $realObject = $relatedModel['object'];
                                    $model->setAssoc($realObject);
                                }
                            } else if ($field->type == Engine::FOREIGNKEY && //
                            array_key_exists($val['model'], $objectMap) && array_key_exists($model->$col, $objectMap[$val['model']])) {
                                $relatedModel = $objectMap[$val['model']][$model->$col];
                                $model->$col = $relatedModel['object'];
                            } else if ($field->type == 'file') {
                                $str = $storagePath . $model->$col;
                                Pluf_FileUtil::copyFile($folder . '/files/' . $model->$col, $str);
                                $model->$col = $str;
                            }
                        }
                        $model->update();
                    }
                }
            }
            Pluf::db()->commit();
        } catch (Exception $ex) {
            Pluf::db()->rollback();
            throw $ex;
        }
    }

    /**
     * Stores all data from the current tenant into the zip file.
     *
     * Some time you need to start a new tenant, so it is better to clone exist one. This
     * function help you to create a new back up zip file from existed tenant.
     *
     * @param string $zipFilePath
     */
    public static function storeData($zipFilePath)
    {
        // Temp folder
        $folder = Pluf_FileUtil::createTempFolder('backup-');

        // TODO: maso, 2019: make module backups in the directory
        $apps = Pluf::f('installed_apps');
        foreach ($apps as $app) {
            $models = ModelUtils::getModelsFromModule($app);
            // Now, for each table, we dump the content in json, this is a
            // memory intensive operation
            $to_json = array();
            foreach ($models as $model) {
                if ($model === 'Pluf_Tenant') {
                    continue;
                }
                $to_json[$model] = self::dump($model, false, $folder);
            }
            file_put_contents(sprintf('%s/%s.json', $folder, $app), json_encode($to_json), LOCK_EX);
        }

        // Zip the backup folder
        Pluf_FileUtil::zipFolder($folder, $zipFilePath);
    }

    /**
     * Given a model or model name, dump the content.
     *
     * If the object is given, only this single object is dumped else
     * the complete table.
     *
     * @param
     *            mixed Model object or model name
     * @param
     *            bool Serialize as JSON (true)
     * @return mixed Array or JSON string
     */
    public static function dump($model, $serialize = true, $folder = null)
    {
        if (is_object($model)) {
            return ($serialize) ? json_encode(array(
                self::prepare($model)
            )) : array(
                self::prepare($model)
            );
        }
        $out = array();
        foreach (Pluf::factory($model)->getList(array(
            'order' => 'id ASC'
        )) as $item) {
            $out[] = self::prepare($item, $folder);
        }
        return ($serialize) ? json_encode($out) : $out;
    }

    /**
     * Return an array, ready to be serialized as json.
     */
    private static function prepare($model, $folder)
    {
        $out = array(
            'model' => $model->_a['model'],
            'pk' => $model->id,
            'fields' => array()
        );
        $storagePath = Pluf_Tenant::storagePath();
        $storagePathLen = strlen($storagePath);

        foreach ($model->_a['cols'] as $col => $val) {
            if ($val['type'] == Engine::MANY_TO_MANY) {
                $func = 'get_' . $col . '_list';
                $out['fields'][$col] = array();
                foreach ($model->$func() as $item) {
                    $out['fields'][$col][] = $item->id;
                }
            } else if ($val['type'] == Engine::FILE) {
                $str = $model->$col;
                if (substr($model->$col, 0, $storagePathLen) == $storagePath) {
                    $str = substr($str, $storagePathLen);
                }
                $out['fields'][$col] = $str;
                if (isset($folder)) {
                    Pluf_FileUtil::copyFile($model->$col, $folder . '/files/' . $str);
                }
            } else {
                $out['fields'][$col] = $model->$col;
            }
        }
        return $out;
    }

    public static function load($json, $deserialize = true)
    {
        $created = array();
        $data = ($deserialize) ? json_decode($json, true) : $json;
        unset($json);
        foreach ($data as $model) {
            $m = new $model['model']();
            $m->setFromFormData($model['fields']);
            $m->id = null;
            $m->create(); // we load in raw mode
            $created[] = array(
                'model' => $model['model'],
                'object' => $m,
                'data' => $model,
                'src_id' => $model['pk']
            );
        }
        return $created;
    }

    private static function isSuportedApp($app)
    {
        return $app === 'CMS' || $app === 'Pluf' || $app === 'Shop';
    }
}
