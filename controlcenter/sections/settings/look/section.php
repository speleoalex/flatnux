<?php
/**
 * @package Flatnux_controlcenter
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 1011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
defined('_FNEXEC') or die('Restricted access');
global $_FN;
$op = FN_GetParam("opt",$_GET);
$themetoedit = FN_GetParam("themetoedit",$_GET);
$edit = FN_GetParam("edit",$_GET);
$editconf = FN_GetParam("editconf",$_GET);
$editoptions = FN_GetParam("editoptions",$_GET);
$edit = FN_GetParam("edit",$_GET);
if ($themetoedit == "")
{
	$themetoedit = $_FN['theme_default'];
}

if ($edit != "" && file_exists("themes/$themetoedit/$edit"))
{
	//($file,$formaction = "",$exit = "",$editor_params = false)
	FN_EditContent(
		"themes/$themetoedit/$edit"
		,"?mod={$_FN['mod']}&amp;opt=$opt&amp;themetoedit=$themetoedit&amp;edit=$edit"
		,"?mod={$_FN['mod']}&amp;opt=$opt&amp;themetoedit=$themetoedit"
		,array("css_file"=>"themes/$themetoedit/style.css","fullpage"=>true)
			
			);
}
elseif ($editconf != "")
{
	$theme = $themetoedit;
	if (count($_POST) > 0)
	{
		FN_JsRedirect("?mod={$_FN['mod']}&opt=$opt&themetoedit=$themetoedit&editconf=$editconf");
	}
	else
	{
		echo "<button onclick=\"resizeThumb(900,250)\">900x250</button>";
		echo "<button onclick=\"resizeThumb(800,600)\">800x600</button>";
		echo "<button onclick=\"resizeThumb(320,240)\">320x240</button>";
		echo "<button onclick=\"resizeThumb(250,900)\">250x900</button>";
		echo "<button onclick=\"resizeThumb(250,900)\">250x900</button>";
		echo "<iframe style=\"border:1px solid inset;height:250px;width:900px\" src=\"index.php?theme=$themetoedit\"></iframe>";
		echo "<br /><img alt=\"\" style=\"vertical-align:middle\" src=\"images/left.png\" /> <a href=\"?mod={$_FN['mod']}&amp;opt=$opt&amp;themetoedit=$themetoedit\">".FN_i18n("back")."</a>";
	}
	FN_EditConfFile("themes/{$theme}/config.php","?mod={$_FN['mod']}&amp;opt=$opt&amp;themetoedit=$themetoedit&amp;editconf=$editconf",""
	);
}
else
{
	$list_themes = get_list_themes();
	$theme = FN_GetParam("theme",$_POST,"html");
	if ($theme != "" && file_exists("themes/$theme"))
	{
		$t = FN_XmlTable("fn_settings");
		$t->UpdateRecord(array("varname"=>"theme","varvalue"=>$theme));
	}
	echo "
<div>
	<form method=\"post\" action=\"\" name=\"feditth\">";
	echo FN_i18n("theme")." : <select name=\"theme\" onchange=\"window.location='?mod=".$_FN['mod']."&themetoedit='+ document.feditth.theme.options[document.feditth.theme.selectedIndex].text + '&amp;opt=$op'\" >";
	foreach ($list_themes as $theme_)
	{
		echo "\n<option ";
		if ($themetoedit == $theme_)
		{
			echo ' selected="selected" ';
		}
		echo ">$theme_</option>";
	}
	echo "
		</select>";
	echo "<button type=\"submit\" >".FN_i18n("apply this theme")."</button>";
	echo "
	</form>
</div>
";
	echo "<div style=\"\">";
	$listfiles = glob("themes/$themetoedit/*.css");
	if (count($listfiles) > 0)
	{
		echo "<table>";
		echo "<tr><td colspan=\"2\">CSS</td></tr>";
		foreach ($listfiles as $file)
		{
			$file2 = basename($file);
			$urlcss = $_FN['siteurl']."/themes/$themetoedit/$file2";
			echo "<tr><td>".basename("$file")."</td><td><a href=\"?mod={$_FN['mod']}&amp;opt=$opt&amp;edit=$file2&amp;themetoedit=$themetoedit\">[".FN_i18n("modify")."]</a></td><td><a href=\"http://jigsaw.w3.org/css-validator/validator?uri=$urlcss&amp;profile=css21&amp;usermedium=all\" onclick=\"window.open(this.href);return false;\">validate</a></td></tr>";
		}
		echo "</table>";
	}
	echo "</div>";
	//echo FN_i18n("preview") . "<br />";
	$theme = $themetoedit;
	if (count($_POST) > 0)
	{
		FN_JsRedirect("?mod={$_FN['mod']}&opt=$opt&themetoedit=$themetoedit&editconf=$editconf");
	}
	else
	{
				echo "
<script>
function resizeThumb(w,h){
	document.getElementById('thumb').style.width=w+'px';
	document.getElementById('thumb').style.height=h+'px';
}
</script>
";
		echo FN_Translate("preview").":<button onclick=\"resizeThumb(900,250)\">900x250</button>";
		echo "<button onclick=\"resizeThumb(800,600)\">800x600</button>";
		echo "<button onclick=\"resizeThumb(320,240)\">320x240</button>";
		echo "<button onclick=\"resizeThumb(640,480)\">640x480</button>";
		echo "<br /><iframe id=\"thumb\" style=\"border:1px solid;height:250px;width:900px\" src=\"index.php?theme=$themetoedit\"></iframe>";
//		echo "<br /><img alt=\"\" style=\"vertical-align:middle\" src=\"images/left.png\" /> <a href=\"?mod={$_FN['mod']}&amp;opt=$opt&amp;themetoedit=$themetoedit\">".FN_i18n("back")."</a>";
		echo "<div style=\"text-align:right\"><img border=\"\" alt=\"\" style=\"vertical-align:middle\"src=\"".FN_FromTheme("images/modify.png")."\" />&nbsp;";
		if (file_exists("themes/$themetoedit/structure.php"))
			echo "<a href=\"?mod={$_FN['mod']}&amp;opt=$opt&amp;themetoedit=$themetoedit&amp;edit=structure.php\">".FN_i18n("modify")." <b>\"$themetoedit\"</b> (structure.php, ".FN_i18n("for advanced users only").")</a><br />";
		if (file_exists("themes/$themetoedit/template.tp.html"))
			echo "<a href=\"?mod={$_FN['mod']}&amp;opt=$opt&amp;themetoedit=$themetoedit&amp;edit=template.tp.html\">".FN_i18n("modify")." <b>\"$themetoedit\"</b> (index.tp.html, ".FN_i18n("for advanced users only").")</a>";
	
		echo "</div>";	
		}
	if (file_exists("themes/$themetoedit/config.php"))
	{
		echo "\n<fieldset>";
		echo "<legend>".FN_Translate("settings")." '$themetoedit'</legend>";
		FN_EditConfFile("themes/{$theme}/config.php","?mod={$_FN['mod']}&amp;opt=$opt&amp;themetoedit=$themetoedit",""
		);
		echo "</fieldset>";
	}
}
function get_list_themes()
{
	$handle = opendir("themes/");
	$list_themes = array();
	while(false !== ($file = readdir($handle)))
	{
		if (!($file == "." or $file == "..") and is_dir("themes/$file"))
		{
			array_push($list_themes,$file);
		}
	}
	closedir($handle);
	natsort($list_themes);
	return $list_themes;
}

?>