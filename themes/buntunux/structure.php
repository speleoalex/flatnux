<?php
/**
 * @package Flatnux_theme_buntunux
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
defined('_FNEXEC') or die('Restricted access');
global $_FN;
$config = FN_LoadConfig("themes/{$_FN['theme']}/config.php");
echo "<?xml version=\"1.0\"?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\">";
echo "<head>";
echo FN_HtmlHeader();
echo "</head>";
echo "\n<body>";
echo "<div id=\"menutop\">";
echo "<ul>";
echo MyHtmlMenu();
echo "</ul>";
if ($config['loginform'])
{
    echo "<div id=\"toploginform\">";
    if ($_FN['user'] == "")
        echo FN_HtmlMyLoginForm();
    else
        echo FN_HtmlMyLogoutForm();
    echo "</div>";
}
echo "<div id=\"logo\">";
echo $_FN['site_title'] . "<br />\n";
echo "<em>" . $_FN['site_subtitle'] . "</em>";
echo "</div>";
echo "</div>\n";


$html = MyHtmlMenu($_FN['mod']);

if ($html)
{
    echo "<div id=\"submenutop\">";
    echo "<ul>";
    echo $html;
    echo "</ul>";
    echo "</div>\n";
}
if ($config['show_blocks_top'])
{
    echo "<div id=\"blocks_top\">";
    echo FN_HtmlBlocks("top");
    echo "</div>\n";
}
echo "<div id=\"section\">";
echo "<table width=\"100%\">
    <tr>";

if ($config['show_blocks_left'])
{
    echo "<td width=\"160\" valign=\"top\" >";
    echo "<div id=\"blocks_left\">";
    echo FN_HtmlBlocks("left");
    echo "</div>\n";
    echo "</td>";
}

echo " <td valign=\"top\" >";
echo "<div id=\"contents\">";
echo FN_HtmlSection();
echo "</div>\n";
echo "</td>";

if ($config['show_blocks_right'])
{
    echo "<td width=\"160\" valign=\"top\" >";
    echo "<div id=\"blocks_right\">";
    echo FN_HtmlBlocks("right");
    echo "</div>\n";
    echo "</td>";
}
echo "</tr></table>";

echo "</div>";
if ($config['show_blocks_bottom'])
{
    echo "<div id=\"blocks_bottom\">";
    echo FN_HtmlBlocks("bottom");
    echo "</div>\n";
}

echo "</body>
</html>
";
?>
