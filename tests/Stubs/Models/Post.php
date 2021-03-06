<?php

namespace Stevebauman\Revision\Tests\Stubs\Models;

use Stevebauman\Revision\Traits\HasRevisionsTrait;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasRevisionsTrait;

    protected $table = 'posts';

    protected $revisionColumns = ['*'];

    protected $revisionColumnsToAvoid = [];

    protected $revisionColumnsFormatted = [
        'id' => 'ID',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
        'title' => 'Post Title',
        'description' => 'Post Description',
    ];

    protected $revisionColumnsMean = [
        'user_id' => 'user.username'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function revisions()
    {
        return $this->morphMany(Revision::class, 'revisionable');
    }

    public function revisionUserId()
    {
        return User::first()->getKey();
    }
}
