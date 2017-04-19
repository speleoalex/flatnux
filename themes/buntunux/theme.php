<?php
/**
 * @package Flatnux_theme_buntunux
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
defined('_FNEXEC') or die('Restricted access');
/**
 *
 * @param string $sectionroot
 * @return string 
 */
function MyHtmlMenu($sectionroot=false)
{
    global $_FN;
    $menu = array();
    $sections = FN_GetSections($sectionroot);
    if (count($sections) == 0)
    {
        if ($sectionroot != "")
        {
            $section = FN_GetSectionValues($sectionroot);
            if (!empty($section['parent']))
            {
                $sections = FN_GetSections($section['parent']);
            }
        }
    }
    foreach ($sections as $section)
    {
        $accesskey = FN_GetAccessKey($section['title'], "index.php?mod={$section['id']}", $section['accesskey']);
        if ($accesskey != "")
            $accesskey = " accesskey=\"$accesskey\"";
        $description = htmlentities($section['description']);
        if ($section['id'] == $_FN['mod'])
            $menu[] = "<a $accesskey class=\"current\" href=\"{$section['link']}\" title=\"$description\" >{$section['title']}</a>";
        else
            $menu[] = "<a $accesskey href=\"{$section['link']}\" title=\"$description\" >{$section['title']}</a>";
    }
    $ret = "";
    if (count($menu) > 0)
        $ret = "<li>" . implode("</li><li>", $menu) . "</li>";
    return $ret;
}


function FN_HtmlMyLoginForm()
{
	global $_FN;
	$html = "";
	$querystring = FN_GetParam("QUERY_STRING", $_SERVER);
	$html.= "<form method=\"post\" action=\"?$querystring&amp;fnlogin=login\" >";
        $getuser = FN_GetParam("username", $_GET,"html");
        $onclick="";
        $title = ( !empty($_FN['username_is_email'])) ? FN_i18n("email") : FN_i18n("username");
        if ($getuser == "")
        {
            $getuser = $title;
            $onclick = "onfocus=\"this.value=''\"";
        }
        $getpassword = FN_GetParam("password", $_GET,"html");
        $titlepassword = FN_i18n("password");
        $valuepassword = "";
        if ($getpassword == "")
        {
            $onclickpassword = "onfocus=\"this.value=''\"";
            $valuepassword = "value=\"$titlepassword\"";
        }
        
	$html.= "<input  title=\"$title\" $onclick type=\"text\" size=\"15\" name=\"username\" value=\"$getuser\"/> ";
	$html.= " <input title=\"$titlepassword\" $onclickpassword type=\"password\" size=\"15\" name=\"password\" $valuepassword />";
	$html.= "<button type=\"submit\" >" . FN_i18n("login") . "</button>";
	$html.= "</form>";
	$fnlogin = FN_GetParam("fnlogin", $_GET);
	$fnuser = FN_GetParam("username", $_POST);
	$fnpwd = FN_GetParam("password", $_POST);
	if ( $fnlogin == "login" && $fnuser != "" && $fnpwd != "" )
	{
		$lpass = md5($fnpwd);
		$us = FN_GetUser($fnuser);
		$passwd = $us['passwd'];
		if ( $passwd != $lpass )
		{
			$html.= " " . FN_i18n("authentication failure");
		}
	}
	return $html;
}
/**
 *
 * @global array $_FN
 */
function FN_HtmlMyLogoutForm()
{
	global $_FN;
	$querystring = FN_GetParam("QUERY_STRING", $_SERVER);
	$html = "<form method=\"post\" action=\"?$querystring&amp;fnlogin=logout\" >";
	$html.= "{$_FN['user']} : <button type=\"submit\">" . FN_i18n("logout") . "</button>";
	$html.= "</form>";
	return $html;
}
?>
