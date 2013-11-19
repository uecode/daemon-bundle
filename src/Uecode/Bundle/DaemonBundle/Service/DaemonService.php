<?php
/**
 * Daemon is a php5 wrapper class for the PEAR library System_Daemon
 *
 * PHP version 5
 *
 * @category  Uecode
 * @package   UecodeDaemonBundle
 * @author    Aaron Scherer <aequasi@gmail.com>
 * @license   MIT
 * @link      https://github.com/uecode/daemon-bundle
 */

namespace Uecode\Bundle\DaemonBundle\Service;


use \Uecode\Daemon;
use \Uecode\Daemon\Exception;

class DaemonService
{

	private $_config = array();
	private $_pid;
	private $_interval = 2;

	/**
	 * @var \Uecode\Daemon;
	 */
	protected $_daemon;

	public function initialize( $options )
	{
		if( empty( $options ) )
			throw new Exception( 'Daemon instantiated without a config!' );

		$this->setConfig( $options );
		$this->setPid( $this->getPid() );
		$this->setDaemon( new Daemon( $this->getConfig( ) ) );
	}

	/**
	 * @param \Uecode\Daemon $daemon
	 */
	public function setDaemon( Daemon $daemon )
	{
		$this->_daemon = $daemon;
	}

	/**
	 * @return \Uecode\Daemon
	 */
	public function getDaemon()
	{
		return $this->_daemon;
	}

	public function setConfig( $config )
	{
		$this->_config = $config;
	}

	public function getConfig( $key = '' )
	{
		if( $key != '' )
			return trim( $this->_config[ $key ] );
		return $this->_config;
	}

	public function getPid()
	{
		if( !empty( $this->_pid ) )
			return $this->_pid;
		return $this->readFile( $this->getConfig( 'appPidLocation' ) );
	}

	public function setPid( $pid )
	{
		$this->_pid = $pid;
	}

	public function setInterval( $interval )
	{
		$this->_interval = $interval;
	}

	public function getInterval()
	{
		return $this->_interval;
	}

	public function start()
	{
		$daemon = $this->getDaemon();
		$this->_daemon->setSigHandler( 'SIGTERM',
			function() use( $daemon )
			{
				$daemon->warning( "Received SIGTERM. " );
				$daemon->stop();
			}
		);

		$status = $this->_daemon->start();
		$this->_daemon->info( '{appName} System Daemon Started at %s', date( "F j, Y, g:i a" ) );
		$this->setPid( $this->getPid() );
		return $status;
	}

	public function restart()
	{
		return $this->_daemon->restart();
	}

	public function iterate( $sec )
	{
		return $this->_daemon->iterate( $sec );
	}

	public function isRunning()
	{
		return $this->_daemon->isRunning();
	}

	public function stop()
	{
		return $this->_daemon->stop();
	}

	private function readFile( $filename, $return = false )
	{
		if( !file_exists( $filename ) )
			return $return;
		return file_get_contents( $filename );
	}
}
