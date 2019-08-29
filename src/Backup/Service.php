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

/**
 * !! You need also to backup Pluf if you want the full backup.
 * !!
 *
 * @param
 *            string Path to the folder where to store the backup
 * @return int The backup was correctly written
 *
 */
class Backup_Service extends Pluf_Service
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
    public function loadData($zipFilePath)
    {}

    /**
     * Stores all data from the current tenant into the zip file.
     *
     * Some time you need to start a new tenant, so it is better to clone exist one. This
     * function help you to create a new back up zip file from existed tenant.
     *
     * @param string $zipFilePath
     */
    public function storeData($zipFilePath)
    {}
}