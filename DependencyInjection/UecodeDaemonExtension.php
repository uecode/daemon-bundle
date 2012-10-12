<?php

namespace Uecode\DaemonBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Uecode\DaemonBundle\UecodeDaemonBundleException;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class UecodeDaemonExtension extends Extension
{
	private $defaultUser = null;

	public function load( array $configs, ContainerBuilder $container )
	{
		$loader = new Loader\YmlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ) );
		$loader->load( 'services.yml' );

		$this->_init( $configs, $container );
	}

	private function getDefaultConfig( $name, $container )
	{
		if ( null === $this->defaultUser && function_exists( 'posix_geteuid' ) ) {
			$this->defaultUser = posix_geteuid();
		}

		$defaults = array(
			'appName'               => $name,
			'appDir'                => $container->getParameter( 'kernel.root_dir' ),
			'appDescription'        => 'Uecode System Daemon',
			'logLocation'           => $container->getParameter(
				'kernel.cache_dir'
			) . '/' . $name . '/' . $container->getParameter( 'kernel.environment' ) . '.' . $name . '.daemon.log',
			'authorName'            => 'Uecode',
			'authorEmail'           => 'symfony2.kernel@127.0.0.1',
			'appPidLocation'        => $container->getParameter(
				'kernel.cache_dir'
			) . '/' . $name . '/' . $name . '.daemon.pid',
			'sysMaxExecutionTime'   => 0,
			'sysMaxInputTime'       => 0,
			'sysMemoryLimit'        => '1024M',
			'appRunAsUID'           => $this->defaultUser
		);

		return $defaults;
	}

	private function _init( $config, $container )
	{
		//merges each configured daemon with default configs
		//and makes sure the pid directory is writable
		$cacheDir   = $container->getParameter( 'kernel.cache_dir' );
		$filesystem = new Symfony\Component\Filesystem\Filesystem();
		foreach ( $config[ 'daemons' ] as $name => $cnf ) {
			if ( null == $cnf ) {
				$cnf = array();
			}
			try {
				$filesystem->mkdir( $cacheDir . '/' . $name . '/', 0777 );
			} catch( UecodeDaemonBundleException $e ) {
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

			$container->setParameter(
				$name . '.daemon.options',
				array_merge( $this->getDefaultConfig( $name, $container ), $cnf )
			);
		}

	}

	public function getXsdValidationBasePath()
	{
		return __DIR__ . '/../Resources/config/';
	}
}
