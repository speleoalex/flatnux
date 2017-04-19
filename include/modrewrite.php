<?php
/**
 * @package Flatnux
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 1011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */

global $_FN;
if (!empty($_FN['links_mode']) && file_exists("include/mod_rewrite/{$_FN['links_mode']}/modrewrite.php"))
{
    require_once ("include/mod_rewrite/{$_FN['links_mode']}/modrewrite.php");
}
else
{
    /**
     * 
     */
    function FN_BuildHtaccess()
    {
        
    }

    /**
     *
     * @global type $_FN
     * @param string $href
     * @param type $sep
     * @param type $full
     * @return string 
     */
    function FN_RewriteLink($href,$sep = "",$full = false)
    {
        global $_FN;
        if ($sep == "")
        {
            if (fn_erg("&amp;",$href))
            {
                $sep = "&amp;";
            }
            else
            {
                $sep = "&";
            }
        }
        if ($full)
        {
            $href = $_FN['siteurl'].$href;
        }
        return $href;
    }

}
?>