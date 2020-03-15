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

use Pluf\Exception;
use Pluf\Test\Client;
use Pluf\Test\TestCase;
use Pluf\Test\Test_Assert;
use Pluf;
use Pluf_HTTP_Request;
use Pluf_Migration;
use User_Account;
use User_Credential;
use User_Role;

/**
 *
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class ServiceTest extends TestCase
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

    // /**
    // *
    // * @test
    // */
    // public function createBackupTest()
    // {
    // // we have to init client for eny test
    // $client = new Client();
    // $client->clean();

    // $name = 'test-content-' . rand();

    // $c = new CMS_Content();
    // $c->file_path = Pluf_Tenant::storagePath() . '/test.txt';
    // $c->mime_time = 'text/plain';
    // $c->name = $name;
    // $c->create();

    // $term = new CMS_Term();
    // $term->name = "my term";
    // $term->create();

    // $termtaxo = new CMS_TermTaxonomy();
    // $termtaxo->term_id = $term;
    // $termtaxo->taxonomy = "test";
    // $termtaxo->create();

    // $termtaxo->setAssoc($c);

    // // create file
    // $myfile = fopen($c->file_path, "w") or die("Unable to open file!");
    // $txt = "John Doe\n";
    // fwrite($myfile, $txt);
    // $txt = "Jane Doe\n";
    // fwrite($myfile, $txt);
    // fclose($myfile);

    // $zipFilePath = __DIR__ . '/tmp/backupfile' . rand() . '.zip';
    // Pluf\Backup\Service::storeData($zipFilePath);
    // $this->assertTrue(file_exists($zipFilePath), 'Backup file is not created');

    // $termtaxo->delete();
    // $term->delete();
    // $c->delete();

    // Pluf\Backup\Service::loadData($zipFilePath);

    // Pluf::loadFunction('CMS_Shortcuts_GetNamedContentOr404');
    // $c = CMS_Shortcuts_GetNamedContentOr404($name);
    // $this->assertFalse($c->isAnonymous());
    // $list = $c->get_term_taxonomies_list();

    // $zipFilePath2 = __DIR__ . '/tmp/backupfile2-' . rand() . '.zip';
    // Pluf\Backup\Service::storeData($zipFilePath2);
    // $this->assertTrue(file_exists($zipFilePath2), 'Backup file is not created');
    // }

    /**
     *
     * @test
     */
    public function loadTemplate()
    {
        // we have to init client for eny test
        $client = new Client(array());
        $client->clean();

        Pluf\Backup\Service::loadData(__DIR__ . '/template-001.zip');

        $zipFilePath2 = '/tmp/templateback-' . rand() . '.zip';
        Pluf\Backup\Service::storeData($zipFilePath2);
        $this->assertTrue(file_exists($zipFilePath2), 'Backup file is not created');
    }
}
