<?php

/**
 * @package Flatnux_functions
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
define("_FNEXEC",1);
defined('_FNEXEC') or die('Restricted access');
$_FN=array();
$_FN_display_errors="on";
$_FN_upload_max_filesize="20M";
$_FN_default_database_driver="xmlphp";
$_FN_default_auth_method="local";
require_once dirname(__FILE__)."/config.vars.php";
if (file_exists(dirname(__FILE__)."/config.vars.local.php"))
    require_once dirname(__FILE__)."/config.vars.local.php";
ini_set("display_errors",$_FN_display_errors);
ini_set("upload_max_filesize","$_FN_upload_max_filesize");
error_reporting(E_ALL);
global $xmldb_default_driver;
$_FN['default_database_driver']=$_FN_default_database_driver;
$xmldb_default_driver=$_FN['default_database_driver'];
$mtime=microtime();
$mtime=explode(" ",$mtime);
$mtime=doubleval($mtime[1])+doubleval($mtime[0]);
$_FN['timestart']=$mtime; // start time
$_FN['filesystempath']=".";
$_FN['consolemode']=false;
if (isset($_SERVER['SHELL']))
{
    $_FN['filesystempath']=realpath(dirname(__FILE__)."/..");
    $_FN['consolemode']=true;
    $_FN['script_path']=$_SERVER['PWD'];
    chdir($_FN['filesystempath']);
}
$_FN['charset_lang']="UTF-8";  //default
$_FN['database']="fndatabase";
//files extra cms --->
$files=glob($_FN['filesystempath']."/extra/*.inc.php");
if (is_array($files))
{
    foreach($files as $file)
    {
        require_once $file;
    }
}

//files extra cms ---<
//files in cms --->
$files=glob($_FN['filesystempath']."/include/*.inc.php");
foreach($files as $file)
{
    require_once $file;
}
//files in cms ---<
require_once $_FN['filesystempath']."/include/xmldb.php";
require_once $_FN['filesystempath']."/include/xmldb_frm.php";
require_once $_FN['filesystempath']."/include/xmldb_query.php";
require_once $_FN['filesystempath']."/include/xmldb_frm_search.php";


require_once $_FN['filesystempath']."/include/auth/$_FN_default_auth_method.php";
include $_FN['filesystempath']."/config.php";
if ($_FN['consolemode'])
{
    $_FN['datadir']=realpath($_FN['datadir']);
}

FN_LoadVarsFromTable($_FN,"fn_settings",array("timestart","consolemode","filesystempath","charset_lang","default_database_driver","section_header_footer","FN_SendMail"));

$_FN['use_urlserverpath']=false;

//----------------------------------timezone----------------------------------->
if (function_exists("date_default_timezone_get"))
{
    if ($_FN['timezone']=="")
    {
        $_FN['timezone']=date_default_timezone_get();
    }
    if (trim(ltrim($_FN['timezone']))=="")
    {
        $_FN['timezone']="UTC";
    }
    date_default_timezone_set($_FN['timezone']);
}
//----------------------------------timezone-----------------------------------<
//##############---dynamic--->
$_FN['enable_mod_rewrite_default']=$_FN['enable_mod_rewrite'];
require_once $_FN['filesystempath']."/include/modrewrite.php";

strstr(PHP_OS,"WIN") ? $_FN ['slash']="\\" : $_FN ['slash']="/";
$_FN['self']=FN_GetParam("PHP_SELF",$_SERVER);
$_FN['user']="";
$mod=basename(FN_GetParam("mod",$_GET));
if (!file_exists($_FN['filesystempath']."/sections/{$_FN['home_section']}"))
    $_FN['home_section']="";
if ($mod=="")
{
    $mod=$_FN['home_section'];
}
$_FN['block']="";
$_FN['mod']=$mod;
$php_self=FN_GetParam("PHP_SELF",$_SERVER);
$_FN ['self']=$php_self;

if ($_FN['siteurl']=="")
{
    if (!$_FN['consolemode']) //consolemode need explicit siteurl
    {
        $dirname=dirname($php_self);
        if (isset($_SERVER ['SCRIPT_FILENAME']))
        {
            $serverpath=dirname($_SERVER ['SCRIPT_FILENAME']);
        }
        elseif (isset($_SERVER ['PATH_TRANSLATED']))
        {
            $_SERVER ['SCRIPT_FILENAME']=$_SERVER ['PATH_TRANSLATED'];
            $serverpath=dirname($_SERVER ['PATH_TRANSLATED']);
        }
        while(!file_exists($serverpath."/include/flatnux.php"))
        {
            $dirname=dirname(preg_replace('/\/$/','',$dirname));
            $serverpath=dirname(preg_replace('/\/$/','',$serverpath));
        }
        if ($dirname=="/"||$dirname=="\\")
            $dirname="";
        // server windows
        $dirname=str_replace("\\","/",$dirname);
        $protocol="http://";
        if (isset($_SERVER ['HTTPS'])&&$_SERVER ['HTTPS']=="on")
            $protocol="https://";
        if (isset($_SERVER ['HTTP_HOST']))
        {
            $siteurl="$protocol".$_SERVER ['HTTP_HOST'].$dirname;
            if (substr($siteurl,strlen($siteurl)-1,1)!="/")
            {
                $siteurl=$siteurl."/";
            }
        }
        else
        {
            $siteurl="";
        }
    }
    else
    {
        $siteurl="";
    }
    $_FN['siteurl']=$siteurl;
}

if (empty($_FN['sitepath']))
{
    $_FN['sitepath']=FN_GetParam("PHP_SELF",$_SERVER);
    if ($_FN['sitepath']=="")
        $_FN['sitepath']="/";
    else
    {
        $_FN['sitepath']=dirname($_FN['sitepath'])."/";
        if ($_FN['sitepath']=="//")
            $_FN['sitepath']="/";
    }
}

$_FN['listlanguages']=explode(",",$_FN['languages']);
$_FN['lang']=$_FN['listlanguages'][0];
$_FN['lang_default']=$_FN['lang'];
global $FN_THEME;
if (!empty($FN_THEME))
{
    $_FN['theme']=$FN_THEME;
}
$_FN['theme_default']=$_FN['theme'];
if ($_FN['theme']==""||!file_exists("themes/{$_FN['theme']}"))
    $_FN['theme']="base";

$_FN['charset_page']=$_FN['charset_lang'];

if (!$_FN['consolemode'])
{
//---------------------url cookie---------------------------------------------->
    if (empty($_FN['urlcookie']))
    {
        $urlcookie=FN_GetParam("PHP_SELF",$_SERVER);
        $path=pathinfo($urlcookie);
        $urlcookie=$path["dirname"]."/";
        $urlcookie=str_replace("\\","/",$urlcookie);
        if ($urlcookie==""||$urlcookie=="\\"||$urlcookie=="//")
            $urlcookie="/";

        $_FN['urlcookie']=$urlcookie;
    }
//---------------------url cookie----------------------------------------------<
    FN_ManageLogin();
//---------------vars in cookie------------------------------------------------
    $_FN['fneditmode']=FN_SaveGetPostParam("fneditmode");

    $_FN['lang']=FN_SaveGetPostParam("lang");
    if (!in_array($_FN['lang'],$_FN['listlanguages']))
        $_FN['lang']=$_FN['lang_default'];


    $_FN['showaccesskey']=FN_SaveGetPostParam("showaccesskey");
    $usertheme=FN_SaveGetPostParam("theme");
    $_FN['section_header_footer']=isset($_FN['section_header_footer']) ? $_FN['section_header_footer'] : "";
    if ($usertheme!="")
    {
        if (file_exists($_FN['filesystempath']."/themes/$usertheme"))
            $_FN['theme']=$usertheme;
        else
            $_FN['theme']=$_FN['theme_default'];
    }
//--------------------preview theme-------------------------------------------->
    if (!empty($_FN['switchtheme'])||FN_IsAdmin())
    {
        $themepreview=FN_GetParam("themepreview",$_GET);
        if ($themepreview!=""&&file_exists($_FN['filesystempath']."/themes/{$_FN['theme']}"))
            $_FN['theme']=$themepreview;
    }
//--------------------preview theme--------------------------------------------<
}

//---init var sections,blocks,sectiontypes --->
if (empty($_FN['blocks']))
    $_FN['blocks']=FN_GetAllBlocks();
if (empty($_FN['sections']))
    $_FN['sections']=FN_GetAllSections();
if (empty($_FN['sectionstypes']))
    $_FN['sectionstypes']=FN_GetAllSectionTypes();
$_FN['sectionvalues']=FN_GetSectionValues($_FN['mod']);
if (!empty($_FN['sectionvalues']['keywords']))
    $_FN['keywords']="{$_FN['sectionvalues']['keywords']}";

//---init var sections,blocks,sectiontypes ---<


if (!file_exists($_FN['filesystempath']."/themes/{$_FN['theme']}"))
{
    $_FN['theme']=$_FN['theme_default'];
    if (!file_exists($_FN['filesystempath']."/themes/{$_FN['theme']}"))
        $_FN['theme']="base";
}
if (file_exists($_FN['filesystempath']."/themes/{$_FN['theme']}/theme.php"))
{
    include_once($_FN['filesystempath']."/themes/{$_FN['theme']}/theme.php");
}
include_once($_FN['filesystempath']."/include/theme.php");
//---------------vars in cookie------------------------------------------------<

FN_LoadMessagesFolder($_FN['filesystempath']."/");

if (!$_FN['consolemode']&&!empty($_FN['maintenance'])&&basename($_SERVER['SCRIPT_FILENAME'])!="controlcenter.php")
{
    if (!FN_IsAdmin())
    {
        die(FN_HtmlMainteanceMode());
    }
}
//--language from module
if (!empty($_FN['sectionvalues']['type']))
{
    FN_LoadMessagesFolder($_FN['filesystempath']."/modules/{$_FN['sectionvalues']['type']}");
}
//--language from section
FN_LoadMessagesFolder($_FN['filesystempath']."/sections/{$_FN['mod']}");


$_FN['days']=array(FN_i18n("sunday"),FN_i18n("monday"),FN_i18n("tuesday"),FN_i18n("wednesday"),FN_i18n("thursday"),FN_i18n("friday"),FN_i18n("saturday"));
$_FN['months']=array(FN_i18n("january"),FN_i18n("february"),FN_i18n("march"),FN_i18n("april"),FN_i18n("may"),FN_i18n("june"),FN_i18n("july"),FN_i18n("august"),FN_i18n("september"),FN_i18n("october"),FN_i18n("november"),FN_i18n("december"));
$_FN['site_title']=FN_i18n($_FN['site_title']);
$_FN['site_subtitle']=FN_i18n($_FN['site_subtitle']);

$_FN['site_title']=FN_i18n($_FN['site_title']);
$_FN['site_subtitle']=FN_i18n($_FN['site_subtitle']);
$_FN['formlogin']=FN_HtmlLoginForm();
//include language----<
if (!$_FN['consolemode']&&!file_exists("sections/".$_FN['mod']))
{
    header("location:".FN_RewriteLink("index.php"));
    die(FN_RewriteLink("index.php"));
}

//##############---dynamic---<
/**
 *
 * @param type $var
 * @param string $str 
 */
function dprint_r($var,$str="")
{
    global $_FN;
    if (empty($_FN['consolemode']))
        echo "<pre style=\"font-size:10px;line-height:12px;border:1px solid green\">";
    echo "$str\n";
    print_r($var);
    if (empty($_FN['consolemode']))
        echo "</pre>";
}

/**
 *
 * @param string $var
 * @param string $str 
 */
function dprint_xml($var,$str="")
{
    global $_FN;
    if (empty($_FN['consolemode']))
    {
        echo "<pre style=\"font-size:10px;line-height:12px;border:1px solid magenta\">";
        echo "$str\n";
        echo htmlspecialchars($var);
        echo "</pre>";
    }
    else
    {
        echo "\n---$str--->\n$var\n<---$str---\n";
    }
}

function dprint_r_get($var)
{
    return print_r($var,true);
}

/**
 * 
 * @global type $_FN
 * @staticvar boolean $oldTimer
 * @param type $str
 * 
 * use: FN_Debug_timer(__FILE__.":".__LINE__);
 */
function FN_Debug_timer($str)
{

    global $_FN;
    static $oldTimer=false;
    $mtime=explode(" ",microtime());
    $mtime=doubleval($mtime[1])+doubleval($mtime[0]);
    if ($oldTimer===false)
        $oldTimer=$mtime;
    $str.=" total ".sprintf("%.4f",abs($mtime-$_FN['timestart']));
    $str.=" -  last:".sprintf("%.4f",abs($mtime-$oldTimer));
    $oldTimer=$mtime;
    echo("<pre style=\"border:1px solid red\">$str</pre>");
}

?>