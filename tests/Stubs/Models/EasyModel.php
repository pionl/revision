<?php

namespace Stevebauman\Revision\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Stevebauman\Revision\Traits\RevisionsTrait;

class EasyModel extends Model
{
    use RevisionsTrait;

    protected $table = "easy_models";

    protected $fillable = [
        "title", "description"
    ];

    protected $revisionCollumnsUseFillable = true;
}