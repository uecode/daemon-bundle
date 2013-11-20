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

	const EVENT_START = 'EVENT_START';

	const EVENT_CYCLE_START = 'EVENT_CYCLE_START';

	const EVENT_CYCLE_END = 'EVENT_CYCLE_END';

	const EVENT_STOP = 'EVENT_STOP';

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
	 * @var InputInterface $input;
	 */
	protected $input;

	/**
	 * @var OutputInterface $output;
	 */
	protected $output;

	/**
	 * @var array
	 */
	protected $events = array();

	/**
	 * @var array Allowed Methods
	 */
	protected $methods = array( 'start', 'stop', 'restart', 'test' );

	/**
	 * @var bool
	 */
	private $test = false;

	/**
	 * @return InputInterface
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * @param InputInterface $input
	 */
	public function setInput( InputInterface $input )
	{
		$this->input = $input;
	}

	/**
	 * Configures the command
	 */
	final protected function configure()
	{
		$this->addMethods();

		$this
			->setName( $this->name )
			->setDescription( $this->description )
			->setHelp( $this->help )
			->addArgument( 'method', InputArgument::REQUIRED, implode( '|', $this->methods ) );

		$this->setArguments();
		$this->setOptions();
	}

	/**
	 * Set add new methods for the daemon
	 */
	protected function addMethods()
	{
	}

	/**
	 * Set the arguments for the command
	 */
	protected function setArguments()
	{
	}

	/**
	 * Set the options for the command
	 */
	protected function setOptions()
	{
	}

	/**
	 * Adds the given method to the list of allowed methods
	 * 
	 * @param string $method
	 */
	protected function addMethod( $method )
	{
		if( !arra_key_exists( $method, $this->methods) ) {
			$this->methods[] = $method;
		}
	}

	/**
	 * Grabs the argument data and runs the argument on the daemon
	 *
	 * @param \Symfony\Component\Console\Input\InputInterface   $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 *
	 * @return void
	 * @throws \Exception
	 */
	final protected function execute( InputInterface $input, OutputInterface $output )
	{
		$method = $input->getArgument( 'method' );
		if ( !in_array( $method, $this->methods ) ) {
			throw new \Exception( sprintf( 'Method must be one of: %s', implode( ', ', $this->methods ) ) );
		}
		$this->setInput( $input );
		$this->setOutput( $output );
		$this->test      = $method == 'test' ? true : false;
		$this->container = $this->getContainer();

		$this->createDaemon();
		call_user_func( array( $this, $method ) );
	}

	/**
	 * Creates and Initializes the daemon
	 */
	final protected function createDaemon()
	{
		$this->daemon = $this->container->get( 'uecode.daemon_service' );
		$daemonName   = strtolower( str_replace( ':', '_', $this->getName() ) );
		if ( !$this->container->hasParameter( $daemonName . '.daemon.options' ) ) {
			throw new \Exception( sprintf( "Couldnt find a daemon for %s", $daemonName . '.daemon.options' ) );
		}
		$this->daemon->initialize( $this->container->getParameter( $daemonName . '.daemon.options' ) );
	}

	/**
	 * Adds an event to the command
	 *
	 * @param string            $type Type of the event, EVENT_START, EVENT_CYCLE_START, EVENT_CYCLE_STOP, EVENT_STOP right now
	 * @param string            $name Name of the event
	 * @param \Closure|callable $function
	 *
	 * @throws \Exception Throws an exception on bad arguments
	 */
	protected function addEvent( $type, $name, $function )
	{
		if ( is_callable( $function ) || $function instanceof \Closure ) {
			if ( is_string( $name ) ) {
				$this->events[ $type ][ $name ] = $function;
			} else {
				throw new \Exception( "Name passed isn't a string. " );
			}
		} else {
			throw new \Exception( "Function passed is not a callable or a closure. " );
		}
	}

	/**
	 * Removes the named event for a given type.
	 *
	 * @param string $type Type of the event, EVENT_START, EVENT_CYCLE_START, EVENT_CYCLE_STOP, EVENT_STOP right now
	 * @param string $name Name of the event
	 */
	protected function removeEvent( $type, $name )
	{
		unset( $this->events[ $type ][ $name ] );
	}

	/**
	 * Starts the Daemon
	 *
	 * @throws \Uecode\DaemonBundle\System\Daemon\Exception
	 */
	final protected function start()
	{
		if ( $this->daemon->isRunning() ) {
			throw new Exception( 'Daemon is already running!' );
		}

		$this->daemon->start();

		$this->runEvents( self::EVENT_START );

		while ( $this->daemon->isRunning() ) {
			// Do stuff here
			$this->runEvents( self::EVENT_CYCLE_START );
			$this->daemonLogic();
			$this->runEvents( self::EVENT_CYCLE_END );
		}
		$this->runEvents( self::EVENT_STOP );
		$this->daemon->stop();
	}

	/**
	 * Restarts the Daemon
	 *
	 * @throws \Uecode\DaemonBundle\System\Daemon\Exception
	 */
	final protected function restart()
	{
		if ( !$this->daemon->isRunning() ) {
			throw new Exception( 'Daemon is not running!' );
		}

		$this->daemon->restart();
		$this->runEvents( self::EVENT_START );
		while ( $this->daemon->isRunning() ) {
			// Do stuff here
			$this->runEvents( self::EVENT_CYCLE_START );
			$this->daemonLogic();
			$this->runEvents( self::EVENT_CYCLE_END );
		}
		$this->runEvents( self::EVENT_STOP );
		$this->daemon->stop();
	}

	/**
	 * Stops the Daemon
	 *
	 * @throws \Uecode\DaemonBundle\System\Daemon\Exception
	 */
	final protected function stop()
	{
		if ( !$this->daemon->isRunning() ) {
			throw new Exception( 'Daemon is not running!' );
		}
		$this->runEvents( self::EVENT_STOP );
		$this->daemon->stop();
	}

	final protected function test()
	{
		$this->runEvents( self::EVENT_START );
		$this->runEvents( self::EVENT_CYCLE_START );
		$this->daemonLogic();
		$this->runEvents( self::EVENT_CYCLE_END );
		$this->runEvents( self::EVENT_STOP );
	}

	protected function runEvents( $type )
	{
		$this->log( "Finding all {$type} events and running them. " );
		$events = $this->getEvents( $type );
		foreach ( $events as $name => $event ) {
			$this->log( "Running the `{$name}` {$type} event. " );
			if( $event instanceof \Closure ) {
				$event( $this );
			} else {
				call_user_func_array( $event, array( $this ) );
			}
		}
	}

	protected function log( $content = '', $level = 'info' )
	{
		if ( null !== $this->test ) {
			$this->getOutput()->writeln( $content );
		}
		$this->container->get( 'logger' )->$level( $content );
	}

	/**
	 * @return OutputInterface
	 */
	public function getOutput()
	{
		return $this->output;
	}

	/**
	 * @param OutputInterface $output
	 */
	public function setOutput( OutputInterface $output )
	{
		$this->output = $output;
	}

	/**
	 * Gets an array of events for the given type
	 *
	 * @param string $type Type of events, EVENT_START, EVENT_CYCLE_START, EVENT_CYCLE_STOP, EVENT_STOP right now
	 *
	 * @return \Closure[]|callable[]
	 */
	protected function getEvents( $type )
	{
		return array_key_exists( $type, $this->events ) ? $this->events[ $type ] : array();
	}

	/**
	 * Sets the events for the command
	 */
	protected function setEvents()
	{
		$this->events = array();
	}

	/**
	 * Daemon Logic Container
	 */
	abstract protected function daemonLogic();

	/**
	 * Gets a service by id.
	 *
	 * @param string $id The service id
	 *
	 * @return object The service
	 */
	protected function get( $id )
	{
		return $this->container->get( $id );
	}
}
