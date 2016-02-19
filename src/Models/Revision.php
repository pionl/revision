<?php

namespace Stevebauman\Revision\Models;

use Illuminate\Database\Eloquent\Model;
use Stevebauman\Revision\Traits\RevisionTrait;

/**
 * Class Revision
 *
 * The base revision implementation for easy usage. Uses Auth model via config entry.
 *
 * @property string     revisionable_type
 * @property int        revisionable_id
 * @property int|string user_id
 * @property int|string key
 * @property mixed      old_value
 * @property mixed      new_value
 * @package Stevebauman\Revision\Traits
 */
class Revision extends Model
{
    use RevisionTrait;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        $user_model = $this->getUserModel();

        return $this->belongsTo($user_model);
    }

    /**
     * Returns the user model
     * @return string
     */
    protected function getUserModel()
    {
        // need only major and minor version
        $version = floatval(app()->version());

        if ($version >= 5.2) {
            return app('config')->get('auth.providers.users.model');
        } else {
            return app('config')->get('auth.model');
        }
    }
}