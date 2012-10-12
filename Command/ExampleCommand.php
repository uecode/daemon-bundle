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
	/**
	 * Configures the Command
	 */
	protected function configure()
	{
		$this
			->setName( 'example' )
			->setDescription( 'Starts an example Daemon' )
			->setHelp( 'Usage: <info>php app/console example start|stop|restart</info>' );
		parent::configure();
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