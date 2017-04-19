<?php
/**
 * @package Flatnux_theme_base
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
defined('_FNEXEC') or die('Restricted access');
global $_FN;
//@unlink ("misc/fndatabase/themes_base/settings.php");
$config = FN_LoadConfig("themes/{$_FN['theme']}/config.php");
//include ("themes/{$_FN['theme']}/config.php");
echo "<?xml version=\"1.0\"?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"{$_FN['lang']}\" xml:lang=\"{$_FN['lang']}\">
<head>
";
$bkimage = $config['background_image'] != "" ? "background-image:url('themes/{$_FN['theme']}/backgrounds/{$config['background_image']}')" : "";
$line_height = round($config['site_fontsize']*1.5);
echo "<style type=\"text/css\" >
body,td {
	color: {$config['page_text_color']};
	font-size:{$config['site_fontsize']};
	font-family:\"{$config['font']}\";
	line-height: {$line_height}px;
}
body{
	$bkimage
}
form{
	border:0px;
	padding:0px;
}
input{
	border:1px {$config['page_text_color']} inset;
	padding:1px;
	font-size:{$config['site_fontsize']};
	vertical-align: middle
}
textarea{
	border:1px {$config['page_text_color']} inset;
}
input[type=\"radio\"] { height:16px;border-style:none; }
input[type=\"checkbox\"] { height:16px;border-style:none; }
input[type=\"button\"] { border-style:outset; }
input[type=\"submit\"] { border-style:outset; }

button{
	border:1px #000080 outset; margin:1px;
	border-radius:5px;
}
#sitebody{
	background-color: {$config['background_color_body']};
}
#maintable{
	width:{$config['width_site']};
	border:{$config['border_size_site']} solid {$config['border_color_site']};
	margin: auto;
	margin-top:{$config['page_margin']};
	min-height:600px;
	padding:{$config['padding']};
	border-collapse: collapse;
	background-color: {$config['page_background_color']};
	color: {$config['page_text_color']};

}

#maintable a{
	color:{$config['page_link_color']};
	text-decoration: none
}
#maintable a:hover{
	text-decoration: underline
}
#header{
	color:{$config['header_text_color']};
	background-color: {$config['background_color_header']};
	text-align: {$config['header_text_align']};
	height:40px;
	font-size: 30px;
}
#topmenu{
	color:{$config['topmenu_text_color']};
	background-color: {$config['topmenu_background_color']};
	text-align: {$config['topmenu_text_align']};
	text-decoration: none;
	padding:{$config['padding']};

}

#topmenu a{
	color:{$config['topmenu_link_color']};
}

#top{
	background-color: {$config['top_background_color']};
	border-bottom:{$config['border_size_site']} solid {$config['top_separator_color']};
	padding:{$config['padding']};
}

#left{
	border-right:{$config['border_size_site']} solid {$config['left_separator_color']};
	background-color: {$config['left_background_color']};
	width:{$config['width_left']};
	padding:{$config['padding']};
}

#right{
	border-left:{$config['border_size_site']} solid {$config['right_separator_color']};
	background-color: {$config['right_background_color']};
	width:{$config['width_right']};
	padding:{$config['padding']};
}

#centerpage{
	color: {$config['page_text_color']};
	background-color: {$config['page_background_color']};
	padding:{$config['padding']};
	text-align:{$config['page_text_align']};
}
#centerpage a{
	color: {$config['page_link_color']};
}

#bottom{
	border-top:{$config['border_size_site']} solid {$config['bottom_separator_color']};
	background-color: {$config['bottom_background_color']};
	padding:{$config['padding']};
}

.block{
	margin-top:4px;
	background-color: transparent;
	border:{$config['block_border_size']} solid {$config['block_border_color']};
}

.block .blocktitle{
	background-color: {$config['block_header_background_color']};
	color:{$config['block_header_text_color']};
	padding:3px;
}

.block .blockcontents{
	background-color: {$config['block_body_background_color']};
	color:{$config['block_body_text_color']};
	padding:3px;
}
.block .blockcontents a{
	color:{$config['block_link_text_color']};
	padding:3px;
}



#footer{
	color: {$config['footer_text_color']};
	background-color: {$config['footer_background_color']};
	text-align: {$config['footer_text_align']};
	border-top:{$config['block_border_size']} solid {$config['footer_separator_color']};
	padding:{$config['padding']};
}

#footer a{
	color: {$config['footer_link_color']};
}

</style>
";
echo FN_HtmlHeader();
$colspan = 1;
if ( $config['show_blocks_left'] )
	$colspan++;

if ( $config['show_blocks_right'] )
	$colspan++;

echo "</head>\n<body id=\"sitebody\">";
echo "\n\t<table id=\"maintable\">";
echo "\n\t\t<tr>\n\t\t\t<td id=\"header\" colspan=\"$colspan\">{$_FN['site_title']}</td>\n\t</tr>";
echo "\n\t<tr>\n\t\t<td id=\"topmenu\" colspan=\"$colspan\">";
echo FN_HtmlMenu("&nbsp;|&nbsp;");
echo "\n\t</td>
</tr>";

if ( $config['show_blocks_top'] )
{
	echo "\n\t\t<tr>\n\t\t\t<td id=\"top\" colspan=\"$colspan\">";
	echo FN_HtmlBlocks("top");
	echo "</td>\n\t</tr>";
}

echo "<tr>
";
if ( $config['show_blocks_left'] )
{
	echo "
	<td id=\"left\" valign=\"top\">";
	echo FN_HtmlBlocks("left");
	echo "
	</td>
";
}



echo "
	<td  id=\"centerpage\"  valign=\"top\">";

if ($_FN['sectionvalues']['parent']!="")
{	
	echo FN_HtmlNavbar()."<br />";
}
echo FN_HtmlSection();
echo "
	</td>
";
if ( $config['show_blocks_right'] )
{
	echo "
	<td  id=\"right\"  valign=\"top\">";
	echo FN_HtmlBlocks("right");
	echo "
	</td>
</tr>
";
}

if ( $config['show_blocks_bottom'] )
{
	echo "
<tr>
	<td  id=\"bottom\" colspan=\"$colspan\">";
	echo FN_HtmlBlocks("bottom");
	echo "
</td></tr>";
}
echo "<tr><td id=\"footer\" colspan=\"$colspan\">" . FN_HtmlCredits()
 . "
<br />Page generated in " . FN_GetExecuteTimer() . " seconds.
	</td>
</tr></table>";
echo "</body>
</html>
";
?>