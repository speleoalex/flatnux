<?php

/**
 * @package Flatnux_module_newsletter
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2014
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 *
 */

/**
 * 
 * @global type $_FN
 * @param type $group
 * @return type
 */
function NS_GetMailUsers($group="_ALLUSERS")
{
    global $_FN;
    $userlist=array();
    if ($group == "_ALLUSERS")
    {
        $userlist=FN_XMLQuery("SELECT * FROM fn_users WHERE active LIKE '1'");
    }
    else
    {
        $userlist=FN_XMLQuery("SELECT * FROM fn_users WHERE active LIKE '1' AND (newsletter LIKE '$group' OR newsletter LIKE '%,$group,%' OR newsletter LIKE '%,$group' OR newsletter LIKE '$group,%')");
        $userlist2=FN_XMLQuery("SELECT * FROM newsletter_subscriptions WHERE (newsletter LIKE '$group' OR newsletter LIKE '%,$group,%' OR newsletter LIKE '%,$group' OR newsletter LIKE '$group,%')");
        if (is_array($userlist2))
        {
            foreach($userlist2 as $u)
            {
                $userlist[]=$u;
            }
        }
    }
    //--from files----->
    $fname="{$_FN['datadir']}/newsletter/$group";
    if (file_exists($fname))
    {
        $force=file_get_contents("$fname");
        $force=str_replace("\n","",str_replace("\r","",trim(ltrim($force))));
        $force=str_replace("\"","",str_replace("'","",trim(ltrim($force))));
        $force=explode(",",$force);
        foreach($force as $email)
        {
            $email=trim(ltrim($email));
            if (FN_CheckMail($email))
            {
                $tmp['email']=$email;
                $userlist[]=$tmp;
            }
        }
    }
    //--from files-----<
    //dprint_r($userlist);
    return ($userlist);
}

/**
 * 
 * @global type $_FN
 * @return type
 */
function NS_GetNsGroups()
{
    $groups=FN_XMLQuery("SELECT * FROM newsletter_newsletters");
    $rgroups=array();
    if (is_array($groups))
    {
        foreach($groups as $group)
        {
            $rgroups[]=$group;
        }
    }
    return $rgroups;
}

/**
 * 
 * @global type $_FN
 */
function NS_SubscribeForm()
{
    global $_FN;
    $email=FN_GetParam("email",$_POST,"html");
    $group=FN_GetParam("group",$_POST,"html");
    if (isset($_POST['email']) && FN_CheckMail($email))
    {
        if (isset($_POST['subscribe']))
        {
            echo NS_Subscribe($email,$group);
        }
        if (isset($_POST['unsubscribe']))
        {
            echo NS_Unsubscribe($email,$group);
        }
    }
    else
    {
        $action=FN_RewriteLink("index.php?mod={$_FN['mod']}");
        $groups=NS_GetNsGroups();
        echo "<p>".FN_Translate("to get our newsletter please enter your email and click on ”subscribe”. If you want to cancel the newsletter please enter your email and click on ”remove”")."</p>";
        $txtmail=FN_Translate("email");
        if (count($groups) == 1)
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

/**
 * 
 * @param type $email
 * @param type $group
 */
function NS_Subscribe($email,$group)
{
    $inUsers=false;
    //------------------------------fn_users----------------------------------->	
    $t=FN_XmlTable("fn_users");
    $recordsToUpdate=$t->GetRecords(array("email"=>$email));
    if (is_array($recordsToUpdate))
    {
        if (isset($recordsToUpdate[0]['newsletter']))
        {
            $inUsers=true;
        }
        foreach($recordsToUpdate as $recordToUpdate)
        {
            $s=explode(",",$recordToUpdate['newsletter']);
            $s[]=$group;
            $s=array_unique($s);
            $recordToUpdate['newsletter']=implode(",",$s);
            $r=$t->UpdateRecord($recordToUpdate);
        }
    }
    //------------------------------fn_users-----------------------------------<
    //------------------newsletter_subscriptions------------------------------->
    $t=FN_XmlTable("newsletter_subscriptions");
    $recordToUpdate=$t->GetRecord(array("email"=>$email));
    if ($inUsers == true && is_array($recordToUpdate))
    {
        $r=$t->DelRecord($recordToUpdate['id']);
    }
    else
    {
        if (is_array($recordToUpdate))
        {
            $s=explode(",",$recordToUpdate['newsletter']);
            $s[]=$group;
            $s=array_unique($s);
            $recordToUpdate['newsletter']=implode(",",$s);
            $r=$t->UpdateRecord($recordToUpdate);
        }
        else
        {
            $recordToInsert=array("newsletter"=>$group,"email"=>$email);
            $r=$t->InsertRecord($recordToInsert);
        }
        //------------------newsletter_subscriptions-------------------------------<
    }
    return "<p>".FN_Translate("you subscribed the newsletter")."</p>";
}

/**
 * 
 * @param type $email
 * @param type $group
 */
function NS_Unsubscribe($email,$group)
{
    $inUsers=false;

    //---------------------------fn_users-------------------------------------->
    $t=FN_XmlTable("fn_users");
    $recordsToUpdate=$t->GetRecords(array("email"=>$email));
    if (is_array($recordsToUpdate))
    {
        $inUsers=true;
        foreach($recordsToUpdate as $recordToUpdate)
        {
            $s_tmp=explode(",",$recordToUpdate['newsletter']);
            $s=array();
            foreach($s_tmp as $it)
            {
                if ($it != $group && $it !== "")
                {
                    $s[]=$group;
                }
            }
            $s=array_unique($s);
            $recordToUpdate['newsletter']=implode(",",$s);
            $r=$t->UpdateRecord($recordToUpdate);
        }
    }
    //---------------------------fn_users--------------------------------------<
    //------------------newsletter_subscriptions------------------------------->
    $t=FN_XmlTable("newsletter_subscriptions");
    $recordsToUpdate=$t->GetRecords(array("email"=>$email));
    if (is_array($recordsToUpdate))
    {
        foreach($recordsToUpdate as $recordToUpdate)
        {
            $s_tmp=explode(",",$recordToUpdate['newsletter']);
            $s=array();
            foreach($s_tmp as $it)
            {
                if ($it != $group && $it !== "")
                {
                    $s[]=$group;
                }
            }
            $s=array_unique($s);
            $recordToUpdate['newsletter']=implode(",",$s);
            if ($recordToUpdate['newsletter'] == "")
            {
                $r=$t->DelRecord($recordToUpdate['id']);
            }
            else
            {
                $r=$t->UpdateRecord($recordToUpdate);
            }
        }
    }
    //------------------newsletter_subscriptions-------------------------------<
    //FN_Alert(FN_Translate("you unsubscribed the newsletter"));
    return "<p>".FN_Translate("you unsubscribed the newsletter")."</p>";
}

/**
 * 
 * @global type $_FN
 */
function NS_Admin()
{
    global $_FN;
    $opt=FN_GetParam("opt",$_GET,"html");
    $forcenewvalues=array("date"=>date("Y/m/d"));
    echo "<h2>".FN_Translate("messages").":</h2>";

    $imgedit="<img style=\"border:0px;vertical-align:middle\" src=\"".FN_FromTheme("images/modify.png")."\" alt=\"\" />";
    $imgedel="<img style=\"border:0px;vertical-align:middle\" src=\"".FN_FromTheme("images/delete.png")."\" alt=\"\" />";
    $imgeview="<img style=\"border:0px;vertical-align:middle\" src=\"".FN_FromTheme("images/news.png")."\" alt=\"\" />";
    $imgnew="<img style=\"border:0px;vertical-align:middle\" src=\"".FN_FromTheme("images/add.png")."\" alt=\"\" />&nbsp;";
    $imgback="<img style=\"border:0px;vertical-align:middle\" src=\"".FN_FromTheme("images/left.png")."\" alt=\"\" />&nbsp;";
    $link="mod={$_FN['mod']}&opt=$opt";
    $params['link']=$link;
    $params['fields']="date|subject|body|status|NS_SendNewsletter()";
    $params['textnew']=$imgnew.FN_Translate("write a message");
    $params['textdelete']="<img title=\"".FN_Translate("delete")."\" style=\"border:0px;vertical-align:middle\" src=\"".FN_FromTheme("images/delete.png")."\" alt=\"\" />";
    $params['textview']=FN_Translate("preview");
    $params['textmodify']=$imgedit." ".FN_Translate("modify");
    $params['forcevaluesinsert']=array("date"=>FN_Now());
    $params['enableview']=true;
    $params['textnorecord']=FN_Translate("no saved messages");
    $params['textviewlist']="<img style=\"border:0px;vertical-align:middle\" src=\"".FN_FromTheme("images/left.png")."\" alt=\"\" />&nbsp;".FN_Translate("view all messages");
    $params['defaultorder']="date";
    $params['defaultorderdesc']=true;
    $params['enableview']=true;
    FN_XmltableEditor("newsletter",$params);
    echo "<h2>".FN_Translate("groups newsletter").":</h2>";
    FN_XmltableEditor("newsletter_newsletters");
    echo "<br /><br /><button onclick=\"window.location='?mod={$_FN['mod']}&opt=$opt'\">";
    echo FN_Translate("cancel");
    echo "</button>";

    echo "<br /><br /><a href=\"?mod={$_FN['mod']}&opt=$opt&action=getall\">".FN_Translate("view all users emails")."</a>";
}

/**
 * 
 * @global type $_FN
 * @param type $id
 * @return string
 */
function NS_SendNewsletter($id)
{

    global $_FN;
    $opt=FN_GetParam("opt",$_GET,"html");

    $Table=xmldb_frm("fndatabase","newsletter",$_FN['datadir'],$_FN['lang'],$_FN['languages']);
    //$item = $Table->xmltable->GetRecordByPrimarykey($id);
    $groups=NS_GetNsGroups();
    //if ( $item['status'] != "sended" )
    {
        $html="<form action=\""."?mod={$_FN['mod']}&amp;opt=$opt"."\" method=\"get\">
		<input name=\"mod\" value=\"{$_FN['mod']}\" type=\"hidden\" />
		<input name=\"opt\" value=\"$opt\" type=\"hidden\" />
		<input name=\"action\" value=\"send\" type=\"hidden\" />
		<input name=\"message\" value=\"$id\" type=\"hidden\" />";
        $html .= FN_Translate("sent to").":<select name=\"group\" >";
        $html .= "<option value=\"_ALLUSERS\">".FN_Translate("all site users")."</option>";
        foreach($groups as $group)
        {

            $html .= "<option value=\"{$group['id']}\">".$group['title']."</option>";
        }
        $html .="</select>
		<button type=\"submit\">".FN_Translate("send")."</button>
</form>";
        return $html;
    }
}

function NS_GetAll()
{
    global $_FN;
    $opt=FN_GetParam("opt",$_GET,"html");

    if (NS_IsAdmin())
    {
        echo "<h3>".FN_Translate("users").":</h3>";
        $all=NS_GetMailUsers("_ALLUSERS");
        $s="";
        if (is_array($all))
        {
            foreach($all as $rec)
            {
                echo "$s{$rec['email']}";
                $s=", ";
            }
        }
        $newsletter_groups=NS_GetNsGroups();
        if (is_array($newsletter_groups))
        {
            foreach($newsletter_groups as $newsletter_group)
            {
                echo "<h3>".FN_Translate("group")." {$newsletter_group['title']} :</h3>";
                $ns_users=NS_GetMailUsers($newsletter_group['id']);
                $s="";
                if (is_array($ns_users))
                    foreach($ns_users as $rec)
                    {
                        echo "$s{$rec['email']}";
                        $s=", ";
                    }
            }
        }
    }
    echo "<br /><br /><button onclick=\"window.location='?mod={$_FN['mod']}&opt=$opt'\">";
    echo "&lt;&lt;&nbsp;".FN_Translate("back");
    echo "</button>";
}

function NS_IsAdmin()
{
    global $_FN;
    $config=FN_LoadConfig("modules/newsletter/config.php","newsletter");
    if (FN_IsAdmin())
    {
        return true;
    }
    if (FN_UserInGroup($_FN['user'],$config['group_newsletters']))
    {
        return true;
    }
    return false;
}

?>