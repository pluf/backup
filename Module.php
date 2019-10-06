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

class Module
{

    const moduleJsonPath = __DIR__ . '/module.json';

    /**
     * All data model relations
     */
    const relations = array();

    const urls = array(
        array(
            'regex' => '#^/snapshots/schema$#',
            'model' => 'Pluf\Backup\Views\Snapshot',
            'method' => 'getSchema',
            'http-method' => 'GET',
            'params' => array(
                'model' => 'Pluf\Backup\Snapshot'
            )
        ),
        /*
         * Snapshots
         */
        array(
            'regex' => '#^/snapshots$#',
            'model' => 'Pluf\Backup\Views\Snapshot',
            'method' => 'findObject',
            'http-method' => 'GET',
            'precond' => array(
                'User_Precondition::ownerRequired'
            ),
            'params' => array(
                'model' => 'Pluf\Backup\Snapshot'
            )
        ),
        array(
            'regex' => '#^/snapshots$#',
            'model' => 'Pluf\Backup\Views\Snapshot',
            'method' => 'createSnapshot',
            'http-method' => 'POST',
            'precond' => array(
                'User_Precondition::ownerRequired'
            )
        ),
        array(
            'regex' => '#^/snapshots$#',
            'model' => 'Pluf\Backup\Views\Snapshot',
            'method' => 'deleteSnapshot',
            'http-method' => 'DELETE',
            'precond' => array(
                'User_Precondition::ownerRequired'
            )
        ),
        /*
         * Snapshot itmes
         */
        array(
            'regex' => '#^/snapshots/(?P<modelId>\d+)$#',
            'model' => 'Pluf\Backup\Views\Snapshot',
            'method' => 'method',
            'http-method' => 'GET',
            'precond' => array(
                'User_Precondition::ownerRequired'
            )
        ),
        array(
            'regex' => '#^/snapshots/(?P<modelId>\d+)$#',
            'model' => 'Pluf\Backup\Views\Snapshot',
            'method' => 'create',
            'http-method' => 'POST',
            'precond' => array(
                'User_Precondition::ownerRequired'
            )
        ),
        array(
            'regex' => '#^/snapshots/(?P<modelId>\d+)$#',
            'model' => 'Pluf\Backup\Views\Snapshot',
            'method' => 'method',
            'http-method' => 'DELETE',
            'precond' => array(
                'User_Precondition::ownerRequired'
            )
        ),
        /*
         * Resource
         */
        array(
            'regex' => '#^/snapshots/(?P<modelId>\d+)/content$#',
            'model' => 'Pluf\Backup\Views\Snapshot',
            'method' => 'downloadSnapshot',
            'http-method' => 'GET',
            'precond' => array(
                'User_Precondition::ownerRequired'
            )
        )
    );
}
