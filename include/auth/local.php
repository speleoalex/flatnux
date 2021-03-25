<?php

if (!defined("FN_AUTH_COST"))
{
    define("FN_AUTH_COST",10);
}
if (!defined("FN_AUTH_METHOD"))
{
    //PASSWORD_DEFAULT OR PASSWORD_BCRYPT
    if (defined('PASSWORD_DEFAULT'))
    {
        define("FN_AUTH_METHOD",PASSWORD_DEFAULT);
    }
}

function FN_LoginInitUrl()
{
    global $_FN;
    $loginmod="login";

    if (isset($_FN['mod']) && isset($_FN['sections']) && isset($_FN['sections'][$_FN['mod']]['type']) && $_FN['sections'][$_FN['mod']]['type']== "login")
    {
        $loginmod=$_FN['mod'];
    }
    elseif (empty($_FN['sections']['login']) || $_FN['sections']['login']['type']== "login")
    {
        if (isset($_FN['sections']))
        {
            foreach($_FN['sections'] as $k=> $v)
            {
                if ($v['type']== "login")
                {
                    $loginmod=$k;
                }
            }
        }
    }
    $_FN['urlregister']=FN_RewriteLink("index.php?mod=$loginmod&amp;op=register","&amp;",true);
    $_FN['urllogin']=FN_RewriteLink("index.php?mod=$loginmod","&amp;",true);
    $_FN['urllogout']=FN_RewriteLink("index.php?fnlogin=logout","&amp;",true);
    $_FN['urlpasswordrecovery']=FN_RewriteLink("index.php?mod=$loginmod&op=recovery","&amp;",true);
    $_FN['urlresendcode']=false;
    if ($_FN['registration_by_email'])
        $_FN['urlresendcode']=FN_RewriteLink("index.php?mod=$loginmod&op=send_code","&amp;",true);
    
}

/**
 * @package Flatnux_auth_local
 * @param array $arrayfilter
 * @return int
 */
function FN_CountUsers($arrayfilter=false)
{
    $Table=FN_GetUserForm();
    return $Table->xmltable->GetNumRecords($arrayfilter);
}

/**
 * 
 * @return type
 */
function FN_GetUserForm()
{
    FN_LoginInitUrl();
    $form=FN_XmlForm("fn_users");
    $form->formvals['passwd']['frm_type']="cryptpasswd";
    $op=FN_GetParam("op",$_GET,"html");
    $pk___xdb_fn_users=FN_GetParam("pk___xdb_fn_users",$_GET,"html");
    if ($op== "editreg" || $pk___xdb_fn_users!= "")
        $form->formvals['passwd']['frm_required']="false";
    $form->LoadFieldsClasses();
    $form->fieldname_active="active";
    $form->fieldname_user="username";
    $form->fieldname_password="passwd";
    return $form;
}

/**
 *
 * @param array $arrayfilter
 * @return array
 */
function FN_GetUsers($arrayfilter=false)
{
    $Table=FN_GetUserForm();
    return $Table->xmltable->GetRecords($arrayfilter);
}

/**
 *
 */
function FN_ManageLogin()
{
    global $_FN;
    FN_LoginInitUrl();
    $fnlogin=FN_GetParam("fnlogin",$_GET);
    $fnuser=FN_GetParam("username",$_POST);
    $fnpwd=FN_GetParam("password",$_POST);
    $captcha_ok=true;
    //-------------------captcha----------------------------------------------->
    if (!empty($_FN['enable_captcha']))
    {
        $captcha=FN_GetSessionValue("captcha");
        $fnlogincode=FN_GetParam("fnlogin_code",$_POST);
        if ($fnlogincode== "" || empty($captcha['fnlogin_code']) || $captcha['fnlogin_code']!= $fnlogincode)
        {
            $captcha_ok=false;
            FN_SetSessionValue("captcha",array("fnlogin_code"=>rand(1000,9999)));
        }
    }
    //-------------------captcha-----------------------------------------------<
    $rememberlogin=FN_GetParam("rememberlogin",$_POST);
    if ($fnlogin== "login" && $fnuser!= "" && $fnpwd!= "")
    {
        if (empty($_FN['username_case_sensitive']))
            $fnuser=strtolower($fnuser);
        if ($captcha_ok && FN_VerifyUserPassword($fnuser,$fnpwd))
        {
            FN_Login($fnuser,$rememberlogin);
        }
        else
        {
            FN_Logout();
            $_FN['login_error']="error username or password";
        }
    }
    $_FN['user']=FN_GetParam("fnuser",$_COOKIE);
    if (!FN_CheckUser() || $fnlogin== "logout")
    {
        FN_Logout();
    }
}

/**
 * 
 */
function FN_LoginForm($templateForm=false)
{
    echo FN_HtmlLoginForm($templateForm);
}

/**
 *
 * @global array $_FN
 */
function FN_HtmlLoginForm($templateForm=false)
{
    global $_FN;
    FN_LoginInitUrl();

    if ($templateForm)
    {
        $tplbasepath="themes/{$_FN['theme']}";
    }
    if (!$templateForm)
    {
        if (file_exists("themes/{$_FN['theme']}/template.{$_FN['mod']}.tp.html"))
        {
            $templateForm=FN_TPL_GetHtmlPart("include FN_HtmlLoginForm",file_get_contents("themes/{$_FN['theme']}/template.{$_FN['mod']}.tp.html"));
            $tplbasepath="themes/{$_FN['theme']}";
        }
    }
    if (file_exists("themes/{$_FN['theme']}/template.type.{$_FN['sectionvalues']['type']}.tp.html"))
    {
        $templateForm=FN_TPL_GetHtmlPart("include FN_HtmlLoginForm",file_get_contents("themes/{$_FN['theme']}/template.type.{$_FN['sectionvalues']['type']}.tp.html"));
        $tplbasepath="themes/{$_FN['theme']}";
    }
    if (!$templateForm)
    {
        if (file_exists("themes/{$_FN['theme']}/template.tp.html"))
        {
            $templateForm=FN_TPL_GetHtmlPart("include FN_HtmlLoginForm",file_get_contents("themes/{$_FN['theme']}/template.tp.html"));
            $tplbasepath="themes/{$_FN['theme']}";
        }
    }
    if (!$templateForm)
    {
        $templateForm=file_get_contents(FN_FromTheme("modules/login/login.tp.html",false));
        $tplbasepath=dirname(FN_FromTheme("modules/login/login.tp.html",false));
    }
    $querystring=FN_GetParam("QUERY_STRING",$_SERVER);
    $querystring=str_replace("&","&amp;",$querystring);
    $querystring=str_replace("fnlogin=login&amp;","",$querystring);
    $querystring=str_replace("fnlogin=logout&amp;","",$querystring);
    $querystring=str_replace("?fnlogin=login","?",$querystring);
    $querystring=str_replace("?fnlogin=logout","?",$querystring);
    $querystring=str_replace("&amp;fnlogin=login","",$querystring);
    $querystring=str_replace("&amp;fnlogin=logout","",$querystring);
    $querystring.="&amp;fnlogin=login";
    $scriptname = (basename($_SERVER['PHP_SELF']));
    if ($scriptname=="index.php")
    {
        $querystring=FN_RewriteLink("index.php?".$querystring,"&amp;",true);
    }
    else{
        $querystring="?".$querystring;
        
    }
    $tplvars=array();
    $tplvars['login_error']="";
    $tplvars['formaction']=$querystring;
    $tplvars['txtusername']=(!empty($_FN['username_is_email'])) ? FN_Translate("email") : FN_Translate("username");
    //------------------------------------ captcha ---------------------------->
    $captcha_ok=true;
    $htmlcaptcha="";
    if (!empty($_FN['enable_captcha']))
    {
        $captcha=FN_GetSessionValue("captcha");
        $fnlogincode=FN_GetParam("fnlogin_code",$_POST);
        if ($fnlogincode== "" || empty($captcha['fnlogin_code']) || $captcha['fnlogin_code']!= $fnlogincode)
            $captcha_ok=false;
        FN_SetSessionValue("captcha",array("fnlogin_code"=>rand(1000,9999)));
        $htmlcaptcha.="<img style=\"vertical-align:middle\" src=\"{$_FN['siteurl']}captcha.php?t=fnlogin_code&amp;".time()."\" alt=\"\" title=\"\" /> <input size=\"4\" name=\"fnlogin_code\"  value = \"\" />";
        $tplvars['htmlcaptcha']=$htmlcaptcha;
    }
    else
    {
        $templateForm=FN_TPL_ReplaceHtmlPart("captcha","",$templateForm);
    }
    //------------------------------------ captcha ----------------------------<
    if (empty($_FN['remember_login']))
    {
        $templateForm=FN_TPL_ReplaceHtmlPart("rememberlogin","",$templateForm);
    }
    if (!empty($_FN['login_error']))
    {
        $tplvars['login_error']=FN_Translate("authentication failure");
    }
    else
    {
        $templateForm=FN_TPL_ReplaceHtmlPart("loginerror","",$templateForm);
    }
    if (empty($_FN['enable_registration']))
    {
        $templateForm=FN_TPL_ReplaceHtmlPart("register","",$templateForm);
    }

    $html=FN_TPL_ApplyTplString($templateForm,$tplvars,$tplbasepath);


    return $html;
}

/**
 *
 * @global array $_FN
 */
function FN_HtmlLogoutForm($templateForm=false)
{
    global $_FN;
    if ($templateForm)
    {
        $tplbasepath="themes/{$_FN['theme']}";
    }
    elseif (file_exists("themes/{$_FN['theme']}/template.tp.html"))
    {
        $templateForm=FN_TPL_GetHtmlPart("include FN_HtmlLogoutForm",file_get_contents("themes/{$_FN['theme']}/template.tp.html"));
        $tplbasepath="themes/{$_FN['theme']}";
    }
    if (!$templateForm)
    {
        $templateForm=file_get_contents(FN_FromTheme("modules/login/logout.tp.html",false));
        $tplbasepath=dirname(FN_FromTheme("modules/login/logout.tp.html",false));
    }
    $tplvars=array();
    $querystring=FN_GetParam("QUERY_STRING",$_SERVER);
    $querystring=str_replace("&","&amp;",$querystring);
    $tplvars['formaction']="?$querystring&amp;fnlogin=logout";
    $html=FN_TPL_ApplyTplString($templateForm,$tplvars,$tplbasepath);
    return $html;
}

/**
 *
 * @global array $_FN
 */
function FN_LogoutForm($templateForm=false)
{
    echo FN_HtmlLogoutForm($templateForm);
}

/**
 *
 * @global  $_FN
 * @param string $fnuser
 * @param string $fnpwd
 * @return bool
 */
function FN_VerifyUserPassword($fnuser,$fnpwd)
{
    global $_FN;
    if (!empty($_FN['FN_VerifyUserPassword']) && $_FN['FN_VerifyUserPassword']!= "FN_VerifyUserPassword" && function_exists($_FN['FN_VerifyUserPassword']))
    {
        return $_FN['FN_VerifyUserPassword']($fnuser,$fnpwd);
    }
    $lpass=md5($fnpwd);
    $us=FN_GetUser($fnuser);
    $passwd=$us['passwd'];
    if ($passwd== $lpass)
        return true;
    if ($passwd== $fnpwd)
        return true;

    if (function_exists("password_hash") && function_exists("password_verify"))
    {
        if (password_verify($fnpwd,$passwd))
        {
            return true;
        }
    }
    return false;
}

/**
 *
 * @global  $_FN
 * @staticvar array $usercache
 * @param string $user
 * @param bool $usecache
 * @return array
 */
function FN_GetUser($user,$usecache=true)
{
    if ($user== "")
        return false;
    static $usercache=false;
    global $_FN;
    if ($usecache)
    {
        if ($usercache && is_array($usercache) && isset($usercache[$user]))
            return $usercache[$user];
    }
    $table=FN_GetUserForm();
    $UserValues=$table->xmltable->GetRecordByPrimaryKey($user);
    if ($UserValues['level']=== "")
        $UserValues['level']="0";
    if (empty($UserValues['username']))
        return false;
    $_FN['uservalues']=$usercache[$user]=$UserValues;
    return $usercache[$user];
}

/**
 *
 * @global  $_FN
 * @staticvar array $usercache
 * @param string $user
 * @param bool $usecache
 * @return int
 */
function FN_GetUserLevel($user,$usecache=true)
{
    $uservalues=FN_GetUser($user,$usecache);
    if (!isset($uservalues['level']))
        return -1;
    return $uservalues['level'];
}

/**
 *
 * @param string $user
 * @return string
 */
function FN_GetPassword($user)
{
    $uservalues=FN_GetUser($user,false);
    if (!isset($uservalues['passwd']))
        return null;
    $pass=$uservalues['passwd'];
    return $pass;
}

/**
 * 
 * @global type $_FN
 * @global type $_FN
 * @param type $fnuser
 * @param type $rememberlogin
 * @return boolean
 */
function FN_Login($fnuser,$rememberlogin=false)
{
    global $_FN;
    $password=Fn_GetPassword($fnuser);
    $us=FN_GetUser($fnuser,false);
    if ($us['active']== 1)
    {
//---------------------url cookie---------------------------------------------->
        global $_FN;
        if (empty($_FN['urlcookie']))
        {
            $urlcookie=FN_GetParam("PHP_SELF",$_SERVER);
            $path=pathinfo($urlcookie);
            $urlcookie=$path["dirname"]."/";
            $urlcookie=str_replace("\\","/",$urlcookie);
            if ($urlcookie== "" || $urlcookie== "\\" || $urlcookie== "//")
                $urlcookie="/";
            $_FN['urlcookie']=$urlcookie;
        }
//---------------------url cookie----------------------------------------------<
        if (empty($_FN['remember_login']) || $rememberlogin== "")
        {
            setcookie("fnuser",$fnuser,0,$_FN['urlcookie']);
            setcookie("secid",md5($fnuser.$password),0,$_FN['urlcookie']);
        }
        else
        {
            setcookie("fnuser",$fnuser,time() + 99999999,$_FN['urlcookie']);
            setcookie("secid",md5($fnuser.$password),time() + 99999999,$_FN['urlcookie']);
        }
        FN_Log("User $fnuser login (function FN_Login).");
        $_FN['user']=$_COOKIE['fnuser']=$fnuser;
        $_COOKIE['secid']=md5($fnuser.$password);

        return true;
    }
    else
    {
        return false;
    }
}

/**
 *
 * @global  $_FN
 * @param string $fnuser
 */
function FN_Logout()
{
//---------------------url cookie---------------------------------------------->
    global $_FN;
    if (empty($_FN['urlcookie']))
    {
        $urlcookie=FN_GetParam("PHP_SELF",$_SERVER);
        $path=pathinfo($urlcookie);
        $urlcookie=$path["dirname"]."/";
        $urlcookie=str_replace("\\","/",$urlcookie);
        if ($urlcookie== "" || $urlcookie== "\\" || $urlcookie== "//")
            $urlcookie="/";
        $_FN['urlcookie']=$urlcookie;
    }
//---------------------url cookie----------------------------------------------<

    setcookie("secid","",0,$_FN['urlcookie']);
    setcookie("fnuser","",0,$_FN['urlcookie']);
    setcookie("secid","",0,"/");
    setcookie("fnuser","",0,"/");
    $_FN['user']="";
    unset($_COOKIE['fnuser']);
    unset($_COOKIE['secid']);
}

/**
 * deleta user
 * @param string $user
 */
function FN_DeleteUser($user)
{
    global $_FN;
    if ($user!= "")
    {
        $table=FN_GetUserForm();
        $uservalues=FN_GetUser($user,false);
        $table->xmltable->DelRecord($user);
        if (function_exists("FN_OnDeleteUser"))
        {
            FN_OnDeleteUser($uservalues);
        }
        if (file_exists("{$_FN['datadir']}/fndatabase/fn_users/".$user))
        {
            remove_dir_rec("{$_FN['datadir']}/fndatabase/fn_users/".$user);
        }
    }
}

/**
 * @param string $user
 * @param array $newvalues
 */
function FN_UpdateUser($user,$newvalues,$password="")
{

    if ($user!= "")
    {
        if (!isset($newvalues['username']))
            $newvalues['username']=$user;
        if ($password!= "")
            $newvalues['passwd']=$password;
        if (isset($newvalues['passwd']) && $newvalues['passwd']== "")
        {
            unset($newvalues['passwd']);
        }
        $table=FN_GetUserForm();
        //dprint_r($newvalues);
        $newvalues=$table->UpdateRecord($newvalues);
        if ($newvalues && function_exists("FN_OnUpdateUser"))
        {
            FN_OnUpdateUser($newvalues,$password);
        }
        FN_GetUser($user,false);
        return $newvalues;
    }
    return false;
}

/**
 *
 * @param array $newvalues
 * @return bool
 */
function FN_AddUser($newvalues,$password="")
{
    if ($newvalues['username']!= "")
    {
        if (empty($_FN['username_case_sensitive']))
        {
            $newvalues['username']=strtolower($newvalues['username']);
        }
        $table=FN_GetUserForm();
        $newvalues['registrationdate']=FN_Now();
        if ($password!= "")
            $newvalues['passwd']=$password;
        if (isset($newvalues['passwd']) && $newvalues['passwd']== "")
        {
            unset($newvalues['passwd']);
        }
        if ($table->InsertRecord($newvalues))
        {
            if (function_exists("FN_OnAddUser"))
            {
                FN_OnAddUser($newvalues,$password);
            }
            return true;
        }
    }
    return false;
}

/**
 *
 * @global array $_FN
 * @return bool 
 */
function FN_CheckUser()
{
    global $_FN;
    $secid=FN_GetParam("secid",$_COOKIE);
    $UserValues=FN_GetUser($_FN['user']);
    if (empty($secid) || empty($UserValues['passwd']))
        return false;
    $RequiredSecid=md5($_FN['user'].$UserValues['passwd']);
    if ($RequiredSecid== $secid)
        return true;
    return false;
}

/**
 *
 * @global array $_FN
 * @param string $user
 * @return string 
 */
function FN_GetUserImage($user)
{
    global $_FN;
    $user=FN_GetUser($user);
    if (!empty($user['avatar']))
    {
        $image=urldecode(FN_GetParam("avatar",$user,"html"));
        $image="{$_FN['datadir']}/{$_FN['database']}/fn_users/{$user['username']}/avatar/$image";
    }
    elseif (!empty($user['avatarimage']))
    {
        $image=urldecode($user['avatarimage']);
        $image="{$_FN['datadir']}/{$_FN['database']}/fn_avatars/$image/filename/$image";
    }
    else
    {
        $image=FN_FromTheme("images/user.png",false);
    }
    return $_FN['siteurl'].$image;
}

/**
 *
 * @global array $_FN
 * @param string $user
 * @return string 
 */
function FN_HtmlUserImage($user)
{
    global $_FN;
    $imagesrc=FN_GetUserImage($user);
    $image="<img alt=\"\" src=\"$imagesrc\" />";
    return $image;
}

//---------password--------------------------------------->
class xmldbfrm_field_md5passwd
{

    function __construct()
    {
        
    }

    function show($params)
    {
        if ($params['is_update'])
            $params['value']="";
        $html="";
        $toltips=($params['frm_help']!= "") ? "title=\"".$params['frm_help']."\"" : "";
        $html.="<input  $toltips value=\"".str_replace('"','\\"',$params['value'])."\" autocomplete=\"off\" name=\"".$params['name']."\" type=\"password\" />\n";
        return $html;
    }

    function view($params)
    {
        $html.="***";
        return "***";
    }

    /**
     *
     * @param string $str
     * @param array $params
     * @return string 
     */
    function formtovalue($str,$params)
    {
        if ($str== "")
            return "";
        $str=md5($str);
        return $str;
    }

    /**
     * 
     * @param type $str
     * @return string
     */
    function valuetoform($str)
    {
        return "";
    }

}

/**
 * 
 */
class xmldbfrm_field_cryptpasswd
{

    function __construct()
    {
        
    }

    function show($params)
    {
        $attributes=isset($params["htmlattributes"]) ? $params["htmlattributes"] : "";
        if ($params['is_update'])
            $params['value']="";
        $html="";
        $toltips=($params['frm_help']!= "") ? "title=\"".$params['frm_help']."\"" : "";
        $html.="<input $attributes  $toltips value=\"".str_replace('"','\\"',$params['value'])."\" autocomplete=\"off\" name=\"".$params['name']."\" type=\"password\" />\n";
        return $html;
    }

    function view($params)
    {
        $html.="***";
        return "***";
    }

    /**
     *
     * @param string $str
     * @param array $params
     * @return string 
     */
    function formtovalue($str,$params)
    {
        if ($str== "")
            return "";
        $options=array('cost'=>FN_AUTH_COST);
        if (function_exists("password_hash"))
        {
            $str=@password_hash($str,FN_AUTH_METHOD,$options);
        }
        else
        {
            $str=md5($str);
        }
        return $str;
    }

    /**
     * 
     * @param type $str
     * @return string
     */
    function valuetoform($str)
    {
        return "";
    }

}

/**
 * 
 * @param type $pasword
 */
function FN_PasswordVerifyConstraints($password)
{

    if (function_exists("FN_PasswordVerifyConstraints_overwrite"))
    {
           
        return FN_PasswordVerifyConstraints_overwrite($password);
    }
//    if (!preg_match('/^[0-9A-Za-z!@#$%]{3,12}$/',$password))
    if (false !== strstr($password," "))
    {
        return 'the password does not meet the requirements';
    }
    return "";
}

?>
