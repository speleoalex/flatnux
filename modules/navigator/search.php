<?php

/**
 * @package Flatnux_module_news
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 *
 */
defined('_FNEXEC') or die('Restricted access');

/**
 *
 * @global array $_FN
 * @param array $tosearch_array
 * @param string $method
 * @param array $sectionvalues
 * @param int $maxres
 * @return array 
 */
function FNSEARCH_module_navigator($tosearch_array,$method,$sectionvalues,$maxres)
{
    global $_FN;
    $results=array();
    $section_to_search=$sectionvalues['id']; // current section 
    $config=FN_LoadConfig("modules/navigator/config.php",$sectionvalues['id']); //load config in section
    $tablename=$config['tables'];
    $Table=FN_XmlTable($tablename);
    $DB=new XMLDatabase($_FN['database'],$_FN['datadir']);
    //--search query ---------------------------------------------------------->
    $query="SELECT * FROM $tablename WHERE  ";
    $tmpmethod="";
    foreach($Table->fields as $fieldstoread=> $fieldvalues)
    {
        if ($fieldstoread!="insert"&&$fieldstoread!="update"&&$fieldstoread!="unirecid"&&$fieldvalues->type!="check")
        {
            //dprint_r($fieldvalues);
            if (isset($fieldvalues->frm_show)&&$fieldvalues->frm_show=="0")
            {
                continue;
            }
            if (isset($fieldvalues->frm_show)&&$fieldvalues->frm_show=="0")
            {
                continue;
            }

            foreach($tosearch_array as $f)
            {
                if ($f!="")
                {
                    $query.=" $tmpmethod ".$fieldstoread." LIKE '%".addslashes($f)."%' ";
                    $tmpmethod=$method;
                }
            }
            $tmpmethod=" OR ";
        }
    }
    if ((isset($viewonlycreator)&&$viewonlycreator==1)&&!FN_IsAdmin())
    {
        $query.=" AND username = '".$_FN['user']."'";
    }
    $query.=" LIMIT 1,$maxres";
    //--search query ----------------------------------------------------------<
    $records=$DB->Query("$query");
    $cont=0;
    if (is_array($records))
    {
        foreach($records as $data)
        {
            $link=FN_RewriteLink("index.php?mod=$section_to_search&amp;op=view&id={$data['unirecid']}");
            $results[$cont]['link']=$link;
            $results[$cont]['title']=$sectionvalues['title'].(isset($data[$config['titlefield']]) ? ": ".$data[$config['titlefield']] : "");
            $results[$cont]['text']="";
            if (!empty($data['description']))
                $results[$cont]['text']=substr(strip_tags($data['description']),0,100);
            $cont++;
        }
    }
    return $results;
}

?>
