<?php
/**
 * @package Flatnux_module_login
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 1011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
defined('_FNEXEC') or die('Restricted access');
require_once("modules/login/functions_login.php");
global $_FN;
if ($_FN['user'] == "")
{
    FN_LoginForm();
}
else
{
    echo "<div  class=\"fnlogin\" style=\"text-align:center\">";
    echo "<a href=\"" . FN_RewriteLink("index.php?mod=login&amp;op=editreg") . "\">{$_FN['user']}</a><br />";
    echo FN_HtmlUserImage($_FN['user']);
    echo "<br /><br />";
    FN_LogoutForm();
    echo "</div>";
}
?>