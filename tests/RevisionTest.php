<?php

namespace Stevebauman\Revision\Tests;

use Stevebauman\Revision\Models\Revision;
use Stevebauman\Revision\Tests\Stubs\Models\User;
use Stevebauman\Revision\Tests\Stubs\Models\Post;

class RevisionTest extends FunctionalTestCase
{
    public $user;

    public function setUp()
    {
        parent::setUp();

        $user = new User();

        $user->username = 'Test';
        $user->save();

        $this->user = $user;
    }

    public function testAfterCreate()
    {
        $post = new Post();

        $post->title = 'Test';
        $post->description = 'Testing';
        $post->save();

        $post->afterCreate();

        $revision = Revision::find(1);

        $this->assertEquals($revision->revisionable_type, 'Stevebauman\Revision\Tests\Stubs\Models\Post');
    }
}
