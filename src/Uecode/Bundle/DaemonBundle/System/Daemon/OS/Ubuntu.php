<?php

namespace Uecode\Bundle\DaemonBundle\System\Daemon\OS;

/**
 * A System_Daemon_OS driver for Ubuntu. Based on Debian
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

use Uecode\Bundle\DaemonBundle\System\Daemon\OS\Debian;

class Ubuntu extends Debian
{
    /**
     * On Linux, a distro-specific version file is often telling us enough
     *
     * @var string
     */
    protected $_osVersionFile = "/etc/lsb-release";
    
}
