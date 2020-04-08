<?php

/**
 *
 * @global array $_FN
 * @param string $unirecid
 * @param string $tablename
 * @param bool $showbackbutton 
 */
function FNDBVIEW_ViewRecordHistory($unirecid,$_tablename="")
{
    global $_FN;
    $tplfile=file_exists("sections/{$_FN['mod']}/history.tp.html") ? "sections/{$_FN['mod']}/history.tp.html" : FN_FromTheme("modules/dbview/history.tp.html",false);
    $tplbasepath=dirname($tplfile)."/";
    $template=file_get_contents($tplfile);
    $tpvars=array();

    $shownavigatebar=true;
    $version=FN_GetParam("version",$_GET);
    $config=FN_LoadConfig();
    $html="";
//--config--<
    $tables=explode(",",$config['tables']);
    if ($_tablename== "")
    {
        $tablename=$tables[0];
    }
    else
    {
        $tablename=$_tablename;
    }
    $t=FN_XmlForm($tablename);
    $Table=FN_XmlForm($tablename);
    $Table_history=FN_XmlForm($tablename."_versions");
    //del history------->
    $action=FN_GetParam("action",$_GET,"flat");
    if ($action== "delete")
    {
        $item=$t->xmltable->GetRecordByPrimarykey($unirecid);
        if (FNDBVIEW_IsAdminRecord($item))
        {
            $Table_history->xmltable->DelRecord($version);
            $version="";
        }
    }
    //del history-------<




    if ($shownavigatebar== true)
    {
        $tpvars['navigationbar']=FNDBVIEW_Toolbar($config,$t->xmltable->GetRecordByPrimarykey($unirecid));
    }
    else
    {
        $tpvars['navigationbar']=array();
    }

    $res=FN_XMLQuery("SELECT * FROM {$tablename}_versions WHERE {$t->xmltable->primarykey} LIKE $unirecid ORDER BY recordupdate DESC");
    if (is_array($res))
    {
        foreach($res as $item)
        {
            $link_deleteversion=FNDBVIEW_MakeLink(array("action"=>"delete","op"=>"history","id"=>$unirecid,"version"=>$item['idversions']),"&");
            $link_version=FNDBVIEW_MakeLink(array("op"=>"history","id"=>$unirecid,"version"=>$item['idversions']),"&");

            if ($version== $item['idversions'])
            {
                $html.="<h3>".FN_GetDateTime($item['recordupdate'])." by {$item['userupdate']}</h3>";
                $html.=FNDBVIEW_ViewRecordPage($item['idversions'],"{$tablename}_versions",false); // visualizza la pagina col record
                if (FNDBVIEW_IsAdminRecord($item))
                    $html.="<div><a href=\"javascript:check('$link_deleteversion')\">".FN_Translate("delete this version")."</a></div>";
                $html.="<hr />";
            }
            else
            {
                $html.="<div>".FN_GetDateTime($item['recordupdate'])." by {$item['userupdate']} <a href=\"$link_version\">".FN_i18n("view")."</a>";
                if (FNDBVIEW_IsAdminRecord($item))
                    $html.="&nbsp;<a href=\"javascript:check('$link_deleteversion')\">".FN_i18n("delete")."</a></div>";
            }
        }
    }
    else
        $html.=FN_Translate("no previous version is available");

    $tpvars['htmlhistory']=$html;
    $html=FN_TPL_ApplyTplString($template,$tpvars);


    return $html;
}

/**
 *
 * @global array $_FN
 * @param string $unirecid
 * @param string $tablename
 * @param bool $showbackbutton 
 */
function FNDBVIEW_ViewRecordPage($unirecid,$_tablename="",$shownavigatebar=true)
{
    global $_FN;

//--config-->
    $config=FN_LoadConfig();
//--config--<
    $tables=explode(",",$config['tables']);
    if ($_tablename== "")
    {
        $tablename=$tables[0];
    }
    else
    {
        $tablename=$_tablename;
    }

    $t=FN_XmlForm($tablename);
    $Table=FN_XmlForm($tablename);


    if (!FNDBVIEW_CanViewRecord($unirecid,$tablename))
    {
        return "";
    }

    $forcelang=isset($_GET['forcelang']) ? $_GET['forcelang'] : $_FN['lang'];
    $row=$Table->xmltable->GetRecordByPrimaryKey($unirecid);
//-------statistiche---------------------->>
    if ($config['enable_statistics']== 1)
    {
        if (isset($row['view']) && $row['view']!= $row[$Table->xmltable->primarykey])
        {
            $Table2=FN_XmlTable($tablename);
            $ff=array();
            $ff['view']=$unirecid;
            $ff['unirecid']=$unirecid;
            //dprint_r($ff);
            $Table2->UpdateRecord($ff);
            $row=$Table2->GetRecordByPrimaryKey($unirecid);
        }
        if (!file_exists("{$_FN['datadir']}/{$_FN['database']}/$tablename"."_stat"))
        {
            $sfields=array();
            $sfields[0]['name']="unirecid";
            $sfields[0]['primarykey']="1";
            $sfields[1]['name']="view";
            createxmltable($_FN['database'],$tablename."_stat",$sfields,$_FN['datadir']);
        }
        $tbtmp=FN_XmlTable($tablename."_stat");

        $tmprow['unirecid']=$row[$t->xmltable->primarykey];
        if (($oldview=$tbtmp->GetRecordByPrimaryKey($row[$t->xmltable->primarykey]))== false)
        {
            $tmprow['view']=1;
            $rowtmp=$tbtmp->InsertRecord($tmprow);
        }
        else
        {
            $oldview['view'] ++;
            $rowtmp=$tbtmp->UpdateRecord($oldview); //aggiunge vista
            $Table2=FN_XmlTable($tablename);
            $row=$Table2->GetRecordByPrimaryKey($unirecid);
        }
    }
//-------statistiche----------------------<<
    $tablename=$Table->tablename;
    $unirecid=isset($row[$t->xmltable->primarykey]) ? $row[$t->xmltable->primarykey] : null;


    //--- template item ----->
    $tplfile=file_exists("sections/{$_FN['mod']}/detail.tp.html") ? "sections/{$_FN['mod']}/detail.tp.html" : FN_FromTheme("modules/dbview/detail.tp.html",false);
    $tplbasepath=dirname($tplfile)."/";
    $template=file_get_contents($tplfile);
    $tpvars=array();

    //--- template item -----<
//---------NAVIGATE BAR-------------------------------------------->
    $htmlNavigationbar="";
    if ($shownavigatebar== true)
    {
        $tpvars['navigationbar']=FNDBVIEW_Toolbar($config,$row);
    }
    else
    {
        $tpvars['navigationbar']=array();
    }
//---------NAVIGATE BAR--------------------------------------------<
//
//------------------------------visualizzazione-------------------------------->
    $linklist=FNDBVIEW_MakeLink(array("op"=>null,null=>null,"&amp;")); //link list
    $link=FNDBVIEW_MakeLink(array("op"=>"view","id"=>"$unirecid","&amp;")); //link  to this page
    $htmlFooter="";
    ob_start();
    if ($shownavigatebar && file_exists("sections/{$_FN['mod']}/viewfooter.php"))
    {
        include ("sections/{$_FN['mod']}/viewfooter.php");
    }
    $htmlFooter=ob_get_clean();
    $htmlHeader="";
    ob_start();
    if ($shownavigatebar && file_exists("sections/{$_FN['mod']}/viewheader.php"))
    {
        include ("sections/{$_FN['mod']}/viewheader.php");
    }
    $htmlHeader=ob_get_clean();
    $tpvars['footer']=$htmlFooter;
    $tpvars['header']=$htmlHeader;
//------------------------------ INNER TABLES---------------------------------->
    ob_start();
    $oldvalues=$row;
    $htmlout="";
    if ($Table->innertables)
    {
        foreach($Table->innertables as $k=> $v)
        {
            $title=$v['tablename'];
            if (isset($v["frm_{$_FN['lang']}"]))
                $title=$v["frm_{$_FN['lang']}"];
            $params=array();
            $params['echo']=false;
            $params['path']=$Table->path;
            $params['enableedit']=true;
            $params['enablenew']=false;
            $params['enabledelete']=false;
            $params['enableview']=true;
            $tinner=explode(",",$v["linkfield"]);
            if (isset($tinner[1]) && $tinner[1]!= "" && isset($oldvalues[$tinner[0]]))
                $params['restr']=array($tinner[1]=>$oldvalues[$tinner[0]]);
            else
                $params['restr']=array($v["linkfield"]=>$oldvalues[$Table->xmltable->primarykey]);
            if (isset($v["tablename"]) && isset($oldvalues[$Table->xmltable->primarykey]) && file_exists("{$_FN['datadir']}/{$_FN['database']}/{$v["tablename"]}.php"))
            {
                $tmptable=FN_XmlForm($v["tablename"],$params);
                $allview=$tmptable->xmltable->getRecords($params['restr']);
                if (is_array($allview) && count($allview) > 0)
                {
                    $ft="<h3>{$title}:</h3>";
                    foreach($allview as $view)
                    {
                        if (FNDBVIEW_CanViewRecord($view[$tmptable->xmltable->primarykey],$v["tablename"]))
                        {
                            echo $ft.FNDBVIEW_ViewRecordPage($view[$tmptable->xmltable->primarykey],$v["tablename"],false);
                            $ft="";
                        }
                    }
                }
            }
        }
    }
    $innerTables=ob_get_clean();
    $tpvars['innertables']=$innerTables;
//------------------------------ INNER TABLES----------------------------------<
    //xdprint_r($tpvars);
    $template=FN_TPL_ApplyTplString($template,$tpvars);
    $Table->SetlayoutTemplateView($template);
    $htmlView=$Table->HtmlShowView($Table->GetRecordTranslatedByPrimarykey($unirecid));
    return $htmlView;

//------------------------------visualizzazione--------------------------------<
}

/**
 * 
 * @global array $_FN
 * @return string
 */
function FNDBVIEW_AdminPerm()
{
    global $_FN;
    //--config-->
    $config=FN_LoadConfig();
    $tables=explode(",",$config['tables']);
    $tablename=$tables[0];
    $xmlform=FN_XmlForm($tablename);
    $op=FN_GetParam("op",$_GET);
    $results=FNDBVIEW_GetResults($config);
    $query="SELECT * FROM $tablename";
    $results=FN_XMLQuery($query);
    $titlefield=explode(",",$config['titlefield']);
    $permissions_records_groups=explode(",",$config['permissions_records_groups']);
    $permissions_records_edit_groups=explode(",",$config['permissions_records_edit_groups']);
    $html="";
    if (!FN_IsAdmin())
        return "";
    if (isset($_POST['groups']))
    {
        foreach($_POST['groups'] as $k=> $v)
        {
            if (is_array($v))
            {
                $newgroups[$k]=implode(",",$v);
            }
        }
    }
    if (isset($_POST['editgroups']))
    {
        foreach($_POST['editgroups'] as $k=> $v)
        {
            if (is_array($v))
            {
                $neweditgroups[$k]=implode(",",$v);
            }
        }
    }
    //dprint_r($_POST);

    $html.="<script>
		
select_allck = function(el){
	var name = el.name.replace('s_','');
	var cklist = document.getElementsByTagName('input');
	for (var i in cklist)
	{
		if (cklist[i].type=='checkbox' && cklist[i].name.indexOf('['+name+']')>=0 && cklist[i].name.indexOf('tgroups')<=0)
		{
			if (el.checked)
			{
				cklist[i].checked = true;
			}
			else
				cklist[i].checked = false;
		}
	}
	//console.log(cklist);
}
select_allcke = function(el){
	var name = el.name.replace('se_','');
	var cklist = document.getElementsByTagName('input');
	for (var i in cklist)
	{
		if (cklist[i].type=='checkbox' && cklist[i].name.indexOf('['+name+']')>=0 && cklist[i].name.indexOf('tgroups')>=0)
		{
			if (el.checked)
			{
				cklist[i].checked = true;
			}
			else
				cklist[i].checked = false;
		}
	}
	//console.log(cklist);
}
</script>";
    //dprint_r($_POST);
    $pagelink=FN_RewriteLink("index.php?mod={$_FN['mod']}&amp;op=$op");
    $html.="<h3>".FN_Translate("manage permissions")."</h3>";
    $html.="<form method=\"post\" action=\"\">";
    $html.="<table style=\"border:1px solid\">";
    $cst=count($titlefield);
    $csg=count($permissions_records_groups);
    $csgw=count($permissions_records_edit_groups);

    $html.="<tr><td   style=\"border:1px solid\" colspan=\"$cst\"></td><td  style=\"border:1px solid\" colspan=\"$csg\">".FN_Translate("read")."</td><td  style=\"border:1px solid;background-color:#dadada;color:#000000\" colspan=\"$csgw\" >".FN_Translate("write")."</td>";
    $htmltitles="<tr>";
    foreach($titlefield as $t)
    {
        $htmltitles.="<td style=\"border:1px solid\" >";
        $htmltitles.=$t;
        $htmltitles.="</td>";
    }
    foreach($permissions_records_groups as $t)
    {
        $htmltitles.="<td style=\"border:1px  solid;text-align:center\">";
        $htmltitles.=$t;

        $htmltitles.="<br /><input type=\"checkbox\" name=\"s_$t\" onchange=\"select_allck(this);\" />";
        $htmltitles.="</td>";
    }
    foreach($permissions_records_edit_groups as $t)
    {
        $htmltitles.="<td style=\"border:1px  solid;text-align:center;background-color:#dadada;color:#000000\">";
        $htmltitles.=$t;

        $htmltitles.="<br /><input type=\"checkbox\" name=\"se_$t\" onchange=\"select_allcke(this);\" />";
        $htmltitles.="</td>";
    }




    $htmltitles.="</tr>";

    $i=0;
    $toupdate=false;
    $saveok=true;
    $html.=$htmltitles;
    //dprint_r($_POST);
    foreach($results as $values)
    {
        //if ($i > 1000)
        //	break;
        $toupdateitem=false;
        if (isset($_POST['oldgroups']))
        {
            $toupdate=true;

            //read
            if (!isset($newgroups[$values[$xmlform->xmltable->primarykey]]))
            {
                $newgroups[$values[$xmlform->xmltable->primarykey]]="";
            }
            if (isset($values['groupview']) && $values['groupview']!= $newgroups[$values[$xmlform->xmltable->primarykey]])
            {
                $toupdateitem=true;
                $values['groupview']=$newgroups[$values[$xmlform->xmltable->primarykey]];
            }
            //edit
            if (!isset($neweditgroups[$values[$xmlform->xmltable->primarykey]]))
            {
                $neweditgroups[$values[$xmlform->xmltable->primarykey]]="";
            }
            if (isset($values['groupinsert']) && $values['groupinsert']!= $neweditgroups[$values[$xmlform->xmltable->primarykey]])
            {
                $toupdateitem=true;
                $values['groupinsert']=$neweditgroups[$values[$xmlform->xmltable->primarykey]];
            }
        }
        if ($toupdateitem)
        {
            $res=$xmlform->xmltable->UpdateRecord($values);
            if (!is_array($res))
                $saveok=false;
        }
        $html.="<tr>";
        foreach($titlefield as $t)
        {
            $html.="<td style=\"border:1px  solid;\">";
            $html.=$values[$t];
            $html.="</td>";
        }
        $usergroups=explode(",",$values['groupview']);
        $usereditgroups=explode(",",$values['groupinsert']);
        //read
        foreach($permissions_records_groups as $t)
        {
            $html.="<td title=\"$t\" style=\"border:1px  solid;text-align:center\">";
            $html.="<input name=\"groups[{$values[$xmlform->xmltable->primarykey]}][$t]\" value=\"$t\" type=\"checkbox\" ";

            if (in_array($t,$usergroups))
            {
                $html.="checked=\"checked\"";
            }
            $html.=" />";
            $html.="</td>";
        }
        //modify
        foreach($permissions_records_edit_groups as $t)
        {
            $html.="<td title=\"$t\" style=\"border:1px  solid;text-align:center;background-color:#dadada;color:#000000\">";
            $html.="<input name=\"editgroups[{$values[$xmlform->xmltable->primarykey]}][$t]\" value=\"$t\" type=\"checkbox\" ";

            if (in_array($t,$usereditgroups))
            {
                $html.="checked=\"checked\"";
            }
            $html.=" />";
            $html.="</td>";
        }
        $html.="</tr>";
        $i++;
    }
    $html.="</table>";
    if ($toupdate)
    {
        if ($saveok)
            $html.=FN_HtmlAlert(FN_Translate("the data were successfully updated"));
        else
            $html.=FN_HtmlAlert(FN_Translate("error"));
    }
    $html.="<input name=\"oldgroups\" value=\"1\" type=\"hidden\" />";
    $l=FN_RewriteLink("index.php?mod={$_FN['mod']}","&");
    $html.="<button type=\"submit\">".FN_Translate("save")."</button>";
    $html.="<button type=\"reset\">".FN_Translate("reset")."</button>";
    $html.="<button onclick=\"window.location='$l'\" type=\"button\">".FN_Translate("go to the contents list")."</button>";
    $html.="</form>";
    return $html;
}

/**
 *
 * @param string $config
 * @param array $row
 * @return string
 */
function FNDBVIEW_Toolbar($config,$row)
{
    global $_FN;
    $ret=array();
    $tables=explode(",",$config['tables']);
    $tablename=$tables[0];
    $t=FN_XmlForm($tablename);
    $op=FN_GetParam("op",$_GET,"html");
    $unirecid=$row[$t->xmltable->primarykey];
    $results=FNDBVIEW_GetResults($config);
    $next=$prev="";
    $k=0;
    if (is_array($results))
        foreach($results as $k=> $item)
        {
            $id=$item[$t->xmltable->primarykey];
            if ($id== $unirecid)
            {
                $prev=isset($results[$k - 1]) ? $results[$k - 1][$t->xmltable->primarykey] : $results[count($results) - 1][$t->xmltable->primarykey];
                $next=isset($results[$k + 1]) ? $results[$k + 1][$t->xmltable->primarykey] : $results[0][$t->xmltable->primarykey];
                break;
            }
        }



    $linkusermodify=FNDBVIEW_MakeLink(array("op"=>"users","id"=>$unirecid),"&");
    $linkmodify=FNDBVIEW_MakeLink(array("op"=>"edit","id"=>$unirecid),"&");
    $linkprev=FNDBVIEW_MakeLink(array("id"=>$prev),"&");
    $linkhistory=FNDBVIEW_MakeLink(array("op"=>"history","id"=>$unirecid),"&");
    $linknext=FNDBVIEW_MakeLink(array("id"=>$next),"&");
    $linklist=FNDBVIEW_MakeLink(array("op"=>null),"&");
    $linkview=FNDBVIEW_MakeLink(array("op"=>"view","id"=>$unirecid),"&");


    $vars['txt_rsults']=( $k + 1)."/".count($results);
    $vars['linkusermodify']=$linkusermodify;
    $vars['linkmodify']=$linkmodify;
    $vars['linklist']=$linklist;
    $vars['linkpreviouspage']=$linkprev;
    $vars['linknextpage']=$linknext;
    $vars['linkhistory']=$linkhistory;

    $ret=$vars;



    //-----next / prev / list buttons ----------------------------------------->
    $vars=array();
    $vars['title']=FN_Translate("go to the contents list");
    $vars['link']=$linklist;
    $vars['image']=FN_FromTheme("images/up.png");
    $ret['viewlist']=$vars;

    $vars=array();
    $vars['title']=FN_Translate("previous record");
    $vars['link']=$linkprev;
    $vars['image']=FN_FromTheme("images/left.png");
    $ret['viewprev']=$vars;

    $vars=array();
    $vars['title']=FN_Translate("next record");
    $vars['image']=FN_FromTheme("images/right.png");
    $vars['link']=$linknext;
    $ret['viewnext']=$vars;
    //-----next / prev / list buttons -----------------------------------------<
    //-----view/modify/history/users buttons ---------------------------------->



    $user_options=array();



    //view button
    $vars['title']=FN_Translate("view");
    $vars['image']=FN_FromTheme("images/mime/doc.png");
    $vars['link']=$linkview;
    $vars['active']=($op== "view");
    $user_options['view']=$vars;

    //history button
    if ($config['enable_history'])
    {
        $vars['title']=FN_Translate("version history");
        $vars['image']=FN_FromTheme("images/read.png");
        $vars['link']=$linkhistory;
        $vars['active']=($op== "history");
        $user_options['history']=$vars;
    }
    if (FNDBVIEW_IsAdminRecord($row))
    {

        //edit button
        $vars['title']=FN_Translate("modify");
        $vars['image']=FN_FromTheme("images/modify.png");
        $vars['link']=$linkmodify;
        $vars['active']=($op== "edit");
        $user_options['edit']=$vars;

        //users button
        $vars['title']=FN_Translate("edit qualified users to modify");
        $vars['image']=FN_FromTheme("images/users.png");
        $vars['link']=$linkusermodify;
        $vars['active']=($op== "users");
        $user_options['users']=$vars;
    }
    //-----view/modify/history/users buttons ----------------------------------<


    $ret['user_options']=$user_options;
    return $ret;
}

/**
 *
 * @global array $_FN
 * @param type $unirecid 
 */
function FNDBVIEW_DelRecordForm($unirecid)
{
    global $_FN;
//--config-->
    $config=FN_LoadConfig();
    $tables=explode(",",$config['tables']);
    $tablename=$tables[0];
//--config--<
    $html="";
    $Table=FN_XmlTable($tablename);
    $row=$Table->GetRecordByPrimaryKey($unirecid);
    if (empty($config['enable_delete']) || $row== null)
        die(FN_Translate("you may not do that"));

    if (!FNDBVIEW_IsAdminRecord($row))
        die(FN_Translate("you may not do that"));

    //hide record 
    if (!empty($config['hide_on_delete']))
    {
        if (!isset($Table->fields['recorddeleted']))
        {
            $tfield['name']="recorddeleted";
            $tfield['type']="bool";
            $tfield['frm_show']="0";

            addxmltablefield($Table->databasename,$Table->tablename,$tfield,$Table->path);
        }
        $newvalues=array("unirecid"=>$unirecid,"recorddeleted"=>1);
        $Table->UpdateRecord($newvalues);
    }
    //delete record
    else
    {
        if ($row!= null)
            $Table->DelRecord($unirecid);
        // elimino i permessi sul record
        $restr=array();
        $listusers=FN_XmlTable("fieldusers");
        $restr['table_unirecid']=$row[$Table->primarykey];
        $restr['tablename']=$tablename;
        $list_field=$listusers->GetRecords($restr);
        if (is_array($list_field))
        {
            foreach($list_field as $field)
            {
                $listusers->DelRecord($field['unirecid']);
            }
        }
        $Table->DelRecord($unirecid);
        if (isset($_FN['modparams'][$_FN['mod']]['editorparams']['table']['function_on_delete']))
        {
            $function=$_FN['modparams'][$_FN['mod']]['editorparams']['table']['function_on_delete'];
            if (function_exists($function))
            {
                $function($newvalues);
            }
        }
    }
    FNDBVIEW_WriteSitemap();
    $html.="<br />".FN_Translate("record was deleted");
    $html.="";
    $link=FNDBVIEW_MakeLink(array("op"=>null)); //list link
    $html.="<br /><br /><button onclick=\"window.location='$link'\"><img border=\"0\" style=\"vertical-align:middle\" src=\"".FN_FromTheme("images/left.png")."\" alt=\"\">&nbsp;".FN_Translate("go to the contents list")."</button>";
    return $html;
}

/**
 *
 * @global array $_FN
 * @param string $unirecid
 * @param object $Table
 * @param array $errors
 * @return type 
 */
function FNDBVIEW_EditRecordForm($unirecid,$Table,$errors=array(),$reloadDataFromDb=false)
{
    global $_FN;
//--config-->


    $config=FN_LoadConfig();
    $tables=explode(",",$config['tables']);
    $tablename=$tables[0];
//--config--<
    $tb=FN_XmlTable($tablename);
    $row=$tb->GetRecordByPk($unirecid);

    $tpvars['navigationbar']=FNDBVIEW_Toolbar($config,$row);

    $html="";
    $html.="
<script type=\"text/javascript\">
//<!--
function set_changed()
{
    var allButtons = document.getElementsByTagName('*');
    for (var i in allButtons)
    {
        if (allButtons[i].onclick)
        {
            try{
            if (allButtons[i].getAttribute && allButtons[i].getAttribute('onclick').indexOf('window.location')==0)
            {
                //console.log(allButtons[i].getAttribute('onclick'));
                allButtons[i].setAttribute('onclick','if (confirm_exitnosave()){'+allButtons[i].getAttribute('onclick')+'}');
            }
            }catch(e){ console.log(e);}
        }
    }
      var allLinks = document.getElementsByTagName('a');
	for (var i in allLinks)
	{
		if (!allLinks[i].onclick || allLinks[i].onclick=='' || allLinks[i].onclick==undefined && allLinks[i].href )
		{
                try{
			if (allLinks[i].setAttribute)
			{
				allLinks[i].setAttribute('onclick','return confirm_exitnosave()');
			}
                        }catch(e){ console.log(e);}
		}
	}
}
function confirm_exitnosave()
{
	if(confirm ('".addslashes(FN_Translate("you exit without to save?"))."'))
	{
		return true;
	}
	return false;
}
//-->
</script>	
";
    if (isset($_POST['__NOSAVE']))
    {
        $html.="
<script type=\"text/javascript\">
//<!--
set_changed();
//-->
</script>";
    }

    //----template--------->
    $tplfile=file_exists("sections/{$_FN['mod']}/formedit.tp.html") ? "sections/{$_FN['mod']}/formedit.tp.html" : FN_FromTheme("modules/dbview/formedit.tp.html",false);

    $template=file_get_contents($tplfile);
    $tpvars['formaction']=FNDBVIEW_MakeLink(array("op"=>"updaterecord","id"=>$unirecid),"&amp;"); //index.php?mod={$_FN['mod']}&amp;op=updaterecord&amp;id=$unirecid
    $tpvars['urlcancel']=FNDBVIEW_MakeLink(array("op"=>null,"id"=>null),"&");
    $template=FN_TPL_ApplyTplString($template,$tpvars);
    $Table->SetlayoutTemplate($template);    //----template---------<    

    if (!isset($_GET['inner']))
    {
        $forcelang=isset($_GET['forcelang']) ? $_GET['forcelang'] : $_FN['lang'];
        if ($reloadDataFromDb)
            $nv=$row;
        else
            $nv=$Table->getbypost();
        $html.=$Table->HtmlShowUpdateForm($unirecid,FN_IsAdmin(),$nv,$errors);
        $pk=$Table->xmltable->primarykey;
    }

//editor inner tables ----------------------------------------------------->
    if ($Table->innertables)
    {

        foreach($Table->innertables as $k=> $v)
        {
            if (isset($_GET['inner']))
            {
                if (!isset($_GET["op___xdb_".$v['tablename']]))
                {
                    //dprint_r($_FN);
                    continue;
                }
            }

            $params=array();
            if (isset($_FN['modparams'][$_FN['mod']]['editorparams']['innertables'][$v["tablename"]]))
                $params=$_FN['modparams'][$_FN['mod']]['editorparams']['innertables'][$v["tablename"]];

            $title=$v['tablename'];
            $innertablemaxrows=isset($v['innertablemaxrows']) ? $v['innertablemaxrows'] : "";

            $tmptable=FN_XmlForm($v["tablename"],$params);
            if (FNDBVIEW_CanEditRecord($Table->xmltable->primarykey,$v["tablename"]))
            {
                $v['enabledelete']=true;
            }


            if (isset($v["frm_{$_FN['lang']}"]))
                $title=$v["frm_{$_FN['lang']}"];
            $html.="<div class=\"FNDBVIEW_innerform\">";
            $innertile=$title;

            if (isset($_GET['inner']))
            {
                $innertile="{$_FN['sections'][$_FN['mod']]['title']} -&gt; {$title}";
                $tmptitle=explode(",",$config['titlefield']);
                foreach($tmptitle as $tmp_t)
                {
                    $sep=" -&gt; ";
                    if (!empty($row[$tmp_t]))
                    {
                        $innertile.="$sep".$row[$tmp_t];
                        $sep=" ";
                    }
                }
            }
            $html.="<h3>$innertile</h3>";
            $params['path']=$Table->path;
            $params['enableedit']=true;
            $params['maxrows']=$innertablemaxrows;
            $params['enablenew']=(!isset($v["enablenew"]) || $v["enablenew"]== 1);
            $params['enabledelete']=(!empty($v["enabledelete"]));
            $tplfile=file_exists("sections/{$_FN['mod']}/forminner.tp.html") ? "sections/{$_FN['mod']}/forminner.tp.html" : FN_FromTheme("modules/dbview/forminner.tp.html",false);
            $templateInner=file_get_contents($tplfile);
            $params['layout_template']=$templateInner;
            $link=FNDBVIEW_MakeLink(array("op"=>"edit","id"=>$unirecid,"inner"=>"1"),"&",true);
            $link=explode("index.php?",$link);
            $params['link']=$link[1];
            $link=FNDBVIEW_MakeLink(array("op"=>"edit","id"=>$unirecid,"inner"=>null),"&",true);
            $params['link_listmode']=$link;
            $params['textviewlist']="";
            if (isset($v['innertablefields']) && $v['innertablefields']!= "")
            {
                $params['fields']=str_replace(",","|",$v['innertablefields']);  //innertablefields	
            }


            //op___xdb_
            $t=explode(",",$v["linkfield"]);
            if (isset($t[1]) && $t[1]!= "" && isset($row[$t[0]]))
                $params['restr']=array($t[1]=>$row[$t[0]]);
            $params['restr']=isset($params['restr']) ? $params['restr'] : false;
            $params['forcenewvalues']=$params['forceupdatevalues']=$params['restr'];
            //dprint_r($params);
            if (isset($v["tablename"]) && isset($row[$Table->xmltable->primarykey]))
            {
                ob_start();
                $params['textnew']=FN_Translate("add a new item into")." ".$title;
                FN_xmltableeditor($v["tablename"],$params);
                $html.=ob_get_clean();
            }
            $html.="</div>";
        }
    }

//editor inner tables -----------------------------------------------------<
    if (empty($_GET['embed']) && empty($_GET['inner']))
    {
        $listlink=FNDBVIEW_MakeLink(array("op"=>null,"id"=>null),"&");
        $html.="<br /><br />";
        $linkCopyAndNew=FN_RewriteLink("index.php?op=new&id=$unirecid","&",true);
        $html.="<button type=\"button\" onclick=\"document.getElementById('frmedit').action='$linkCopyAndNew';document.getElementById('frmedit').submit();\" ><img style=\"vertical-align:middle\" src=\"".FN_FromTheme("images/modify.png")."\" alt=\"\">&nbsp;".FN_Translate("copy data and add new")."</button>";

        $html.="<button type=\"button\" onclick=\"window.location='$listlink'\"><img style=\"vertical-align:middle\" src=\"".FN_FromTheme("images/up.png")."\" alt=\"\">&nbsp;".FN_Translate("view list")."</button>";
        $link=FNDBVIEW_MakeLink(array("op"=>"view","id"=>$unirecid,"inner"=>null));

        $html.=" <button type=\"button\" id=\"exitform2\"  onclick=\"window.location='$link'\"><img style=\"vertical-align:middle\" src=\"".FN_FromTheme("images/left.png")."\" alt=\"\">&nbsp;".FN_Translate("exit and view")."</button>";
    }
    else
    {

        $editlink=FNDBVIEW_MakeLink(array("op"=>"edit","id"=>$unirecid,"inner"=>null),"&");
        $html.="<br />
		<br />
		<button onclick=\"window.location='$editlink'\" >
		<img border=\"0\" style=\"vertical-align:middle\" src=\"".FN_FromTheme("images/left.png")."\" alt=\"\" />&nbsp;".FN_Translate("back")."</button>";
    }
    return $html;
}

/**
 *
 * @global array $_FN
 * @param object $Table
 * @param array $errors 
 */
function FNDBVIEW_NewRecordForm($Table,$errors=array())
{
    global $_FN;
//--config-->
    $config=FN_LoadConfig();
//--config--<
    //----template--------->
    $tplfile=file_exists("sections/{$_FN['mod']}/form.tp.html") ? "sections/{$_FN['mod']}/form.tp.html" : FN_FromTheme("modules/dbview/form.tp.html",false);
    $template=file_get_contents($tplfile);
    $tpvars=array();
    $tpvars['formaction']=FNDBVIEW_MakeLink(array("op"=>"new"),"&amp;");
    $tpvars['urlcancel']=FNDBVIEW_MakeLink(array("op"=>null,"id"=>null),"&");
    $template=FN_TPL_ApplyTplString($template,$tpvars);
    $Table->SetlayoutTemplate($template);
    $html="";
    //----template---------<
//----gestione esci senza salvare ------->
    $html.="
<script type=\"text/javascript\">
function set_changed()
{
try{
    document.getElementById('exitform').setAttribute('onclick','confirm_exitnosave()');
    }catch(e){}
}
function confirm_exitnosave()
{
    if(confirm ('".addslashes(FN_Translate("you exit without to save?"))."'))
    {
        window.location='?mod={$_FN['mod']}';
    }
}
</script>";

    if (isset($_POST['__NOSAVE']))
    {
        $html.="
<script type=\"text/javascript\">
set_changed();
</script>";
    }
//----gestione esci senza salvare -------<
    $nv=$Table->getbypost();
    $Table->ShowInsertForm(FN_IsAdmin(),$nv,$errors);
}

/**
 *
 * @global array $_FN
 * @param string $unirecid
 */
function FNDBVIEW_UsersForm($unirecid)
{


    global $_FN;
//--config-->
    $config=FN_LoadConfig();
    $tables=explode(",",$config['tables']);
    $tablename=$tables[0];
//--config--<
    $Table=FN_XmlTable($tablename);
    $row=$Table->GetRecordByPrimaryKey($unirecid);
    $pk=$Table->primarykey;
    $tplfile=file_exists("sections/{$_FN['mod']}/users.tp.html") ? "sections/{$_FN['mod']}/users.tp.html" : FN_FromTheme("modules/dbview/users.tp.html",false);
    $template=file_get_contents($tplfile);
    $tpvars=array();
    $tpvars['navigationbar']=FNDBVIEW_Toolbar($config,$row);
    $html="";
    $titles=explode(",",$config['titlefield']);
    $t=array();
    foreach($titles as $tt)
    {
        $t[]=$row[$tt];
    }
    $title=implode(" ",$t);
    $html.="<h2>$title</h2>";
    $usertoadd=FN_GetParam("usertoadd",$_POST);
    $usertodel=FN_GetParam("usertodel",$_GET);
    if ($usertodel!= "")
    {
        $fieldusers=FN_XmlTable("fieldusers");
        $r=array();
        $r['tablename']=$tablename;
        $r['username']=$usertodel;
        $r['table_unirecid']=$unirecid;
        $old=$fieldusers->GetRecords($r);
        if (!isset($old[0]))
            $html.="error delete:".FN_Translate("this user not exists");
        $old=$old[0];
        $fieldusers->DelRecord($old[$fieldusers->primarykey]);
    }
    if ($usertoadd!= "")
    {
        if (FN_GetUser($usertoadd)== null)
        {
            $html.=FN_Translate("this user not exists");
        }
        else
        if (FNDBVIEW_UserCanEditField($usertoadd,$row))
        {
            $html.=FN_Translate("this user is already enabled");
        }
        else
        {
            $fieldusers=FN_XmlTable("fieldusers");
            $r=array();
            $r['tablename']=$tablename;
            $r['username']=$usertoadd;
            $r['table_unirecid']=$unirecid;
            $fieldusers->InsertRecord($r);
            $rname=$row[$pk];
            if (isset($row['name']))
                $rname=$row['name'];
            else
                foreach($Table->fields as $gk=> $g)
                {
                    if (!isset($g->frm_show) || $g->frm_show!= 0)
                    {
                        $rname=$row[$gk];
                        break;
                    }
                }
            //dprint_r($Table->fields);
            $message=FN_Translate("you were added to the users allowed to edit this content")." \"".$rname."\" \n\n";
            $message.=FN_Translate("If you want to edit the content you have to login :")."\n".$_FN['siteurl']."index.php?mod=login\n";
            $message.=FN_Translate("and login as user").":\"$usertoadd\"\n";
            $message.=FN_Translate("then click on -user allowed to edit- and manage the permissions")."\n".$_FN['siteurl']."index.php?mod={$_FN['mod']}&op=edit&id=$unirecid\n";
            $user_record=FN_GetUser($usertoadd);
            $subject="[{$_FN['sitename']}] ".$rname;
            $to=FN_GetUser($usertoadd);
            FN_SendMail($to['email'],$subject,$message,false);
            FN_Log("{$_FN['mod']}",$_SERVER['REMOTE_ADDR']."||".$_FN['user']."||added user $usertoadd record: ".$rname." in table $tablename.");
        }
    }
    if (!FNDBVIEW_IsAdminRecord($row))
    {
        return (FN_Translate("you may not do that"));
        return;
    }
    $link=FNDBVIEW_MakeLink(array("op"=>"users","id"=>$row[$pk]));
    $html.="
	<form
		action=\"$link\"
		method=\"post\">
		<table>
			<tr>
				<td>";
    $html.=FN_Translate("add user");
    $html.=": </td>
			<td></td>
			<td><input type=\"text\" name=\"usertoadd\" /></td>
		</tr>
		<tr>
			<td colspan=\"2\"><input type=\"hidden\" name=\"$pk\"
			  value=\"$unirecid\" /> <input type=\"submit\" /></td>
		</tr>
	</table>
</form>
";
    $users=array();
    $users=FNDBVIEW_GetFieldUserList($row,$tablename,false);
    if (is_array($users))
        foreach($users as $user)
        {
            $link=FNDBVIEW_MakeLink(array("op"=>"users","id"=>$row[$pk],"usertodel"=>$user['username']));
            $html.="<br />".$user['username']."<input type=\"button\" value=\"".FN_Translate("delete")."\" onclick=\"check('$link')\" />";
        }

    $tpvars['htmlusers']=$html;
    $html=FN_TPL_ApplyTplString($template,$tpvars);
    return $html;
}

function FNDBVIEW_GetSearchForm($orders,$tablename,$search_options,$search_min,$search_fields,$search_partfields="")
{

    global $_FN;
    $q=FN_GetParam("q",$_REQUEST);
    //--config-->
    $config=FN_LoadConfig();
    $config['search_fields']=explode(",",$config['search_fields']);
    $config['search_orders']=explode(",",$config['search_orders']);
    $config['search_min']=explode(",",$config['search_min']);
    $config['search_partfields']=explode(",",$config['search_partfields']);
    $config['search_options']=explode(",",$config['search_options']);
    //--config--<    
    $_table_form=FN_XmlForm($tablename);
    $data=$config;
    $data['q']=FN_GetParam("q",$_REQUEST,"html");
    $data['formaction']=FNDBVIEW_MakeLink();


    $order=FN_GetParam("order",$_REQUEST);
    $desc=FN_GetParam("desc",$_REQUEST);
    if ($order== "")
    {
        $order=$config['defaultorder'];
        if ($desc== "")
            $desc=1;
    }
    //-------------------------rules------------------------------------------->
    $rules=array();
    if ($config['table_rules'])
    {
        if (!file_exists("{$_FN['datadir']}/{$_FN['database']}/{$config['table_rules']}.php"))
        {
            $xml='<?php exit(0);?>
<tables>
	<field>
		<name>rule</name>
		<primarykey>1</primarykey>
		<frm_show>0</frm_show>
		<extra>autoincrement</extra>
	</field>
	<field>
		<name>title</name>
		<frm_i18n>rule title</frm_i18n>
		<frm_multilanguages>auto</frm_multilanguages>
		<frm_show>1</frm_show>
	</field>	
	<field>
		<name>query</name>
		<frm_i18n>query</frm_i18n>
		<frm_type>text</frm_type>
		<frm_cols>80</frm_cols>
		<frm_rows>10</frm_rows>
		<frm_show>1</frm_show>
	</field>
	<field>
		<name>function</name>
		<frm_i18n>function</frm_i18n>
		<frm_show>1</frm_show>
	</field>
</tables>';
            FN_Write($xml,"{$_FN['datadir']}/{$_FN['database']}/{$config['table_rules']}.php");
        }
        $tablerules=FN_XmlForm($config['table_rules']);
        $rules=$tablerules->xmltable->GetRecords();
        foreach($rules as $k=> $rule)
        {
            $rules[$k]['selected']=(!empty($_REQUEST['rule']) && $_REQUEST['rule']== $rule['rule']) ? "selected=\"selected\"" : "";
            $rules[$k]['value']=$rules[$k]['rule'];
        }
        $data['table_rules']=array();
        $data['table_rules']['rules']=$rules;
    }
    else
    {
        $data['table_rules']=false;
       // $data['rules']=array();
    }

//    dprint_r($data);
    //-------------------------rules------------------------------------------->
    //----------------------search exact phrase-------------------------------->
    $search_fields_items=array();
    //dprint_r($rules);
    foreach($search_fields as $fieldname)
    {
        if (isset($_table_form->formvals[$fieldname]))
        {
            $val=FN_GetParam("$fieldname",$_REQUEST);
            $search_fields_array['suffix']="";
            if (isset($_table_form->formvals[$fieldname]['frm_suffix']))
                $search_fields_array['suffix']=$_table_form->formvals[$fieldname]['frm_suffix'];
            $search_fields_array['title']=$_table_form->formvals[$fieldname]['title'];
            $search_fields_array['value']=$val;
            $search_fields_array['name']="sfield_$fieldname";
            $search_fields_items[]=$search_fields_array;
        }
    }
    $data['search_fields']=$search_fields_items;
    //------------- looking for a part of the text ---------------------------->
    $search_fields_items=array();
    foreach($config['search_partfields'] as $fieldname)
    {
        if (isset($_table_form->formvals[$fieldname]))
        {
            $search_fields_array=array();
            //dprint_r($_table_form->formvals[$partf]);
            $val=FN_GetParam("spfield_$fieldname",$_REQUEST);
            $search_fields_array['suffix']="";
            if (isset($_table_form->formvals[$fieldname]['frm_suffix']))
                $search_fields_array['suffix']=$_table_form->formvals[$fieldname]['frm_suffix'];
            $search_fields_array['title']=$_table_form->formvals[$fieldname]['title'];
            $search_fields_array['value']=$val;
            $search_fields_array['name']="spfield_$fieldname";
            $search_fields_items[]=$search_fields_array;
        }
    }
    $data['search_partfields']=$search_fields_items;
    //------------------ looking for a part of the text -----------------------<    
    //---------------------- looking search_min ------------------------------->
    $search_fields_items=array();
    foreach($config['search_min'] as $fieldname)
    {
        if (isset($_table_form->formvals[$fieldname]))
        {
            $search_fields_array=array();
            //dprint_r($_table_form->formvals[$partf]);
            $val=FN_GetParam("min_$fieldname",$_REQUEST);
            $search_fields_array['suffix']="";
            if (isset($_table_form->formvals[$fieldname]['frm_suffix']))
                $search_fields_array['suffix']=$_table_form->formvals[$fieldname]['frm_suffix'];
            $search_fields_array['title']=$_table_form->formvals[$fieldname]['title'];
            $search_fields_array['value']=$val;
            $search_fields_array['name']="min_$fieldname";
            $search_fields_items[]=$search_fields_array;
        }
    }
    $data['search_min']=$search_fields_items;
    //---------------------- looking search_min -------------------------------< 
    //------------------------- search filters -------------------------------->
    $search_options=array();
    foreach($config['search_options'] as $option)
    {
        $search_fields_items=array();
        if (isset($_table_form->formvals[$option]['options']))
        {
            $search_fields_items['title']=$_table_form->formvals[$option]['title'];
            //$htmlform.="<div class=\"navigatorformtitleCK\" ><span>$optiontitle:</span></div>";
            $options=array();
            if (is_array($_table_form->formvals[$option]['options']))
            {
                foreach($_table_form->formvals[$option]['options'] as $c)
                {
                    $getid="s_opt_{$option}_{$tablename}_{$c['value']}";
                    $search_fields_array['title']=$c['title'];
                    $search_fields_array['value']=$c['value'];
                    $search_fields_array['name']=$getid;
                    $search_fields_array['id']="i_$getid";
                    $ck="";
                    if (isset($_REQUEST[$getid]))
                        $ck="checked=\"checked\"";
                    $search_fields_array['checked']=$ck;
                    $options[]=$search_fields_array;
                }
            }
            $search_fields_items['options']=$options;
            $search_options[]=$search_fields_items;
        }
    }
    $data['search_options']=$search_options;

    //------------------------- search filters --------------------------------<
    //----------------------------- order by ---------------------------------->
    $orderby=array();
    if (count($orders) > 0)
    {
        foreach($orders as $o)
        {
            $orderby_item=array();
            if (!isset($_table_form->xmltable->fields[$o]))
                continue;
            $tt="frm_{$_FN['lang']}";
            if (isset($_table_form->xmltable->fields[$o]->$tt))
                $no=$_table_form->xmltable->fields[$o]->$tt;
            elseif (isset($_table_form->xmltable->fields[$o]->frm_i18n))
            {
                $no=FN_Translate($_table_form->xmltable->fields[$o]->frm_i18n);
            }
            else
                $no=$_table_form->xmltable->fields[$o]->title;
            if ($order== $o)
                $s="selected=\"selected\"";
            else
                $s="";

            $orderby_item['value']=$o;
            $orderby_item['title']=$no;
            $orderby_item['selected']=$s;
            $orderby[]=$orderby_item;
        }
        $ck=($desc== "") ? "" : "checked=\"checked\"";
        $data['checked_desc']=$ck;
    }
    $data['order_by']=$orderby;
    //----------------------------- order by ----------------------------------<    
    return $data;
}

/**
 * 
 * @param $orders
 * @param $tables
 * @param $config['search_options']
 */
function FNDBVIEW_SearchForm($orders,$tablename,$search_options,$search_min,$search_fields,$search_partfields="")
{
    global $_FN;
    $q=FN_GetParam("q",$_GET);
    $order=FN_GetParam("order",$_GET);
    $desc=FN_GetParam("desc",$_GET);
    //--config-->
    $config=FN_LoadConfig();
    $config['search_fields']=explode(",",$config['search_fields']);
    $config['search_orders']=explode(",",$config['search_orders']);
    $config['search_min']=explode(",",$config['search_min']);
    $config['search_partfields']=explode(",",$config['search_partfields']);
    $config['search_options']=explode(",",$config['search_options']);
    //--config--<
    if ($order== "")
    {
        $order=$config['defaultorder'];
        if ($desc== "")
            $desc=1;
    }
    $_table_form=FN_XmlForm($tablename);
    //------------------------------table rules-------------------------------->
    if ($config['table_rules'])
    {
        if (!file_exists("{$_FN['datadir']}/{$_FN['database']}/{$config['table_rules']}.php"))
        {
            $xml='<?php exit(0);?>
<tables>
	<field>
		<name>rule</name>
		<primarykey>1</primarykey>
		<frm_show>0</frm_show>
		<extra>autoincrement</extra>
	</field>
	<field>
		<name>title</name>
		<frm_i18n>rule title</frm_i18n>
		<frm_multilanguages>auto</frm_multilanguages>
		<frm_show>1</frm_show>
	</field>	
	<field>
		<name>query</name>
		<frm_i18n>query</frm_i18n>
		<frm_type>text</frm_type>
		<frm_cols>80</frm_cols>
		<frm_rows>10</frm_rows>
		<frm_show>1</frm_show>
	</field>
	<field>
		<name>function</name>
		<frm_i18n>function</frm_i18n>
		<frm_show>1</frm_show>
	</field>
</tables>';
            FN_Write($xml,"{$_FN['datadir']}/{$_FN['database']}/{$config['table_rules']}.php");
        }
    }
}

?>