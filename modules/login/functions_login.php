<?php

/**
 * @package Flatnux_module_login
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */

/**
 *
 * @global array $_FN
 * @return  
 */
function FNREG_ManageRecovery()
{
    global $_FN;
    $_FN['result']=array();
    $postuser=FN_GetParam("username",$_REQUEST,"html");
    $getuser=FN_GetParam("user",$_REQUEST,"html");
    $rnd=FN_GetParam("rnd",$_GET,"html");
    $errors="";
    $tplvars=$_FN;
    $tplvars['txtusername']=FN_Translate("username");

    // step 1 generate rnd and send -------->
    if ($postuser!= "")
    {
        if (!FN_GetUser($postuser))
        {
            $errors=$postuser." : ".fn_i18n("user does not exist");
        }
        else
        {
            $uservalues=FN_GetUser($postuser);
            $newrnd=md5(rand(1,99999999));
            FN_UpdateUser($postuser,array("rnd"=>$newrnd));
            $server=FN_RewriteLink("index.php?mod={$_FN['mod']}&op=recovery&user=$postuser&rnd=$newrnd&lang={$_FN['lang']}","&",true);
            //$body=FN_Translate("password recovery")."\n"."\n".fn_i18n("to retrieve the password, please follow this link").":\n$server\n\n".FN_Translate("a new password will be generated and sent to your email address");
            $subject=FN_i18n("password recovery");
            $tpl_filemail=FN_FromTheme("modules/login/mailrecovery_step1.tp.html",false);
            if (file_exists($tpl_filemail))
            {
                $uservalues['url']=$server;
                $body=FN_TPL_ApplyTplFile($tpl_filemail,$uservalues);
                $tmpsubject=get_xml_single_element("title",$body);
                if ($tmpsubject)
                {
                    $subject=$tmpsubject;
                }
            }
            //send mail
            $email=trim(ltrim($uservalues['email']));
            if ($email== "" && FN_CheckMail($uservalues[$username_field]))
            {
                $email=$uservalues[$username_field];
            }
            if (FN_CheckMail($email))
            {
                FN_SendMail($email,$_FN['sitename']." - ".$subject,$body,true);
                FN_Log("User $postuser recover password step 1.");
                $tplvars['txtresults']=FN_Translate("it has been sended one email to you to the address")." ".$email." ".FN_Translate("with the instructions to complete the registration","aa");
            }
            else
            {
                $tplvars['txtresults']=FN_Translate("error");
            }
            $tplfile=FN_FromTheme("modules/login/passwordrecovery_step1.tp.html",false);
            $tplbasepath=dirname($tplfile)."/";
            $templateForm=file_get_contents($tplfile);
            if ($_FN['username_is_email'])
            {
                $tplvars['txtusername']=FN_Translate("email");
            }
            $tplvars['urlnext']=FN_RewriteLink("index.php?mod=".$_FN['mod'],"&",true);
            $templateForm=FN_TPL_ApplyTplString($templateForm,$tplvars,$tplbasepath);
            echo $templateForm;
            $_FN['result']['txtresults']=$tplvars['txtresults'];
            return;
        }
    }
    // step 1 generate rnd and send ---------<
    // step 2 generate password and send ---->
    if ($getuser!= "" && $rnd!= "")
    {
        $txtresults="";
        $uservalues=FN_GetUser($getuser);
        $email=trim(ltrim($uservalues['email']));
        if ($email== "" && FN_CheckMail($uservalues[$username_field]))
        {
            $email=$uservalues[$username_field];
        }
        if (!$uservalues)
        {
            $errors=FN_i18n("user does not exist");
        }
        if ($uservalues['rnd']== "")
        {
            $errors=FN_i18n("The password has been sent to your email address, if you have not yet received it occurs in spam or repeat the procedure password recovery");
        }
        elseif ($uservalues['rnd']!= $rnd)
        {
            $errors=FN_i18n("authentication failure");
        }
        if ($errors== "")
        {
            $chrs=array('0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
            $newpass="";
            for($i=0; $i < 10; $i++)
            {
                $ch=rand(0,35);
                $newpass.=$chrs[$ch];
            }
            $subject=$_FN['sitename']." - ".FN_i18n("password recovery");
            $body=fn_i18n("password recovery")."\n"."\n".fn_i18n("this is the new password")." : $newpass\n\n";
            $tpl_filemail=FN_FromTheme("modules/login/mailrecovery_step2.tp.html",false);
            if (file_exists($tpl_filemail))
            {
                $uservalues['password']=$newpass;
                $body=FN_TPL_ApplyTplFile($tpl_filemail,$uservalues);
                $tmpsubject=get_xml_single_element("title",$body);
                if ($tmpsubject)
                {
                    $subject=$tmpsubject;
                }
            }
            FN_UpdateUser($getuser,array("rnd"=>""),$newpass);
            //send mail
            FN_SendMail($email,$subject,$body,false);
            FN_Log("User $getuser recover password. step 2, password sended");
            $txtresults=FN_Translate("a new password will be sent to your e-mail address.");
        }
        else
        {
            $txtresults=$errors;
        }
        $tplfile=FN_FromTheme("modules/login/passwordrecovery_step2.tp.html",false);
        $tplbasepath=dirname($tplfile)."/";
        $templateForm=file_get_contents($tplfile);
        $tplvars=$_FN;
        $tplvars['txtusername']=FN_Translate("username");
        $tplvars['txtresults']=$txtresults;
        if ($_FN['username_is_email'])
        {
            $tplvars['txtusername']=FN_Translate("email");
        }
        $tplvars['urlnext']=FN_RewriteLink("index.php?mod=".$_FN['mod'],"&",true);
        $templateForm=FN_TPL_ApplyTplString($templateForm,$tplvars,$tplbasepath);
        echo $templateForm;
        $_FN['result']['txtresults']=$tplvars['txtresults'];

        return;
    }
    // step 2 generate password and send ----<
    //request password recovery ---->
    $tplfile=FN_FromTheme("modules/login/passwordrecovery.tp.html",false);
    $tplbasepath=dirname($tplfile)."/";
    $templateForm=file_get_contents($tplfile);
    $tplvars=$_FN;
    $tplvars['txtusername']=FN_Translate("username");
    if ($_FN['username_is_email'])
    {
        $tplvars['txtusername']=FN_Translate("email");
    }
    $tplvars['formaction']=FN_RewriteLink("index.php?mod={$_FN['mod']}&amp;op=recovery","&amp;",true);
    $tplvars['urlcancel']=FN_RewriteLink("index.php?mod=".$_FN['mod'],"&",true);
    $tplvars['login_error']=$errors;
    $templateForm=FN_TPL_ApplyTplString($templateForm,$tplvars,$tplbasepath);
    if (empty($tplvars['login_error']))
    {
        $templateForm=FN_TPL_ReplaceHtmlPart("loginerror","",$templateForm);
    }
    echo $templateForm;
    if (!empty($tplvars['txtresults']))
        $_FN['result']['txtresults']=$tplvars['txtresults'];
    if (!empty($errors))
        $_FN['result']['error']=$errors;

    //request password recovery ----<
}

/**
 *
 * @global array $_FN
 * @param string $user
 */
function FNREG_ManageEditRegister($user="")
{
    global $_FN;
    $errors=array();
    $form=FN_GetUserForm();
    $username_field=empty($form->fieldname_user) ? "username" : $form->fieldname_user;
    $active_field=empty($form->fieldname_active) ? "username" : $form->fieldname_active;

    $form->SetLayout("table");
    if ($user== "")
        $user=$_FN['user'];
    $newvalues=FN_GetUser($user);
    $uservalues=$newvalues;
    $oldvalues=$newvalues;
    $reg_ok=false;
    $postvar=$_POST;
    if ($oldvalues)
    {
        if (isset($postvar['updateuser']))
        {
            foreach($form->formvals as $key=> $value)
            {
                if (isset($value['type']) && ($value['type']== 'image' || $value['type']== 'file'))
                {
                    if (isset($_FILES[$key]['name']))
                    {
                        if ($_FILES[$key]['name']!= "")
                        {
                            $newvalues[$key]=FN_GetParam("name",$_FILES[$key]);
                        }
                    }
                    if (isset($postvar["__isnull__$key"]))
                    {
                        $newvalues[$key]="";
                    }
                }
                else
                {
                    if (isset($postvar[$key]))
                        $newvalues[$key]=FN_GetParam($key,$postvar,"html");
                }
            }
            //dprint_r($_POST);
        }

        $newvalues[$form->xmltable->primarykey]=$oldvalues[$form->xmltable->primarykey];
        if (!FN_IsAdmin())
        {
            $newvalues['email']=$oldvalues['email'];
            $newvalues[$active_field]=$oldvalues[$active_field];
            $newvalues['level']=$oldvalues['level'];
            $newvalues['group']=$oldvalues['group'];
        }
        else
        {
            if (isset($_POST[$active_field]))
                $newvalues[$active_field]=FN_GetParam($active_field,$_POST,"html");
            if (isset($_POST['level']))
                $newvalues['level']=FN_GetParam("level",$_POST,"html");
            $groups=FN_GetGroups();
            $insgroup=array();
            foreach($groups as $g)
            {
                if (isset($_POST['group-'.$g]))
                {
                    $insgroup[]=$g;
                }
            }
            $newvalues['group']=implode(",",$insgroup);
        }



        if (isset($_POST['updateuser']))
        {
            $errors=$form->Verify($newvalues,true);
            if (count($errors)== 0)
            {
                $reg_ok=true;
            }
            else
            {
                $_FN['result']['errors']=$errors;
            }
        }

        if (!$reg_ok)
        {
            echo "";
            $us="";
            if (FN_IsAdmin() && $user!= $_FN['user'])
                $us="&user=$user";
            echo "";
            $templateForm='<form enctype="multipart/form-data" action="{formaction}" method="post" name="register" >
    <h2>{i18n:Edit profile}:</h2>
    <table style="margin:auto">
        <!-- contents -->
        <!-- group -->
        <tr><td colspan="2" style="text-align:center"><b>{groupname}</b></td></tr>
        <!-- end_group -->
        <!-- item -->
        <tr>
            <td valign="top">{title}<!-- error --><span style="color:red"><br />{error}</span><!-- end_error --></td>
            <td valign="top">{input}</td>
        </tr>
        <!-- end_item -->
        <!-- endgroup -->
        <!-- end_endgroup -->
        <!-- end_contents -->
        <tr>
            <td>
                <input name="updateuser" value="1" type="hidden" />
                <button type="submit">{i18n:Save}</button>
                <button onclick="window.location = \'{urlcancel}\'">{i18n:Cancel}</button>
            </td>
        </tr>
    </table>
</form>
';
            $tplfile=FN_FromTheme("modules/login/editreg.tp.html",false);
            $tplbasepath=dirname($tplfile)."/";
            if (file_exists($tplfile))
            {
                $templateForm=file_get_contents($tplfile);
            }
            $tplvars=$_FN;
            $tplvars['formaction']=FN_RewriteLink("index.php?mod=".$_FN['mod']."&amp;op=editreg$us");
            $tplvars['urlcancel']=FN_RewriteLink("index.php?mod=".$_FN['mod']);
            $templateForm=FN_TPL_ApplyTplString($templateForm,$tplvars,$tplbasepath);
            $templateForm=str_replace("{json}",json_encode(array("errors"=>$errors,"fields"=>$form->formvals),JSON_FORCE_OBJECT),$templateForm);
            $form->SetlayoutTemplate($templateForm);
            $form->ShowUpdateForm($newvalues[$form->xmltable->primarykey],FN_IsAdmin(),false,$errors);
        }
        else
        {


            if (false!= FN_UpdateUser($newvalues[$username_field],$newvalues))
            {
                //FN_Log("User updated:{$newvalues['username']}");
                echo FN_Translate("the data were successfully updated");
                FN_Login($newvalues[$username_field]);
            }
            else
            {
                echo FN_Translate("error");
                echo "<br /><br /><a href=\"javascript:history.back()\">&lt;&lt; ".FN_Translate("back")."</a>";
            }
            echo "<br /><br /><a href=\"".FN_RewriteLink("index.php?mod={$_FN['mod']}")."\">".FN_Translate("next")." &gt;&gt;</a>";
        }
    }
}

/**
 *
 * @global array $_FN
 * @return bool
 */
function FNREG_ManageRegister($actionform="")
{
    global $_FN;
    $config=FN_LoadConfig("modules/login/config.php");
    $form=FN_GetUserForm();
    $username_field=empty($form->fieldname_user) ? "username" : $form->fieldname_user;
    $active_field=empty($form->fieldname_active) ? "username" : $form->fieldname_active;
    $tplvalues = array("message"=>"");
    $tplfile=FN_FromTheme("modules/login/manageregister.tp.html",false);
    //dprint_r($tplfile);
    $op=FN_GetParam("op",$_GET);
    $conditions=FN_XMLQuery("SELECT * FROM fn_conditions WHERE enabled LIKE '1' ORDER BY position");
    if (!is_array($conditions))
    {
        $conditions=array();
    }
    //-----------------end_reg------------------------------------------------->
    if ($op== "end_reg")
    {
        $user=trim(ltrim(FN_GetParam("user",$_GET,"html")));
        $id=trim(ltrim(FN_GetParam("id",$_GET,"html")));
        $uservalues=FN_GetUser($user);
        $message="";
        if ($user!= "" && $id!= "" && isset($uservalues['rnd']) && $uservalues['rnd']== $id)
        {
            //complete registration-------------------------------------------->
            FN_UpdateUser($user,array("$active_field"=>1)); //TODO: creare Activateuser
            if (function_exists("FN_SendMailWelcome"))
            {
                //if (!$uservalues[$active_field])
                    FN_SendMailWelcome($uservalues);
            }
            else
            {
                $ishtml=true;
                $tpl_filemail=FN_FromTheme("modules/login/mailwelcome.{$_FN['lang']}.tp.html",false);
                if (!file_exists($tpl_filemail))
                    $tpl_filemail=FN_FromTheme("modules/login/mailwelcome.tp.html",false);
                $subject=FN_Translate("confirm registration site")." ".$_FN['sitename'];
                if (file_exists("{$_FN['datadir']}/messages/WelcomeMessage.{$_FN['lang']}.txt")) //obsolete
                {
                    $tpl_filemail="{$_FN['datadir']}/messages/WelcomeMessage.{$_FN['lang']}.txt";
                    $ishtml=false;
                }
                if (file_exists("{$_FN['datadir']}/messages/mailwelcome.{$_FN['lang']}.tp.html"))
                {
                    $tpl_filemail="{$_FN['datadir']}/messages/mailwelcome.{$_FN['lang']}.tp.html";
                    $ishtml=true;
                }
                $tplvalues=$uservalues;
                $tplvalues['username']=$uservalues[$username_field];
                $tplvalues['sitename']=$_FN['sitename'];
                $tplvalues['siteurl']=$_FN['siteurl'];
                $mailbody=FN_TPL_ApplyTplFile($tpl_filemail,$tplvalues);
                $mailbody=str_replace("!USERNAME!",$uservalues[$username_field],$mailbody);
                $mailbody=str_replace("!SITENAME!",$_FN['sitename'],$mailbody);
                $mailbody=str_replace("!SITEURL!",$_FN['siteurl'],$mailbody);
                $mailbody=FN_FixNewline($mailbody);
                $tmpsubject=get_xml_single_element("title",$mailbody);
                if ($tmpsubject)
                {
                    $subject=$tmpsubject;
                }
                if (!$uservalues[$active_field])
                {
                    FN_SendMail($uservalues['email'],$subject,$mailbody,$ishtml);
                }
            }
            if (function_exists("FN_OnConfirmUser"))
            {
                if (!$uservalues[$active_field])
                    FN_OnConfirmUser($uservalues);
            }
            $tplvalues['message'].= FN_Translate("registration has been completed");
            echo FN_TPL_ApplyTplFile($tplfile,$tplvalues);
            return true;
            //complete registration--------------------------------------------<
        }
        else
        {
            $tplvalues['message'].= FN_Translate("error registration");
        }
        echo FN_TPL_ApplyTplFile($tplfile,$tplvalues);
        return false;
    }
    //-----------------end_reg-------------------------------------------------<
    //-----------------registration (post values)------------------------------>
    
    $newvalues=$form->getbypost();
    foreach($form->formvals as $key=> $value)
    {
        if (isset($value['type']) && ($value['type']== 'image' || $value['type']== 'file') && isset($_FILES[$key]['name']))
        {
            if ($_FILES[$key]['name']!= "")
            {
                $newvalues[$key]=FN_GetParam("name",$_FILES[$key]);
            }
        }
        else
        {
            $newvalues[$key]=FN_GetParam($key,$_POST,"html");
        }
    }
    if (!empty($_FN['registration_by_email']))
    {
        $newvalues[$active_field]=0;
    }
    else
    {
        $newvalues[$active_field]=1;
    }
    $newvalues['level']=0;

    if (!empty($_FN['username_is_email']) && !empty($form->formvals['username']))
    {
        $form->formvals['username']['frm_show']=0;
        $form->formvals['username']['frm_required']=0;
        $form->formvals['email']['frm_validator']="FN_CheckMail";
        $form->formvals['email']['unique']=1;
        $form->formvals['username']['frm_validchars'].="01234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-@.";
        if (!empty($newvalues['email']))
        {
            $newvalues['username']=$newvalues['email'];
        }
    }
    $rnd=$newvalues['rnd']=md5(rand(1000000000,9999999999)).md5(rand(1000000000,9999999999));
    $newvalues['ip']=FN_GetParam("REMOTE_ADDR",$_SERVER,"html");
    $insgroup=array(0=>"users");
    if (FN_IsAdmin())
    {
        $newvalues[$active_field]=FN_GetParam("active",$_POST,"html");
        $newvalues['level']=FN_GetParam("level",$_POST,"html");
        $groups=FN_GetGroups();
        foreach($groups as $g)
        {
            if (isset($_POST['group-'.$g]))
            {
                $insgroup[]=$g;
            }
        }
    }
    else
    {
        $newvalues[$active_field]=( $_FN['registration_by_email']== 1 ) ? 0 : 1;
    }
    $newvalues['group']=implode(",",$insgroup);
    $errors=array();
    if (isset($_POST['email']) || isset($_POST[$username_field]))
    {
        $errors=$form->Verify($newvalues);
        $conditions_ok=true;
        foreach($conditions as $condition)
        {
            if (empty($condition['optional']) && empty($_POST['conditions_'.$condition['id']]))
            {
                $errors['conditions']=array("title"=>FN_Translate("conditions of registration users"),"field"=>"captcha","error"=>FN_Translate("to register is required to accept"));
                $conditions_ok=false;
            }
        }
        //---check captcha----------------------------------------------------->
        $captcha_ok=true;
        if (!empty($config['enable_captcha']))
        {
            $captcha=FN_GetSessionValue("captcha");
            $security_code=FN_GetParam("security_code",$_POST);
            if (empty($captcha['security_code']) || $captcha['security_code']!= $security_code)
            {
                $captcha_ok=false;
                $errors['security_code']=array("title"=>FN_Translate("captcha"),"field"=>"captcha","error"=>FN_Translate("incorrect security code"));
            }
        }
        //---check captcha-----------------------------------------------------<
        if (count($errors)== 0 && $conditions_ok== true && $captcha_ok== true)
        {
            $email=$newvalues['email'];
            $name=$newvalues[$username_field];
            if (!empty($_FN['registration_by_email']) && $newvalues[$active_field]!= 1)
            {
                $subject=FN_Translate("confirm registration site")." ".$_FN['sitename'];
                $link=FN_RewriteLink("index.php?mod=".$_FN['mod']."&op=end_reg&user=".urlencode($newvalues[$username_field])."&id=$rnd","&",true);
                $tpl_filemail=FN_FromTheme("modules/login/mailconfirm.{$_FN['lang']}.tp.html",false);
                $ishtml=true;
                if (!file_exists($tpl_filemail))
                    $tpl_filemail=FN_FromTheme("modules/login/mailconfirm.tp.html",false);
                if (file_exists("{$_FN['datadir']}/messages/mailconfirm.{$_FN['lang']}.txt")) //obsolete
                {
                    $tpl_filemail="{$_FN['datadir']}/messages/mailconfirm.{$_FN['lang']}.txt";
                    $ishtml=false;
                }
                if (file_exists("{$_FN['datadir']}/messages/mailwelcome.{$_FN['lang']}.tp.html"))
                {
                    $tpl_filemail="{$_FN['datadir']}/messages/mailwelcome.{$_FN['lang']}.tp.html";
                    $ishtml=true;
                }

                $tplvalues=$newvalues;
                $tplvalues['message']="";
                $tplvalues['username']=$newvalues[$username_field];
                $tplvalues['sitename']=$_FN['sitename'];
                $tplvalues['siteurl']=$_FN['siteurl'];
                $tplvalues['url']=$link;
                $mailbody=FN_TPL_ApplyTplFile($tpl_filemail,$tplvalues);
                $mailbody=str_replace("!CONFIRMREGISTRATIONADDRESS!",$link,$mailbody);
                $mailbody=str_replace("!USERNAME!",$name,$mailbody);
                $mailbody=str_replace("!SITENAME!",$_FN['sitename'],$mailbody);
                $mailbody=str_replace("!SITEURL!",$_FN['sitename'],$mailbody);
                if (!strstr($mailbody,$link))
                    $mailbody.="\n$link";
                $mailbody=FN_FixNewline($mailbody);
                $tmpsubject=get_xml_single_element("title",$mailbody);
                if ($tmpsubject)
                {
                    $subject=$tmpsubject;
                }
                if (FN_AddUser($newvalues))
                {
                    if (!FN_SendMail($email,$subject,$mailbody,$ishtml))
                    {
                        $tplvalues['message'].= FN_Translate("the system failed to send the confirmation email");
                    }
                    else
                    {
                        $tplvalues['message'].= "<br /><br />".FN_Translate("it has been sended one email to you to the address")." ".htmlentities($newvalues['email'])." ".FN_i18n("with the instructions to complete the registration")."<br />";
                        $tplvalues['message'].= FN_Translate("check your inbox");
                        $tplvalues['message'].= "<br /><br /><div ><a href=\"".FN_RewriteLink("index.php")."\" >".FN_Translate("next")." &gt;&gt;&gt;</a></div>";
                    }
                    echo FN_TPL_ApplyTplFile($tplfile,$tplvalues);
                    return false;
                }
                else
                {
                    $tplvalues['message'].= FN_Translate("error");
                }
            }
            else
            {
                $tplvalues['message'].= "<br />".FN_Translate("user already confirmed")."<br />";
                FN_AddUser($newvalues);
                FN_Login($newvalues[$username_field]);
                echo FN_TPL_ApplyTplFile($tplfile,$tplvalues);
                return true;
            }
        }
    }
    //-----------------registration (post values)------------------------------<
    
//--------------------registration form---------------------------------------->
    if ($actionform== "")
    {
        $actionform=FN_RewriteLink("index.php?mod=".$_FN['mod']."&amp;op=register");
    }

    $templateForm='<form enctype="multipart/form-data" action="{formaction}" method="post" name="register" >
    <h2>{i18n:Edit profile}:</h2>
    <table style="margin:auto">
        <!-- contents -->
        <!-- group -->
        <tr><td colspan="2" style="text-align:center"><b>{groupname}</b></td></tr>
        <!-- end_group -->
        <!-- item -->
        <tr>
            <td valign="top">{title}<!-- error --><span style="color:red"><br />{error}</span><!-- end_error --></td>
            <td valign="top">{input}</td>
        </tr>
        <!-- end_item -->
        <!-- endgroup -->
        <!-- end_endgroup -->
        <!-- end_contents -->
        <!-- captcha -->
        
        <!-- endcaptcha -->
        <!-- conditions -->
        
        <!-- endconditions -->
 

         <tr>
            <td>
                <input name="updateuser" value="1" type="hidden" />
                <button type="submit">{i18n:Save}</button>
                <button onclick="window.location = \'{urlcancel}\'">{i18n:Cancel}</button>
            </td>
        </tr>
    </table>
</form>
';
    $tplfile=FN_FromTheme("modules/login/register.tp.html",false);
    $tplbasepath=dirname(FN_FromTheme("modules/login/register.tp.html",false))."/";
    if (file_exists($tplfile))
    {
        $templateForm=file_get_contents($tplfile);
    }

    $tplvars=$_FN;
    $tplvars['formaction']=$actionform;
    $tplvars['urlcancel']=FN_RewriteLink("index.php?mod=".$_FN['mod']);
    $out=array();
    $tp_captcha=preg_match('/<!-- captcha -->(.*)<!-- endcaptcha -->/is',$templateForm,$out);
    $tp_captcha=!isset($out[0]) ? "" : $out[0];
    $tp_conditions=preg_match('/<!-- conditions -->(.*)<!-- endconditions -->/is',$templateForm,$out);
    $tp_conditions=!isset($out[0]) ? "" : $out[0];



    $htmlcaptcha="";
    //----------------captcha--------------->
    if (!empty($config['enable_captcha']))
    {
        $tplvars['txt_error_security_code']="";
        FN_SetSessionValue("captcha",array("security_code"=>rand(1000,9999)));
        $htmlcaptcha.="<img style=\"vertical-align:middle\" src=\"{$_FN['siteurl']}captcha.php\" alt=\"\" title=\"\" /> <input size=\"4\" name=\"security_code\"  value = \"\" />";
        if (isset($_POST['security_code']) && $security_code!= $captcha['security_code'])
            $tplvars['txt_error_security_code']=FN_Translate("incorrect security code");
        $tplvars['htmlcaptcha']=$htmlcaptcha;
    }
    else
    {
        $templateForm=str_replace($tp_captcha,"",$templateForm);
    }
    //----------------captcha---------------<
    //----------------conditions------------>
    $htmlconditions="";
    if (is_array($conditions) && count($conditions) > 0)
    {
        $tcond=FN_XmlForm("fn_conditions");
        foreach($conditions as $condition)
        {
            $htmlconditions.="<div>";
            $condition=$tcond->GetRecordTranslatedByPrimarykey($condition['id']);
            if (!empty($condition['title']))
                $htmlconditions.="<b>".$condition['title']."</b><br />";
            $htmlconditions.="<div style=\"height:100px;overflow:auto;border:1px inset\" >";
            $htmlconditions.=$condition['text'];
            $htmlconditions.="</div>";
            $ck="";
            if (!empty($_POST['conditions_'.$condition['id']]))
            {
                $ck="checked=\"checked\"";
            }
            $htmlconditions.="<input name=\"conditions_{$condition['id']}\" type=\"checkbox\" $ck/>".FN_Translate("accept");
            if (!empty($_POST[$username_field]) && empty($condition['optional']) && empty($_POST['conditions_'.$condition['id']]))
                $htmlconditions.=" <span style=\"background-color:#ffffff;color:red\">".FN_Translate("to register is required to accept")."</span>";
            $htmlconditions.="</div>";
        }
        $tplvars['htmlconditions']=$htmlconditions;
    }
    else
    {
        $templateForm=str_replace($tp_conditions,"",$templateForm);
    }

    //----------------conditions------------<
    $templateForm=FN_TPL_ApplyTplString($templateForm,$tplvars,$tplbasepath);
    $templateForm=str_replace("{json}",json_encode(array("errors"=>$errors,"fields"=>$form->formvals),JSON_FORCE_OBJECT),$templateForm);
    $form->SetlayoutTemplate($templateForm);
    $form->ShowInsertForm(FN_IsAdmin(),$newvalues,$errors);
    //--------------------registration form----------------------------------------<


    return false;
}

/**
 *
 * @global array $_FN
 * @return string
 */
function FNREG_GetConfirmationMessage()
{
    global $_FN;
    if (!file_exists("{$_FN['datadir']}/messages/ConfirmationMessage.{$_FN['lang']}.txt"))
    {
        return FN_i18n("to complete the registration click on the following link").":\n!CONFIRMREGISTRATIONADDRESS!";
    }
    else
    {
        return file_get_contents("{$_FN['datadir']}/messages/ConfirmationMessage.{$_FN['lang']}.txt");
    }
}

/**
 *
 * @global array $_FN
 * @return string
 */
function FNREG_GetWelcomeMessage()
{
    global $_FN;
    if (!file_exists("{$_FN['datadir']}/messages/WelcomeMessage.{$_FN['lang']}.txt"))
    {
        return FN_i18n("welcome !USERNAME!, you are now registered to").": !SITENAME!\n\n!SITEURL!";
    }
    else
    {
        return file_get_contents("{$_FN['datadir']}/messages/WelcomeMessage.{$_FN['lang']}.txt");
    }
}

//---------ux_conditions--------------------------------------->
/**
 *
 * @author alessandro
 *
 */
class xmldbform_field_conditions_check
{

    function __construct()
    {
        
    }

    function show($params)
    {
        global $_FN;
        $this->starttagtitle=isset($params['frm_starttagtitle']) ? $params['frm_starttagtitle'] : "<tr><td  valign=\"top\" >";
        $this->endtagtitle=isset($params['frm_endtagtitle']) ? $params['frm_endtagtitle'] : "</td>";
        $this->starttagvalue=isset($params['frm_starttagvalue']) ? $params['frm_starttagvalue'] : "<td valign=\"top\" >";
        $this->endtagvalue=isset($params['frm_endtagvalue']) ? $params['frm_endtagvalue'] : "</td></tr>";
        $lang_user=$_FN['lang'];
        if (!file_exists($_FN['datadir']."/conditions"))
        {
            mkdir($_FN['datadir']."/conditions");
        }
        $file_conditions=$_FN['datadir']."/conditions/conditions.$lang_user.html";
        if (!file_exists($file_conditions))
        {
            if (file_exists("modules/login/conditions/conditions.$lang_user.html"))
            {
                FN_Copy("modules/login/conditions/conditions.$lang_user.html",$file_conditions);
            }
        }
        $toltips="";
        $strhiddenfield=$params['strhiddenfield'];
        $oldval=$params['value'];
        echo $this->starttagtitle.$params['title'].$this->endtagtitle;
        echo $this->starttagvalue;
        $ch="";
        if ($oldval!= "")
            $ch="checked=\"checked\"";
        if ($oldval!= "yes")
        {
            $ch="";
        }
        echo "\n$strhiddenfield ";
        echo "<input type=\"hidden\" value=\"".htmlspecialchars($oldval)."\" name=\"__check__".$params['name']."\"  />";
        echo "<div style=\"border:1px inset;overflow:auto;height:100px;width:100%;\" >";
        echo file_get_contents($file_conditions);
        echo "</div>";
        echo "<input name=\"".$params['name']."\" type=\"hidden\" value=\"no\" />";
        echo "<br />".FN_Translate("I accept")."&nbsp;<input style=\"vertical-align:middle\" $toltips $ch type=\"checkbox\" value=\"yes\" name=\"".$params['name']."\"  />";
        echo $this->endtagvalue;
    }

}

/**
 * contitions field validator
 * @param $value
 * @return misc true or error message
 */
function fn_accept_condition_validator($value)
{
    if ($value!= "yes")
    {
        return FN_Translate("you must accept the terms of use");
    }
    else
        return true;
}

//---------ux_conditions---------------------------------------<
?>