<?php

namespace Stevebauman\Revision\Traits;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Stevebauman\Revision\Models\Revision;

/**
 * Trait RevisionsTrait
 *
 * The basic revisions trait that implements the basic Revision model and laravels Auth gateway.
 *
 * @package Stevebauman\Revision\Traits
 */
trait RevisionsTrait
{
    use HasRevisionsTrait;

    /**
     * Returns the class for revision model
     * @return string
     */
    protected function getRevisionModelClass()
    {
        return Revision::class;
    }

    /**
     * Uses the Eloquents method to get the table
     * @return string
     */
    public function getTable()
    {
        return parent::getTable();
    }

    /**
     * Returns the revions
     * @return MorphMany
     */
    public function revisions()
    {
        return $this->morphMany($this->getRevisionModelClass(), "revisionable");
    }

    /**
     * Get the current loged user id.
     * @return int|null
     */
    public function revisionUserId()
    {
        if (\Auth::check()) {
            return \Auth::user()->getAuthIdentifier();
        }

        return null;
    }
}