<?php

namespace Stevebauman\Revision\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Stevebauman\Revision\Models\Revision;

trait HasRevisionsTrait
{
    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    abstract public function getTable();

    /**
     * The morphMany revisions relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    abstract public function revisions();

    /**
     * The current users ID for storage in revisions.
     *
     * @return int|string
     */
    abstract public function revisionUserId();

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
            // Make sure the column exists inside the original attributes array
            if(array_key_exists($column, $this->revisionOriginalAttributes)) {
                $originalValue = $this->revisionOriginalAttributes[$column];

                // Only create a new revision record if the value has changed
                if($originalValue  != $this->getAttribute($column)) {
                    // Retrieve the old value from the original attributes property.
                    $oldValue = array_get($this->revisionOriginalAttributes, $column);

                    // Retrieve the new value from the current attributes.
                    $newValue = $this->getAttribute($column);

                    // Create a new revision record.
                    $this->processCreateRevisionRecord($column, $oldValue, $newValue);
                }
            }
        }
    }

    /**
     * Returns the revision columns formatted array.
     *
     * @return null|array
     */
    public function getRevisionColumnsFormatted()
    {
        return $this->revisionColumnsFormatted;
    }

    /**
     * Returns the revision columns mean array.
     *
     * @return null|array
     */
    public function getRevisionColumnsMean()
    {
        return $this->revisionColumnsMean;
    }

    /**
     * Sets the revision columns.
     *
     * @param array $columns
     *
     * @return $this
     */
    public function setRevisionColumns(array $columns = ['*'])
    {
        if(property_exists($this, 'revisionColumns')) {
            $this->revisionColumns = $columns;
        }

        return $this;
    }

    /**
     * Sets the revision columns to avoid.
     *
     * @param array $columns
     *
     * @return $this
     */
    public function setRevisionColumnsToAvoid(array $columns = [])
    {
        if(property_exists($this, 'revisionColumnsToAvoid')) {
            $this->revisionColumnsToAvoid = $columns;
        }

        return $this;
    }

    /**
     * Returns the revision columns.
     *
     * @return array
     */
    protected function getRevisionColumns()
    {
        $columns = (is_array($this->revisionColumns) ? $this->revisionColumns : []);

        if(count($columns) === 1 && $columns[0] === '*') {
            // If the amount of columns is equal to one, and
            // the column is equal to the star character,
            // we'll retrieve all the attribute keys
            // indicating the table columns.
             $columns = Schema::getColumnListing($this->getTable());
        }

        // Filter the returned columns by the columns to avoid
        return array_filter($columns, function($column) {
            $columnsToAvoid = $this->revisionColumnsToAvoid;

            if(is_array($columnsToAvoid) && count($columnsToAvoid) > 0) {
                if(in_array($column, $columnsToAvoid)) return false;
            }

            return $column;
        });
    }

    /**
     * Creates a new revision record.
     *
     * @param string|int $key
     * @param mixed      $oldValue
     * @param mixed      $newValue
     *
     * @return bool|Model
     */
    private function processCreateRevisionRecord($key, $oldValue, $newValue)
    {
        /**
         * Construct a new revision model instance. The instance can be different class.
         * Base revision class used only for autocomplete.
         * @var Revision $revision
         */
        $revision = $this->revisions()->getRelated()->newInstance();

        $revision->revisionable_type = $this->getMorphClass();
        $revision->revisionable_id = $this->getKey();
        $revision->user_id = $this->revisionUserId();
        $revision->key = $key;
        $revision->old_value = $oldValue;
        $revision->new_value = $newValue;

        if($revision->save()) {
            return $revision;
        }

        return false;
    }
}
