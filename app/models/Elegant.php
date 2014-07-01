<?php

/**
 * @author Sergio Carlos Morales Angeles
 *
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 *
 * Class Elegant
 */
abstract class Elegant extends Eloquent
{
    /**
     * Get the rules used to validate the object
     * @return array
     */
    public abstract function getValidationRules();

    /**
     * Validate the current object
     * @throws Exception_Elegant_ValidationFailed
     */
    public function validate()
    {
        // If there are no rules, then exit early
        if (count($this->getValidationRules()) == 0) {
            return;
        }

        $validator = Validator::make(
            $this->attributes,
            $this->getValidationRules()
        );

        if ($validator->fails()) {
            throw new Exception_Elegant_ValidationFailed(
                implode(',', $validator->messages()->all())
            );
        }
    }

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = array())
    {
        $this->validate();
        return parent::save($options);
    }

    /**
     * Utility function that will insert into a many-to-many relationship but also ensures that the inserted object is
     * immediately accessible from the relationship property.
     * This causes an extra query to be performed to reload the object.
     *
     * @param string $propertyName Name of the property that represents the relationship
     * @param mixed $value
     */
    public function insertIntoManyToManyRelationship($propertyName, $value)
    {
        $this->$propertyName()->attach($value);
        $this->reloadProperty($propertyName);
    }

    /**
     * Utility function that will remove from a many-to-many relationship but also ensures that the removed object is
     * immediately unavailable from the relationship property.
     * This causes an extra query to be performed to reload the object.
     *
     * @param string $propertyName Name of the property that represents the relationship
     * @param mixed $value
     */
    public function removeFromManyToManyRelationship($propertyName, $value)
    {
        $this->$propertyName()->detach($value);
        $this->reloadProperty($propertyName);
    }

    /*
     * Utility function that will leave only the provided values
     * in the many to many relationship
     *
     * @param string $propertyName Name of the property that represents the relationship
     * @param array $values
     */
    public function syncManyToManyRelationship($propertyName, $values)
    {
        // 'sync' does not require reloading the property afterwards for immediate availability
        $this->$propertyName()->sync($values);
    }

    /**
     * Due to some ORM caching, this will reload a given property for this object
     * @param string $propertyName
     */
    public function reloadProperty($propertyName)
    {
        $this->load($propertyName);
    }
}
