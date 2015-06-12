<?php

namespace Stevebauman\Revision\Tests\Stubs\Models;

use Stevebauman\Revision\Traits\HasRevisionsTrait;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasRevisionsTrait;

    protected $table = 'posts';

    protected $revisionColumns = ['*'];

    public function revisions()
    {
        return $this->morphMany('Stevebauman\Revision\Models\Revision', 'revisionable');
    }

    public function revisionUser()
    {
        return User::find(1);
    }

    public function revisionUserId()
    {
        return $this->revisionUser()->id;
    }
}
