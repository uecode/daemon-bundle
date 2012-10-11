<?php

namespace Uecode\DaemonBundle\Service;

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

use Uecode\DaemonBundle\System\Daemon as System_Daemon;
use Uecode\DaemonBundle\System\Daemon\Exception as UecodeDaemonBundleException;

class DaemonService
{

    private $_config = array();
    private $_pid;
    private $_interval = 2;
    
    public function __construct( )
    {
    }

	public function initialize( $options )
	{
		if (!empty($options)) {
			$options = $this->validateOptions($options);
			$this->setConfig($options);
		} else {
			throw new UecodeDaemonBundleException('Daemon instantiated without a config');
		}
	}
    
    private function validateOptions($options)
    {
        if (null === ($options['appRunAsUID'])) {
            throw new UecodeDaemonBundleException('Daemon instantiated without user or group');
        }
            
        if (!isset($options['appRunAsGID'])) {
            try {
                $user = posix_getpwuid($options['appRunAsUID']);
                $options['appRunAsGID'] = $user['gid'];
            } catch (UecodeDaemonBundleException $e) {
                echo 'Exception caught: ',  $e->getMessage(), "\n";
            }
        }
        
        return $options;
    }
    
    public function setConfig($config)
    {
        $this->_config = $config;
    }
    
    public function getPid()
    {
        if (file_exists($this->_config['appPidLocation'])) {
            $fh = fopen($this->_config['appPidLocation'], "r");
            $pid = fread($fh, filesize($this->_config['appPidLocation']));
            fclose($fh);
            return trim($pid);
        } else {
            return null;
        }
        
    }
    
    public function setPid($pid)
    {
        $this->_pid = $pid;
    }
    
    public function setInterval($interval)
    {
        $this->_interval = $interval;
    }
    
    public function getInterval()
    {
        return $this->_interval;
    }
    
    public function getConfig()
    {
        return $this->_config;
    }
    
    public function start()
    {
	    if( empty( $this->_config ) )
		    throw new UecodeDaemonBundleException('Daemon instantiated without a config');

        System_Daemon::setOptions($this->getConfig());
        System_Daemon::start();
        
        System_Daemon::info('{appName} System Daemon Started at %s',
            date("F j, Y, g:i a")
        );
        
        $this->setPid($this->getPid());
        
    }
    
    public function reStart()
    {
        System_Daemon::setOptions($this->getConfig());
        $pid = $this->getPid();
        System_Daemon::info('{appName} System Daemon flagged for restart at %s',
            date("F j, Y, g:i a")
        );
        $this->stop();
        exec("ps ax | awk '{print $1}'", $pids);
        while(in_array($pid, $pids, true)) {
            unset($pids);
            exec("ps ax | awk '{print $1}'", $pids);
            $this->iterate(5);
        }
        System_Daemon::info('{appName} System Daemon Started at %s',
            date("F j, Y, g:i a")
        );
        
        $this->start();
        
        
    }
    
    public function iterate($sec) {
        System_Daemon::iterate($sec);
    }
    
    public function isRunning() 
    {
        if (!System_Daemon::isDying() && $this->_pid != null && $this->_pid == $this->getPid()) {
            System_Daemon::iterate($this->_interval);
            return true;
        } else {
            return false;
        }
    }
    
    public function stop()
    {
        if (file_exists($this->_config['appPidLocation'])) {
            unlink($this->_config['appPidLocation']);
            System_Daemon::info('{appName} System Daemon Terminated at %s',
                date("F j, Y, g:i a")
            );
        } else {
            System_Daemon::info('{appName} System Daemon Stop flag sent at %s',
                date("F j, Y, g:i a")
            );
        }
    }
}
