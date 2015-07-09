<?php

namespace Stevebauman\Revision\Tests\Stubs\Models;

use Stevebauman\Revision\Traits\RevisionTrait;
use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    use RevisionTrait;

    /**
     * The revisions table.
     *
     * @var string
     */
    protected $table = 'revisions';

    /**
     * The belongsTo user relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
