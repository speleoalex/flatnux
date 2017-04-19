<?php
/**
 * @package Flatnux_module_newsarhive
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 *
 */
defined( '_FNEXEC' ) or die( 'Restricted access' );
global $_FN;
include ("sections/{$_FN['vmod']}/config.php");
$vcss = time();
//section css
if ( file_exists("sections/{$_FN['vmod']}/style.css") )
	echo "<style type=\"text/css\" >\n@import url({$_FN['siteurl']}sections/{$_FN['vmod']}/style.css?$vcss);\n</style>";
//theme css
if ( file_exists("themes/{$_FN['idmod']}/modules/{$_FN['idmod']}.css") )
	echo "<style type=\"text/css\" >\n@import url({$_FN['siteurl']}themes/{$_FN['idmod']}/modules/{$_FN['idmod']}.css?$vcss);\n</style>";
?>