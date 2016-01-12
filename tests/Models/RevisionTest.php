<?php

namespace Stevebauman\Revision\Tests\Models;

use Stevebauman\Revision\Models\Revision;
use Stevebauman\Revision\Tests\FunctionalTestCase;
use Stevebauman\Revision\Tests\Stubs\Models\User;

class RevisionTest extends FunctionalTestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('auth.model', \Illuminate\Foundation\Auth\User::class);
    }

    /**
     * Tests the user model relation with the stub model
     */
    public function testUserModel()
    {
        $userModel = User::class;

        $revision = $this->getMock(Revision::class, [
            "getUserModel"
        ]);

        $revision->method("getUserModel")->willReturn($userModel);

        $relation = $revision->user();

        $this->assertNotNull($relation);
        $this->assertEquals($userModel, get_class($relation->getRelated()));
    }

    /**
     * Tests calling the app config usage
     */
    public function testAppConfig()
    {
        $revision = new Revision();

        $this->assertEquals(\Illuminate\Foundation\Auth\User::class, get_class($revision->user()->getRelated()));
    }

    /**
     * Tests if the object uses revisionable trait
     */
    public function testHasRevisionableTrait()
    {
        $revision = new Revision();
        $this->assertTrue(method_exists($revision, "revisionable"));
    }
}
