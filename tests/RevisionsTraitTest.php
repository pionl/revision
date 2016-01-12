<?php

namespace Stevebauman\Revision\Tests;

use Illuminate\Auth\GenericUser;
use Stevebauman\Revision\Models\Revision;
use Stevebauman\Revision\Tests\Stubs\Models\EasyModel;

class RevisionsTraitTest extends FunctionalTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mock;
    protected $table;

    public function setUp()
    {
        parent::setUp();

        $this->table = (new EasyModel())->getTable();

        $this->mock = $this->getMock(EasyModel::class, [
            "save"
        ]);
    }

    /**
     * Ensures that the trait calls parent get table
     */
    public function testTable()
    {
        $this->assertEquals($this->table, $this->mock->getTable());
    }

    /**
     * Checks if the revision morph many uses correct class
     */
    public function testRevisions()
    {
        $revisions = $this->mock->revisions();
        $this->assertNotNull($revisions);
        $this->assertEquals(Revision::class, get_class($revisions->getRelated()));

        $this->assertEquals("revisions.revisionable_type", $revisions->getMorphType());
    }

    /**
     * Test the user not loged variant
     */
    public function testAuthNotLoged()
    {
        \Auth::shouldReceive("check")->once()->andReturn(false);
        $this->assertNull($this->mock->revisionUserId());
    }

    /**
     * Tests the variant when user is loged
     */
    public function testAuthLoged()
    {
        // set the user is loged
        \Auth::shouldReceive("check")->once()->andReturn(true);

        // setupt he authed user
        \Auth::shouldReceive("user")->once()->andReturn(new GenericUser(["id" => 1]));

        $this->assertEquals($this->mock->revisionUserId(), 1);
    }

}
