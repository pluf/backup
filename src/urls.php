<?php
return array(
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
        'method' => 'getObject',
        'http-method' => 'GET',
        'params' => array(
            'model' => 'Pluf\Backup\Snapshot'
        ),
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