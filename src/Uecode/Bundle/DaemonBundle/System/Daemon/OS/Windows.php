<?php

namespace Uecode\Bundle\DaemonBundle\System\Daemon\OS;

/**
 * A System_Daemon_OS driver for Windows
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

class Windows extends OS
{
    /**
     * Determines wether this system is compatible with this OS
     *
     * @return boolean
     */
    public function isInstalled() 
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== "WIN") {
            return false;
        }
        
        return true;
    }
}
