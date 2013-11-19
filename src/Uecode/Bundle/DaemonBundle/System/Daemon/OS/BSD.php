<?php

namespace Uecode\Bundle\DaemonBundle\System\Daemon\OS;

/**
 * A System_Daemon_OS driver for BSD
 *
 * @category  System
 * @package   Daemon
 * @author    Kevin van Zonneveld <kevin@vanzonneveld.net>
 * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
 * @version   SVN: Release: $Id$
 * @link      http://trac.plutonia.nl/projects/system_daemon
 * * 
 */

use Uecode\Bundle\DaemonBundle\System\Daemon\OS;
 
class BSD extends OS
{
    /**
     * Template path
     *
     * @var string
     */
    protected $_autoRunTemplatePath = '#datadir#/template_BSD';
    
    /**
     * Determines wether the system is compatible with this OS
     *
     * @return boolean
     */
    public function isInstalled() 
    {
        if (!stristr(PHP_OS, "Darwin")
            && !stristr(PHP_OS, "BSD")) {
            return false;
        }
        
        return true;
    }
    
}
