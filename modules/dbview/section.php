<?php
/**
 * @package Flatnux_module_dbview
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
#<fnmodule>dbview</fnmodule>
defined('_FNEXEC') or die('Restricted access');
global $_FN;
//extra editor params 
$_FN['modparams'][$_FN['mod']]['editorparams']=isset($_FN['modparams'][$_FN['mod']]['editorparams']) ? $_FN['modparams'][$_FN['mod']]['editorparams'] : array();
require_once "modules/dbview/functions_section.php";
require_once "modules/dbview/functions_view.php";
if (file_exists("sections/{$_FN['mod']}/custom_functions.php"))
    require_once "sections/{$_FN['mod']}/custom_functions.php";

$file=FN_GetParam("file",$_GET,"flat");



if ((false=== strpos($file,"..")) && $file!= "" && file_exists("sections/{$_FN['mod']}/$file"))
{
    include "sections/{$_FN['mod']}/$file";
}
else
{
    FNDBVIEW_Init();
    $unirecid=FN_GetParam("id",$_GET,"html");
    $op=FN_GetParam("op",$_GET,"html");
    $downloadfile=FN_GetParam("downloadfile",$_GET);
    $mode=FN_GetParam("mode",$_GET);
//-------------------------------config---------------------------------------->
    $config=FN_LoadConfig();
    $tablename=$config['tables'];
    $recordsperpage=$config['recordsperpage'];
    $config['search_orders']=explode(",",$config['search_orders']);
    $config['search_options']=explode(",",$config['search_options']);
    $config['navigate_groups']=explode(",",$config['navigate_groups']);
    $config['search_fields']=explode(",",$config['search_fields']);
    $config['search_partfields']=explode(",",$config['search_partfields']);
//-------------------------------config----------------------------------------<

    if ($mode== "go")
    {
        FNDBVIEW_GoDownload($downloadfile);
        exit();
    }
    $html="";
    require_once $_FN['filesystempath']."/include/xmldb_frm_search.php";
    $Table=FN_XmlForm($tablename);

    $Search=new xmldb_searchform($_FN['database'],$tablename,$_FN['datadir'],$_FN['lang'],$_FN['languages'],false);
    $html.=$Search->HtmlSearchForm();
    if ($config['enable_permissions_each_records'])
    {
        if (!isset($Table->formvals['groupview']))
        {
            $field=array();
            $field['name']='groupview';
            $field['frm_i18n']='limits the display of the content in these groups';
            $field['foreignkey']='fn_groups';
            $field['fk_link_field']='groupname';
            $field['fk_show_field']='groupname';
            $field['frm_type']='multicheck';
            $field['type']='string';
            addxmltablefield($_FN['database'],$tablename,$field,$_FN['datadir']);
        }
        $Table->formvals['groupview']['frm_show']=1;
        if ($config['permissions_records_groups']!= "")
        {
            $allAllowedGroups=explode(",",$config['permissions_records_groups']);
            if (isset($Table->formvals['groupview']['options']))
            {
                foreach($Table->formvals['groupview']['options'] as $k=> $v)
                {
                    if (!in_array($v['value'],$allAllowedGroups))
                    {
                        unset($Table->formvals['groupview']['options'][$k]);
                    }
                }
            }
        }
    }
    else
    {
        if (isset($Table->formvals['groupview']))
        {
            $Table->formvals['groupview']['frm_show']=0;
        }
    }


    if ($config['enable_permissions_edit_each_records'])
    {
        if (!isset($Table->formvals['groupinsert']))
        {
            $field=array();
            $field['name']='groupinsert';
            $field['frm_i18n']='limits the edit of the content to these groups';
            $field['foreignkey']='fn_groups';
            $field['fk_link_field']='groupname';
            $field['fk_show_field']='groupname';
            $field['frm_type']='multicheck';
            $field['type']='string';
            $field['frm_setonlyadmin']='1';
            $field['frm_allowupdate']='onlyadmin';
            $field['type']='string';
            addxmltablefield($_FN['database'],$tablename,$field,$_FN['datadir']);
        }
        if (!empty($config['groupadmin']) && FN_UserInGroup($_FN['user'],$config['groupadmin']))
        {
            $Table->formvals['groupinsert']['frm_show']=1;
            unset($Table->formvals['groupinsert']['frm_setonlyadmin']);
            $Table->formvals['groupinsert']['frm_allowupdate']="";
        }

        if ($config['permissions_records_edit_groups']!= "")
        {
            $allAllowedGroups=explode(",",$config['permissions_records_edit_groups']);
            if (isset($Table->formvals['groupinsert']['options']))
            {
                foreach($Table->formvals['groupinsert']['options'] as $k=> $v)
                {
                    if (!in_array($v['value'],$allAllowedGroups))
                    {
                        unset($Table->formvals['groupinsert']['options'][$k]);
                    }
                }
            }
        }
    }
    else
    {
        if (isset($Table->formvals['groupinsert']))
        {
            $Table->formvals['groupinsert']['frm_show']=0;
        }
    }
//-----------------------------principale ------------------------------------->
    if (FNDBVIEW_CanViewRecords())
    {
        switch($op)
        {
            case "history" :
                $shownavigatebar=true;
                if ($config['enable_history'])
                    $html.=FNDBVIEW_ViewRecordHistory($unirecid,false,$shownavigatebar); // visualizza la pagina col record
                break;
            case "view" :
                $shownavigatebar=true;
                if (isset($_GET['embed']))
                    $shownavigatebar=false;
                if (isset($_GET['inner']))
                    $shownavigatebar=false;

                $html.=FNDBVIEW_ViewRecordPage($unirecid,false,$shownavigatebar); // visualizza la pagina col record
                break;
            case "writecomment" :
                $html.=FNDBVIEW_WriteComment($unirecid);
                break;
            case "request" :
                $html.=FNDBVIEW_Request($unirecid);
                break;
            case "edit" :
                $html.=FNDBVIEW_EditRecordForm($unirecid,$Table); // form edita record
                if (file_exists("sections/{$_FN['mod']}/bottom_edit.php"))
                {
                    include ("sections/{$_FN['mod']}/bottom_edit.php");
                }

                break;
            case "new" :
                if (isset($_POST['xmldbsave']))
                {
                    $html.=FNDBVIEW_InsertRecord($Table);
                    $html.=FNDBVIEW_WriteSitemap();
                }
                else
                {
                    $html.=FNDBVIEW_NewRecordForm($Table); //  form nuovo record
                }
                break;
            case "users" :
                $html.=FNDBVIEW_UsersForm($unirecid); //  form nuovo record
                break;
            case "admingroups" :
                $html.=FNDBVIEW_AdminPerm($unirecid); //  permessi records
                break;
            case "delcomment" :
                $html.=FNDBVIEW_DelComment($unirecid); //  form nuovo record
                break;
            case "del" :
                $html.=FNDBVIEW_DelRecordForm($unirecid); //  form nuovo record
                break;
            case "updaterecord" :
                if (count($_POST)== 0 || isset($_POST['__NOSAVE']) || !isset($_POST['xmldbsave']))
                {
                    $html.=FNDBVIEW_EditRecordForm($unirecid,$Table);
                }
                else
                {
                    $html.=FNDBVIEW_UpdateRecord($Table); // esegue aggiornamento record
                }
                break;
            case "insertrecord" : // esegue inserimento record
                break;
            case "updatesitemap" :
                FNDBVIEW_WriteSitemap();
                dprint_xml(file_get_contents("sitemap-$tablename.xml"));
                dprint_xml(file_get_contents("index-$tablename.html"));
                break;
            default :
                if (FN_IsAdmin() && isset($_GET["refresh_rss"]))
                {
                    FNDBVIEW_GenerateRSS();
                }

                $html.=FN_HtmlContent("sections/{$_FN['mod']}");
                $html.=FNDBVIEW_ViewGrid(); //griglia con tutti i records
                ob_start();
                if (file_exists("sections/{$_FN['mod']}/bottom.php"))
                {
                    include ("sections/{$_FN['mod']}/bottom.php");
                }
                $html.=ob_get_clean();


                if (FN_IsAdmin() && $config['permissions_records_groups'] && $config['enable_permissions_each_records'])
                {
                    $l=FN_RewriteLink("index.php?mod={$_FN['mod']}&amp;op=admingroups");
                    $html.="<div><br /><a href=\"$l\">".FN_Translate("access control")."</a></div>";
                }

                break;
        }
    }
    else
    {
        $html=FN_i18n("you are not authorized to view the data");
    }
    echo $html;
//-----------------------------principale -------------------------------------<
}

/**
 *
 */
function FNDBVIEW_ViewGrid()
{
    global $_FN;
    //--config-->
    $config=FN_LoadConfig();
    $tablename=$config['tables'];
    $search_fields=$config['search_fields']!= "" ? explode(",",$config['search_fields']) : array();
    $search_partfields=$config['search_fields']!= "" ? explode(",",$config['search_partfields']) : array();
    $search_orders=$config['search_orders']!= "" ? explode(",",$config['search_orders']) : array();
    $navigate_groups=$config['navigate_groups']!= "" ? explode(",",$config['navigate_groups']) : array();
    $search_options=$config['search_options']!= "" ? explode(",",$config['search_options']) : array();
    $search_min=$config['search_min']!= "" ? explode(",",$config['search_min']) : array();
    //--config--<
    $recordsperpage=FN_GetParam("rpp",$_GET);
    if ($recordsperpage== "")
        $recordsperpage=$config['recordsperpage'];
    if (file_exists("sections/{$_FN['mod']}/top.php"))
    {
        include ("sections/{$_FN['mod']}/top.php");
    }
    $p=FN_GetParam("p",$_GET);
    $op=FN_GetParam("op",$_GET);
    $navigate=1;
    $results=FNDBVIEW_GetResults($config);
    ob_start();
    if (file_exists("sections/{$_FN['mod']}/grid_header.php"))
    {
        include("sections/{$_FN['mod']}/grid_header.php");
    }
    $tplvars['html_header']=ob_get_clean();
    $tplvars['html_categories']="";
    //----------------barra si navigazione categorie--------------------------->
    $tplvars['categories']=array();
    if ($config['default_show_groups'])
    {
        $categories=FNDBVIEW_Navigate($results,$navigate_groups);
        $tplvars['categories']=$categories['filters'];
        //dprint_r($tplvars['categories']);
    }
    //----------------barra si navigazione categorie---------------------------<
    //-----------------------pagina con i risultati---------------------------->
    $tplvars['html_export']="";
    if (!empty($config['enable_export']))
    {
        $tplvars['html_export']="<a href=\"".FNDBVIEW_MakeLink(array("export"=>1),"&amp;")."\">".FN_Translate("export to csv")."</a>";
    }
    //  dprint_r($tplvars['categories']);



    if (FNDBVIEW_CanAddRecord())
    {
        $link=FNDBVIEW_MakeLink(array("op"=>"new"),"&");
        $tplvars['url_addnew']=$link;
    }
    $tplvars['html_footer']="";
    if (file_exists("sections/{$_FN['mod']}/grid_footer.php"))
    {
        include("sections/{$_FN['mod']}/grid_footer.php");
        $tplvars['html_footer'].=ob_get_clean();
    }
    if (isset($_GET['debug']))
    {
        dprint_r(__FILE__." ".__LINE__." : ".FN_GetExecuteTimer());
    }
    $searchform=FNDBVIEW_GetSearchForm($search_orders,$tablename,$search_options,$search_min,$search_fields,$search_partfields);
    $tplvars=array_merge($tplvars,$searchform);

    $res=FNDBVIEW_PrintList($results,$tplvars);
    if (isset($_GET['debug']))
    {
        dprint_r(__FILE__." ".__LINE__." : ".FN_GetExecuteTimer());
    }

    return $res;
    //-----------------------pagina con i risultati----------------------------<
}

/**
 *
 * @global type $_FN
 * @param type $results
 * @param type $groups 
 */
function FNDBVIEW_Navigate($results,$groups)
{

    global $_FN;
    $return=array();
    //--config-->
    $config=FN_LoadConfig();
    $tablename=$config['tables'];
    //--config--<
    $gresults=array();
    $Table=FN_XmlForm($tablename);

    //----foreign key ---->
    $i=0;
    if (is_array($results))
        foreach($results as $data)
        {
            //$data = $Table->xmltable->GetRecordByPrimaryKey($item[$Table->xmltable->primarykey]);
            foreach($groups as $group)
            {
                if (isset($Table->formvals[$group]['fk_show_field']))
                {
                    $fs=$Table->formvals[$group]['fk_show_field'];
                }
                //echo "$group ";
                if ($group!= "" && isset($data[$group]))
                    $gresults[$group][$data[$group]]=isset($gresults[$group][$data[$group]]) ? $gresults[$group][$data[$group]] + 1 : 1;
                $i++;
            }
        }
    //$return['gresults']=$gresults;
    $ret_groups=array();
    foreach($gresults as $groupname=> $group)
    {
        $fk=$Table->xmltable->fields[$groupname]->foreignkey;
        if (isset($Table->formvals[$groupname]['fk_link_field']))
            $pklink=$Table->formvals[$groupname]['fk_link_field'];
        else
            $pklink="";
        if ($fk!= "" && file_exists("{$_FN['datadir']}/{$_FN['database']}/$fk.php"))
        {

            $tablegroup=xmldb_table($_FN['database'],$fk,$_FN['datadir']);
        }
        $tplvars['filtertitle']=$Table->formvals[$groupname]['title'];
        $tplvars['urlremovefilter']="";
        if (isset($_GET["nv_$groupname"]))
        {
            $link=FNDBVIEW_MakeLink(array("nv_$groupname"=>null,"page"=>1));
            $tplvars['urlremovefilter']=$link;
        }
        else
        {
            $tplvars['urlremovefilter']=false;
        }
        $group2=array();
        foreach($group as $groupcontentsname=> $groupcontentsnums)
        {
            $tmp['total']=$groupcontentsnums;
            $tmp['name']=$groupcontentsname;
            $group2[]=$tmp;
        }
        $group2=FN_ArraySortByKey($group2,"name");
        foreach($group2 as $group)
        {
            $groupcontentsnums=$group['total'];
            $groupcontentsname=$group['name'];
            if ($groupcontentsname== "")
                $groupcontentstitle=FN_Translate("---");
            else
            {
                if ($pklink!= "")
                {
                    $restr=array($pklink=>$group['name']);
                    $t=$tablegroup->GetRecord($restr);
                    $ttitles=$groupname;
                    if (isset($Table->xmltable->fields[$groupname]->fk_show_field))
                        $ttitles=explode(",",$Table->xmltable->fields[$groupname]->fk_show_field);
                    $groupcontentstitle="";
                    $sep="";
                    foreach($ttitles as $tt)
                    {
                        if (isset($t[$tt]) && $t[$tt]!= "")
                        {
                            $groupcontentstitle.=$sep.$t[$tt];
                            $sep=" &bull; ";
                        }
                    }
                    if ($groupcontentstitle== "")
                        $groupcontentstitle=$group['name'];
                }
                else
                    $groupcontentstitle=$group['name'];
            }
            $link=FNDBVIEW_MakeLink(array("nv_$groupname"=>"$groupcontentsname","page"=>1));
            $tplvars['urlfilteritem']=$link;
            $tplvars['titleitem']=$groupcontentstitle;
            $tplvars['counteritem']=$groupcontentsnums;

            $ret_groups[$groupname]['groups'][$groupcontentsname]=$tplvars;
            foreach($tplvars as $k=> $v)
            {
                $ret_groups[$groupname][$k]=$v;
            }
//            $ret_groups[$groupname]['groups'][$group['name']]['items']=$tplvars;
            //$ret_groups[$groupname]['vals'][]=$tplvars;
            //array("group"=>$group,"vals"=>$tplvars);
        }
    }
    $return['filters']=array();
    $return['filters']=$ret_groups;
    return $return;
}

/**
 * 
 * @param string $tablename
 * @param string $res
 */
function FNDBVIEW_HtmlItem($tablename,$pk)
{
    global $_FN;
    //--config-->
    $config=FN_LoadConfig();
    $titles=explode(",",$config['titlefield']);
    //--config--<
    $tplvars=array();
    $Table=FN_XmlForm($tablename);
    $data=$Table->GetRecordTranslatedByPrimarykey($pk,false);
    //-----image----------------------->
    $photo=isset($data[$config['image_titlefield']]) ? $Table->xmltable->getFilePath($data,$config['image_titlefield']) : "";
    $photo_fullsize=isset($data[$config['image_titlefield']]) ? $_FN['siteurl'].$Table->xmltable->getFilePath($data,$config['image_titlefield']) : "";

    if ($photo!= "")
    {
//        $photo="{$_FN['datadir']}/fndatabase/{$tablename}/{$data[$Table->xmltable->primarykey]}/{$config['image_titlefield']}/{$data[$config['image_titlefield']]}";
    }
    elseif (file_exists("sections/{$_FN['mod']}/default.png"))
    {
        $photo="sections/{$_FN['mod']}/default.png";
    }
    else
        $photo="modules/dbview/default.png";
    if (empty($config['image_size']))
        $config['image_size']=200;
    if (file_exists("thumb.php"))
        $img="{$_FN['siteurl']}thumb.php?format=png&h={$config['image_size']}&w={$config['image_size_h']}&f=".$photo;
    else
        $img="$photo";

    $counteritems=0;
    //-----image-----------------------<
    $tplvars['item_urlview']=FNDBVIEW_MakeLink(array("op"=>"view","id"=>$pk),"&amp;");
    $tplvars['item_urledit']=FNDBVIEW_MakeLink(array("op"=>"edit","id"=>$pk),"&amp;");
    $tplvars['item_urldelete']=FNDBVIEW_MakeLink(array("op"=>"del","id"=>$pk),"&amp;");
    $tplvars['item_urlimage']=$img;
    $tplvars['item_urlimage_fullsize']=$photo_fullsize;

    $dettlink=FNDBVIEW_MakeLink(array("op"=>"view","id"=>$pk),"&amp;");

    //----title-------------------------------->
    $titlename="";
    foreach($titles as $titleitem)
        if (isset($data[$titleitem]))
        {
            if (!empty($Table->formvals[$titleitem]['fk_link_field']))
            {
                $titlename.="{$data[$titleitem]}&nbsp;";
            }
            else
            {
                $titlename.="{$data[$titleitem]}&nbsp;";
            }
        }
        else
        {
            foreach($data as $tv)
            {
                $titlename=$tv;
                break;
            }
            $titlename=isset($titlename[1]) ? $titlename[1] : "";
        }
    $tplvars['item_title']=FN_FixEncoding($titlename);
    //----title--------------------------------<
    //-------------------------------valori----------------------------------->
    $row=$data;
    $t=FN_XmlForm($tablename);
    $colsuffix="1";
    $itemvalues=array();
    foreach($Table->formvals as $fieldform_valuesk=> $field) // $fieldform_valuesk=> $fieldform_values
    {

        if (isset($field['frm_showinlist']) && $field['frm_showinlist']!= 0)
            if (isset($row[$field['name']]) && $row[$field['name']]!= "")
            {
                $counteritems++;
                $fieldform_values=$field;
                $multilanguage=false;
                $view_value="";

                //--------------get value from frm----------------------------->
                $languagesfield="";
                if (isset($fieldform_values['frm_multilanguages']) && $fieldform_values['frm_multilanguages']!= "")
                {
                    $multilanguage=true;
                    $languagesfield=explode(",",$fieldform_values['frm_multilanguages']);
                }
                $fieldform_values['name']=$fieldform_valuesk;
                $fieldform_values['messages']=$Table->messages;
                $fieldform_values['value']=XMLDB_FixEncoding($row[$fieldform_valuesk],$_FN['charset_page']);
                $fieldform_values['values']=$row;
                $fieldform_values['fieldform']=$Table;
                $fieldform_values['oldvalues']=$row;
                $fieldform_values['oldvalues_primarikey']=$pk;
                $fieldform_values['multilanguage']=$multilanguage;
                $fieldform_values['lang_user']=$_FN['lang'];
                $fieldform_values['lang']=$Table->lang;
                $fieldform_values['languagesfield']=$languagesfield;
                $fieldform_values['frm_help']=isset($fieldform_values['frm_help']) ? $fieldform_values['frm_help'] : "";
                $row[$field['name']]=html_entity_decode($row[$field['name']]);

                if (isset($fieldform_values['frm_functionview']) && $field['frm_functionview']!= "" && function_exists($field['frm_functionview']))
                {
                    eval("\$view_value = ".$field['frm_functionview'].'($data,$fieldform_valuesk);');
                    $showfield=false;
                }
                else
                {
                    $fname="xmldb_frm_view_".$field['frm_type'];
                    if (function_exists($fname))
                    {
                        $view_value=$fname($fieldform_values);
                    }
                    elseif (method_exists($Table->formclass[$fieldform_valuesk],"view"))
                    {
                        $view_value=$Table->formclass[$fieldform_valuesk]->view($fieldform_values);
                    }
                    else
                    {
                        $view_value=$data[$field['name']];
                    }
                }
                //--------------get value from frm-----------------------------<
                $itemvalues[]=array("title"=>$field['title'],"value"=>$view_value,"fieldtype"=>$field['frm_type'],"fieldname"=>$fieldform_valuesk);
                $tplvars['viewvalue_'.$field['name']]=$view_value;
                $tplvars['title_'.$field['name']]=$field['title'];
            }
    }
    $tplvars['itemvalues']=$itemvalues;
    //-------------------------------valori-----------------------------------<
    //-------------------------------footer----------------------------------->

    if (FNDBVIEW_IsAdminRecord($row,$tablename,$_FN['database']))
    {
        if (empty($config['enable_delete']))
        {
            $tplvars['item_urldelete']=false;
        }
    }
    else
    {
        $tplvars['item_urldelete']=false;
        $tplvars['item_urledit']=false;
    }
    if (file_exists("sections/{$_FN['mod']}/pdf.php"))
    {
        $tplvars['url_pdf']="{$_FN['siteurl']}pdf.php?mod={$_FN['mod']}&amp;id=$pk";
    }
    $tplvars['counteritems']="$counteritems";
    $counteritems++;
    $tplvars['counteritems_1']="$counteritems";
    $counteritems++;
    $tplvars['counteritems_2']="$counteritems";
    $counteritems++;
    $tplvars['counteritems_3']="$counteritems";

    //-------------------------------footer-----------------------------------<
    return $tplvars;
}

/**
 * 
 * @param unknown_type $results
 */
function FNDBVIEW_PrintList($results,$tplvars)
{
    global $_FN;
    //--config-->
    $config=FN_LoadConfig();
    $tablename=$config['tables'];
    $tplvars['items']=array();
    $tplvars['pages']=array();
    //--config--<
    $page=FN_GetParam("page",$_GET);
    $recordsperpage=FN_GetParam("rpp",$_GET);
    if ($recordsperpage== "")
        $recordsperpage=$config['recordsperpage'];
    //---template------>
    $tplfile=file_exists("sections/{$_FN['mod']}/list.tp.html") ? "sections/{$_FN['mod']}/list.tp.html" : FN_FromTheme("modules/dbview/list.tp.html",false);
    $templateString=file_get_contents($tplfile);
    $tplbasepath=dirname($tplfile)."/";
    //---template------<
    $t=FN_XmlForm($tablename);
    {
        if (is_array($results) && ($c=count($results)) > 0)
        {
            //---------------------calcolo paginazione -------------------->
            if ($page== "")
                $page=1;
            $num_records=count($results);
            $numPages=ceil($num_records / $recordsperpage);
            $start=($page * $recordsperpage - $recordsperpage) + 1;
            $end=$start + $recordsperpage - 1;

            if ($end > $num_records)
                $end=$num_records;
            //---------------------calcolo paginazione --------------------<
            //---------------------tabella paginazione -------------------->
            $tpl_vars=array();
            $tp_str_navpages_theme=FN_TPL_GetHtmlPart("nav pagination",$templateString);
            if ($tp_str_navpages_theme!= "")
            {
                $tp_str_navpages=$tp_str_navpages_theme;
                $templateString=str_replace($tp_str_navpages_theme,"{html_pages}",$templateString);
            }
            //----------------------------pages---------------------------->
            //risultati per pagina ----<
            if ($page > 1)
            {
                $linkpage=FNDBVIEW_MakeLink(array("page"=>$page - 1,"addtocart"=>null),"&amp;");
                $tplvars['linkpreviouspage']=$linkpage;
            }
            else
            {
                $tplvars['linkpreviouspage']=false;
            }
            $max_pages=8;
            $startpage=$page;
            $scarto=$startpage / $max_pages;
            if ($scarto!= 0)
            {
                $scarto=$startpage % $max_pages;
                $startpage-=($scarto);
                if ($page < $startpage)
                    $startpage=$page;
                if ($startpage < 1)
                    $startpage=1;
            }
            $ii=$startpage;
            $tp_pages=array();
            for($i=$startpage; $i<= $numPages; $i++)
            {
                $tpPage=array();
                if ($ii>= $startpage + $max_pages)
                    break;
                $linkpage=FNDBVIEW_MakeLink(array("page"=>$i,"addtocart"=>null),"&");
                $hclass="";
                if ($page== $i)
                {
                    $tpPage['active']=true;
                }
                else
                {
                    $tpPage['active']=false;
                }

                $tpPage['link']=$linkpage;
                $tpPage['txt_page']=$i;
                $tplvars['pages'][]=$tpPage;
                $ii++;
            }
            if ($page < $numPages)
            {
                $linkpage=FNDBVIEW_MakeLink(array("page"=>$page + 1,"addtocart"=>null),"&amp;");
                $tplvars['linknextpage']=$linkpage;
            }
            else
            {
                $tplvars['linknextpage']=false;
            }

            $tplvars['txt_rsults']=FN_Translate("search results","Aa")."  $start - $end  ".FN_i18n("of")." $num_records"."";
            //---------------------tabella paginazione --------------------<

            for($c=$start - 1; $c<= $end - 1 && isset($results[$c]); $c++)
            {
                $item=FNDBVIEW_HtmlItem($tablename,$results[$c][$t->xmltable->primarykey]);
                $tplvars['items'][]=$item;
            }
        }
    }
    $html=FN_TPL_ApplyTplString($templateString,$tplvars,$tplbasepath);
    if (isset($_GET['debug']))
    {
        dprint_r(__FILE__." ".__LINE__." : ".FN_GetExecuteTimer());
    }


    //dprint_r($tplvars);
    return $html;
}
?>

<script type="text/javascript">
    function call_ajax(url, div)
    {
        var xsreq;
        loading(div);
        if (window.XMLHttpRequest) {
            xsreq = new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            xsreq = new ActiveXObject("Microsoft.XMLHTTP");
        }
        if (xsreq) {
            xsreq.onreadystatechange = function () {
                try {
                    ajDone(url, div, xsreq);
                } catch (e) {
                    alert(e)
                }
            };
            xsreq.open("POST", url, true);
            xsreq.send("");
        }
    }
    function ajDone(url, div, xsreq)
    {

        if (xsreq.readyState == 4)
        {
            var divs = div.split(",");
            var output = xsreq.responseText;
            for (var i in divs)
            {
                div = divs[i];
                console.log(div);
                //alert (div);
                try {
                    var d = document.getElementsByTagName('body')[0];
                    var olddiv = document.getElementById("__fnloading");
                    d.removeChild(olddiv);
                } catch (e) {
                    //alert(e);
                }

                var el = document.createElement("div");
                el.innerHTML = output;
                var nodes = el.getElementsByTagName('div');
                for (var i = 0; i < nodes.length; i++) {
                    if (nodes[i].id == div)
                    {
                        document.getElementById(div).innerHTML = nodes[i].innerHTML;
                    }
                }
                nodes = el.getElementsByTagName('span');
                for (var i = 0; i < nodes.length; i++) {
                    if (nodes[i].id == div)
                    {
                        document.getElementById(div).innerHTML = nodes[i].innerHTML;
                    }
                }
            }
        }
    }
    function getScrollY() {
        var scrOfX = 0, scrOfY = 0;
        if (typeof (window.pageYOffset) == 'number') {
            //Netscape compliant
            scrOfY = window.pageYOffset;
            scrOfX = window.pageXOffset;
        } else if (document.body && (document.body.scrollLeft || document.body.scrollTop)) {
            //DOM compliant
            scrOfY = document.body.scrollTop;
            scrOfX = document.body.scrollLeft;
        } else if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
            //IE6 standards compliant mode
            scrOfY = document.documentElement.scrollTop;
            scrOfX = document.documentElement.scrollLeft;
        }
        return scrOfY;
        //return [ scrOfX, scrOfY ];
    }
    function getScrollX() {
        var scrOfX = 0, scrOfY = 0;
        if (typeof (window.pageYOffset) == 'number') {
            //Netscape compliant
            scrOfY = window.pageYOffset;
            scrOfX = window.pageXOffset;
        } else if (document.body && (document.body.scrollLeft || document.body.scrollTop)) {
            //DOM compliant
            scrOfY = document.body.scrollTop;
            scrOfX = document.body.scrollLeft;
        } else if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
            //IE6 standards compliant mode
            scrOfY = document.documentElement.scrollTop;
            scrOfX = document.documentElement.scrollLeft;
        }
        return scrOfX;
    }
    function loading(_div)
    {

        var divs = new Array();
        divs = _div.split(",");
        _div=divs[0];
        try {
            var d = document.getElementsByTagName('body')[0];
            var olddiv = document.getElementById("__fnloading");
            d.removeChild(olddiv);
        } catch (e) {
        }



        var div;
        div = document.createElement('div');
        div.innerHTML = 'loading...';
        div.setAttribute('id', "__fnloading");
        oHeight = document.getElementsByTagName('body')[0].clientHeight + getScrollY();
        oWidth = document.getElementsByTagName('body')[0].clientWidth + getScrollX();
        oHeight = oHeight + "px";
        oWidth = oWidth + "px";
        try {
            div.style.backgroundColor = '#000000';
            div.style.color = '#ffffff';
            div.style.zIndex = '8';
            div.style.display = 'block';
            div.style.position = 'absolute';
            div.style.width = oWidth;
            div.style.height = "auto";
            div.style.top = '0px';
            div.style.left = '0px';
            div.style.textAlign = 'center';
            div.style.opacity = '0.5';
            div.style.filter = 'alpha(opacity=50)';
        } catch (e)
        {
        }
        div.innerHTML = "<div style=\"color:#ffffff;margin-top:" + getScrollY() + "px\" ><br />loading...<br /><br /><img  src='<?php echo "{$_FN['siteurl']}modules/dbview/"?>loading.gif' /><br /><br /></div>";
        document.getElementsByTagName('body')[0].appendChild(div);
    }
</script>
