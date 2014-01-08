<?php
/**
 * @author Michał Matulka <therealmikz@gmail.com>
 * @date   Jan 8, 2014
 */

namespace Uecode\Bundle\DaemonBundle\Command;

use React\EventLoop;

/**
 * Basic ReactPHP support
 *
 * @author Michał Matulka <therealmikz@gmail.com>
 */
abstract class ReactCommand extends AbstractCommand
{

    /**
     * @var EventLoop\LoopInterface
     */
    private $eventLoop;

    abstract protected function prepareLoop(EventLoop\LoopInterface $loop);

    /**
     *
     * @return EventLoop\LoopInterface
     */
    private function initLoop()
    {
        $this->eventLoop = EventLoop\Factory::create();
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
