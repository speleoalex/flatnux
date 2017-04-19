<?php
/**
 * @package Flatnux_module_newsletter
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2014
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 *
 */
global $_FN;
chdir("../../");
include "include/flatnux.php";
require_once("modules/newsletter/functions.php");
$config = FN_LoadConfig("modules/newsletter/config.php","newsletter");
FN_LoadMessagesFolder("modules/newsletter/");

//dprint_r($config);
if (!NS_IsAdmin())
{
	die("");
}
$ishtml = true;
$message = FN_GetParam("m",$_GET,"flat");
$group = FN_GetParam("group",$_GET,"html");
$users_newsletter = NS_GetMailUsers($group);
$tablenl = new XMLTable("fndatabase","newsletter",$_FN['datadir']);
$messagevalues = $tablenl->GetRecordByPrimaryKey($message);
if (true || $messagevalues['status'] == "" || $messagevalues['status'] == "unsended")
{
	$messagevalues['status'] = "processing";
	$tablenl->UpdateRecord($messagevalues);
}
if (!file_exists("{$_FN['datadir']}/fndatabase/newsletter_send.php"))
{
	$str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<?php exit(0);?>
<tables>
	<field>
		<name>email</name>
		<primarykey>1</primarykey>
	</field>
	<field>
		<name>messages</name>
		<type>text</type>
	</field>	
</tables>
";
	FN_Write($str,"{$_FN['datadir']}/fndatabase/newsletter_send.php");
}
$tablesended = new XMLTable("fndatabase","newsletter_send",$_FN['datadir']);
echo "<html><head><title></title></head><body style=\"background-color:#ffffff;color:#000000;font-size:10px; font-family: monospace\">";
if (isset($messagevalues['subject']) && isset($messagevalues['body']))
{
	$spediti = 0;
	if (is_array($users_newsletter))
	{
		$numUsers = count($users_newsletter);
		foreach ($users_newsletter as $user)
		{
			$username = $user['email'];
			if (isset($user['username']))
				$username = $user['username'];
			$usernl = $tablesended->GetRecordByPrimaryKey($user['email']);
			if (FN_CheckMail($user['email']))
			{
				if (!isset($usernl['messages']))
				{
					$usernl = $tablesended->InsertRecord(array("email"=>$user['email'],"messages"=>""));
				}
				$messages = explode(",",$usernl['messages']);
				if (!in_array("n{$message}n",$messages))
				{
					echo FN_Translate("sent")." $spediti/$numUsers ".FN_i18n("messages")."";
					echo "<br />{$usernl['email']}: ".FN_i18n("to be sent");
					echo "<br />".FN_i18n("send message to").": {$usernl['email']} ";
					//sostituisco con i valori dei campi--->
					$body = $messagevalues['body'];
					$subject = $messagevalues['subject'];
					foreach ($user as $k=> $v)
					{
						$body = str_replace('{'.$k.'}',$v,$body);
						$subject = str_replace('{'.$k.'}',$v,$subject);
					}
					//sostituisco con i valori dei campi---<
					if (FN_SendMail($usernl['email'],$subject,$body,$ishtml))
					{
						$messages[] = "n{$message}n";
						$usernl['messages'] = implode(",",$messages);
						$tablesended->UpdateRecord($usernl);
						echo "<span style=\"color:green\">".FN_Translate("message sent")."</span>";
						FN_JsRedirect("?m=$message&group=$group");
						echo "";
						echo "</body></html>";
						exit();
					}
					else
					{
						echo "<span style=\"color:red\">".FN_Translate("delivery failure")."</span>";
						echo "";
						echo "\n<script language=\"javascript\">\nsetTimeout(function(){window.location='?m=$message';},{$config['newsletter_sleep_time']});\n</script>\n";
						echo "";
						echo "</body></html>";
						exit();
					}
				}
				else
				{
					$spediti++;
				}
			}
			else
			{
				echo "<span style=\"color:red\">No mail:$username {$user['email']}</span><br />";
			}
		}
	}
	$messagevalues['status'] = "sended";
	$tablenl->UpdateRecord($messagevalues);
	echo "sent $spediti messages";
	FN_Alert(fn_i18n("operation complete"));
	echo "</body></html>";
}
?>