<?php namespace Contracts\Repositories;

use App\Contracts\Instances\InstanceInterface;


/**
 * Houses functionality relating to saving and retrieving
 * models.
 */

interface RepositoryInterface
{

    /**
     * Find a model by its primary key.
     *
     * @param $id
     */
    public function find($id);

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param $id
     */
    public function findOrFail($id);

    /**
     * Get all of the models from the database.
     */
    public function all();

    /**
     * Save the model to the database.
     *
     * @param InstanceInterface $model
     *
     * @return bool
     */
    public function update(InstanceInterface $model);

    /**
     * Save the model to the database.
     *
     * @param array $attributes
     *
     * @return Model
     */
    public function create(array $attributes);

    /**
     * Save the model to the database.
     *
     * @param \Contracts\Instances\InstanceInterface $model
     *
     * @return bool
     */
    public function save(InstanceInterface $model);

    /**
     * Delete the model from the database.
     *
     * @param \Contracts\Instances\InstanceInterface $model
     *
     * @return bool
     */
    public function delete(InstanceInterface $model);

}
