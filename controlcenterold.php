<?php
/**
 * @package Flatnux_controlcenter
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 1011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
ob_start();
$script_name = basename(__FILE__);
define("_CONTROLCENTER_SCRIPTNAME",$script_name);
require_once ("include/flatnux.php");
$_FN['controlcenter']=$script_name;
header("Content-Type: text/html; charset={$_FN['charset_page']}");
$_FN['theme'] = "base";
$_FN['fneditmode'] = "0";
$opt = FN_GetParam("opt",$_GET,"html");
$op = FN_GetParam("op",$_GET,"html");
$modcont = FN_GetParam("modcont",$_GET,"flat");
//-------------------------init table cc_users--------------------------------->
if (!file_exists("{$_FN['datadir']}/{$_FN['database']}/fn_cc_users.php"))
{
	$xml = '<?xml version="1.0" encoding="UTF-8"?>
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
</tables>
';
	FN_Write($xml,"{$_FN['datadir']}/{$_FN['database']}/fn_cc_users.php");
}
//-------------------------init table cc_users---------------------------------<

$configsection = $sect = "";
$plugin = "";
$editconf = "";
$fileconfig_to_edit = "";
$_FN['mod'] = "";
if (FN_erg("^fnc_ccnf_section_",$opt))
{
	$configsection = $sect = FN_erg_replace("^fnc_ccnf_section_","",$opt);
	$_FN['mod'] = $sect;
	$_FN['sectionvalues'] = FN_GetSectionValues($_FN['mod']);
}
elseif (FN_erg("^fnc_ccnf_plugin_",$opt))
{
	$plugin = FN_erg_replace("^fnc_ccnf_plugin_","",$opt);
}
elseif (FN_erg("^fnc_ccnf_config_",$opt))
{
	$editconf = $opt;
}
if (FN_erg("^fnc_ccnf_config_section_",$opt))
{
	$section_to_edit_config = FN_erg_replace("^fnc_ccnf_config_section_","",$opt);
	$sectionvalues = FN_GetSectionValues($section_to_edit_config);
	$configsection = $_FN['mod'] = $sectionvalues['id'];
	$_FN['sectionvalues'] = FN_GetSectionValues($_FN['mod']);

	if (file_exists("sections/{$sectionvalues['id']}/config.php"))
		$fileconfig_to_edit = "sections/{$sectionvalues['id']}/config.php";
	else
		$fileconfig_to_edit = "modules/{$sectionvalues['type']}/config.php";
}
elseif (FN_erg("^fnc_ccnf_config_plugin_",$opt))
{
	$plugin_to_edit_config = FN_erg_replace("^fnc_ccnf_config_plugin_","",$opt);
	$fileconfig_to_edit = "plugins/$plugin_to_edit_config/config.php";
}

if ($opt != "" && !preg_match("/^[a-z0-9_]+\/[a-z0-9_]+$/si",$opt))
{
	$opt = "";
}


echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n<html>";
echo "<head>".FN_HtmlHeader(false,false);
//-------------------------------------------MENU TOP-------------------------->
echo "<script  type=\"text/javascript\">
var myThemeOfficeBase = \"{$_FN['siteurl']}controlcenter/js/ThemeOffice/\"
</script>";
echo "<script type=\"text/javascript\" src=\"controlcenter/js/JSCookMenu.js\"></script>";
echo "<script type=\"text/javascript\" src=\"controlcenter/js/effect.js\"></script>";
echo "<link rel=\"stylesheet\" href=\"controlcenter/js/ThemeOffice/theme.css\" type=\"text/css\">
<script type=\"text/javascript\" src=\"controlcenter/js/ThemeOffice/theme.js\"></script>";
echo "<link rel=\"stylesheet\" href=\"controlcenter/css/style.css?".time()."\" type=\"text/css\">";
//-------------------------------------------MENU TOP--------------------------<
echo "</head>";
echo "<body>";
if ($modcont != "" && FN_CanModifyFile($_FN['user'],$modcont) && file_exists($modcont))
{
	$linkcancel = FN_RewriteLink("index.php?mod={$_FN['mod']}");
	FN_EditContent($modcont,$linkcancel,$linkcancel);
}
elseif (!FN_IsAdmin() && FNCC_GetCCSections() == false)
{
	//------------------------------------------------------login form----->
	echo "<div id=\"fncc_login\">";
	echo "<div id=\"fncc_loginformtitle\">";
	echo FN_i18n("control center");
	echo "</div>";
	echo "<div id=\"fncc_loginform\">";
	FN_LoginForm();
	echo "<a href=\"".FN_RewriteLink("index.php")."\">".FN_Translate("go to")." ".$_FN['sitename']."</a>";
	echo "</div>";
	echo "</div>";
	//------------------------------------------------------login form-----<
}
else
{
//-----------------------------MAIN PAGE--------------------------------------->
	$version = htmlspecialchars(file_get_contents("VERSION"));
	echo "<div id=\"fncc_top\">";
	echo "<a id=\"fncc_controlcenter\" href=\"{$_FN['controlcenter']}\"><img alt=\"home\" src=\"".FN_FromTheme("images/configure.png")."\" />&nbsp;".FN_Translate("control center")."</a>";
	echo "<a id=\"fncc_logout\" href=\"?fnlogin=logout\"><img alt=\"home\" src=\"".FN_FromTheme("images/user.png")."\" />&nbsp;".FN_i18n("logout")."</a>";
	echo "<a id=\"fncc_gohome\" href=\"index.php?theme=__default__\"><img alt=\"home\" src=\"".FN_FromTheme("images/home.png")."\" />&nbsp;".FN_Translate("go to").": {$_FN['sitename']}</a>";
	echo "<span id=\"fncc_languages\" href=\"?fnlogin=logout\">".FNCC_HtmlLanguages()."</span>";
	echo "</div>";
	echo "<div id=\"fncc_menutop\">";
	echo FNCC_TopMenu();
	echo "</div>";
	$opt = FN_GetParam("opt",$_GET,"html");
	$sectionvalues = FN_GetSectionValues($sect);
	if (!empty($opt) && UserCanAdmin($opt))
	{

		echo FNCC_HtmlMenuLeft();
		echo "<div id=\"fncc_contents\">";
		if (FN_erg("fnc_ccnf_config_section_",$opt))
		{
			$t = str_replace("fnc_ccnf_config_section_","",$opt);
			$s = FN_GetSectionValues($t);
			$title = $s['title'];
		}
		elseif (FN_erg("fnc_ccnf_section_",$opt))
		{
			$t = str_replace("fnc_ccnf_section_","",$opt);
			$s = FN_GetSectionValues($t);
			$title = $s['title'];
		}
		elseif (FN_erg("fnc_ccnf_plugin_",$opt))
		{
			$t = str_replace("fnc_ccnf_plugin_","",$opt);
			$title = "".FN_GetFolderTitle("plugins/$t");
		}
		else
		{
			$title = FN_GetFolderTitle("controlcenter/sections/$opt/");
		}
		echo "<div class=\"fncc_sectiontitle\" >$title</div>";

		if (!empty($opt) && file_exists("controlcenter/sections/$opt/section.php"))
		{
			include "controlcenter/sections/$opt/section.php";
		}
		elseif (file_exists("sections/{$sect}/controlcenter/settings.php"))
		{
			$_FN['mod'] = $sect;
			include "sections/{$sect}/controlcenter/settings.php";
			echo "<hr />";
		}
		elseif (!empty($sectionvalues['type']) && file_exists("modules/{$sectionvalues['type']}/controlcenter/settings.php"))
		{
			$_FN['mod'] = $sect;
			include "modules/{$sectionvalues['type']}/controlcenter/settings.php";
			echo "<hr />";
		}
		elseif (file_exists("plugins/{$plugin}/controlcenter/settings.php"))
		{
			$_FN['mod'] = "";
			include "plugins/{$plugin}/controlcenter/settings.php";
			echo "<hr />";
		}
		elseif (file_exists("plugins/{$plugin}/config.php"))
		{
			FN_EditConfFile("plugins/{$plugin}/config.php","?opt=$editconf");
		}
		elseif ($fileconfig_to_edit != "" && file_exists($fileconfig_to_edit))
		{
			FN_EditConfFile("$fileconfig_to_edit","?opt=$editconf");
		}
		else
		{
			echo FNCC_createCenterSubMenu($opt);
		}
		if ($configsection != "")
		{
			echo "<div style=\"text-align:right\"><br /><a href=\"".FN_RewriteLink("index.php?mod={$_FN['mod']}","&amp;")."\">".FN_Translate("go to page")." {$_FN['sectionvalues']['title']}</a></div>";
		}
		echo "</div>";
		if (file_exists("controlcenter/sections/$opt/help/"))
		{
			echo "<img style=\"float:right;cursor:pointer\" src=\"controlcenter/images/help.png\" alt=\"\" onclick=\"document.getElementById('fncc_helpframe').style.display='block';\" />";
			echo "<div id=\"fncc_helpframe\"><img style=\"float:right;cursor:pointer\" onclick=\"document.getElementById('fncc_helpframe').style.display='none';\" src=\"images/delete.png\" alt=\"close\" />".FN_HtmlContent("controlcenter/sections/$opt/help/")."</div>";
		}
	}
	else
	{
		echo "<table width=\"100%\"><tr>";
		echo "<td  width=\"50%\" valign=\"top\" >";
		echo FNCC_HtmlCenterMenu();
		echo "</td>";
		echo "<td  width=\"50%\" valign=\"top\">";
		echo FNCC_HtmlDashBoard();
		echo "</td>";
		echo "</tr></table>";
		echo "<div id=\"fncc_footer\">";
		echo "<span id=\"fncc_version\"><a href=\"http://www.flatnux.org\" >Flatnux CMS</a> - Author:<a href=\"mailto:speleoalex@gmail.com\">Alessandro Vernassa</a> - Version:$version";
		echo "</div>";
	}
}
//-----------------------------MAIN PAGE---------------------------------------<
echo "</body>";
echo "</html>";
/**
 * html main user menu
 */
function FNCC_TopMenu()
{
	global $_FN;
	$html ="<div id=\"myMenuID\"></div>";
	$html .= "\n<script type=\"text/javascript\"> ";
	$html .= "var myMenu = ";
	$html .= "[";
	$html .= FNCC_gennode("");
	$html .= "];
	cmDraw ('myMenuID', myMenu, 'hbr', cmThemeOffice);
</script>
";
	return $html;
}

/**
 *
 * @param string $path
 */
function FNCC_GenNode($path = "")
{
	global $_FN;
	static $i = 0;
	$html = "";
	static $slevel = 0;
	$items = FNCC_GetMenuItems();
	if ($path !== "")
	{
		if (isset($items[$path]['sections']))
		{
			$items = $items[$path]['sections'];
		}
		else
		{
			return "";
		}
	}
	$slevel++;
	foreach ($items as $k=> $item)
	{
		$link = $item['link'];
		$title = htmlentities($item['title'],ENT_QUOTES);
		$title = str_replace("\n","",$title);
		$title = str_replace("\r","",$title);
		$img = "";
		if (isset($item['image']))
		{
			$img = "<img height=\"16\" src=\"{$item['image']}\" style=\"vertical-align:middle\" alt=\"\"/>";
		}
		$html .=",\n\t\t[";
		$html .="'$img ','$title   ', '$link','',''";
		if (isset($item['sections']) && count($item['sections']) > 0)
		{
			$html .= FNCC_GenNode($k);
		}
		$html .= "] ";
		$slevel--;
	}
	return $html;
}

/**
 * @global array $_FN
 * @return string
 */
function FNCC_HtmlMenuLeft()
{
	global $_FN;
	$opt = FN_GetParam("opt",$_GET,"flat");
	$sectiongroups = array();
	$html = "<div id=\"fncc_leftmenu\">";
	$dirs = FNCC_GetMenuItems();
	foreach ($dirs as $sectiongroup)
	{
		$titlesectiongroup = $sectiongroup['title'];
		$html.= "<div class=\"fncc_leftmenu\">";
		$html.= "<div class=\"fncc_leftmenutitle\">{$sectiongroup['title']}</div>";
		foreach ($sectiongroup['sections'] as $section)
		{
			$classcurrent = "";
			if ($opt == $section['opt'])
			{
				$classcurrent = "class=\"current\"";
			}
			$html.= "<a $classcurrent href=\"{$section['link']}\"><img alt=\"\" src=\"{$section['image']}\" /><span>{$section['title']}</span></a>";
		}
		$html.= "</div>";
	}
	$html.= "</div>";
	return $html;
}

/**
 * @global array $_FN
 * @return string
 */
function FNCC_HtmlCenterMenu()
{
	global $_FN;
	$sectiongroups = array();
	$meenuitems = FNCC_GetMenuItems();
	$html = "<div id=\"fncc_centermenu\">";
	foreach ($meenuitems as $menuitemgroup)
	{
		$html.= "<div class=\"fncc_centermenu\">";
		$html.= "<div class=\"fncc_centermenutitle\">{$menuitemgroup['title']}</div>";
		foreach ($menuitemgroup['sections'] as $menuitem)
		{
			$html.= "\n<a href=\"{$menuitem['link']}\"><img alt=\"\" src=\"{$menuitem['image']}\" /><br /><span>{$menuitem['title']}</span></a>";
		}
		$html.= "</div>";
	}
	$html.= "\n</div>";
	$html.= "</div>";
	return $html;
}

/**
 * @global array $_FN
 * @return string
 */
function FNCC_createCenterSubMenu($opt)
{
	global $_FN;
	$sectiongroups = array();
	$meenuitems = FNCC_GetMenuItems();
	if (!isset($meenuitems[$opt]['title']))
		return "";
	$html = "<div class=\"fncc_centermenu\">";
	$html.= "<div class=\"fncc_centermenutitle\">".$meenuitems[$opt]['title']."</div>";
	foreach ($meenuitems[$opt]['sections'] as $section)
	{
		$html.= "\n<a href=\"{$section['link']}\"><img alt=\"\" src=\"{$section['image']}\" /><br /><span>{$section['title']}</span></a>";
	}
	$html.= "</div>\n";
	return $html;
}

/**
 *
 * @return array
 */
function FNCC_GetSectionsConfigs()
{
	$sections = FN_GetSections("",true,true,true);
	$configs = array();
	foreach ($sections as $section)
	{
		if (!empty($section['type']) && (file_exists("modules/{$section['type']}/config.php") /* || file_exists("modules/{$section['type']}/controlcenter/settings.php") */))
		{
			$section['cc_icon'] = "controlcenter/sections/settings/cms/icon.png";
			;
			$configs[] = $section;
		}
		elseif (file_exists("sections/{$section['id']}/config.php") /* || file_exists("sections/{$section['id']}/controlcenter/settings.php") */)
		{
			$section['cc_icon'] = "controlcenter/sections/settings/cms/icon.png";
			;
			$configs[] = $section;
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
	$sections = FN_GetSections(false,true,true,true);
	$configs = array();
	foreach ($sections as $section)
	{
		if (!empty($section['type']) && file_exists("modules/{$section['type']}/controlcenter/settings.php"))
		{
			$section['cc_icon'] = FN_FromTheme("controlcenter/images/configure.png");
			$configs[] = $section;
		}
		elseif (file_exists("sections/{$section['id']}/controlcenter/settings.php"))
		{
			$section['cc_icon'] = FN_FromTheme("controlcenter/images/configure.png");
			$configs[] = $section;
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
	$sections = glob("plugins/*");

	$configs = array();
	foreach ($sections as $section)
	{
		if (file_exists("$section/config.php"))
		{
			if (file_exists("$section/icon.png"))
				$_section['cc_icon'] = "$section/icon.png";
			else
				$_section['cc_icon'] = "controlcenter/sections/settings/cms/icon.png";
			$_section['title'] = FN_GetFolderTitle($section);
			$_section['id'] = basename($section);
			$configs[] = $_section;
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
	$sections = glob("plugins/*");

	$configs = array();
	foreach ($sections as $section)
	{
		if (file_exists("$section/controlcenter/settings.php"))
		{
			if (file_exists("$section/controlcenter/icon.png"))
				$_section['cc_icon'] = "$section/controlcenter/icon.png";
			else
				$_section['cc_icon'] = FN_FromTheme("controlcenter/images/configure.png");
			$_section['title'] = FN_GetFolderTitle($section);
			$_section['id'] = basename($section);
			$configs[] = $_section;
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
	$opt = FN_GetParam("opt",$_GET,"flat");
	$html = "";
	foreach ($_FN['listlanguages'] as $l)
	{
		$image = FN_FromTheme("images/flags/$l.png");
		$html .= "<a  href=\"?lang=$l&amp;mod={$_FN['mod']}&amp;opt=$opt\"><img src=\"$image\" alt=\"$l\" title=\"$l\" /></a>&nbsp;";
	}
	return $html;
}

/**
 * 
 */
function FNCC_HtmlDashBoard()
{
	if (!FN_IsAdmin())
	{
		return;
	}
	$sectiondirs = glob("sections/*");
	foreach ($sectiondirs as $sectiondir)
	{
		$section = $_FN['mod'] = basename($sectiondir);
		$section = FN_GetSectionValues($section);
		if (!empty($section['type']))
		{
			$sectiondir = "modules/{$section['type']}";
			if (file_exists("$sectiondir/controlcenter/fncc_dashboard.php"))
			{
				echo "<div class=\"fncc_dashboard_item\">";
				echo "<div  class=\"fncc_dashboard_itemtitle\" >".$section['title']."</div>";
				include "$sectiondir/controlcenter/fncc_dashboard.php";
				echo "</div>";
			}
		}
	}
}

/**
 * 
 * @global type $_FN
 * @return boolean
 */
function FNCC_GetCCSections()
{
	global $_FN;
	$sections = FN_XMLQuery("SELECT * FROM fn_cc_users WHERE username LIKE '{$_FN['user']}'");
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
	$opt = FN_GetParam("opt",$_GET);
	$sectionsEnabled = FNCC_GetCCSections();
	$sectionsEnabled = explode(",",$sectionsEnabled);
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
	$opt = FN_GetParam("opt",$_GET);
	$menu = array();
	$toShow = false;
	$section_enabled = FN_XMLQuery("SELECT * FROM fn_cc_users WHERE username LIKE '{$_FN['user']}'");
	if (!empty($section_enabled[0]['ccsections']))
	{
		$toShow = explode(",",$section_enabled[0]['ccsections']);
	}

	$dirs = FN_ListDir("controlcenter/sections/",false);
	FN_NatSort($dirs);
	$sectionsIngroup = array();
	foreach ($dirs as $sectiongroup)
	{
		$menu[$sectiongroup] = array();
		$sections = FN_ListDir("controlcenter/sections/$sectiongroup");
		FN_NatSort($sections);
		$sectionsIngroup = array();
		foreach ($sections as $section)
		{
			$item['opt'] = "$sectiongroup/$section";
			if (is_array($toShow) && !in_array($item['opt'],$toShow))
			{
				
			}
			else
			{
				$item['link'] = "?opt={$item['opt']}";
				$item['title'] = FN_GetFolderTitle("controlcenter/sections/$sectiongroup/$section");
				$icon = FN_FromTheme("controlcenter/images/configure.png");
				if (file_exists("controlcenter/sections/$sectiongroup/$section/icon.png"))
					$icon = "controlcenter/sections/$sectiongroup/$section/icon.png";
				$item['image'] = $icon;
				$sectionsIngroup[] = $item;
			}
			if ($sectiongroup == "settings" && $section == "cms")
			{
//---------------get list of config.php in plugins and sections---------------->

				$_sectionsIngroup = array();
				$dirsconf = FNCC_GetSectionsConfigs();
				foreach ($dirsconf as $_section)
				{
					$item['opt'] = "fnc_ccnf_config_section_{$_section['id']}";
					if (is_array($toShow) && !in_array($item['opt'],$toShow))
					{
						continue;
					}
					$item['link'] = "?opt={$item['opt']}";
					$item['title'] = $_section['title'];
					$item['image'] = $_section['cc_icon'];
					$sectionsIngroup[] = $item;
				}
				//customs configs----<
				//plugins configs---->
				$dirsconf = FNCC_GetPluginsConfigs();
				foreach ($dirsconf as $_section)
				{
					$item['opt'] = "fnc_ccnf_config_plugin_{$_section['id']}";
					if (is_array($toShow) && !in_array($item['opt'],$toShow))
					{
						continue;
					}
					$item['link'] = "?opt={$item['opt']}";
					$item['title'] = $_section['title'];
					$item['image'] = $_section['cc_icon'];
					$sectionsIngroup[] = $item;
				}
				//plugins configs----<
//---------------get list of config.php in plugins and sections----------------<				
			}
		}
		if (count($sectionsIngroup) == 0)
		{
			unset($menu[$sectiongroup]);
		}
		else
		{
			$menu[$sectiongroup]['link'] = "{$_FN['controlcenter']}?opt=$sectiongroup";
			$menu[$sectiongroup]['title'] = FN_GetFolderTitle("controlcenter/sections/$sectiongroup");
			$menu[$sectiongroup]['sections'] = $sectionsIngroup;
		}
	}


//---------------get settings.php in plugins and sections --------------------->
	$menu['fnc_ccnf_plugin']['link'] = "";
	$menu['fnc_ccnf_plugin']['title'] = FN_Translate("pages and plugins");
	$menu['fnc_ccnf_plugin']['sections'] = $sectionsIngroup;
	//customs configs config---->
	$sectionsIngroup = array();
	$dirs = FNCC_GetSectionsSettings();
	foreach ($dirs as $section)
	{
		$item['opt'] = "fnc_ccnf_section_{$section['id']}";
		if (is_array($toShow) && !in_array($item['opt'],$toShow))
		{
			continue;
		}
		$item['link'] = "?opt={$item['opt']}";
                $ttype = "";
                if (!empty($section['type']))
                {
                    $ttype = " ({$section['type']})";
                }
		$item['title'] = FN_Translate("page").": ".$section['title']."$ttype";
		$item['image'] = $section['cc_icon'];
		$sectionsIngroup[] = $item;
	}
	//customs configs----<
	//plugins configs---->
	$dirs = FNCC_GetPluginsSettings();
	foreach ($dirs as $section)
	{
		$item['opt'] = "fnc_ccnf_plugin_{$section['id']}";
		if (is_array($toShow) && !in_array($item['opt'],$toShow))
		{
			continue;
		}
		$item['link'] = "?opt={$item['opt']}";
		$item['title'] = FN_Translate("plugin").": ".$section['title'];
		$item['image'] = $section['cc_icon'];
		$sectionsIngroup[] = $item;
	}
//---------------get settings.php in plugins and sections ---------------------<		
	$menu['fnc_ccnf_plugin']['sections'] = $sectionsIngroup;
	if (count($sectionsIngroup) == 0)
		unset($menu['fnc_ccnf_plugin']);
        
	return $menu;
}

?>