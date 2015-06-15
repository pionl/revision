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

    public function testCreate()
    {
        $post = new Post();

        $post->title = 'Test';
        $post->description = 'Testing';
        $post->save();

        $revisions = Revision::all();

        $this->assertEquals(5, $revisions->count());

        $idRevision = $revisions->get(0);

        $this->assertEquals(Post::class, $idRevision->revisionable_type);
        $this->assertEquals(1, $idRevision->revisionable_id);
        $this->assertEquals('id', $idRevision->key);
        $this->assertNull($idRevision->old_value);

        // Test Revision User
        $this->assertEquals(1, $idRevision->user_id);
        $this->assertInstanceOf(User::class, $idRevision->getUserResponsible());
    }

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
        $this->assertEquals(6, $revisions->count());

        $titleRevision = $revisions->get(5);

        $this->assertEquals('title', $titleRevision->key);
        $this->assertEquals('Test', $titleRevision->old_value);
        $this->assertEquals('Modified', $titleRevision->new_value);
    }

    public function testOnlyColumns()
    {
        Post::bootHasRevisionsTrait();

        $post = new Post();
        $post->setRevisionColumns(['title']);

        $post->title = 'Testing';
        $post->description = 'Testing';
        $post->save();

        $revisions = Revision::all();

        $this->assertEquals(1, $revisions->count());
        $this->assertEquals('title', $revisions->get(0)->key);
        $this->assertEquals('Testing', $revisions->get(0)->new_value);
        $this->assertNull($revisions->get(0)->old_value);
    }

    public function testAvoidColumns()
    {
        Post::bootHasRevisionsTrait();

        $post = new Post();

        $post->setRevisionColumnsToAvoid(['title']);
        $post->title = 'Testing';
        $post->description = 'Testing';
        $post->save();

        $revisions = Revision::all();

        $this->assertEquals(4, $revisions->count());
        $this->assertEquals('id', $revisions->get(0)->key);
        $this->assertEquals('description', $revisions->get(1)->key);
        $this->assertEquals('created_at', $revisions->get(2)->key);
        $this->assertEquals('updated_at', $revisions->get(3)->key);
    }

    public function testColumnFormatting()
    {
        Post::bootHasRevisionsTrait();

        $post = new Post();

        $post->title = 'Testing';
        $post->description = 'Testing';
        $post->save();

        $revisions = $post->revisions;

        $this->assertEquals('ID', $revisions->get(0)->getColumnName());
        $this->assertEquals('Post Title', $revisions->get(1)->getColumnName());
        $this->assertEquals('Post Description', $revisions->get(2)->getColumnName());
        $this->assertEquals('Created', $revisions->get(3)->getColumnName());
        $this->assertEquals('Updated', $revisions->get(4)->getColumnName());
    }

    public function testColumnMeans()
    {
        Post::bootHasRevisionsTrait();

        $post = new Post();

        $post->user_id = $this->user->id;
        $post->title = 'Testing';
        $post->description = 'Testing';
        $post->save();

        $revisions = $post->revisions;

        $this->assertEquals('Test', $revisions->get(0)->getNewValue());
    }

    public function testGetRevisionColumnsFormatted()
    {
        $post = new Post();

        $columns = [
            'id' => 'ID',
            'created_at' => 'Created',
            'updated_at' => 'Updated',
            'title' => 'Post Title',
            'description' => 'Post Description',
        ];

        $this->assertEquals($columns, $post->getRevisionColumnsFormatted());
    }

    public function testGetRevisionColumnsMean()
    {
        $post = new Post();

        $means = [
            'user_id' => 'user.username'
        ];

        $this->assertEquals($means, $post->getRevisionColumnsMean());
    }
}
