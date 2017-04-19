<?php
/**
 * @package Flatnux_module_news
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 *
 */
defined( '_FNEXEC' ) or die( 'Restricted access' );
global $_FN;
FN_LoadMessagesFolder("modules/news/");
require_once ("modules/news/functions.php");
$config = FN_LoadConfig("modules/news/config.php");
//dprint_r($config);
$DB = new XMLDatabase("fndatabase", $_FN['datadir']);
//published
echo "<b>".FN_i18n("news statistics").":</b><br />";
$allnews = $DB->query("SELECT unirecid,status FROM {$config['tablename']} WHERE status LIKE '1' ");
$published = count($allnews);
//unpublished
$allnews = $DB->query("SELECT unirecid,status FROM {$config['tablename']} WHERE status LIKE '0' AND guestnews LIKE '' ");
$unpublished = count($allnews);
//signed
$allnews = $DB->query("SELECT unirecid,status FROM {$config['tablename']} WHERE status LIKE '0' AND guestnews <> '' ");
$signed = count($allnews);
echo "<a href=\"controlcenter.php?mod={$_FN['mod']}&op=edit&opt=fnc_ccnf_section_{$_FN['mod']}\">" . FN_i18n("published news") . "</a> : $published <br />";
if ( $unpublished > 0 )
	echo "<a href=\"controlcenter.php?mod={$_FN['mod']}&op=edit&opt=fnc_ccnf_section_{$_FN['mod']}\">" . FN_i18n("unpublished news") . "</a> : $unpublished<br />";
else
	echo "" . FN_i18n("no unpublished news") . "<br />";
if ( $signed > 0 )
	echo "<a href=\"controlcenter.php?mod={$_FN['mod']}&op=edit&signews=1&opt=fnc_ccnf_section_{$_FN['mod']}\">" . FN_i18n("signed news") . "</a> : $signed<br />";
else
	echo "" . FN_i18n("no signed news") . "<br />";
?>