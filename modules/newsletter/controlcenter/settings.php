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
$action=FN_GetParam("action",$_GET,"html");

switch($action)
{
    case "" :
        NS_Admin();
        echo "<br />";
        FNCC_NS_SubscribeForm();
        break;
    case "getall" :
        NS_GetAll();
        break;
    case "send" :
        $messagetosend=FN_GetParam("message",$_GET,"html");
        $group=FN_GetParam("group",$_GET,"html");
        echo "<iframe height=\"200\" width=\"600\" src=\"{$_FN['siteurl']}/modules/newsletter/newsletter_send.php?m=$messagetosend&group=$group\"></iframe>";
        echo "<br /><button onclick=\"window.location='?mod={$_FN['mod']}&opt=$opt'\">";
        echo FN_i18n("next");
        echo "</button>";
        break;
}

/**
 * 
 * @global type $_FN
 */
function FNCC_NS_SubscribeForm()
{


    global $_FN;
    $res="";
    $email=FN_GetParam("email",$_POST,"html");
    $group=FN_GetParam("group",$_POST,"html");
    if (isset($_POST['email'])&&FN_CheckMail($email))
    {
        if (isset($_POST['subscribe']))
        {
            NS_Subscribe($email,$group);
            echo "$email ".FN_Translate("subscribed to the newsletter")." $group";
        }
        if (isset($_POST['unsubscribe']))
        {
            NS_Unsubscribe($email,$group);
            echo "$email ".FN_Translate("deleted to the newsletter")." $group";
        }
    }
//    else

    echo "<p>". FN_Translate("email").":"."</p>";
    {
        $opt=FN_GetParam("opt",$_GET);
        $action=("controlcenter.php?mod={$_FN['mod']}&opt=$opt");
        $groups=NS_GetNsGroups();
        $txtmail=FN_Translate("email");
        if (count($groups)==1)
        {
            $group=$groups[0];
            echo "<form method=\"post\" action=\"$action\">\n";
            echo"<p><input type=\"text\" name=\"email\" value=\"$txtmail\" onfocus=\"if (this.value === '$txtmail') {
                      this.value = ''
                  }\" 
		  onblur=\"if (this.value === '') {
                      this.value = '$txtmail'
                  }\" /></p>";
            if (file_exists("blocks/{$_FN['block']}/privacy.php"))
            {
                include ("blocks/{$_FN['block']}/privacy.php");
            }
            echo "<p><input type=\"hidden\" name=\"group\" value=\"{$group['id']}\" />";
            echo "<input type=\"submit\" name=\"subscribe\" value=\"".FN_Translate("subscribe")."\" />";
            echo "<input type=\"submit\" name=\"unsubscribe\" value=\"".FN_Translate("unsubscribe")."\" /></p>";
            echo "</form>";
        }
        else
        {
            foreach($groups as $group)
            {
                echo "<form  method=\"post\" action=\"$action\">\n";
                echo "<p>Newsletter <b>".$group['title'].":</b></p>";
                echo"<p><input type=\"text\" name=\"email\" value=\"$txtmail\" onfocus=\"if (this.value === '$txtmail') {
                      this.value = ''
                  }\" 
		  onblur=\"if (this.value === '') {
                      this.value = '$txtmail'
                  }\" /></p>";
                echo "<p><input type=\"hidden\" name=\"group\" value=\"{$group['id']}\" />";
                echo "<input type=\"submit\" name=\"subscribe\" value=\"".FN_Translate("subscribe")."\" />";
                echo "<input type=\"submit\" name=\"unsubscribe\" value=\"".FN_Translate("unsubscribe")."\" /></p>";
                echo "</form>";
            }
        }
    }
}

?>