<?php

namespace Uecode\Bundle\DaemonBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DaemonExtension extends Extension
{
	private $defaultUser = null;

	public function load( array $configs, ContainerBuilder $container )
	{
		$loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ) );
		$loader->load( 'services.yml' );

		$configs = $container->getParameter( 'uecode.daemon' );
		$this->_init( $configs, $container );
	}

	private function _init( $config, $container )
	{
		//merges each configured daemon with default configs
		//and makes sure the pid directory is writable
		$filesystem = new Filesystem();
		foreach ( $config[ 'daemons' ] as $name => $cnf ) {
			if ( null == $cnf ) {
				$cnf = array();
			}
			try {
				$pidDir = $cnf[ 'appPidDir' ];
				$filesystem->mkdir( $pidDir , 0777 );
			} catch( \Exception $e ) {
				echo 'UecodeDaemonBundle exception: ', $e->getMessage(), "\n";
			}

			if ( isset( $cnf[ 'appUser' ] ) || isset( $cnf[ 'appGroup' ] ) ) {
				if ( isset( $cnf[ 'appUser' ] ) && ( function_exists( 'posix_getpwnam' ) ) ) {
					$user = posix_getpwnam( $cnf[ 'appUser' ] );
					if ( $user ) {
						$cnf[ 'appRunAsUID' ] = $user[ 'uid' ];
					}
				}

				if ( isset( $cnf[ 'appGroup' ] ) && ( function_exists( 'posix_getgrnam' ) ) ) {
					$group = posix_getgrnam( $cnf[ 'appGroup' ] );
					if ( $group ) {
						$cnf[ 'appRunAsGID' ] = $group[ 'gid' ];
					}
				}

				if ( !isset( $cnf[ 'appRunAsGID' ] ) ) {
					$user                 = posix_getpwuid( $cnf[ 'appRunAsUID' ] );
					$cnf[ 'appRunAsGID' ] = $user[ 'gid' ];
				}
			}

			$cnf[ 'logLocation' ] = rtrim( $cnf[ 'logDir' ], '/' ) . '/' . $cnf[ 'appName' ] . 'Daemon.log';
			$cnf[ 'appPidLocation' ] = rtrim( $cnf[ 'appPidDir' ], '/' ) . '/' . $cnf[ 'appName' ] . '/' . $cnf[ 'appName' ] . '.pid';
			unset( $cnf[ 'logDir' ], $cnf[ 'appPidDir'] );

			$container->setParameter( $name . '.daemon.options', $cnf );
		}

	}

	public function getXsdValidationBasePath()
	{
		return __DIR__ . '/../Resources/config/';
	}
}
