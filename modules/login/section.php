<?php

/**
 * @package Flatnux_module_login
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
defined('_FNEXEC') or die('Restricted access');
require_once("modules/login/functions_login.php");
global $_FN;
$op=FN_GetParam("op",$_GET);
$username=FN_GetParam("user",$_GET);
if(!FN_IsAdmin())
    $username=$_FN['user'];
switch($op)
{
    case "register":
    case "end_reg":
        if(!empty($_FN['enable_registration']))
        {
            FNREG_ManageRegister();
        }
        break;
    case "editreg":
        //if (!empty($_FN['enable_registration']) || FN_IsAdmin())
        {
            FNREG_ManageEditRegister($username);
        }
        break;
    case "recovery":
        FNREG_ManageRecovery();
        break;

    default:
        if(empty($op))
        {
            echo FN_HtmlContent("sections/{$_FN['mod']}");
        }
        if($_FN['user']=="")
        {
            FN_LoginForm();
        }
        else
        {
            $templateStr=file_get_contents(FN_FromTheme("modules/login/profile.tp.html",false));
            $tplbasepath=dirname(FN_FromTheme("modules/login/profile.tp.html",false))."/";
            $tplvars=$_FN;
            $tplvars=$_FN;
            $tplvars['urllogout']=FN_RewriteLink("index.php?mod={$_FN['mod']}&amp;fnlogin=logout");
            $tplvars['urleditprofile']=FN_RewriteLink("index.php?mod={$_FN['mod']}&amp;op=editreg");
            $tplvars['username']=$_FN['user'];
            $tplvars['urlimage']=FN_GetUserImage($_FN['user']);
            $tplvars['urllogout']=FN_RewriteLink("index.php?mod={$_FN['mod']}&amp;fnlogin=logout");
            echo FN_TPL_ApplyTplString($templateStr,$tplvars,$tplbasepath);
        }
        break;
}
?>