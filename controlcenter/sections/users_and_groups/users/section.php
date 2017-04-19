<?php

/**
 * @package Flatnux_controlcenter
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2003-2005
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 *
 */
defined('_FNEXEC') or die('Restricted access');
global $_FN;
/**
 * 
 * @param type $user
 * @param type $Table
 * @return type
 */
function EnableDisable($user,$Table)
{
    global $_FN;
    $values=$Table->xmltable->GetRecordByPrimarykey($user);
    $opt=FN_GetParam("opt",$_GET);
    $filter=FN_GetParam("filter",$_GET);
    $gopt="";
    if($opt!="")
    {
        $gopt="&amp;opt=$opt";
    }
    $page=FN_GetParam("page___xdb_{$Table->tablename}",$_GET,"int");
    if($page!="")
    {
        $page="&amp;page___xdb_{$Table->tablename}=$page";
    }
    $htmlsendmail="<input type=\"checkbox\" name=\"sendwelcomemessage\" value=\"1\"/>".FN_Translate("send welcome message");
    if($values['active']!=1)
        return "<form action=\"".("?mod={$_FN['mod']}&amp;filter=$filter$page$gopt")."\" method=\"post\"><img src=\"images/useronline/level_n.gif\" alt=\"\"/><input name=\"active\" value=\"1\" type=\"hidden\"/><input name=\"userid\" value=\"$user\" type=\"hidden\"/><button type=\"submit\">".FN_Translate("enable")."</button>$htmlsendmail</form>";
    else
        return "<form action=\"".("?mod={$_FN['mod']}&amp;filter=$filter$page$gopt")."\" method=\"post\"><img src=\"images/useronline/level_y.gif\" alt=\"\"/><input name=\"active\" value=\"0\" type=\"hidden\"/><input name=\"userid\" value=\"$user\" type=\"hidden\"/><button type=\"submit\">".FN_Translate("disable")."</button></form>";
}

$userid=FN_GetParam("userid",$_POST);
if($userid)
{
    $active=FN_GetParam("active",$_POST);
    $sendwelcomemessage=FN_GetParam("sendwelcomemessage",$_POST);

    if($_FN['user']==$userid)
        FN_Alert("operation is not permitted");
    else
    {
        require_once 'modules/login/functions_login.php';
        FN_UpdateUser($userid,array("active"=>$active));
        if($active&&$sendwelcomemessage)
        {
            $uservalues=FN_GetUser($userid);
            $mailbody=FNREG_GetWelcomeMessage();
            $mailbody=str_replace("!USERNAME!",$uservalues['username'],$mailbody);
            $mailbody=str_replace("!SITENAME!",$_FN['sitename'],$mailbody);
            $mailbody=str_replace("!SITEURL!",$_FN['siteurl'],$mailbody);
            $mailbody=FN_FixNewline($mailbody);
            $ishtml=false;
            if(FN_SendMail($uservalues['email'],FN_Translate("confirm registration site")." ".$_FN['sitename'],$mailbody,$ishtml))
                FN_Alert(FN_Translate("it has been sended one email to you to the address")." ".$uservalues['email']);
        }
    }
}


$table=FN_GetUserForm();
$table->formvals['username']['frm_allowupdate']="onlyadmin";

if(isset($table->formvals['passwd']))
{
    if (isset($_POST['passwd']) && $_POST['passwd']=="")
    {
        unset($_POST['passwd']);
    }
    $table->formvals['passwd']['frm_required']=false;
    $table->formvals['level']['frm_show']="1";
    $table->formvals['active']['frm_show']="1";
    $table->formvals['group']['frm_show']="1";
}
$table->LoadFieldsClasses();


$params=array();
$params['fields']="username|email|level|group|EnableDisable()";
if(isset($table->formvals['registrationdate']))
{
    $table->formvals['registrationdate']['frm_show']="1";
    $params['fields']="username|email|level|group|registrationdate|EnableDisable()";
}

$getfilters=FN_GetParam("filter",$_GET,"flat");
$postfilter=array();
$filters=explode("^",$getfilters);
$arrayfilter=array();
foreach($filters as $filter)
{
    if(!empty($filter))
    {
        $tmp=explode(":",$filter);
        $arrayfilter[$tmp[0]]=isset($tmp[1])?$tmp[1]:false;
    }
}
global $_FN;
$op=FN_GetParam("opt",$_GET,"html");


//-----form----->
$fv=array();
$exlude=array("passwd","emailhidden","avatarimage","avatar","rnd","level","groups","active","ip","registrationdate");
if(empty($_GET['op___xdb_fn_users']))
{
    echo "<form action=\"?mod={$_FN['mod']}&amp;opt=$op\" method=\"post\" >";
    echo "<fieldset><legend>".FN_Translate("filter")."</legend>";
    echo "<table>";
    foreach($table->formvals as $k=> $v)
    {
        if(!in_array($k,$exlude))
        {
            if(isset($_POST[$k]))
            {
                $fv[$k]=FN_GetParam($k,$_POST,"html");
            }
            elseif(isset($arrayfilter[$k]))
            {
                $fv[$k]=$arrayfilter[$k];
            }
            if(isset($fv[$k]))
            {
                $arrayfilter[$k]=$fv[$k];
                if($arrayfilter[$k]!=false&&$arrayfilter[$k]!=="")
                {
                    $postfilter[$k]="$k:{$arrayfilter[$k]}";
                    $arrayfilter[$k]="%".$arrayfilter[$k]."%";
                }
                else
                {

                    unset($arrayfilter[$k]);
                }
            }

            echo "\n<tr>";
            echo "<td>{$table->formvals[$k]['title']}</td>";
            $fv[$k]=isset($fv[$k])?$fv[$k]:"";
            echo "<td><input name=\"$k\" value=\"{$fv[$k]}\"/></td>";
            echo "</tr>";
        }
    }
    echo "<tr><td colspan=\"2\"><button type=\"submit\">".FN_Translate("search")."</button></td></tr>";
    echo "</table></fieldset></form>";
//-----form----->
}
if(count($postfilter)>0)
{
    $getfilters=implode("^",$postfilter);
}

$params['filters']="";
if(count($arrayfilter))
{
    $params['filters']=$arrayfilter;
}
$link="mod={$_FN['mod']}&amp;opt=$op&amp;filter={$getfilters}";
$params['link']="$link";
$params['list_onsave']=false;
$params['textviewlist']="<img style=\"vertical-align:middle;border:0px;\" alt=\"\"  src=\"".FN_FromTheme("images/users.png")."\" />&nbsp;".FN_Translate("user list");
$params['textnew']="<img style=\"vertical-align:middle;border:0px;\" alt=\"\"  src=\"".FN_FromTheme("images/add.png")."\" />&nbsp;".FN_Translate("add a user");
$params['textmodify']="<img alt=\"".FN_Translate("modify")."\" title=\"".FN_Translate("modify")."\" style=\"vertical-align:middle;border:0px;\" alt=\"\"  src=\"".FN_FromTheme("images/modify.png")."\" />";
$params['textdelete']="<img alt=\"".FN_Translate("delete")."\" title=\"".FN_Translate("delete")."\" style=\"vertical-align:middle;border:0px;\" alt=\"\"  src=\"".FN_FromTheme("images/delete.png")."\" />";

echo "<a ".($getfilters==""?" style=\"font-weight:bold\" ":"")."href=\"?mod={$_FN['mod']}&amp;opt=$op&amp;filter=\">".FN_Translate("view all")."</a>&nbsp;&nbsp;|&nbsp;&nbsp;";
echo "<a ".($getfilters=="active:1"?" style=\"font-weight:bold\" ":"")."href=\"?mod={$_FN['mod']}&amp;opt=$op&amp;filter=active:1\"><img src=\"images/useronline/level_y.gif\" alt=\"\"/> ".FN_Translate("view only enabled")."</a>&nbsp;&nbsp;|&nbsp;&nbsp;";
echo "<a ".($getfilters=="active:0"?" style=\"font-weight:bold\" ":"")."href=\"?mod={$_FN['mod']}&amp;opt=$op&amp;filter=active:0\"><img src=\"images/useronline/level_n.gif\" alt=\"\"/> ".FN_Translate("view only disabled")."</a>&nbsp;&nbsp;|&nbsp;&nbsp;";
echo "<a ".($getfilters=="level:10"?" style=\"font-weight:bold\" ":"")."href=\"?mod={$_FN['mod']}&amp;opt=$op&amp;filter=level:10\">".FN_Translate("view only administrators")."</a>&nbsp;&nbsp;|&nbsp;&nbsp;";
echo "<br />";
FN_xmltableeditor($table,$params);
?>