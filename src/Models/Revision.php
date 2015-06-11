<?php

namespace Stevebauman\Revision\Models;

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
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function revisionable()
    {
        return $this->morphTo();
    }
}
