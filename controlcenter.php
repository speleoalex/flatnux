<?php

/**
 * @package Flatnux_controlcenter
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 1011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
ob_start();

$script_name=basename(__FILE__);
require_once ("include/flatnux.php");
$_FN['controlcenter']=$script_name;
if (empty($_FN['controlcenter_theme']) || !file_exists("controlcenter/themes/{$_FN['controlcenter_theme']}"))
    $_FN['controlcenter_theme']="classic";
if (file_exists("controlcenter/themes/{$_FN['controlcenter_theme']}/theme.php"))
    require_once("controlcenter/themes/{$_FN['controlcenter_theme']}/theme.php");
header("Content-Type: text/html; charset={$_FN['charset_page']}");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
$_FN['theme']="base";
$_FN['fneditmode']="0";
$opt=FN_GetParam("opt",$_GET,"html");
$op=FN_GetParam("op",$_GET,"html");
$modcont=FN_GetParam("modcont",$_GET,"flat");
if ($opt== "")
{
    $section_enabled=FN_XMLQuery("SELECT * FROM fn_cc_users WHERE username LIKE '{$_FN['user']}'");
    if (!empty($section_enabled[0]['default']))
    {
        $opt=$section_enabled[0]['default'];
        $_GET['opt']=$opt;
    }
}
//-------------------------init table cc_users--------------------------------->
$xml='<?xml version="1.0" encoding="UTF-8"?>
<?php exit(0);?>
<tables>
	<field>
		<name>username</name>
		<primarykey>1</primarykey>
	</field>
	<field>
		<name>ccsections</name>
		<frm_type>multiselect</frm_type>
		<frm_options>a,b</frm_options>
	</field>
	<field>
		<name>default</name>
		<frm_type>select</frm_type>
		<frm_options>a,b</frm_options>
	</field>
</tables>
';
if (!file_exists("{$_FN['datadir']}/{$_FN['database']}/fn_cc_users.php"))
{
    FN_Write($xml,"{$_FN['datadir']}/{$_FN['database']}/fn_cc_users.php");
}
//-------------------------init table cc_users---------------------------------<

$configsection=$sect="";
$plugin="";
$editconf="";
$fileconfig_to_edit="";
$_FN['mod']="";


$opt=FN_GetParam("opt",$_GET,"html");

$_FN['configsection']=$configsection;
$params=array();
$params['urlcontrolcenter']=$_FN['siteurl']."/".$_FN['controlcenter'];
$params['version']=htmlspecialchars(file_get_contents("VERSION"));
$params['section_title']="".FN_GetFolderTitle("controlcenter/sections/$opt/"); //todo
$items=FNCC_GetMenuItems();
$can_view=false;
foreach($items as $sections)
{
    foreach($sections['sections'] as $section)
    {
        if ($section['opt']== $opt)
        {
            $params['section_title']=$section['title'];
            $can_view=true;
        }
    }
}

if ($opt!= "" && $can_view== false && !FN_IsAdmin())
{
    //------------------------------------------------------login form----->
    $params=array();
    $params['formaction']=$_FN['controlcenter']."?fnlogin=login";
    $html=FN_TPL_include_tpl(FN_TPL_ApplyTplFile("controlcenter/themes/{$_FN['controlcenter_theme']}/controlcenter.login.tp.html",$params),$params);
    echo $html;
    die();
    //------------------------------------------------------login form-----<    
}

$params['languages']=FNCC_HtmlLanguages();
if (file_exists("controlcenter/sections/$opt/help/"))
{
    $params['htmlhelp']=FN_HtmlContent("controlcenter/sections/$opt/help/");
}

if (($modcont!= "" && !FN_CanModifyFile($_FN['user'],$modcont)) || !FN_IsAdmin() && FNCC_GetCCSections()== false)
{
    //------------------------------------------------------login form----->
    $params=array();
    $params['formaction']=$_FN['controlcenter']."?fnlogin=login";
    $html=FN_TPL_include_tpl(FN_TPL_ApplyTplFile("controlcenter/themes/{$_FN['controlcenter_theme']}/controlcenter.login.tp.html",$params),$params);
    echo $html;
    die();
    //------------------------------------------------------login form-----<
}
else
{
//-----------------------------MAIN PAGE--------------------------------------->
    $sectionvalues=FN_GetSectionValues($sect);
    if (!empty($opt) && UserCanAdmin($opt))
    {
        $html=FN_TPL_include_tpl(FN_TPL_ApplyTplFile("controlcenter/themes/{$_FN['controlcenter_theme']}/controlcenter.tp.html",$params),$params);
    }
    else
    {
        if (file_exists("controlcenter/themes/{$_FN['controlcenter_theme']}/controlcenter.dashboard.tp.html"))
        {
            $html=FN_TPL_include_tpl(FN_TPL_ApplyTplFile("controlcenter/themes/{$_FN['controlcenter_theme']}/controlcenter.dashboard.tp.html",$params),$params);
        }
        else
        {
            $str=file_get_contents("controlcenter/themes/{$_FN['controlcenter_theme']}/controlcenter.tp.html");
            $str=str_replace("include ccsection","include dashboard",$str);
            $html=FN_TPL_include_tpl(FN_TPL_ApplyTplString($str,$params,"controlcenter/themes/{$_FN['controlcenter_theme']}/"),$params);
        }
    }
    $html=str_replace("</head>","{$_FN['section_header_footer']}</head>",$html);
}
//-----------------------------MAIN PAGE---------------------------------------<
die($html);

function FN_TPL_tp_create_loginform()
{
    return FN_HtmlLoginForm();
}

/**
 * 
 * @global type $_FN
 * @return string
 */
function FN_TPL_tp_create_ccsection()
{
    global $_FN;
    $opt=FN_GetParam("opt",$_GET);
    $modcont=FN_GetParam("modcont",$_GET,"flat");
    //----edit file on filesystem----->
    if ($modcont!= "" && FN_CanModifyFile($_FN['user'],$modcont) && file_exists($modcont))
    {
        $linkcancel=FN_RewriteLink("index.php?mod={$_FN['mod']}");
        FN_EditContent($modcont,$linkcancel,$linkcancel);
        $html=ob_get_clean();
        return $html;
    }
    //----edit file on filesystem-----<
    //----edit config.php-----<
    $fileconfig_to_edit=false;
    if (FN_erg("^fnc_ccnf_config_section_",$opt))
    {
        $section_to_edit_config=FN_erg_replace("^fnc_ccnf_config_section_","",$opt);
        $sectionvalues=FN_GetSectionValues($section_to_edit_config);
        $configsection=$_FN['mod']=$sectionvalues['id'];
        $_FN['sectionvalues']=FN_GetSectionValues($_FN['mod']);
        if (file_exists("sections/{$sectionvalues['id']}/config.php"))
            $fileconfig_to_edit="sections/{$sectionvalues['id']}/config.php";
        else
            $fileconfig_to_edit="modules/{$sectionvalues['type']}/config.php";
    }
    if (FN_erg("^fnc_ccnf_config_block_",$opt))
    {
        $section_to_edit_config=FN_erg_replace("^fnc_ccnf_config_block_","",$opt);
        $sectionvalues=FN_GetBlockValues($section_to_edit_config);
        $configsection=$_FN['block']=$sectionvalues['id'];
        $_FN['sectionvalues']=FN_GetBlockValues($sectionvalues['id']);
        if (file_exists("blocks/{$sectionvalues['id']}/config.php"))
            $fileconfig_to_edit="blocks/{$sectionvalues['id']}/config.php";
        else
            $fileconfig_to_edit="modules/{$sectionvalues['type']}/config.php";
    }


    if (FN_erg("^fnc_ccnf_config_plugin_",$opt))
    {
        $plugin_to_edit_config=FN_erg_replace("^fnc_ccnf_config_plugin_","",$opt);
        $fileconfig_to_edit="plugins/$plugin_to_edit_config/config.php";
    }
    //editor
    if ($fileconfig_to_edit!= "" && file_exists($fileconfig_to_edit))
    {
        ob_start();
        echo FNCC_HtmlEditConfFile("$fileconfig_to_edit","?opt=$opt");
        return ob_get_clean();
    }
    //----edit config.php-----<
    //----settings.php-----<
    $filetoinclude=false;
    if (FN_erg("^fnc_ccnf_section_",$opt))
    {
        $sect=FN_erg_replace("^fnc_ccnf_section_","",$opt);
        $sectionvalues=FN_GetSectionValues($sect);
        $_FN['mod']=$sect;
        $_FN['sectionvalues']=$sectionvalues;
        if (file_exists("sections/{$sect}/controlcenter/settings.php"))
        {
            $filetoinclude="sections/$sect/controlcenter/settings.php";
        }
        elseif (!empty($sectionvalues['type']) && file_exists("modules/{$sectionvalues['type']}/controlcenter/settings.php"))
        {
            $filetoinclude="modules/{$sectionvalues['type']}/controlcenter/settings.php";
        }
    }
    if (FN_erg("^fnc_ccnf_block_",$opt))
    {
        $sect=FN_erg_replace("^fnc_ccnf_block_","",$opt);
        $_FN['block']=$sect;
        $sectionvalues=FN_GetBlockValues($sect);

        $_FN['sectionvalues']=$sectionvalues;
        if (file_exists("blocks/{$sect}/controlcenter/settings.php"))
        {
            $filetoinclude="modules/$sect/controlcenter/settings.php";
        }
        elseif (!empty($sectionvalues['type']) && file_exists("modules/{$sectionvalues['type']}/controlcenter/settings.php"))
        {
            $filetoinclude="modules/{$sectionvalues['type']}/controlcenter/settings.php";
        }
    }
    if (FN_erg("^fnc_ccnf_plugin_",$opt))
    {
        $filetoinclude=false;
        $sect=FN_erg_replace("^fnc_ccnf_plugin_","",$opt);
        $filetoinclude="plugins/{$sect}/controlcenter/settings.php";
    }
    if ($filetoinclude)
    {
        if (FN_erg("^fnc_ccnf_section_",$opt))
        {
            $t=str_replace("fnc_ccnf_section_","",$opt);
            $s=FN_GetSectionValues($t);
            $configsection=$sect=FN_erg_replace("^fnc_ccnf_section_","",$opt);
            $_FN['mod']=$sect;
        }
        if (FN_erg("^fnc_ccnf_block_",$opt))
        {
            $t=str_replace("fnc_ccnf_block_","",$opt);
            $s=FN_GetBlockValues($t);
            $configsection=$sect=FN_erg_replace("^fnc_ccnf_block_","",$opt);
        }
        if (FN_erg("^fnc_ccnf_plugin_",$opt))
        {
            $t=str_replace("fnc_ccnf_plugin_","",$opt);
            $s['title']=$t;
        }
        $title=$s['title'];
        $_FN['sectionvalues']=FN_GetSectionValues($_FN['mod']);
        include "$filetoinclude";
        return ob_get_clean();
    }

    $title=FN_GetFolderTitle("controlcenter/sections/$opt/");
    ob_start();
    if (!empty($opt) && file_exists("controlcenter/sections/$opt/section.php"))
    {
        include "controlcenter/sections/$opt/section.php";
    }
    $html=ob_get_clean();
    return $html;
}

/**
 *
 * @return array
 */
function FNCC_GetSectionsConfigs()
{
    global $_FN;
    $sections=FN_GetSections("",true,true,true);
    $blocks=$_FN['blocks'];
    $configs=array();
    foreach($sections as $section)
    {
        $section['opt']="fnc_ccnf_config_section_".$section['id'];
        $section['description']=" ";
        if (!empty($section['type']) && (file_exists("modules/{$section['type']}/config.php") /* || file_exists("modules/{$section['type']}/controlcenter/settings.php") */))
        {
            $section['cc_icon']="controlcenter/sections/settings/cms/icon.png";
            $section['title']=FN_Translate("page")." ".FN_GetFolderTitle("modules/{$section['type']}/").": ".$section['title']."";
            $section['description']=ucfirst(FN_Translate("settings")." ".FN_GetFolderTitle("modules/{$section['type']}/").": ".$section['title']."");

            if (file_exists("modules/{$section['type']}/cc_icon.png"))
                $section['cc_icon']=FN_FromTheme("modules/{$section['type']}/cc_icon.png");

            $configs[]=$section;
        }
        elseif (file_exists("sections/{$section['id']}/config.php") /* || file_exists("sections/{$section['id']}/controlcenter/settings.php") */)
        {
            $section['cc_icon']="controlcenter/sections/settings/cms/icon.png";
            $section['title']=FN_Translate("settings in").$section['title'];
            $configs[]=$section;
        }
    }
    foreach($blocks as $section)
    {
        $section['opt']="fnc_ccnf_config_block_".$section['id'];
        $section['description']=" ";
        if (!empty($section['type']) && (file_exists("modules/{$section['type']}/config.php") /* || file_exists("modules/{$section['type']}/controlcenter/settings.php") */))
        {
            $section['cc_icon']="controlcenter/sections/settings/cms/icon.png";
            $section['title']=FN_Translate("block")." ".(FN_GetFolderTitle("modules/{$section['type']}/")." in \"".$section['title']."\"");

            if (file_exists("modules/{$section['type']}/cc_icon.png"))
                $section['cc_icon']=FN_FromTheme("modules/{$section['type']}/cc_icon.png");

            $configs[]=$section;
        }
        elseif (file_exists("blocks/{$section['id']}/config.php") /* || file_exists("sections/{$section['id']}/controlcenter/settings.php") */)
        {
            $section['cc_icon']="controlcenter/sections/settings/cms/icon.png";
            $section['title']=FN_Translate("settings in").$section['title'];
            $configs[]=$section;
        }
    }


    return $configs;
}

/**
 *
 * @return array
 */
function FNCC_GetSectionsSettings()
{
    global $_FN;
    $sections=FN_GetSections(false,true,true,true);
    $blocks=$_FN['blocks'];
    $configs=array();
    foreach($sections as $section)
    {
        $section['opt']="fnc_ccnf_section_{$section['id']}";
        $ttype=ucfirst(FN_GetFolderTitle($_FN['filesystempath']."/modules/{$section['type']}"));
        if ($section['type']== "")
            $section['title']=$section['title'];
        else
            $section['title']=$ttype." in \"".$section['title']."\"";
        $section['description']=" ";
        if (!empty($section['type']) && file_exists("modules/{$section['type']}/controlcenter/settings.php"))
        {

            $section['cc_icon']=FN_FromTheme("controlcenter/images/configure.png");
            if (file_exists("modules/{$section['type']}/controlcenter/icon.png"))
                $section['cc_icon']=FN_FromTheme("modules/{$section['type']}/controlcenter/icon.png");
            $configs[]=$section;
        }
        elseif (file_exists("sections/{$section['id']}/controlcenter/settings.php"))
        {
            $section['cc_icon']=FN_FromTheme("controlcenter/images/configure.png");
            if (file_exists("sections/{$section['id']}/controlcenter/icon.png"))
                $section['cc_icon']=FN_FromTheme("sections/{$section['id']}/controlcenter/icon.png");
            $configs[]=$section;
        }
    }
    foreach($blocks as $section)
    {
        $section['opt']="fnc_ccnf_block_{$section['id']}";
        $section['description']=" ";

        if (!empty($section['type']) && file_exists("modules/{$section['type']}/controlcenter/settings.php"))
        {

            $section['cc_icon']=FN_FromTheme("controlcenter/images/configure.png");
            if (file_exists("modules/{$section['type']}/controlcenter/icon.png"))
                $section['cc_icon']=FN_FromTheme("modules/{$section['type']}/controlcenter/icon.png");
            $ttype=ucfirst(FN_GetFolderTitle($_FN['filesystempath']."/modules/{$section['type']}"));
            $section['title']=$ttype." in \"".$section['title']."\"";
            $configs[]=$section;
        }
        elseif (file_exists("sections/{$section['id']}/controlcenter/settings.php"))
        {
            $section['cc_icon']=FN_FromTheme("controlcenter/images/configure.png");
            $section['title']=FN_Translate("block")." ".$section['title'];
            if (file_exists("sections/{$section['id']}/controlcenter/icon.png"))
                $section['cc_icon']=FN_FromTheme("blocks/{$section['id']}/controlcenter/icon.png");
            $configs[]=$section;
        }
    }
    return $configs;
}

/**
 *
 * @return array
 */
function FNCC_GetPluginsConfigs()
{
    $sections=glob("plugins/*");

    $configs=array();
    foreach($sections as $section)
    {
        if (file_exists("$section/config.php"))
        {
            if (file_exists("$section/icon.png"))
                $_section['cc_icon']="$section/icon.png";
            else
                $_section['cc_icon']="controlcenter/sections/settings/cms/icon.png";
            $_section['title']=FN_GetFolderTitle($section);
            $_section['id']=basename($section);
            $configs[]=$_section;
        }
    }
    return $configs;
}

/**
 *
 * @return array
 */
function FNCC_GetPluginsSettings()
{
    $sections=glob("plugins/*");

    $configs=array();
    foreach($sections as $section)
    {
        if (file_exists("$section/controlcenter/settings.php"))
        {
            if (file_exists("$section/controlcenter/icon.png"))
                $_section['cc_icon']="$section/controlcenter/icon.png";
            else
                $_section['cc_icon']=FN_FromTheme("controlcenter/images/configure.png");
            $_section['title']=FN_GetFolderTitle($section);
            $_section['id']=basename($section);
            $configs[]=$_section;
        }
    }
    return $configs;
}

/**
 *
 * @global array $_FN
 * @return string
 */
function FNCC_HtmlLanguages()
{
    global $_FN;
    $opt=FN_GetParam("opt",$_GET,"flat");
    $html="";
    foreach($_FN['listlanguages'] as $l)
    {
        $image=FN_FromTheme("images/flags/$l.png");
        $html.="<a  href=\"?lang=$l&amp;mod={$_FN['mod']}&amp;opt=$opt\"><img src=\"$image\" alt=\"$l\" title=\"$l\" /></a>&nbsp;";
    }
    return $html;
}

/**
 * 
 */
function FNCC_HtmlDashBoard($htmltemplate)
{
    global $_FN;
    if (!FN_IsAdmin())
    {
        return;
    }
    $html="";
    if (strstr($htmltemplate,"dashboard_contents")=== false)
    {
        $htmltemplate="<h2>{dashboard_title}</h2>
                    <div>{dashboard_contents}</div>";
    }

    $sectiondirs=glob("sections/*");
    foreach($sectiondirs as $sectiondir)
    {
        $section=$_FN['mod']=basename($sectiondir);
        $section=FN_GetSectionValues($section);
        if (!empty($section['type']))
        {
            $sectiondir="modules/{$section['type']}";
            if (is_dir($sectiondir) && file_exists("$sectiondir/controlcenter/fncc_dashboard.php"))
            {
                $params['dashboard_title']=$section['title'];
                ob_start();
                include "$sectiondir/controlcenter/fncc_dashboard.php";
                $params['dashboard_contents']=ob_get_clean();
                $html.=FN_TPL_ApplyTplString($htmltemplate,$params);
            }
        }
        $_FN['mod']="";
    }
    $sectiondirs=glob("plugins/*");
    foreach($sectiondirs as $sectiondir)
    {
        $section=basename($sectiondir);
        $title=FN_GetFolderTitle($sectiondir);
        if (is_dir($sectiondir) && file_exists("$sectiondir/controlcenter/fncc_dashboard.php"))
        {

            $params['dashboard_title']=$title;
            ob_start();
            include "$sectiondir/controlcenter/fncc_dashboard.php";
            $params['dashboard_contents']=ob_get_clean();
            $html.=FN_TPL_ApplyTplString($htmltemplate,$params);
        }
    }

    $cc_sectiondirs=glob("controlcenter/sections/*");
    {
        foreach($cc_sectiondirs as $cc_sectiondir)
        {
            $sectiondirs=glob("$cc_sectiondir/*");
            foreach($sectiondirs as $sectiondir)
            {
                if (is_dir($sectiondir) && file_exists("$sectiondir/fncc_dashboard.php"))
                {

                    $params['dashboard_title']=FN_GetFolderTitle($sectiondir);
                    ;
                    ob_start();
                    include "$sectiondir/fncc_dashboard.php";
                    $params['dashboard_contents']=ob_get_clean();
                    $html.=FN_TPL_ApplyTplString($htmltemplate,$params);
                }
            }
        }
    }
    return $html;
}

/**
 * 
 * @global type $_FN
 * @return boolean
 */
function FNCC_GetCCSections()
{
    global $_FN;
    $sections=FN_XMLQuery("SELECT * FROM fn_cc_users WHERE username LIKE '{$_FN['user']}'");
    if (isset($sections[0]['ccsections']))
    {
        return $sections[0]['ccsections'];
    }
    return false;
}

/**
 * 
 * @global type $_FN
 * @param type $section
 * @return boolean
 */
function UserCanAdmin($section)
{
    global $_FN;
    if (FN_IsAdmin())
    {
        return true;
    }
    $opt=FN_GetParam("opt",$_GET);
    $sectionsEnabled=FNCC_GetCCSections();
    $sectionsEnabled=explode(",",$sectionsEnabled);
    if (in_array($opt,$sectionsEnabled))
    {
        return true;
    }
    return false;
}

/**
 *
 * @global global $_FN
 * @return type 
 */
function FNCC_GetMenuItems()
{
    global $_FN;
    $opt=FN_GetParam("opt",$_GET);
    $menu=array();
    $toShow=false;
    $section_enabled=FN_XMLQuery("SELECT * FROM fn_cc_users WHERE username LIKE '{$_FN['user']}'");
    $default="";
    if (!empty($section_enabled[0]['ccsections']))
    {
        $toShow=explode(",",$section_enabled[0]['ccsections']);
        $default=isset($section_enabled[0]['default']) ? $section_enabled[0]['default'] : "";
        if ($default && !in_array($default,$toShow))
            $toShow[]=$default;
    }

    $dirs=FN_ListDir("controlcenter/sections/",false);
    FN_NatSort($dirs);
    $sectionsIngroup=array();
    /* dprint_r("");
      dprint_r($toShow);
      dprint_r(""); */
    foreach($dirs as $sectiongroup)
    {
        $menu[$sectiongroup]=array();
        $sections=FN_ListDir("controlcenter/sections/$sectiongroup");
        FN_NatSort($sections);
        $sectionsIngroup=array();
        foreach($sections as $i=> $section)
        {
            $item['opt']="$sectiongroup/$section";
            $item['id']="$sectiongroup/$section";
            $item['description']="";
            if (is_array($toShow) && !in_array($item['opt'],$toShow))
            {
                
            }
            else
            {
                $item['link']="?opt={$item['opt']}";
                $item['title']="".FN_GetFolderTitle("controlcenter/sections/$sectiongroup/$section");
                $item['description']=$item['title'];
                $icon=FN_FromTheme("controlcenter/images/configure.png");
                if (file_exists("controlcenter/sections/$sectiongroup/$section/icon.png"))
                    $icon="controlcenter/sections/$sectiongroup/$section/icon.png";
                $item['image']=$icon;
                $sectionsIngroup[]=$item;
            }
        }
        if ($sectiongroup== "settings")
        {
//---------------get list of config.php in plugins and sections---------------->
            $_sectionsIngroup=array();
            $dirsconf=FNCC_GetSectionsConfigs();
            foreach($dirsconf as $_section)
            {
                $item=array();
                $item['opt']=$_section['opt'];
                $item['id']="$sectiongroup/{$_section['opt']}";
                $item['description']="";

                //  dprint_r($item);

                if (is_array($toShow) && !in_array($item['opt'],$toShow))
                {
                    continue;
                }
                $item['link']="?opt={$item['opt']}";
                $item['title']=$_section['title'];
                $item['image']=$_section['cc_icon'];
                $item['description']=$item['title'];
                $sectionsIngroup[]=$item;
            }

            //customs configs----<
            //plugins configs---->
            $dirsconf=FNCC_GetPluginsConfigs();
            foreach($dirsconf as $_section)
            {
                $item['opt']="fnc_ccnf_config_plugin_{$_section['id']}";
                $item['id']="$sectiongroup/fnc_ccnf_config_plugin_{$_section['id']}";
                $item['description']="";

                if (is_array($toShow) && !in_array($item['opt'],$toShow))
                {
                    continue;
                }
                $item['link']="?opt={$item['opt']}";
                $item['title']=$_section['title'];
                $item['image']=$_section['cc_icon'];
                $item['description']=$item['title'];

                $sectionsIngroup[]=$item;
            }
            //plugins configs----<
//---------------get list of config.php in plugins and sections----------------<				
        }
        if ($sectiongroup== "contents")
        {
            $dirs=FNCC_GetSectionsSettings();
            //dprint_r($dirs);
            foreach($dirs as $section)
            {
                $item['opt']=$section['opt'];
                $item['id']=$section['opt'];
                $item['description']="";

                if (is_array($toShow) && !in_array($item['opt'],$toShow))
                {
                    continue;
                }
                $item['link']="?opt={$item['opt']}";
                $ttype="";
                if (!empty($section['type']))
                {

                    $ttype=ucfirst(FN_GetFolderTitle($_FN['filesystempath']."/modules/{$section['type']}")." ");
                }
                $item['title']=$section['title'];
                $item['image']=$section['cc_icon'];
                $item['description']=$section['title'];
                $sectionsIngroup[]=$item;
            }
            //customs configs----<            
        }
        if (count($sectionsIngroup)== 0)
        {
            unset($menu[$sectiongroup]);
        }
        else
        {
            $menu[$sectiongroup]['description']="";
            $menu[$sectiongroup]['opt']=$sectiongroup;
            $menu[$sectiongroup]['id']=$sectiongroup;
            $menu[$sectiongroup]['link']="{$_FN['controlcenter']}?opt=$sectiongroup";
            $menu[$sectiongroup]['title']=FN_GetFolderTitle("controlcenter/sections/$sectiongroup");
            $menu[$sectiongroup]['sections']=$sectionsIngroup;
        }
    }


//---------------get settings.php in plugins and sections --------------------->
    $menu['fnc_ccnf_plugin']['description']="";
    $menu['fnc_ccnf_plugin']['link']="";
    $menu['fnc_ccnf_plugin']['opt']="fnc_ccnf_plugin_";
    $menu['fnc_ccnf_plugin']['id']="fnc_ccnf_plugin_";
    $menu['fnc_ccnf_plugin']['title']=FN_Translate("plugins");
    $menu['fnc_ccnf_plugin']['sections']=$sectionsIngroup;
    //customs configs config---->

    $sectionsIngroup=array();

    //customs configs----<
    //plugins configs---->
    $dirs=FNCC_GetPluginsSettings();
    foreach($dirs as $section)
    {
        $item['opt']="fnc_ccnf_plugin_{$section['id']}";
        $item['id']="fnc_ccnf_plugin_{$section['id']}";
        $item['description']="";

        if (is_array($toShow) && !in_array($item['opt'],$toShow))
        {
            continue;
        }
        $item['link']="?opt={$item['opt']}";
        $item['title']=$section['title'];
        $item['image']=$section['cc_icon'];
        $sectionsIngroup[]=$item;
    }
//---------------get settings.php in plugins and sections ---------------------<		
    $menu['fnc_ccnf_plugin']['sections']=$sectionsIngroup;
    if (count($sectionsIngroup)== 0)
        unset($menu['fnc_ccnf_plugin']);

    return $menu;
}

/**
 * 
 * @return string
 */
function FN_TPL_tp_create_dashboard($params)
{
    global $_FN;
    $html=FNCC_HtmlDashBoard($params);
    return $html;
}

/**
 * 
 * @global global $_FN
 * @param type $str
 * @return type
 */
function FN_TPL_tp_create_ccmenu($str)
{
    global $_FN;
    $opt=FN_GetParam("opt",$_GET);
    $sections=FNCC_GetMenuItems();
    $htmlout="";
    //dprint_r($sections);
    $tp_menuitem=FN_TPL_GetHtmlPart("menuitem",$str,"<a href=\"link\">title</a><br />");
    $tp_menuitemactive=FN_TPL_GetHtmlPart("menuitemactive",$str,$tp_menuitem);
    $tp_menuitemdropdown=FN_TPL_GetHtmlPart("menuitemdropdown",$str);
    $tp_menuitemdropdownactive=FN_TPL_GetHtmlPart("menuitemdropdownactive",$str);
    if ($tp_menuitemdropdownactive== "")
        $tp_menuitemdropdownactive=$tp_menuitemdropdown;
    $tp_menuitem=preg_replace("/<a([^>]+)(href)=(\")([^\"]*)(\")/im","<a\\1\\2=\\3{link}\\3",$tp_menuitem);
    $tp_menuitem=preg_replace("/<a([^>]+)(href)=(\')([^\']*)(\')/im","<a\\1\\2=\\3{link}\\3",$tp_menuitem);
    $tp_menuitemactive=preg_replace("/<a([^>]+)(href)=(\")([^\"]*)(\")/im","<a\\1\\2=\\3{link}\\3",$tp_menuitemactive);
    $tp_menuitemactive=preg_replace("/<a([^>]+)(href)=(\')([^\']*)(\')/im","<a\\1\\2=\\3{link}\\3",$tp_menuitemactive);
    $tp_menuitemdropdown=preg_replace("/<a([^>]+)(href)=(\")([^\"]*)(\")/im","<a\\1\\2=\\3#\\3",$tp_menuitemdropdown);
    $tp_menuitemdropdown=preg_replace("/<a([^>]+)(href)=(\')([^\']*)(\')/im","<a\\1\\2=\\3#\\3",$tp_menuitemdropdown);
    $tp_menuitemdropdownactive=preg_replace("/<a([^>]+)(href)=(\")([^\"]*)(\")/im","<a\\1\\2=\\3#\\3",$tp_menuitemdropdownactive);
    $tp_menuitemdropdownactive=preg_replace("/<a([^>]+)(href)=(\')([^\']*)(\')/im","<a\\1\\2=\\3#\\3",$tp_menuitemdropdownactive);
    if (strpos($tp_menuitem,'{title}')=== false)
    {
        $tp_menuitem=preg_replace("/(<a.*>)(.*)(<\/a)/im","\\1{title}\\3",$tp_menuitem);
    }
    if (strpos($tp_menuitemactive,'{title}')=== false)
    {
        $tp_menuitemactive=preg_replace("/(<a.*>)(.*)(<\/a)/im","\\1{title}\\3",$tp_menuitemactive);
    }
    if (strpos($tp_menuitemdropdown,'{title}')=== false)
    {
        $tp_menuitemdropdown=preg_replace("/(<a.*>)(.*)(<\/a)/im","\\1{title}\\3",$tp_menuitemdropdown);
    }
    if (strpos($tp_menuitemdropdownactive,'{title}')=== false)
    {
        $tp_menuitemdropdownactive=preg_replace("/(<a.*>)(.*)(<\/a)/im","\\1{title}\\3",$tp_menuitemdropdownactive);
    }
    //add title
    if (false== strpos($tp_menuitem,"title="))
    {
        $tp_menuitem=str_replace("<a","<a title=\"{description}\" ",$tp_menuitem);
    }
    //add title
    if (false== strpos($tp_menuitemactive,"title="))
    {
        $tp_menuitemactive=str_replace("<a","<a title=\"{description}\" ",$tp_menuitemactive);
    }
    //add title
    if (false== strpos($tp_menuitemactive,"title="))
    {
        $tp_menuitemdropdown=str_replace("<a","<a title=\"{description}\" ",$tp_menuitemdropdown);
    }
    //add title
    if (false== strpos($tp_menuitemactive,"title="))
    {
        $tp_menuitemdropdownactive=str_replace("<a","<a title=\"{description}\" ",$tp_menuitemdropdownactive);
    }
    //add accesskey
    if (false== strpos($tp_menuitem,"{accesskey"))
    {
        $tp_menuitem=str_replace("<a","<a accesskey=\"{accesskey}\" ",$tp_menuitem);
    }
    //add accesskey
    if (false== strpos($tp_menuitemactive,"{accesskey"))
    {
        $tp_menuitemactive=str_replace("<a","<a accesskey=\"{accesskey}\" ",$tp_menuitemactive);
    }
    //add accesskey
    if (false== strpos($tp_menuitemdropdown,"{accesskey"))
    {
        $tp_menuitemdropdown=str_replace("<a","<a accesskey=\"{accesskey}\" ",$tp_menuitemdropdown);
    }
    //add accesskey
    if (false== strpos($tp_menuitemdropdownactive,"{accesskey"))
    {
        $tp_menuitemdropdownactive=str_replace("<a","<a accesskey=\"{accesskey}\" ",$tp_menuitemdropdownactive);
    }

    foreach($sections as $sectionvalues)
    {
        //dprint_r($sectionvalues);
        $sectionvalues['accesskey']="";
        $htmlmenuitem="";
        if (FN_erg("^fnc_ccnf_config_section",$opt) || FN_erg("^fnc_ccnf_config_block",$opt))
        {
            $opt="settings/$opt";
        }
        elseif (FN_erg("^fnc_ccnf_config",$opt))
        {
            $opt="settings/$opt";
        }
        elseif (FN_erg("^fnc_ccnf_section",$opt) || FN_erg("^fnc_ccnf_block",$opt)) //nc_ccnf_block
        {
            $opt="contents/$opt";
        }
        $sectionvalues['title']=htmlspecialchars($sectionvalues['title'],ENT_QUOTES);
        $sectionvalues['description']=htmlspecialchars($sectionvalues['description'],ENT_QUOTES);

        //dprint_r(" $opt,{$sectionvalues['id']}  $tp_menuitemdropdownactive");
        if ($tp_menuitemdropdownactive!= "" && false!== strpos($opt,$sectionvalues['id']))
        {
            $htmlmenuitem=FN_TPL_ApplyTplString($tp_menuitemdropdownactive,$sectionvalues,false);
            $tp_submenuitem_ori_template=FN_TPL_GetHtmlPart("submenu",$tp_menuitemdropdownactive);
        }
        elseif ($tp_menuitemdropdown!= "")
        {
            $htmlmenuitem=FN_TPL_ApplyTplString($tp_menuitemdropdown,$sectionvalues,false);
            $tp_submenuitem_ori_template=FN_TPL_GetHtmlPart("submenu",$tp_menuitemdropdown);
        }
        $tp_submenuitem_ori=FN_TPL_GetHtmlPart("submenu",$htmlmenuitem);
        $tp_submenuitem_new=$tp_submenuitem_ori;
        $submenu_str=FN_TPL_tp_create_ccsubmenu_($tp_submenuitem_ori_template,$sectionvalues['sections']);
        $tp_submenuitem_new=str_replace($tp_submenuitem_ori,$submenu_str,$tp_submenuitem_ori);
        $htmlmenuitem=str_replace($tp_submenuitem_ori,$tp_submenuitem_new,$htmlmenuitem);
        $htmlout.=$htmlmenuitem;
    }
    return $htmlout;
}

/**
 *
 * @global array $_FN
 * @return string 
 */
function FN_TPL_tp_create_ccsubmenu_($str,$sections)
{
    global $_FN;
    $opt=FN_GetParam("opt",$_GET);
    if ($str== "")
        return "";
    if (!$sections  )
        return "";
    if (!is_array($sections))
    {
       // dprint_r($sections);
        return "";
    }
    preg_match('/<!-- submenuitems -->(.*)<!-- endsubmenuitems -->/is',$str,$out);
    $tp_menuitem_old=FN_TPL_GetHtmlPart("submenuitems",$str,"<li><a href=\"link\">title</a></li>");


    $tp_menuitem=FN_TPL_GetHtmlPart("submenuitem",$str);
    $tp_menuitemactive=FN_TPL_GetHtmlPart("submenuitemactive",$str,$tp_menuitem);
    $tp_menuitem=preg_replace("/<a([^>]+)(href)=(\")([^\"]*)(\")/im","<a\\1\\2=\\3{link}\\3",$tp_menuitem);
    $tp_menuitem=preg_replace("/<a([^>]+)(href)=(\')([^\']*)(\')/im","<a\\1\\2=\\3{link}\\3",$tp_menuitem);
    $tp_menuitemactive=preg_replace("/<a([^>]+)(href)=(\")([^\"]*)(\")/im","<a\\1\\2=\\3{link}\\3",$tp_menuitemactive);
    $tp_menuitemactive=preg_replace("/<a([^>]+)(href)=(\')([^\']*)(\')/im","<a\\1\\2=\\3{link}\\3",$tp_menuitemactive);
    if (strpos($tp_menuitem,'{title}')=== false)
    {
        $tp_menuitem=preg_replace("/(<a.*>)(.*)(<\/a)/im","\\1{title}\\3",$tp_menuitem);
        $tp_menuitemactive=preg_replace("/(<a.*>)(.*)(<\/a)/im","\\1{title}\\3",$tp_menuitemactive);
    }
    //add title
    if (false== strpos($tp_menuitem,"title="))
    {
        $tp_menuitem=str_replace("<a","<a title=\"{description}\" ",$tp_menuitem);
    }
    //add title
    if (false== strpos($tp_menuitemactive,"title="))
    {
        $tp_menuitemactive=str_replace("<a","<a title=\"{description}\" ",$tp_menuitemactive);
    }
    //add accesskey
    if (false== strpos($tp_menuitem,"{accesskey"))
    {
        $tp_menuitem=str_replace("<a","<a accesskey=\"{accesskey}\" ",$tp_menuitem);
    }
    //add accesskey
    if (false== strpos($tp_menuitemactive,"{accesskey"))
    {
        $tp_menuitemactive=str_replace("<a","<a accesskey=\"{accesskey}\" ",$tp_menuitemactive);
    }


    $htmlout="";
    foreach($sections as $sectionvalues)
    {
        $sectionvalues['title']=htmlspecialchars($sectionvalues['title'],ENT_QUOTES);
        $sectionvalues['description']=htmlspecialchars($sectionvalues['description'],ENT_QUOTES);

        //dprint_r("$opt {$sectionvalues['id']} ");
        $sectionvalues['accesskey']="";
        if ($opt== $sectionvalues['opt'])
            $htmlout.=FN_TPL_ApplyTplString($tp_menuitemactive,$sectionvalues,false);
        else
            $htmlout.=FN_TPL_ApplyTplString($tp_menuitem,$sectionvalues,false);
        if (strpos($htmlout,'{submenu}')!== false)
        {
            $htmlout=str_replace("{submenu}",FN_TPL_tp_create_ccsubmenu_($str,$sectionvalues['id']),$htmlout);
        }
    }
    if ($htmlout!= "")
        $htmlout=str_replace($tp_menuitem_old,$htmlout,$str);
    return $htmlout;
}

/**
 * 
 * @global type $_FN
 * @param type $tablename
 * @param type $params
 */
function FNCC_XmltableEditor($tablename,$params=false,$params2=false)
{
    global $_FN;
    if (is_array($params2))
    {
        if (empty($params2['layout_template']) && file_exists("controlcenter/themes/{$_FN['controlcenter_theme']}/form.tp.html"))
        {
            $params2['layout_template']=file_get_contents("controlcenter/themes/{$_FN['controlcenter_theme']}/form.tp.html");
        }
        if (empty($params['html_template_grid']) && file_exists("controlcenter/themes/{$_FN['controlcenter_theme']}/grid.tp.html"))
        {
            $params2['html_template_grid']=file_get_contents("controlcenter/themes/{$_FN['controlcenter_theme']}/grid.tp.html");
        }
        if (empty($params['html_template_view']) && file_exists("controlcenter/themes/{$_FN['controlcenter_theme']}/view.tp.html"))
        {
            $params2['html_template_view']=file_get_contents("controlcenter/themes/{$_FN['controlcenter_theme']}/view.tp.html");
        }
        
    }
    else
    {
        if (empty($params['layout_template']) && file_exists("controlcenter/themes/{$_FN['controlcenter_theme']}/form.tp.html"))
        {
            $params['layout_template']=file_get_contents("controlcenter/themes/{$_FN['controlcenter_theme']}/form.tp.html");
        }
        if (empty($params['html_template_grid']) && file_exists("controlcenter/themes/{$_FN['controlcenter_theme']}/grid.tp.html"))
        {
            $params['html_template_grid']=file_get_contents("controlcenter/themes/{$_FN['controlcenter_theme']}/grid.tp.html");
        }
        if (empty($params['html_template_view']) && file_exists("controlcenter/themes/{$_FN['controlcenter_theme']}/view.tp.html"))
        {
            $params['html_template_view']=file_get_contents("controlcenter/themes/{$_FN['controlcenter_theme']}/view.tp.html");
        }
    }
    FN_XmltableEditor($tablename,$params,$params2);
}

/**
 * 
 * @global array $_FN
 * @staticvar string $html
 * @param type $body
 * @param type $title
 * @return string
 */
function FN_HtmlModalWindow($body,$title="",$textbutton="ok")
{
    global $_FN;
    static $html="";
    if ($title== "")
    {
        $title="{$_FN['sitename']}";
    }

    if ($html== "" && file_exists("controlcenter/themes/{$_FN['controlcenter_theme']}/modal.tp.html"))
    {
        $html=file_get_contents("controlcenter/themes/{$_FN['controlcenter_theme']}/modal.tp.html");
    }

    if ($html== "")
    {
        $html="\n<script language=\"javascript\">";
        $html.="\n setTimeout(function(){alert(\"".str_replace("\n","\\n",addslashes($body))."\",0)});";
        $html.="\n</script>\n";
        return $html;
    }
    $html=FN_TPL_ApplyTplString($html,array("title"=>$title,"body"=>$body,"textbutton"=>$textbutton,"idmodal"=>uniqid("modal_")));
    return $html;
    //dprint_xml($html);
    //die();
}

/**
 * 
 * @param type $file
 * @param type $formaction
 * @param type $exit
 * @param type $allow
 * @param type $write_to_file
 * @param type $mod
 * @param type $block
 * @param type $tableHtmlattibutes
 */
function FNCC_HtmlEditConfFile($file,$formaction="",$exit="",$allow=false,$write_to_file=false,$mod="",$block="",$tableHtmlattibutes="")
{
    global $_FN;
    if ($tableHtmlattibutes== "" && file_exists("controlcenter/themes/{$_FN['controlcenter_theme']}/editconf.tp.html"))
    {
        preg_match("/<!-- editconf table attributes -->(.*)<!-- end editconf table attributes -->/is",file_get_contents("controlcenter/themes/{$_FN['controlcenter_theme']}/editconf.tp.html"),$out);
        $tableHtmlattibutes=empty($out[1]) ? "" : $out[1];
    }

    return FN_HtmlEditConfFile($file,$formaction,$exit,$allow,$write_to_file,$mod,$block,$tableHtmlattibutes);
}

?>
