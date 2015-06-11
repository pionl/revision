<?php

namespace Stevebauman\Revision\Traits;

use Illuminate\Database\Eloquent\Model;

trait RevisionTrait
{
    /**
     * The revisionable morphTo relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function revisionable()
    {
        return $this->morphTo();
    }

    /**
     * Returns the revision user object.
     *
     * @return mixed
     */
    public function getUserResponsible()
    {
        $model = $this->revisionable;

        return $model->revisionUser();
    }

    /**
     * Returns the revisions column name.
     *
     * @return string
     */
    public function getColumnName()
    {
        $model = $this->revisionable;

        $column = $this->key;

        if(is_array($model->revisionColumnsFormatted) && array_key_exists($column, $model->revisionColumnsFormatted)) {
            return $model->revisionColumnsFormatted[$column];
        }

        return $column;
    }

    /**
     * Returns the old value of the model.
     *
     * @return mixed
     */
    public function getOldValue()
    {
        return $this->getRevisedValue('old_value');
    }

    /**
     * Returns the new value of the model.
     *
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->getRevisedValue('new_value');
    }

    /**
     * Returns the specified revisions key value.
     *
     * @param string $valueKey
     *
     * @return mixed
     */
    public function getRevisedValue($valueKey)
    {
        $model = $this->revisionable;

        $value = $this->$valueKey;

        /*
         * Check if the column key is inside
         * the column means property array
         */
        if($means = $this->getColumnMeans($this->key, $model)) {
            return $this->getColumnMeansProperty($means, $model, $value);
        }

        return $value;
    }

    /**
     * Returns the keys accessor on the specified model.
     *
     * If the key does not have an accessor, it returns false.
     *
     * @param int|string $key
     * @param Model      $model
     *
     * @return bool|string
     */
    private function getColumnMeans($key, $model)
    {
        if(is_array($model->revisionColumnsMean) && array_key_exists($key, $model->revisionColumnsMean)) {
            return $model->revisionColumnsMean[$key];
        }

        return false;
    }

    /**
     * Retrieves a relationships nested property from a column.
     *
     * @param string $key
     * @param Model  $model
     * @param mixed  $value
     *
     * @return mixed
     */
    private function getColumnMeansProperty($key, $model, $value)
    {
        // Explode the dot notated key
        $attributes = explode('.', $key);

        // Assign a temporary object to the specified model
        $tmpStr = $model;

        // Go through each attribute
        foreach ($attributes as $attribute) {
            if ($attribute === end($attributes)) {
                /*
                 * If we're at the end of the attributes array,
                 * we'll see if the temporary object is an instance
                 * of an Eloquent Model.
                 */
                if ($tmpStr instanceof Model) {
                    if($tmpStr->hasGetMutator($attribute)) {
                        /*
                         * If the relationship model has a get mutator
                         * for the current attrubte, we'll run it through
                         * the mutator and pass on the revisioned value.
                         */
                        $tmpStr = $tmpStr->mutateAttribute($attribute, $value);
                    } else {
                        /*
                         * Looks like the relationship model doesn't
                         * have a mutator for the attribute, we'll
                         * return the models attribute.
                         */
                        $tmpStr = $tmpStr->$attribute;
                    }
                }
            } else {
                $tmpStr = $tmpStr->$attribute;
            }
        }

        return $tmpStr;
    }
}
