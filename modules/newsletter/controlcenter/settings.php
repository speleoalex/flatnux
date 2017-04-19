<?php
/**
 * @package Flatnux_module_newsletter
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2014
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 *
 */
defined('_FNEXEC') or die('Restricted access');
require_once("modules/newsletter/functions.php");
FN_LoadMessagesFolder("modules/newsletter/");
$action = FN_GetParam("action",$_GET,"html");
switch($action)
{
	case "" :
		NS_Admin();
		break;
	case "getall" :
		NS_GetAll();
		break;
	case "send" :
		$messagetosend = FN_GetParam("message",$_GET,"html");
		$group = FN_GetParam("group",$_GET,"html");
		echo "<iframe height=\"200\" width=\"600\" src=\"{$_FN['siteurl']}/modules/newsletter/newsletter_send.php?m=$messagetosend&group=$group\"></iframe>";
		echo "<br /><button onclick=\"window.location='?mod={$_FN['mod']}&opt=$opt'\">";
		echo FN_i18n("next");
		echo "</button>";
		break;
}
?>