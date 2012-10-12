<?php
/**
 * @author Aaron Scherer <aequasi@gmail.com>
 * @date Oct 12, 2012
 */
namespace Uecode\DaemonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Uecode\DaemonBundle\System\Daemon\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use \Uecode\DaemonBundle\Service\DaemonService;

/**
 * Example Command class
 */
class ExampleCommand extends ExtendCommand
{
	protected $name = 'example';
	protected $description = 'Starts an example Daemon';
	protected $help = 'Usage: <info>php app/console example start|stop|restart 1 [--sleep|-s 2]</info>';

	/**
	 * Configures the Command
	 */
	protected function setArguments()
	{
		$this->addArgument( 'debug', InputArgument::OPTIONAL, 'Debug mode?' );
	}

	protected function setOptions()
	{
		$this->addOption( 'sleep', 's', 5, 'How long should we sleep for?' );
	}

	/**
	 * Sample Daemon Logic. Logs `Daemon is running!` every 5 seconds
	 * @param \Symfony\Component\Console\Input\InputInterface   $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 */
	protected function daemonLogic( InputInterface $input, OutputInterface $output )
	{
		// Do a little logging
		$this->container->get( 'logger' )->info( 'Daemon is running!' );
		// And then sleep for 5 seconds
		$this->daemon->iterate( 5 );
	}
}