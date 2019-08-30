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

use \Pluf;
use \Pluf_Exception;
use \Pluf_Tenant;
use \ZipArchive;

/**
 * !! You need also to backup Pluf if you want the full backup.
 * !!
 *
 * @param
 *            string Path to the folder where to store the backup
 * @return int The backup was correctly written
 *
 */
class Service extends \Pluf_Service
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
    { }

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
        $key = 'backup-' . md5(microtime() . rand(0, 123456789));
        $folder = Pluf::f('tmp_folder', '/var/tmp') . '/' . $key;
        if (!mkdir($folder, 0777, true)) {
            throw new Pluf_Exception('Failed to create folder in temp');
        }

        // TODO: maso, 2019: make module backups in the directory
        $apps = Pluf::f('installed_apps');
        foreach ($apps as $app) {
            if ($app === 'Backup') {
                continue;
            }
            if (false == ($file = Pluf::fileExists($app . '/module.json'))) {
                continue;
            }
            $myfile = fopen($file, "r") or die("Unable to open module.json!");
            $json = fread($myfile, filesize($file));
            fclose($myfile);
            $moduel = json_decode($json, true);
            if (!array_key_exists('model', $moduel)) {
                continue;
            }
            $models = $moduel['model'];
            // Now, for each table, we dump the content in json, this is a
            // memory intensive operation
            $to_json = array();
            foreach ($models as $model) {
                $to_json[$model] = self::dump($model, false);
            }
            file_put_contents(sprintf('%s/%s.json', $folder, $app), json_encode($to_json), LOCK_EX);
        }

        // Zip the backup folder
        $zip = new ZipArchive();
        $zip->open($zipFilePath, ZipArchive::CREATE);
        foreach (glob($folder . '/*') as $f) {
            $zip->addFile($f, basename($f));
        }
        $zip->close();
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
    public static function dump($model, $serialize = true)
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
            $out[] = self::prepare($item);
        }
        return ($serialize) ? json_encode($out) : $out;
    }

    /**
     * Return an array, ready to be serialized as json.
     */
    public static function prepare($model)
    {
        $out = array(
            'model' => $model->_a['model'],
            'pk' => $model->id,
            'fields' => array()
        );
        $storagePath = Pluf_Tenant::storagePath();
        $storagePathLen = strlen($storagePath);

        foreach ($model->_a['cols'] as $col => $val) {
            $field = new $val['type']();
            if ($field->type == 'manytomany') {
                $func = 'get_' . $col . '_list';
                $out['fields'][$col] = array();
                foreach ($model->$func() as $item) {
                    $out['fields'][$col][] = $item->id;
                }
            }
            if ($field->type == 'Pluf_DB_Field_File') {
                $str = $model->$col;
                if (substr($model->$col, 0, $storagePathLen) == $storagePath) {
                    $str = substr($str, $storagePathLen);
                }
                $out['fields'][$col] = $str;
            } else {
                $out['fields'][$col] = $model->$col;
            }
        }
        return $out;
    }


    /**
     * !! You need also to backup Pluf if you want the full backup.
     * !!
     *
     * @param
     *            string Path to the folder where to store the backup
     * @return int The backup was correctly written
     *        
     */
    function Backup_Shortcuts_BackupRun($folder, $multitinancy = true)
    {
        if (!is_dir($folder)) {
            if (false == @mkdir($folder, 0777, true)) {
                throw new Pluf_Form_Invalid(
                    'An error occured when creating the file path.'
                );
            }
        }
        $apps = Pluf::f('installed_apps');
        $db = Pluf::db();
        foreach ($apps as $app) {
            if ($app === 'Backup') {
                continue;
            }
            if (false == ($file = Pluf::fileExists($app . '/module.json'))) {
                continue;
            }
            $myfile = fopen($file, "r") or die("Unable to open module.json!");
            $json = fread($myfile, filesize($file));
            fclose($myfile);
            $moduel = json_decode($json, true);
            if (!array_key_exists('model', $moduel)) {
                continue;
            }
            $models = $moduel['model'];
            // Now, for each table, we dump the content in json, this is a
            // memory intensive operation
            $to_json = array();
            //         foreach ($models as $model) {
            //             $to_json[$model] = Pluf_Test_Fixture::dump($model, false);
            //         }
            file_put_contents(
                sprintf('%s/%s.json', $folder, $app),
                json_encode($to_json),
                LOCK_EX
            );
        }
        return true;
    }

    /**
     *
     * @param
     *            string Path to the backup folder
     * @return bool Success
     */
    function Backup_Shortcuts_RestoreRun($folder, $multitinancy = true)
    {
        $apps = Pluf::f('installed_apps');
        $db = Pluf::db();
        $schema = new Pluf_DB_Schema($db);
        foreach ($apps as $app) {
            if ($app === 'Backup') {
                continue;
            }
            if (false == ($file = Pluf::fileExists($app . '/module.json'))) {
                continue;
            }
            $myfile = fopen($file, "r") or die("Unable to open module.json!");
            $json = fread($myfile, filesize($file));
            fclose($myfile);
            $moduel = json_decode($json, true);
            if (!array_key_exists('model', $moduel)) {
                continue;
            }
            $models = $moduel['model'];
            if (sizeof($models) == 0) {
                continue;
            }
            foreach (array_reverse($models) as $model) {
                $schema->model = new $model();
                $schema->dropTables();
            }
            foreach ($models as $model) {
                $schema->model = new $model();
                $schema->createTables();
            }
            $full_data = json_decode(
                file_get_contents(sprintf('%s/%s.json', $folder, $app)),
                true
            );
            //         foreach ($full_data as $model => $data) {
            //             Pluf_Test_Fixture::load($data, false);
            //         }
        }
        return true;
    }
}
