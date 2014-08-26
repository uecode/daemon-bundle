<?php

namespace Uecode\Bundle\DaemonBundle\Command;

use React\EventLoop\LoopInterface;
use React\EventLoop\Factory;

/**
 * Basic ReactPHP support
 *
 * @author Michał Matulka <therealmikz@gmail.com>
 */
abstract class ReactCommand extends AbstractCommand
{
    /**
     * @var \React\EventLoop\LoopInterface
     */
    private $eventLoop;

    abstract protected function prepareLoop(LoopInterface $loop);

    /**
     * @return \React\EventLoop\LoopInterface
     */
    private function initLoop()
    {
        $this->eventLoop = Factory::create();
        $this->prepareLoop($this->eventLoop);

        return $this->eventLoop;
    }

    protected function loop()
    {
        $this->initLoop();
        $this->eventLoop->run();
    }

    final protected function test()
    {
        $this->runEvents(self::EVENT_START);
        $loop = $this->initLoop();
        $this->runEvents(self::EVENT_CYCLE_START);
        $loop->tick();
        $this->runEvents(self::EVENT_CYCLE_END);
        $this->runEvents(self::EVENT_STOP);
    }
}
