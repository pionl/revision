<?php

namespace Stevebauman\Revision\Tests;

use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;

abstract class FunctionalTestCase extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->migrateTables();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    private function migrateTables()
    {
        Schema::create('users', function ($table) {
            $table->increments('id');
            $table->string('username');
            $table->timestamps();
        });

        Schema::create('posts', function ($table) {
            $table->increments('id');
            $table->string('title');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('revisions', function ($table) {
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
