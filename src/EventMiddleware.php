<?php

namespace League\Tactician\CommandEvents;

use League\Event\EmitterAwareInterface;
use League\Event\EmitterInterface;
use League\Event\EmitterTrait;
use League\Tactician\CommandEvents\Event;
use League\Tactician\Command;
use League\Tactician\Middleware;

/**
 * Provides an event-driven middleware functionality
 */
class EventMiddleware implements Middleware, EmitterAwareInterface
{
    use EmitterTrait;

    /**
     * @param EmitterInterface $emitter
     */
    public function __construct(EmitterInterface $emitter = null)
    {
        $this->setEmitter($emitter);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Command $command, callable $next)
    {
        try {
            $this->getEmitter()->emit(new Event\CommandReceived($command));

            $returnValue = $next($command);

            $this->getEmitter()->emit(new Event\CommandExecuted($command));

            return $returnValue;
        } catch (\Exception $e) {
            $this->getEmitter()->emit($event = new Event\CommandFailed($command, $e));

            if (!$event->isExceptionCaught()) {
                throw $e;
            }
        }
    }
}