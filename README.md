#Uecode DaemonBundle #
##Overview##
DaemonBundle is a wrapper for the PEAR library System_Daemon which was created by Kevin Vanzonneveld.

This will enable you to install the symfony bundle and easily convert your Symfony2 console scripts into system daemons.

pcntl is required to be configured in your PHP binary to use this. On my Ubuntu server I was able to install pcntl easily with the following command:

    sudo apt-get install php-5.3-pcntl-zend-server 

##System_Daemon PEAR package##
System_Daemon is a PHP class that allows developers to create their own daemon 
applications on Linux systems. The class is focussed entirely on creating & 
spawning standalone daemons

More info at:

- [Blog Article: Create daemons in PHP][1]
- [Report Issues][2]
- [Package Statistics][3]
- [Package Home][4]

  [1]: http://kevin.vanzonneveld.net/techblog/article/create_daemons_in_php/
  [2]: http://pear.php.net/bugs/report.php?package=System_Daemon
  [3]: http://pear.php.net/package-stats.php?pid=798&cid=37
  [4]: http://pear.php.net/package/System_Daemon


##DaemonBundle Config##

THIS BUNDLE REQUIRES THE Uecode\Bundle\UecodeBundle (https://github.com/uecode/uecode-bundle)

Place Uecode\Bundle\Daemonbundle in your src directory and do the following:

### composer.json ###

	"uecode/daemon-bundle": "dev-master",

### appKernel.php ###
Add The DaemonBundle to your kernel bootstrap sequence

    public function registerBundles()
    {
        $bundles = array(
            //...
            new Uecode\Bundle\DaemonBundle\DaemonBundle(),
        );
        //...

        return $bundles;
    }

### config.yml ###
By Default, system daemons have a sensible configuration. If you need to change any configuration setting , you could do it by adding this configuration to your project config. Only the values that need to be changed should be added, the bundle extension will merge your daemon configs into its defaults. YOU MUST HAVE AT LEAST THIS PIECE TO WORK

    app/config.yml

    #Uecode DaemonBundle Config
    uecode:
        daemon:

### config.yml - Extras ###
    app/config.yml

    #UecodeDaemonBundle Configuration Example
    uecode:
        daemon:
            daemons:
                #creates a daemon using default options
                example: ~
    
                #an example of all the available options
                explicitexample:
                    appName: example
                    appDir: %kernel.root_dir%
                    appDescription: Example of how to configure the DaemonBundle
                    logDir: %kernel.logs_dir%
                    authorName: Aaron Scherer
                    authorEmail: aequasi@gmail.com
                    appPidDir: %kernel.cache_dir%/daemons/
                    sysMaxExecutionTime: 0
                    sysMaxInputTime: 0
                    sysMemoryLimit: 1024M
                    appUser: apache
                    appGroup: apache
                    appRunAsGID: 1000
                    appRunAsUID: 1000

##Creating a Daemon##

##Code##
Make sure you extend \Uecode\Bundle\DaemonBundle\Command\ExtendCommand

	<?php
    namespace Uecode\Bundle\DaemonBundle\Command;

    use \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
    use \Symfony\Component\Console\Input\InputInterface;
    use \Symfony\Component\Console\Input\ArrayInput;
    use \Symfony\Component\Console\Output\OutputInterface;
    use \Symfony\Component\DependencyInjection\Container;

    use \Uecode\Bundle\DaemonBundle\System\Daemon\Exception;
    use \Uecode\Bundle\DaemonBundle\Service\DaemonService;

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
    			->setHelp( 'Usage: <info>php app/console example start|stop|restart</info>' )
    			->addArgument( 'method', InputArgument::REQUIRED, 'start|stop|restart' );
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

##Usage##
Once you have Daemonized your symfony Console Commands, you can simply run them from the command line like so:

    aequasi@ue:~/example$ php app/console queue:start

    aequasi@ue:~/example$ php app/console queue:stop

    aequasi@ue:~/example$ php app/console queue:restart


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/uecode/daemon-bundle/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

