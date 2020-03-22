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
namespace Pluf\Test\Backup;

use const False\MyClass\true;
use function GuzzleHttp\json_decode;
use Pluf\Exception;
use Pluf\Test\Client;
use Pluf\Test\TestCase;
use Pluf;
use Pluf_Migration;
use User_Account;
use User_Credential;
use User_Role;

/**
 *
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class RestTest extends TestCase
{

    /**
     *
     * @beforeClass
     */
    public static function installApps()
    {
        Pluf::start(__DIR__ . '/../conf/config.php');
        $m = new Pluf_Migration();
        $m->install();
        $m->init();

        // Test user
        $user = new User_Account();
        $user->login = 'test';
        $user->is_active = true;
        if (true !== $user->create()) {
            throw new Exception();
        }
        // Credential of user
        $credit = new User_Credential();
        $credit->setFromFormData(array(
            'account_id' => $user->id
        ));
        $credit->setPassword('test');
        if (true !== $credit->create()) {
            throw new Exception();
        }

        $per = User_Role::getFromString('tenant.owner');
        $user->setAssoc($per);
    }

    /**
     *
     * @afterClass
     */
    public static function uninstallApps()
    {
        $m = new Pluf_Migration();
        $m->uninstall();
    }

    /**
     *
     * @test
     */
    public function gettingSnapshotSchema()
    {
        // we have to init client for eny test
        $client = new Client();
        $client->clean();

        // login
        $response = $client->get('/backup/snapshots/schema');
        $this->assertNotNull($response);
        $this->assertEquals($response->status_code, 200);
    }

    /**
     *
     * @test
     */
    public function loadTemplate()
    {
        // we have to init client for eny test
        $client = new Client();
        $client->clean();

        // login
        $response = $client->post('/user/login', array(
            'login' => 'test',
            'password' => 'test'
        ));
        $this->assertNotNull($response);
        $this->assertEquals($response->status_code, 200);

        // create snapshot
        $response = $client->post('/backup/snapshots', array(
            'title' => 'test',
            'description' => 'test'
        ));
        $this->assertNotNull($response);
        $this->assertEquals($response->status_code, 200);
    }

    /**
     *
     * @test
     */
    public function dowloadTheSnapshot()
    {
        // we have to init client for eny test
        $client = new Client();
        $client->clean();

        // login
        $response = $client->post('/user/login', array(
            'login' => 'test',
            'password' => 'test'
        ));
        $this->assertNotNull($response);
        $this->assertEquals($response->status_code, 200);

        // create snapshot
        $response = $client->post('/backup/snapshots', array(
            'title' => 'test',
            'description' => 'test'
        ));
        $this->assertNotNull($response);
        $this->assertEquals($response->status_code, 200);

        $actual = json_decode($response->content, true);

        // download snapshot
        $response = $client->get('/backup/snapshots/' . $actual['id'] . '/content');
        $this->assertNotNull($response);
        $this->assertEquals($response->status_code, 200);
    }
}
