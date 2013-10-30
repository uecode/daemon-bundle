<?php
namespace Uecode\Bundle\DaemonBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Uecode\Bundle\DaemonBundle\DependencyInjection\Compiler\InitPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

class DaemonBundle extends Bundle
{

	public function build(ContainerBuilder $container)
	{
		parent::build($container);
		$container->addCompilerPass(new InitPass(), PassConfig::TYPE_OPTIMIZE);
	}
}
