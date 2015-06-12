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

    /**
     * @test
     */
    public function testCreate()
    {
        $post = new Post();

        $post->title = 'Test';
        $post->description = 'Testing';
        $post->save();

        $revisions = Revision::all();
        $this->assertEquals(3, $revisions->count());

        $createdAtRevision = $revisions->get(0);

        $this->assertEquals('Stevebauman\Revision\Tests\Stubs\Models\Post', $createdAtRevision->revisionable_type);
        $this->assertEquals(1, $createdAtRevision->revisionable_id);
        $this->assertEquals('created_at', $createdAtRevision->key);
        $this->assertNull($createdAtRevision->old_value);
        $this->assertEquals($post->created_at, $createdAtRevision->new_value);
    }

    /**
     * @test
     */
    public function testModify()
    {
        Post::bootHasRevisionsTrait();
        $post = new Post();

        $post->title = 'Test';
        $post->description = 'Testing';
        $post->save();

        $post->title = 'Modified';
        $post->save();

        $revisions = Revision::all();
        $this->assertEquals(4, $revisions->count());

        $titleRevision = $revisions->get(3);

        $this->assertEquals('title', $titleRevision->key);
        $this->assertEquals('Test', $titleRevision->old_value);
        $this->assertEquals('Modified', $titleRevision->new_value);
    }
}
