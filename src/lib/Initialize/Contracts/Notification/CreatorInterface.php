<?php namespace Contracts\Notification;

/**
 * Handles creator events
 */

use App\Contracts\Instances\InstanceInterface;
use App\Validators\Validator;

interface CreatorInterface
{
    /**
     * Handle the creationSucceeded event
     *
     * @param InstanceInterface $instance
     *
     * @return mixed
     */
    public function creationSucceeded(InstanceInterface $instance);

    /**
     * Handle the creationFailed event
     *
     * @param Validator $validator
     *
     * @return mixed
     */
    public function creationFailed(Validator $validator);
}