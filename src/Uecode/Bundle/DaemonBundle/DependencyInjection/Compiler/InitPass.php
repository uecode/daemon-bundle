<?php
namespace Uecode\Bundle\DaemonBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Dawid zulus Pakula
 */
class InitPass implements CompilerPassInterface
{

	/**
	 * {@inheritDoc}
	 */
	public function process(ContainerBuilder $container)
	{
		if (! $container->hasParameter('uecode.daemon')) {
			return;
		}
		$config = $container->getParameter('uecode.daemon');
		// merges each configured daemon with default configs
		// and makes sure the pid directory is writable
		$filesystem = new Filesystem();
		foreach ($config['daemons'] as $name => $cnf) {
			if (null == $cnf) {
				$cnf = array();
			}
			try {
				$pidDir = $cnf['appPidDir'];
				$filesystem->mkdir($pidDir, 0777);
			} catch (\Exception $e) {
				echo 'UecodeDaemonBundle exception: ', $e->getMessage(), "\n";
			}

			if (isset($cnf['appUser']) || isset($cnf['appGroup'])) {
				if (isset($cnf['appUser']) && (function_exists('posix_getpwnam'))) {
					$user = posix_getpwnam($cnf['appUser']);
					if ($user) {
						$cnf['appRunAsUID'] = $user['uid'];
					}
				}

				if (isset($cnf['appGroup']) && (function_exists('posix_getgrnam'))) {
					$group = posix_getgrnam($cnf['appGroup']);
					if ($group) {
						$cnf['appRunAsGID'] = $group['gid'];
					}
				}

				if (! isset($cnf['appRunAsGID'])) {
					$user = posix_getpwuid($cnf['appRunAsUID']);
					$cnf['appRunAsGID'] = $user['gid'];
				}
			}

			$cnf['logLocation'] = rtrim($cnf['logDir'], '/') . '/' . $cnf['appName'] . 'Daemon.log';
			$cnf['appPidLocation'] = rtrim($cnf['appPidDir'], '/') . '/' . $cnf['appName'] . '/' . $cnf['appName'] . '.pid';
			unset($cnf['logDir'], $cnf['appPidDir']);

			$container->setParameter($name . '.daemon.options', $cnf);
		}
	}
}
