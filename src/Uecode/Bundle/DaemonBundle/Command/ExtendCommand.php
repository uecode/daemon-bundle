<?php
/**
 * @author Aaron Scherer <aequasi@gmail.com>
 * @date   Oct 12, 2012
 */
namespace Uecode\Bundle\DaemonBundle\Command;

use \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\DependencyInjection\Container;

use \Uecode\Bundle\DaemonBundle\System\Daemon\Exception;
use \Uecode\Bundle\DaemonBundle\Service\DaemonService;

/**
 * Extendable Command class
 */
abstract class ExtendCommand extends AbstractCommand
{

    final protected function test()
    {
        $this->runEvents(self::EVENT_START);
        $this->runEvents(self::EVENT_CYCLE_START);
        $this->daemonLogic();
        $this->runEvents(self::EVENT_CYCLE_END);
        $this->runEvents(self::EVENT_STOP);
    }

    protected function loop()
    {
        while ($this->daemon->isRunning()) {
            // Do stuff here
            $this->runEvents(self::EVENT_CYCLE_START);
            $this->daemonLogic();
            $this->runEvents(self::EVENT_CYCLE_END);
        }
    }

    /**
     * Daemon Logic Container
     */
    abstract protected function daemonLogic();
}
