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
namespace Pluf\Backup\Views;

use Pluf\Backup\Service as BackupService;
use Pluf_Views;
use Pluf_HTTP_Response_File;
use Pluf_Tenant;

class Snapshot extends Pluf_Views
{

    /**
     * Creates new instance of snapshot
     *
     * @param Pluf_HTTP_Request $request
     * @param array $match
     * @param array $params
     */
    public function createSnapshot($request, $match, $params)
    {
        // create the object
        $params['model'] = 'Pluf\Backup\Snapshot';
        $snapshot = parent::createObject($request, $match, $params);

        /*
         * TODO: maso, 2019: create a job and schedule for workers.
         */
        // create snapshot
        try {
            $snapshotPath = Pluf_Tenant::storagePath() . '/backup/' . $snapshot->id;
            BackupService::storeData($snapshotPath);
            $snapshot->state = 'ready';
            $snapshot->file_path = $snapshotPath;
            $snapshot->update();
        } catch (Exception $ex) {
            $snapshot->state = 'error';
            $snapshot->update();
        }

        // return the result;
        return $snapshot;
    }

    /**
     * Download the snapshot file
     *
     * @param Pluf_HTTP_Request $request
     * @param array $match
     * @param array $params
     */
    public function downloadSnapshot($request, $match, $params)
    {
        // GET data
        $params['model'] = 'Pluf\Backup\Snapshot';
        $snapshot = parent::getObject($request, $match, $params);

        // Do
        $response = new Pluf_HTTP_Response_File($snapshot->file_path, 'application/zip');
        $response->headers['Content-Disposition'] = sprintf('attachment; filename="%s"', 'snapshot-' . $snapshot->id . '.zip');
        return $response;
    }
}