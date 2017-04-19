<?php
/**
 * @package Flatnux_module_newsletter
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2014
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 *
 */
defined('_FNEXEC') or die('Restricted access');
global $_FN;
require_once("modules/newsletter/functions.php");
$config = FN_LoadConfig("modules/newsletter/config.php");


if (!file_exists("{$_FN['datadir']}/newsletter"))
{
	mkdir("{$_FN['datadir']}/newsletter");
}
//newsletter group --->
if (!empty($config['group_newsletters']))
{
	$table = new XMLTable("fndatabase","fn_groups",$_FN['datadir']);
	$grouplist = $table->GetRecordByPk($config['group_newsletters']);
	if (!$grouplist)
	{
		$table->InsertRecord(array("groupname"=>$config['group_newsletters']));
	}
}
//newsletter group ---<
//----init tables and templates------------------------------------------------>
if (file_exists("modules/newsletter/templates/newsletter.{$_FN['lang']}.html"))
{
	$strtemplate = file_get_contents("modules/newsletter/templates/newsletter.{$_FN['lang']}.html");
	if (!file_exists("{$_FN['datadir']}/newsletter/newsletter_template.{$_FN['lang']}.html"))
	{
		FN_Write($strtemplate,"{$_FN['datadir']}/newsletter/newsletter_template.{$_FN['lang']}.html");
	}
}
//email
//if ( !file_exists("{$_FN['datadir']}/newsletter/emails.txt") )
//{
//	FN_Write("", "{$_FN['datadir']}/newsletter/emails.txt");
//}
//messages
if (!file_exists("{$_FN['datadir']}/fndatabase/newsletter.php"))
{
	$str = file_get_contents("modules/newsletter/install/newsletter.php");
	FN_Write($str,"{$_FN['datadir']}/fndatabase/newsletter.php");
}
//messages
if (!file_exists("{$_FN['datadir']}/fndatabase/newsletter_newsletters.php"))
{
	$str = file_get_contents("modules/newsletter/install/newsletter_newsletters.php");
	FN_Write($str,"{$_FN['datadir']}/fndatabase/newsletter_newsletters.php");
	$t = FN_XmlTable("newsletter_newsletters");
	$t->InsertRecord(array("title"=>"default"));
}
//subscriptions
if (!file_exists("{$_FN['datadir']}/fndatabase/newsletter_subscriptions.php"))
{
	$str = file_get_contents("modules/newsletter/install/newsletter_subscriptions.php");
	FN_Write($str,"{$_FN['datadir']}/fndatabase/newsletter_subscriptions.php");
}

//field newsletter in users
$field = array();
$field['name'] = "newsletter";
$field['type'] = "multicheck";
$field['frm_i18n'] = "Subscriptions";
$field['frm_it'] = "Sottoscrizioni";
$field['frm_en'] = "Subscriptions";
$field['showinprofile'] = "0";
$field['frm_required'] = "0";
$field['foreignkey'] = "newsletter_newsletters";
$field['fk_link_field'] = "id";
$field['fk_show_field'] = "title";
$field['frm_show'] = "1";
addxmltablefield($_FN['database'],"fn_users",$field,$_FN['datadir']);
////----init tables and templates------------------------------------------------<

$action = FN_GetParam("action",$_GET,"html");
$opmod = FN_GetParam("opmod",$_GET,"html");
$pk = FN_GetParam("pk___xdb_newsletter",$_GET,"html");
$messagetosend = FN_GetParam("message",$_GET,"html");
switch($action)
{
	case "" :
		if (NS_IsAdmin())
		{
			NS_Admin();
		}
		else
		{
			NS_SubscribeForm();
		}
		break;
	case "getall" :
		NS_GetAll();
		break;
	case "send" :
		$group = FN_GetParam("group",$_GET,"html");
		echo "<iframe height=\"200\" width=\"600\" src=\"{$_FN['siteurl']}/modules/newsletter/newsletter_send.php?m=$messagetosend&group=$group\"></iframe>";
		echo "<br /><button onclick=\"window.location='?mod={$_FN['mod']}'\">";
		echo FN_i18n("next");
		echo "</button>";
		break;
}
/**
 *
 * @param <type> $email
 * @return <type>
 */
function is_email_valid($email)
{
	if (eregi("^([a-z0-9_\.-])+@(([a-z0-9_-])+\\.)+[a-z]{2,6}$",trim($email)))
		return 1;
	else
		return 0;
}

?>