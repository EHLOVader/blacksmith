<?php namespace Contracts\Notification;

/**
 * Handles creator events
 */

use App\Contracts\Instances\InstanceInterface;

interface DestroyerInterface
{
    /**
     * Handle the destroySucceeded event
     *
     * @param InstanceInterface $instance
     *
     * @return mixed
     */
    public function destroySucceeded(InstanceInterface $instance);

    /**
     * Handle the destroyFailed event
     *
     * @param InstanceInterface $instance
     *
     * @return mixed
     */
    public function destroyFailed(InstanceInterface $instance);
} 