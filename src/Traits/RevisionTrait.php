<?php

namespace Stevebauman\Revision\Traits;

trait RevisionTrait
{
    abstract public function revisionable();

    /**
     * Returns the revisions column name.
     *
     * @return string
     */
    public function getColumnName()
    {
        $model = $this->revisionable;

        if(property_exists($model, 'revisionColumnsFormatted') && is_array($model->revisionColumnsFormatted)) {
            if(array_key_exists($this->key, $model->revisionColumnsFormatted)) {
                return $model->revisionColumnsFormatted[$this->key];
            }
        }

        return $this->key;
    }

    /**
     * Returns the old value of the model.
     *
     * @return mixed
     */
    public function getOldValue()
    {
        $model = $this->revisionable;

        $oldValue = $this->old_value;

        $accessor = $this->getAccessor($model);

        if($accessor) {
            if($model->hasGetMutator($accessor)) {
                return $model->mutateAttribute($accessor, $oldValue);
            }
        }

        return $oldValue;
    }

    /**
     * Returns the new value of the model.
     *
     * @return mixed
     */
    public function getNewValue()
    {
        $model = $this->revisionable;

        $newValue = $this->new_value;

        $accessor = $this->getAccessor($model);

        if($accessor) {
            if($model->hasGetMutator($accessor)) {
                return $model->mutateAttribute($accessor, $newValue);
            }
        }

        return $newValue;
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
     * Retrieves a models accessor if it exists.
     *
     * @param $model
     *
     * @return bool|Mixed
     */
    private function getAccessor($model)
    {
        if(property_exists($model, 'revisionColumnsMean') && is_array($model->revisionColumnsMean)) {
            if(array_key_exists($this->key, $model->revisionColumnsMean)) {
                $accessor = $model->revisionColumnsMean[$this->key];

                return $accessor;
            }
        }

        return false;
    }
}
