<?php
/**
 * @author Aaron Scherer <aequasi@gmail.com>
 * @date Oct 12, 2012
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
abstract class ExtendCommand extends ContainerAwareCommand
{
	/**
	 * @var DaemonService $daemon
	 */
	protected $daemon;

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * @var string Command Name
	 */
	protected $name;

	/**
	 * @var string Command Description
	 */
	protected $description;

	/**
	 * @var string Command Help
	 */
	protected $help;

	/**
	 * @var bool
	 */
	private $test = false;

	/**
	 * @var InputInterface $input;
	 */
	protected $input;

	/**
	 * @var OutputInterface $output;
	 */
	protected $output;

	/**
	 * Configures the command
	 */
	final protected function configure()
	{
		$this
			->setName( $this->name )
			->setDescription( $this->description )
			->setHelp( $this->help )
			->addArgument( 'method', InputArgument::REQUIRED, 'start|stop|restart|test' );

		$this->setArguments();
		$this->setOptions();
	}

	/**
	 * Set the arguments for the command
	 */
	protected function setArguments(){}

	/**
	 * Set the options for the command
	 */
	protected function setOptions(){}

	protected function log( $content = '', $level = 'info' )
	{
		if( null !== $this->test ) $this->getOutput()->writeln( $content );
		$this->container->get( 'logger' )->$level( $content );
	}

	/**
	 * Grabs the argument data and runs the argument on the daemon
	 * @param \Symfony\Component\Console\Input\InputInterface   $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return void
	 * @throws \Exception
	 */
	final protected function execute( InputInterface $input, OutputInterface $output )
	{
		$method = $input->getArgument( 'method' );
		if ( !in_array( $method, array( 'start', 'stop', 'restart', 'test' ) ) ) {
			throw new \Exception( 'Method must be `start`, `stop`, `restart`, or `test`' );
		}
		$this->setInput( $input );
		$this->setOutput( $output );
		$this->test = $method == 'test' ? true : false;
		$this->container = $this->getContainer();

		$this->createDaemon( );
		call_user_func( array( $this, $method ) );
	}

	/**
	 * Creates and Initializes the daemon
	 */
	final protected function createDaemon( )
	{
		$this->daemon = $this->container->get( 'uecode.daemon' );
		$daemonName = str_replace( ':', '_', $this->getName() );
		$this->daemon->initialize( $this->container->getParameter( $daemonName . '.daemon.options' ) );
	}

	/**
	 * Starts the Daemon
	 * @throws \Uecode\DaemonBundle\System\Daemon\Exception
	 */
	final protected function start( )
	{
		if( $this->daemon->isRunning() )
		{
			throw new Exception( 'Daemon is already running!' );
		}

		$this->daemon->start();
		while ( $this->daemon->isRunning() ) {
			// Do stuff here
			$this->daemonLogic( );
		}
		$this->daemon->stop();

	}

	/**
	 * Restarts the Daemon
	 * @throws \Uecode\DaemonBundle\System\Daemon\Exception
	 */
	final protected function restart( )
	{
		if( !$this->daemon->isRunning() )
		{
			throw new Exception( 'Daemon is not running!' );
		}

		$this->daemon->restart();
		while ( $this->daemon->isRunning() ) {
			// Do stuff here
			$this->daemonLogic( );
		}
		$this->daemon->stop();
	}

	/**
	 * Stops the Daemon
	 * @throws \Uecode\DaemonBundle\System\Daemon\Exception
	 */
	final protected function stop( )
	{
		if( !$this->daemon->isRunning() )
		{
			throw new Exception( 'Daemon is not running!' );
		}
		$this->daemon->stop();
	}

	final protected function test( )
	{
		$this->daemonLogic( );
	}

	/**
	 * Gets a service by id.
	 *
	 * @param string $id The service id
	 *
	 * @return object The service
	 */
	protected function get($id)
	{
		return $this->container->get($id);
	}

	/**
	 * @param InputInterface $input
	 */
	public function setInput( InputInterface $input )
	{
		$this->input = $input;
	}

	/**
	 * @return InputInterface
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * @param OutputInterface $output
	 */
	public function setOutput( OutputInterface $output )
	{
		$this->output = $output;
	}

	/**
	 * @return OutputInterface
	 */
	public function getOutput()
	{
		return $this->output;
	}



	/**
	 * Daemon Logic Container
	 */
	abstract protected function daemonLogic( );
}
