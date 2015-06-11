<?php

namespace Stevebauman\Revision\Tests;

use Illuminate\Database\Capsule\Manager as DB;

abstract class FunctionalTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->configureDatabase();
        $this->migrateTables();
    }

    private function configureDatabase()
    {
        $db = new DB();

        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);

        $db->bootEloquent();

        $db->setAsGlobal();
    }

    private function migrateTables()
    {
        DB::schema()->create('users', function ($table) {
            $table->increments('id');
            $table->string('username');
            $table->timestamps();
        });

        DB::schema()->create('posts', function ($table) {
            $table->increments('id');
            $table->string('title');
            $table->text('description');
            $table->timestamps();
        });

        DB::schema()->create('revisions', function ($table) {
            $table->increments('id');
            $table->string('revisionable_type');
            $table->integer('revisionable_id');
            $table->integer('user_id')->nullable();
            $table->string('key');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamps();

            $table->index(array('revisionable_id', 'revisionable_type'));
        });
    }
}
