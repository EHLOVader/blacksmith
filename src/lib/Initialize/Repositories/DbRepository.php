<?php namespace Repositories;

use App\Contracts\Instances\InstanceInterface;
use App\Contracts\Repositories\RepositoryInterface;

class DbRepository implements RepositoryInterface
{

    /**
     * @inheritDoc
     */
    public function find($id)
    {
        //@todo modify with implementation
    }

    /**
     * @inheritDoc
     */
    public function findOrFail($id)
    {
        //@todo modify with implementation
    }

    /**
     * @inheritDoc
     */
    public function all()
    {
        //@todo modify with implementation
    }

    /**
     * @inheritDoc
     */
    public function update(InstanceInterface $model)
    {
        //@todo modify with implementation
    }

    /**
     * @inheritDoc
     */
    public function create(array $attributes)
    {
        //@todo modify with implementation
    }

    /**
     * @inheritDoc
     */
    public function save(InstanceInterface $model)
    {
        //@todo modify with implementation
    }

    /**
     * @inheritDoc
     */
    public function delete(InstanceInterface $model)
    {
        //@todo modify with implementation
    }
}
