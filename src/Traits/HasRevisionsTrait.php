<?php

namespace Stevebauman\Revision\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasRevisionsTrait
{
    /**
     * The morphMany revisions relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    abstract function revisions();

    /**
     * The hasOne revision user relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    abstract function revisionUser();

    /**
     * The current users ID for storage in revisions.
     *
     * @return int|string
     */
    abstract function revisionUserId();

    /**
     * The original model attributes
     * before it has been saved.
     *
     * @var array
     */
    private $revisionOriginalAttributes = [];

    /**
     * The trait boot method.
     */
    public static function bootHasRevisionsTrait()
    {
        static::saving(function(Model $model) {
            $model->beforeSave();
        });

        static::saved(function(Model $model) {
            $model->afterSave();
        });
    }

    /**
     * Retrieves the models original attributes
     * before the model has been saved.
     */
    public function beforeSave()
    {
        $columns = $this->getRevisionColumns();

        foreach($columns as $column) {
            $this->revisionOriginalAttributes[$column] = $this->getOriginal($column);
        }
    }

    /**
     * Retrieves the models updated attributes
     * and saves the changes in a revision record
     * per revision column.
     */
    public function afterSave()
    {
        $columns = $this->getRevisionColumns();

        foreach($columns as $column) {
            $originalValue = $this->revisionOriginalAttributes[$column];

            /*
             * Only create a new revision
             * record if the value has changed
             */
            if($originalValue  != $this->getAttribute($column)) {

                // Construct a new revision model instance.
                $revision = $this->revisions()->getRelated()->newInstance();

                $revision->revisionable_type = get_class($this);
                $revision->revisionable_id = $this->getKey();
                $revision->user_id = $this->revisionUserId();
                $revision->key = $column;
                $revision->old_value = array_get($this->revisionOriginalAttributes, $column);
                $revision->new_value = $this->getAttribute($column);

                $revision->save();
            }
        }
    }

    /**
     * Returns the revision columns.
     *
     * @return array|bool
     */
    private function getRevisionColumns()
    {
        $columns = $this->revisionColumns;

        if(count($columns) > 0)
        {
            /*
             * If the amount of columns is equal to one,
             * and the column is equal to the star character,
             * we'll retrieve all the attribute keys indicating
             * the table columns.
             */
            if(count($columns) === 1 && $columns[0] === '*')
            {
                return array_keys($this->getAttributes());
            } else
            {
                return $columns;
            }
        }

        return [];
    }
}
