<?php

global $_FN;
$config = FN_LoadConfig("themes/{$_FN['theme']}/config.php");
$_FN['show_search_form'] = $config['show_search_form'];

/**
 * 
 * @global type $_FN
 * @param type $section
 * @return string
 */
function FN_GetSectionProprieties($section)
{
    global $_FN;
    $section = FN_GetSectionValues($section);
    $section['menuclass'] = "arrow-right";
    if ($section['type'] == 'login')
    {
        $section['menuclass'] = "user";
    }
    if ($section['type'] == 'news')
    {
        $section['menuclass'] = "rss";
    }
    if ($section['type'] == 'search')
    {
        $section['menuclass'] = "search";
    }
    if ($section['id'] == 'sitemap')
    {
        $section['menuclass'] = "list";
    }

    if ($section['id'] == $_FN['home_section'])
    {
        $section['menuclass'] = "home";
    }
    return $section;
}

?>
