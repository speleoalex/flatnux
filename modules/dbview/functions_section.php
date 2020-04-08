<?php

/**
 * @package Flatnux_module_dbview
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
#<fnmodule>dbview</fnmodule>
global $_FN;

//ini_set('max_input_vars', 30000);
//------------------- tabella permessi tabelle -------------------------



function FNDBVIEW_Init()
{
    global $_FN;
    $htmlLog="";
    if (!file_exists("{$_FN['datadir']}/fndatabase/fieldusers"))
    {
        $sfields=array();
        $sfields[0]['name']="unirecid";
        $sfields[0]['primarykey']="1";
        $sfields[0]['extra']="autoincrement";
        $sfields[1]['name']="username";
        $sfields[2]['name']="tablename";
        $sfields[3]['name']="table_unirecid";
        $htmlLog.=createxmltable("fndatabase","fieldusers",$sfields,$_FN['datadir']);
    }
    $config=FN_LoadConfig();
    $tablename=$config['tables'];

//--------------- creazione tabelle ------------------------------->
    if (!file_exists("{$_FN['datadir']}/{$_FN['database']}/{$tablename}") || !file_exists("{$_FN['datadir']}/{$_FN['database']}/{$tablename}.php"))
    {
        $str_table=file_get_contents("modules/dbview/install/fn_files.php");
        $str_table=str_replace("fn_files",$tablename,$str_table);
        FN_Write($str_table,$_FN['datadir']."/".$_FN['database']."/$tablename.php");
    }

    if ($config['enable_history'] && !file_exists("{$_FN['datadir']}/{$_FN['database']}/{$tablename}_versions.php"))
    {
        $Table=FN_XmlTable($tablename);
        if (!isset($Table->fields['recorddeleted']))
        {
            $tfield['name']="userupdate";
            $tfield['type']="varchar";
            $tfield['frm_show']="0";
            addxmltablefield($Table->databasename,$Table->tablename,$tfield,$Table->path);
        }

        $str_table=file_get_contents($_FN['datadir']."/".$_FN['database']."/$tablename.php");
        $str_table=str_replace("<primarykey>1</primarykey>","",$str_table);
        $str_table=str_replace("<tables>","<tables>
    <field>
		<name>idversions</name>
		<primarykey>1</primarykey>
		<extra>autoincrement</extra>
		<type>string</type>
	</field>",$str_table);
        FN_Write($str_table,$_FN['datadir']."/".$_FN['database']."/{$tablename}_versions.php");
    }
//------------------- tabella delle statistiche -------------------------
    if ($config['enable_statistics']== 1)
    {
        if (!file_exists("{$_FN['datadir']}/{$_FN['database']}/{$tablename}"."_stat") || !file_exists("{$_FN['datadir']}/{$_FN['database']}/{$tablename}"."_stat.php"))
        {
            //$htmlLog.= "<br>creazione statistiche $tablename";
            $sfields=array();
            $sfields[0]['name']="unirecid";
            $sfields[0]['primarykey']="1";
            $sfields[1]['name']="view";
            $htmlLog.=createxmltable($_FN['database'],$tablename."_stat",$sfields,$_FN['datadir']);
        }
    }
//------------------- tabella permessi tabelle -------------------------
    if (!file_exists("{$_FN['datadir']}/{$_FN['database']}/fieldusers"))
    {
        $sfields=array();
        $sfields[0]['name']="unirecid";
        $sfields[0]['primarykey']="1";
        $sfields[0]['extra']="autoincrement";
        $sfields[1]['name']="username";
        $sfields[2]['name']="tablename";
        $sfields[3]['name']="table_unirecid";
        $htmlLog.=createxmltable($_FN['database'],"fieldusers",$sfields,$_FN['datadir']);
    }
//------------------- tabella permessi tabelle -------------------------
    if ($config['enable_permissions_each_records'] && $config['permissions_records_groups']!= "")
    {
        $tmp=explode(",",$config['permissions_records_groups']);
        foreach($tmp as $group)
        {
            FN_CreateGroupIfNotExists($group);
        }
    }
//------------------- tabella commenti-------------------------
//--------------- creazione tabelle -------------------------------<
}



/**
 *
 * @global array $_FN
 * @staticvar boolean $listok
 * @param array $config
 * @param array $params
 * @return array 
 */
function FNDBVIEW_GetResults($config=false,$params=false)
{
    global $_FN;
    static $listok=false;
    //------------------------------load config-------------------------------->
    if ($config== false)
    {
        $config=FN_LoadConfig();
    }

    $search_options=$config['search_options']!= "" ? explode(",",$config['search_options']) : array();
    $search_min=$config['search_min']!= "" ? explode(",",$config['search_min']) : array();
    $search_partfields=$config['search_partfields']!= "" ? explode(",",$config['search_partfields']) : array();
    $search_fields=$config['search_fields']!= "" ? explode(",",$config['search_fields']) : array();
    $tablename=$config['tables'];
    $groups=($config['navigate_groups']!= "") ? explode(",",$config['navigate_groups']) : array();
    //------------------------------load config--------------------------------<
    if ($params=== false)
        $params=$_REQUEST;
    $q=FN_GetParam("q",$params);
    $navigate=1;
    $listfind=explode(" ",$q);
    $order=FN_GetParam("order",$params);
    $desc=FN_GetParam("desc",$params);
    $rule=FN_GetParam("rule",$params);
    $rulequery="";
    if ($rule!= "" && !empty($config['table_rules']))
    {
        $tablerules=FN_XmlTable($config['table_rules']);
        $rulevalues=$tablerules->GetRecordByPrimaryKey($rule);
        if (!empty($rulevalues['function']) && function_exists($rulevalues['function']))
        {
            return $rulevalues['function']($rulevalues);
        }
        elseif (!empty($rulevalues['query']))
        {
            $rulequery="{$rulevalues['query']}";
        }
    }
    if ($order== "")
    {
        $order=$config['defaultorder'];
        if ($desc== "")
            $desc=1;
    }
    {
        $t=FN_XmlForm($tablename);
        $fields=array();
        $ftoread=$groups;
        $ftoread[]=$t->xmltable->primarykey;
        $ftoread=implode(",",$ftoread);
        $query="SELECT $ftoread FROM $tablename WHERE   ";
        $wherequery="";
        $and="";
        if (!empty($rulequery))
        {
            $wherequery=" $rulequery ";
            $and="AND";
        }

        if ($config['enable_permissions_each_records'] && isset($t->formvals['groupview']) && !FN_IsAdmin())
        {
            $exists_group=false;
            $wherequery.="(";
            $usergroups=FN_GetUser($_FN['user']);
            $usergroups=isset($usergroups['group']) ? explode(",",$usergroups['group']) : array("");

            $wherequery.="username LIKE '{$_FN['user']}' OR groupview LIKE ''";
            $or=" OR";
            foreach($usergroups as $usergroup)
            {
                if ($usergroup!= "")
                {
                    $wherequery.="$or groupview LIKE '$usergroup' OR groupview LIKE '%$usergroup' OR groupview LIKE '$usergroup%' ";
                    $or="OR";
                    $exists_group=true;
                }
            }
            $wherequery.=") ";
            $and=" AND ";
        }

        if ($order== "")
        {
            $order=$t->xmltable->primarykey;
            if ($desc== "")
                $desc=1;
        }

        if (isset($t->xmltable->fields['recorddeleted']))
        {
            $wherequery.="$and recorddeleted <> '1'";
            $and="AND";
        }
        if ($config['appendquery']!= "")
        {
            $wherequery.="$and {$config['appendquery']} ";
            $and="AND";
        }
        $method=" OR ";
        $endmethod="";
        //-----------------------ricerca del testo ---------------------------->
        $findtextquery="";
        $tmpmethod="";
        foreach($t->xmltable->fields as $fieldstoread=> $fieldvalues)
        {
            if ($fieldstoread!= "insert" && $fieldstoread!= "update" && $fieldstoread!= "unirecid" && $fieldstoread!= "unirecid" && $fieldvalues->type!= "check")
            {
                foreach($listfind as $f)
                {
                    if ($f!= "")
                    {
                        if (isset($fieldvalues->foreignkey) && isset($fieldvalues->fk_link_field))
                        {
                            $fk=FN_XmlTable($fieldvalues->foreignkey);
                            $fkshow=explode(",",$fieldvalues->fk_show_field);
                            $fkfields="";
                            if ($fieldvalues->fk_show_field!= "")
                                $fkfields=",".$fieldvalues->fk_show_field;
                            //prendo il primo
                            $fk_query="SELECT {$fieldvalues->fk_link_field}$fkfields FROM {$fieldvalues->foreignkey} WHERE ";
                            $or="";
                            foreach($fkshow as $fkitem)
                            {
                                $fk_query.="$or {$fkitem} LIKE '%".addslashes($f)."%'";
                                $or="OR";
                            }
                            if (!isset($listok[$f][$fieldvalues->foreignkey]))
                            {
                                $rt=FN_XMLQuery($fk_query);
                                $listok[$f][$fieldvalues->foreignkey]=$rt;
                            }
                            if (is_array($listok[$f][$fieldvalues->foreignkey]) && count($listok[$f][$fieldvalues->foreignkey]) > 0)
                            {
                                $findtextquery_tmp=" $tmpmethod (";
                                $m="";
                                $exists_tmp=false;
                                foreach($listok[$f][$fieldvalues->foreignkey] as $fk_item)
                                {
                                    //dprint_r($fk_item);
                                    $vv="";
                                    if (isset($fk_item[$fieldvalues->fk_link_field]))
                                    {
                                        $exists_tmp=true;
                                        $vv=str_replace("'","\\'",$fk_item[$fieldvalues->fk_link_field]);
                                        $findtextquery_tmp.="$m $fieldstoread = '$vv'";
                                        $m=" OR ";
                                    }
                                }
                                $findtextquery_tmp.=")";
                                if (!$exists_tmp)
                                    $findtextquery_tmp="";
                                $tmpmethod=$method;
                            }
                            else
                            {
                                $findtextquery_tmp=" $tmpmethod (".$fieldstoread." LIKE '%".addslashes($f)."%') ";
                            }
                            $findtextquery.=$findtextquery_tmp;
                        }
                        else
                        {
                            $findtextquery.=" $tmpmethod ".$fieldstoread." LIKE '%".addslashes($f)."%' ";
                        }
                        $tmpmethod=$method;
                    }
                }
                $tmpmethod=" OR ";
            }
        }
        if ($findtextquery!= "")
        {
            $wherequery.="$and ($findtextquery) ";
            $and="AND";
        }
        //-----------------------ricerca del testo ----------------------------<
        //---check ---->
        $_tables[$tablename]=FN_XmlForm($tablename);
        //dprint_r($_tables);
        foreach($search_options as $option)
        {
            $checkquery="";
            $tmet="";
            if (isset($_tables[$tablename]->formvals[$option]['options']) && is_array($_tables[$tablename]->formvals[$option]['options']))
            {
                foreach($_tables[$tablename]->formvals[$option]['options'] as $c)
                {
                    $otitle=$c['title'];
                    $ovalue=$c['value'];
                    $ogetid="s_opt_{$option}_{$tablename}_{$c['value']}";
                    $sopt=FN_GetParam($ogetid,$params,"html");
                    if ($sopt!= "")
                    {
                        $checkquery.=" $tmet $option LIKE '$ovalue' ";
                        $tmet="OR";
                    }
                }
            }
            if ($checkquery!= "")
            {
                $wherequery.="$and ($checkquery) ";
                $and="AND";
            }
        }
        //---check ----<
        //min---->
        $minquery="";
        $tmet="";
        foreach($search_min as $min)
        {
            if (isset($_tables[$tablename]->formvals[$min]))
            {
                $getmin=FN_GetParam("min_$min",$params,"html");
                if ($getmin!= "")
                {
                    $getmin=intval($getmin);
                    $minquery.=" $tmet $min > $getmin ";
                    $tmet="AND";
                }
            }
        }
        if ($minquery!= "")
        {
            $wherequery.="$and ($minquery) ";
            $and="AND";
        }
        //min----<
        //searchfields---->
        $sfquery="";
        $tmet="";
        foreach($search_fields as $sfield)
        {
            if (isset($_tables[$tablename]->formvals[$sfield]))
            {
                $get_sfield=FN_GetParam("sfield_$sfield",$params,"html");
                if ($get_sfield!= "")
                {
//                    $sfquery.=" $tmet ($sfield LIKE '$get_sfield' OR $sfield LIKE '$get_sfield.%') ";
                    $sfquery.=" $tmet ($sfield LIKE '$get_sfield') ";
                    $tmet="AND";
                }
            }
        }
        if ($sfquery!= "")
        {
            $wherequery.="$and ($sfquery) ";
            $and="AND";
        }
        //searchfields----<
        //searchpartfields---->
        $sfquery="";
        $tmet="";
        foreach($search_partfields as $sfield)
        {
            if (isset($_tables[$tablename]->formvals[$sfield]))
            {
                $get_sfield=FN_GetParam("spfield_$sfield",$params,"html");
                if ($get_sfield!= "")
                {
                    $sfquery.=" $tmet $sfield LIKE '%$get_sfield%' ";
                    $tmet="AND";
                }
            }
        }
        if ($sfquery!= "")
        {
            $wherequery.="$and ($sfquery) ";
            $and="AND";
        }
        //searchpartfields----<
        //-----------------------record is visible only creator---------------->
        if ($config['viewonlycreator']== 1)
        {
            if (!FN_IsAdmin())
            {

                if ($_FN['user']!= "")
                {
                    $wherequery.="$and (username LIKE '{$_FN['user']}' OR username LIKE '%,{$_FN['user']}' OR username LIKE '%,{$_FN['user']},%' OR username LIKE '%,{$_FN['user']}') ";


                    $listusers=FN_XmlTable("fieldusers");
                    $MyRecords=$listusers->GetRecords(array("tablename"=>$tablename,"username"=>$_FN['user']));
                    if (is_array($MyRecords))
                    {
                        foreach($MyRecords as $MyRecord)
                        {
                            $wherequery.="OR {$_tables[$tablename]->xmltable->primarykey} = '{$MyRecord['table_unirecid']}'";
                        }
                    }
                }
            }
            $and="AND";
        }
        //-----------------------record is visible only creator----------------<
        if ($navigate== 1)
        {
            $groupquery="";
            $tmet="";
            foreach($groups as $group)
            {
                if (isset($_REQUEST["nv_{$group}"]))
                {
                    $navigate=FN_GetParam("nv_{$group}",$params);
                    $groupquery.="$tmet $group LIKE '".addslashes($navigate)."' ";
                    $tmet="AND";
                }
            }
            if ($groupquery!= "")
            {
                $wherequery.="$and ($groupquery) ";
                $and="AND";
            }
        }

        if ($wherequery== "")
            $wherequery="1";
        $orderquery="";
        if ($order!= "")
        {
            $orderquery.=" ORDER BY $order";
            if ($desc!= "")
                $orderquery.=" DESC";
        }
        $query="$query $wherequery $orderquery";
        $usenative=true;
        if (isset($_GET['debug']))
        {
            dprint_r(__FILE__." ".__LINE__." : ".FN_GetExecuteTimer());
        }
        $query =str_replace("\n","",$query);
        $query =str_replace("\r","",$query);
        
        if (!empty($config['search_query_native_mysql']))
        {
            $xmltable=FN_XmlTable($tablename);
            $query=str_replace("FROM $tablename WHERE","FROM {$xmltable->driverclass->sqltable} WHERE",$query);
            $res=$xmltable->driverclass->dbQuery($query);
        }
        else
        {
            $res=FN_XMLQuery($query);
        }

        //DEBUG: print query
        if (isset($_GET['debug']))
//        if (FN_IsAdmin())
        {
            dprint_r ($query);
            dprint_r ($_REQUEST);
            dprint_r ($orderquery);
            dprint_r(__FILE__." ".__LINE__." : ".FN_GetExecuteTimer());
        }
//----------------export------------------------------------------------------->
        if (!empty($res) && !empty($config['enable_export']) && isset($_GET['export']))
        {
            $first=true;
            $csvres=array();
            foreach($res as $row)
            {
                $rec=$_tables[$tablename]->xmltable->GetRecordByPrimarykey($row[$_tables[$tablename]->xmltable->primarykey]);
                if ($first)
                {
                    $first=false;
                    foreach($rec as $k=> $v)
                    {
                        $title=$k;
                        if (isset($_tables[$tablename]->formvals[$k]['title']))
                            $title=$_tables[$tablename]->formvals[$k]['title'];
                        $r[$k]=$title;
                    }
                    $csvres[]=$r;
                }
                $csvres[]=$rec;
                //break;
            }
            FNDBVIEW_SaveToCSV($csvres,"export.csv");
        }
//----------------export------------------------------------------------------->		
    }
    //  dprint_r(__LINE__." : ".FN_GetExecuteTimer());

    return $res;
}

/**
 *
 * @param type $data 
 */
function FNDBVIEW_SaveToCSV($data,$filename)
{
    $sep=",";
    $str="";
    foreach($data as $row)
    {
        $arraycols=array();
        foreach($row as $cell)
        {
            $arraycols[]="\"".str_replace("\"","\"\"",$cell)."\"";
        }
        $str.=implode($sep,$arraycols)."\n";
    }
    FN_SaveFile($str,$filename,"application/vnd.ms-excel");
}

/**
 *
 * @global array $_FN
 * @param type $params
 * @param type $sep
 * @param type $norewrite
 * @return string 
 */
function FNDBVIEW_MakeLink($params=false,$sep="&amp;",$norewrite=false)
{

    global $_FN;
    $blank="____k_____";
    $register=array("mod","op","q","page","order","desc","nav","rule","viewmode");
    $search_min="";
    $search_options="";
    $navigate_groups="";
    $search_fields="";
    $search_partfields="";
    $config=FN_LoadConfig();
    $tmp=explode(",",$config['search_min']);
    foreach($tmp as $key)
    {
        $register[]="min_".$key;
    }
    $tmp=explode(",",$config['search_fields']);
    foreach($tmp as $key)
    {
        $register[]="sfield_".$key;
    }
    $tmp=explode(",",$config['search_partfields']);
    foreach($tmp as $key)
    {
        $register[]="spfield_".$key;
    }
    $link=array();
    foreach($_REQUEST as $key=> $value)
    {
        if (in_array($key,$register) || fn_erg("^s_opt_",$key) || fn_erg("^mint_",$key) || fn_erg("^nv_",$key))
        {
            $link[$key]="$key=".FN_GetParam("$key",$_REQUEST);
        }
    }
    if (is_array($params))
    {
        foreach($params as $key=> $value)
        {
            $link[$key]="$key=".urlencode($params[$key]);
            if ($params[$key]=== null)
                unset($link[$key]);
            elseif ($params[$key]=== "")
                $link[$key]="$key=$blank";
        }
    }

    $link="index.php?".implode($sep,$link);
    if ($norewrite)
        return $_FN['siteurl'].str_replace($blank,"",$link);
    return str_replace($blank,"",FN_RewriteLink($link,$sep,true));
}

/**
 *
 * @param type $text
 * @param type $blacklist
 * @return type 
 */
function FNDBVIEW_SecureHtml($text,$blacklist="script,iframe,frame,object,embed")
{
    $blacklist=explode(",",$blacklist);
    $ok=false;
    while($ok== false)
    {
        $ok=true;
        foreach($blacklist as $itemtag)
        {
            while(preg_match("/<$itemtag/s",$text))
            {
                $ok=false;
                $text=preg_replace("/<$itemtag/s","",$text);
                $text=preg_replace("/<\\/$itemtag>/s","",$text);
            }
        }
    }
    return $text;
}

/**
 *
 * @global array $_FN
 * @param type $file 
 */
function FNDBVIEW_GoDownload($file)
{
    global $_FN;
    //--config-->
    $config=FN_LoadConfig();
    $tables=explode(",",$config['tables']);
    $tablename=$tables[0];
    //--config--<
    // evita di accedere a directory esterne
    if (stristr($file,".."))
        die(FN_Translate("you may not do that"));
    // se il file non esiste lo crea

    if ($config['enablestats']== 1)
    {
        if (!file_exists("{$_FN['datadir']}/{$_FN['database']}/$tablename"."_download_stat") || !file_exists("{$_FN['datadir']}/{$_FN['database']}/$tablename"."_stat.php"))
        {
            //$html .= "<br>creazione statistiche $tablename";
            $sfields=array();
            $sfields[1]['name']="filename";
            $sfields[1]['primarykey']="1";
            $sfields[2]['name']="numdownload";
            $sfields[2]['defaultvalue']="0";
            createxmltable($_FN['database'],$tablename."_download_stat",$sfields,$pathdatabase);
        }
        $stat=FN_XmlTable($tablename."_download_stat");
        $oldval=$stat->GetRecordByPrimaryKey($file);
        $r['filename']=$file;
        if ($oldval== null)
        {
            $r['numdownload']=1;
            $stat->InsertRecord($r);
        }
        else
        {
            //incrementa download
            $r['numdownload']=$oldval['numdownload'] + 1;
            $stat->UpdateRecord($r);
        }
    }
    FN_SaveFile("{$_FN['datadir']}/{$_FN['database']}/$tablename/$file");
}

/**
 *
 * @param string $unirecid 
 */
function FNDBVIEW_GetUsersComments($unirecid)
{
    global $_FN;
    //--config-->
    $config=FN_LoadConfig();
    $tables=explode(",",$config['tables']);
    $tablename=$tables[0];
    //--config--<
    $comments=FN_XMLQuery("SELECT DISTINCT username FROM {$tablename}_comments WHERE unirecidrecord LIKE '$unirecid'");
    $ret=false;
    foreach($comments as $comment)
    {
        $user=FN_GetUser($comment['username']);
        if (isset($user['email']))
        {
            $ret[$user['email']]=$user;
        }
    }
    return $ret;
}

/**
 * writecomment
 * aggiunge un commento al record
 * 
 * @param string unirecid record
 */
function FNDBVIEW_WriteComment($unirecid)
{
    global $_FN;
    //--config-->
    $config=FN_LoadConfig();
    $html="";
    $tables=explode(",",$config['tables']);
    $tablename=$tables[0];
    //--config--<
    $tablelinks=FN_XmlForm("$tablename"."_comments");
    $tablelinks->SetLayout("flat");
    $err=$newvalues=array();
    $exitlink=FNDBVIEW_MakeLink(array("op"=>"view","id"=>$unirecid),"&");
    $formlink=FNDBVIEW_MakeLink(array("op"=>"writecomment","id"=>$unirecid),"&");
    if (isset($_POST['comment']))
    {
        $newvalues=$tablelinks->getbypost();
        $newvalues['comment']=htmlspecialchars($newvalues['comment']);
        $newvalues['unirecidrecord']=$unirecid;
        $newvalues['username']=$_FN['user'];
        $newvalues['insert']=time();
        $err=$tablelinks->Verify($newvalues);
        if (count($err)== 0)
        {
            $tablelinks->xmltable->InsertRecord($newvalues);
//---------- send mail -------------------------------------------------------->
            if (!empty($config['enable_comments_notify']))
            {
                $Table=FN_XmlForm($tablename);
                $row=$Table->xmltable->GetRecordByPrimarykey($unirecid);
                $uservalues=FN_GetUser($newvalues['username']);
                $rname=$row[$Table->xmltable->primarykey];
                if (isset($row['name']))
                    $rname=$row['name'];
                else
                {
                    foreach($Table->xmltable->fields as $gk=> $g)
                    {
                        if (!isset($g->frm_show) || $g->frm_show!= 0)
                        {
                            $rname=$row[$gk];
                            break;
                        }
                    }
                }
                $usercomments=FNDBVIEW_GetUsersComments($unirecid);
                if (!empty($uservalues['email']))
                {
                    $usercomments[$uservalues['email']]=$uservalues;
                }

                $userlang=$_FN['lang_default'];
                $usersended=array();
                //-------email to comment ownwer------------------------------->
                foreach($usercomments as $usercomment)
                {
                    if (isset($usercomment['lang']))
                        $userlang=$usercomment['lang'];
                    //dprint_r($uservalues);
                    //dprint_r($usercomment);
                    if ($uservalues['email']== $usercomment['email']) //onwer
                    {
                        $body=$_FN['user']." ".FN_Translate("added a comment to your content","aa");
                    }
                    else
                    {
                        $body=$_FN['user']." ".FN_Translate("added a comment","aa");
                    }
                    $body.="<br /><br />$rname<br /><br />".FN_Translate("to see the comments go to this address","aa",$userlang);
                    $link=FN_RewriteLink("index.php?mod={$_FN['mod']}&op=view&id=$unirecid","&",true);
                    $body.="<br /><a href=\"$link\">$link</a><br /><br />";
                    if (!isset($usersended[$usercomment['email']]))
                    {
                        FN_SendMail($usercomment['email'],$_FN['sitename']."-".$_FN['sectionvalues']['title'],$body,true);
                    }
                    $usersended[$usercomment['email']]=$usercomment['email'];
                }
                //-------email to comment ownwer-------------------------------<
                //-------email to recotd ownwer-------------------------------->
                $MyTable=FN_XmlForm($tablename);
                $Myrow=$MyTable->xmltable->GetRecordByPrimaryKey($unirecid);
                $Myuser_record=FN_GetUser($Myrow['username']);
                if (!isset($usersended[$Myuser_record['email']]))
                {
                    FN_SendMail($Myuser_record['email'],$_FN['sitename']."-".$_FN['sectionvalues']['title'],$body,true);
                }
                //-------email to recotd ownwer--------------------------------<
            }
//---------- send mail --------------------------------------------------------<
        }
        $html.=FN_Translate("the message has been sent")."<br />";
        $html.="<button type=\"button\" class=\"button\" onclick=\"window.location='$exitlink'\" >".FN_Translate("next")."</button>";
        return $html;
    }

    if ($_FN['user']!= "" && $unirecid!= "")
    {
        $html.="<br />";
        $html.="\n<form method=\"post\" enctype=\"multipart/form-data\" action=\"$formlink\" >";
        $html.="\n<table>";
        $html.="\n<tr><td colspan=\"2\"><b>".FN_Translate("add comment")."</b></tr></td>";
        $html.="\n<tr><td colspan=\"2\">".FN_Translate("required fields")."</tr></td>";
        $html.="\n<tr><td colspan=\"2\">";
        $html.=FN_htmlBbcodesPanel("comment","formatting");
        $html.=FN_htmlBbcodesPanel("comment","emoticons");
        $html.=FN_htmlBbcodesJs();
        $html.="<br />";
        $html.=$tablelinks->HtmlShowInsertForm(false,$newvalues,$err);
        $html.="\n</td></tr>";
        $html.="\n<tr><td colspan=\"2\"><input class=\"submit\" type=\"submit\" value=\"".FN_Translate("save")."\"/>";

        $html.="<input type='button' class='button' onclick='window.location=(\"$exitlink\")'  value='".FN_Translate("cancel")."' />";
        $html.="</tr></td>";
        $html.="\n";
        $html.="\n</table>";
        $html.="\n</form>";
    }
    return $html;
}

/**
 * delcomment
 * 
 * elimina un commento dal record
 * 
 * @param string unirecid record 
 */
function FNDBVIEW_DelComment($unirecid)
{
    global $_FN;
//--config-->
    $config=FN_LoadConfig();
    $tables=explode(",",$config['tables']);
    $tablename=$tables[0];
//--config--<
    $html="";
    $tablelinks=FN_XmlForm("$tablename"."_comments");
    if (FN_IsAdmin() && isset($_GET['unirecidrecord']) && $_GET['unirecidrecord']!= "")
    {
        $r['unirecid']=$_GET['unirecidrecord'];
        $tablelinks->xmltable->DelRecord($r['unirecid']);
        $html.=FN_Translate("the comment was deleted")."<br />";
        FN_Log("{$_FN['mod']}",$_SERVER['REMOTE_ADDR']."||".$_FN['user']."||Table $tablename delete comments in record $unirecid");
        $Table=FN_XmlForm($_FN['database']);
        $newvalues=$Table->xmltable->GetRecordByPrimaryKey($unirecid);
        $newvalues['update']=time();
        $Table->xmltable->UpdateRecord($newvalues);
    }
    return $html;
}


/**
 *
 * @global array $_FN
 * @param object $Table 
 */
function FNDBVIEW_UpdateRecord($Table)
{
    global $_FN;
//--config-->
    $config=FN_LoadConfig();
    $tables=explode(",",$config['tables']);
    $tablename=$tables[0];
//--config--<
    $Table=FN_XmlForm($tablename);
    $username=$_FN['user'];
    if ($username== "")
        die(FN_Translate("you may not do that"));
    $newvalues=$Table->getbypost();
    if (isset($_POST["_xmldbform_pk_".$Table->xmltable->primarykey]))
        $pkold=FN_GetParam("_xmldbform_pk_".$Table->xmltable->primarykey,$_POST);
    else
        $pkold=FN_GetParam($Table->xmltable->primarykey,$_POST);
    $pk=FN_GetParam($Table->xmltable->primarykey,$_POST);
    $oldvalues=$Table->xmltable->GetRecordByPrimarykey($pkold);
    if (!FNDBVIEW_CanAddRecord() && !FNDBVIEW_UserCanEditField($username,$oldvalues))
        return (FN_Translate("you may not do that"));
    $toupdate=false;
    if (is_array($oldvalues))
        foreach($oldvalues as $k=> $v)
        {
            if (isset($newvalues[$k]) && $oldvalues[$k]!== $newvalues[$k])
            {
                $toupdate=true;
                break;
            }
            if (isset($newvalues[$k]) && $newvalues[$k]!= "" && $oldvalues[$k]== $newvalues[$k] && ($Table->xmltable->fields[$k]->type== "file" || $Table->xmltable->fields[$k]->type== "image"))
            {
                $filename=$Table->xmltable->getFilePath($oldvalues,$k);
                if (filesize($filename)!= filesize($_FILES[$k]['tmp_name']))
                {
                    // die ("$filename toupdate");
                    $toupdate=true;
                    break;
                }
            }
        }
    $newvalues['update']=time();
    foreach($Table->formvals as $f)
    {
        if (isset($newvalues[$f['name']]) && isset($Table->formvals[$f['name']]['frm_uppercase']))
        {
            if ($Table->formvals[$f['name']]['frm_uppercase']== "uppercase")
            {
                $_POST[$f['name']]=$newvalues[$f['name']]=strtoupper($newvalues[$f['name']]);
            }
            elseif ($Table->formvals[$f['name']]['frm_uppercase']== "lowercase")
            {
                $_POST[$f['name']]=$newvalues[$f['name']]=strtolower($newvalues[$f['name']]);
            }
        }
        if (isset($Table->formvals[$f['name']]['frm_onrowupdate']) && $Table->formvals[$f['name']]['frm_onrowupdate']!= "")
        {
            $dv=$Table->formvals[$f['name']]['frm_onrowupdate'];
            $fname=$f['name'];
            $rv="";
            eval("\$rv=$dv;");
            eval("\$newvalues"."['$fname'] = '$rv' ;");
        }
    }

    $errors=$Table->VerifyUpdate($newvalues,$pkold);
    if ($pkold!= $pk)
    {
        $newExists=$Table->xmltable->GetRecordByPrimaryKey($pk);
        if (isset($newExists[$Table->xmltable->primarykey]))
        {
            $newvalues[$Table->xmltable->primarykey]=$pkold;
            $errors[$Table->xmltable->primarykey]=array("title"=>$Table->formvals[$Table->xmltable->primarykey]['title'],"field"=>$Table->xmltable->primarykey,"error"=>FN_Translate("there is already an item with this value"));
        }
    }
    if (count($errors)== 0)
    {
        if (FN_IsAdmin())
        {
            if (!isset($_POST['userupdate']) || $_POST['userupdate']== "")
            {
                $_POST['userupdate']=$newvalues['userupdate']=$_FN['user'];
            }
        }
        else
            $newvalues['userupdate']=$_FN['user'];
        //-----verifica se sono abilitato all' aggiornamento ---------------
        if ($toupdate)
        {
            //--------------history-------------------------------------------->
            $newvalues['recordupdate']=xmldb_now();
            if ($config['enable_history'])
            {
                $_FILES_bk=$_FILES;
                $_FILES=array();
                $tv=FN_XmlTable($tablename."_versions");
                foreach($Table->xmltable->fields as $k=> $v)
                {
                    if (($v->type== "file" || $v->type== "image") && $oldvalues[$k]!= "")
                    {
                        $oldfile=$Table->xmltable->getFilePath($oldvalues,$k);
                        $_FILES[$k]['name']=$oldvalues[$k];
                        $_FILES[$k]['tmp_name']=$oldfile;
                    }
                }
                $bb=$tv->InsertRecord($oldvalues);
                $_FILES=$_FILES_bk;
            }
            //--------------history--------------------------------------------<
            $Table->UpdateRecord($newvalues,$pkold);
            FN_Log("{$_FN['mod']}",$_SERVER['REMOTE_ADDR']."||".$_FN['user']."||Table $tablename modified.");
            FN_Alert(FN_Translate("record updated"));
        }
    }
    if (isset($_FN['modparams'][$_FN['mod']]['editorparams']['table']['function_on_update']))
    {
        $function=$_FN['modparams'][$_FN['mod']]['editorparams']['table']['function_on_update'];
        if (function_exists($function))
        {
            $function($newvalues);
        }
    }

    return FNDBVIEW_EditRecordForm($newvalues[$Table->xmltable->primarykey],$Table,$errors);
}

/**
 * insert record
 * @global array $_FN
 * @param type $Table 
 */
function FNDBVIEW_InsertRecord($Table)
{
    global $_FN;
//--config-->
    $config=FN_LoadConfig();
    $tables=explode(",",$config['tables']);
    $tablename=$tables[0];
//--config--<
    $html="";
//-----verifica se sono abilitato all' inserimento ---------------
    $username=$_FN['user'];
    if (!FNDBVIEW_CanAddRecord())
        die(FN_Translate("you may not do that"));
    $newvalues=$Table->getbypost();
    $newvalues['insert']=time();
    $newvalues['update']=time();
    $newvalues['username']=$username;
    foreach($Table->formvals as $f)
    {
        if (isset($newvalues[$f['name']]) && isset($Table->formvals[$f['name']]['frm_uppercase']))
        {
            if ($Table->formvals[$f['name']]['frm_uppercase']== "uppercase")
            {
                $_POST[$f['name']]=$newvalues[$f['name']]=strtoupper($newvalues[$f['name']]);
            }
            elseif ($Table->formvals[$f['name']]['frm_uppercase']== "lowercase")
            {
                $_POST[$f['name']]=$newvalues[$f['name']]=strtolower($newvalues[$f['name']]);
            }
        }
        if ((isset($Table->formvals[$f['name']]['frm_onrowupdate']) && $Table->formvals[$f['name']]['frm_onrowupdate']!= ""))
        {
            $dv=$Table->formvals[$f['name']]['frm_onrowupdate'];
            $fname=$f['name'];
            $rv="";
            eval("\$rv=$dv;");
            eval("\$newvalues"."['$fname'] = '$rv' ;");
        }
    }
    //dprint_r($newvalues);
    //die();
    $errors=$Table->VerifyInsert($newvalues);

    if (count($errors)== 0)
    {
        $newvalues['recordupdate']=xmldb_now();
        $newvalues['recordinsert']=xmldb_now();
        $newvalues['userupdate']=$_FN['user'];
        $newvalues['username']=$_FN['user'];

        if (!empty($config['enable_permissions_edit_each_records']) && $config['enable_permissions_edit_each_records']== 1)
        {
            if ($config['permissions_records_edit_groups']!= "")
            {
                $allAllowedGroups=explode(",",$config['permissions_records_edit_groups']);
                $groupinsert=array();
                foreach($allAllowedGroups as $allAllowedGroup)
                {
                    if ($allAllowedGroup!= "" && FN_UserInGroup($_FN['user'],$allAllowedGroup))
                    {
                        $groupinsert[]=$allAllowedGroup;
                    }
                }
                $groupinsert=implode(",",$groupinsert);
                if (!FNDBVIEW_IsAdmin())
                {
                    $newvalues['groupinsert']=$groupinsert;
                }
            }
        }
        $record=$Table->xmltable->InsertRecord($newvalues);
        $nrec=array();
        // se esistono i campi "visualizzato volte"
        if (isset($record['view']))
        {
            $nrec['view']=$record[$Table->xmltable->primarykey];
            $nrec[$Table->xmltable->primarykey]=$record[$Table->xmltable->primarykey];
            $record=$Table->xmltable->UpdateRecord($nrec);
        }
        //------ aggiorno la tabella degli utenti associati alla riga
        $users=FN_XmlTable("fieldusers");
        $r=array();
        $r['tablename']=$tablename;
        $r['username']=$username;
        $r['table_unirecid']=$record[$Table->xmltable->primarykey];
        $users->InsertRecord($r);

        if (isset($_FN['modparams'][$_FN['mod']]['editorparams']['table']['function_on_insert']))
        {
            $function=$_FN['modparams'][$_FN['mod']]['editorparams']['table']['function_on_insert'];
            if (function_exists($function))
            {
                $function($newvalues);
            }
        }

        FN_Log("{$_FN['mod']}",$_SERVER['REMOTE_ADDR']."||".$username."||Table $tablename record added.");
        $html.=FN_HtmlAlert(FN_Translate("the data were successfully inserted"));
        //----mail inserimento nuovo record -------->
        if (!empty($config['mailalert']))
        {
            $subject=FN_Translate("created new record in")." {$_FN['sectionvalues']['title']}";
            if (!empty($record['name']))
                $subject.=": ".$record['name'];
            $body="\n".FN_Translate("posted by")." ".$r['username'];
            $body.="\n\n".FN_Translate("to view go to the address").": ";
            $body.="\n".$_FN['siteurl']."/index.php?mod={$_FN['mod']}&op=view&id=".$record[$Table->xmltable->primarykey];
            $body.="\n\n".$_FN['sitename']."";
            FN_SendMail($config['mailalert'],$subject,$body,false);
        }
        //----mail inserimento nuovo record --------<
        $html.=FNDBVIEW_EditRecordForm($record[$Table->xmltable->primarykey],$Table,$errors,true);
    }
    else
    {
        $html.=FNDBVIEW_NewRecordForm($Table,$errors);
    }
    return $html;
}

function FNDBVIEW_IsAdmin()
{
    if (FN_IsAdmin())
        return true;
    global $_FN;
    $config=FN_LoadConfig();
    if (!empty($config['groupadmin']) && FN_UserInGroup($_FN['user'],$config['groupadmin']))
        return true;
    return false;
}

/**
 * 
 * @global array $_FN
 * @param type $row
 * @param type $tablename
 * @param type $databasename
 * @param type $pathdatabase
 * @return type
 */
function FNDBVIEW_GetFieldUser($row,$tablename,$databasename,$pathdatabase)
{
    global $_FN;
    $listusers=FN_XmlTable("fieldusers");
    $t=FN_XmlTable($tablename);
//restrizioni per la 'pseudoquery'
    $restr=array();
    $field['username']='-';
    $restr['table_unirecid']=$row[$t->primarykey];
    $restr['tablename']=$tablename;
    $listusers=FN_XmlTable("fieldusers");
    $field=$listusers->GetRecord($restr);
    return $field['username'];
}

/**
 * getFieldUserList
 * torna gli utenti abilitati a modificare un record
 * @param string $row
 * @param string $tablename
 * @param string $databasename
 * @param string $pathdatabase
 */
function FNDBVIEW_GetFieldUserList($row,$tablename,$usecache=true)
{
    static $userPerm=false;
    $t=FN_XmlTable($tablename);
    if (!$userPerm || !$usecache)
    {
        $listusers=FN_XmlTable("fieldusers");
        $userPerm=$listusers->GetRecords();
    }
    $ret=array();
    foreach($userPerm as $row_perm)
    {
        if ($row[$t->primarykey]== $row_perm['table_unirecid'] && $tablename== $row_perm['tablename'])
        {
            $ret[]=$row_perm;
        }
    }
    return $ret;
}

/**
 * isUser
 * torna true se l' utente corrente pu? modificare il record
 * @param string $row
 * @param string $tablename
 * @param string $databasename
 * @param string $pathdatabase */
function FNDBVIEW_IsAdminRecord($row)
{
    global $_FN;
//--config-->
    $config=FN_LoadConfig();
    $tablename=$config['tables'];
//--config--<
    if (FN_IsAdmin())
        return true;
    $user=$_FN['user'];
    if ($_FN['user']== "")
        return false;
    if (isset($row['username']) && $row['username']== $_FN['user'])
        return true;
    if (isset($row['user']) && $row['user']== $user)
        return true;
    if ($_FN['user']!= "" && $config['groupadmin']!= "" && FN_UserInGroup($_FN['user'],$config['groupadmin']))
    {
        return true;
    }
    //permessi per ogni record------------------------------------------------->
    if (empty($config['viewonlycreator']))
    {
        if (!empty($config['enable_permissions_edit_each_records']) && $config['enable_permissions_edit_each_records']== 1)
        {
            $record=$row;
            if (empty($record['groupinsert']))
            {
                return true;
            }
            else
            {
                $groups_can_insert=explode(",",$record['groupinsert'].",".$config['groupadmin']);
                foreach($groups_can_insert as $gr_can_insert)
                {
                    if ($gr_can_insert!= "" && FN_UserInGroup($_FN['user'],$gr_can_insert))
                    {
                        return true;
                    }
                }
                return false;
            }
        }
        else
        {
            /*
              if (empty($record['groupinsert']) )
              {

              return true;
              }
             * 
             */
        }
    }
    //permessi per ogni record-------------------------------------------------<	

    if (FNDBVIEW_UserCanEditField($user,$row))
    {
        return true;
    }

    return false;
}

/**
 * canaddrecord
 * return true if user can add record
 */
function FNDBVIEW_CanAddRecord()
{
    global $_FN;
    if (FN_IsAdmin())
        return true;

    $config=FN_LoadConfig();
    //dprint_r($config);
//include ("sections/" . $_FN['mod'] . "/config.php");
    if ($_FN['user']!= "" && $config['groupadmin']!= "" && FN_UserInGroup($_FN['user'],$config['groupadmin']))
        return true;
    if ($_FN['user']!= "" && $config['groupinsert']!= "" && FN_UserInGroup($_FN['user'],$config['groupinsert']))
        return true;
    if ($_FN['user']!= "" && $config['groupinsert']== "")
        return true;
    return false;
}

/**
 * canaddrecord
 * return true if user can view record
 *
 */
function FNDBVIEW_CanViewRecords()
{
    global $_FN;
    if (FN_IsAdmin())
        return true;
    $config=FN_LoadConfig();
    if ($_FN['user']!= "" && $config['groupadmin']!= "" && FN_UserInGroup($_FN['user'],$config['groupadmin']))
        return true;
    if ($_FN['user']!= "" && $config['groupview']!= "" && FN_UserInGroup($_FN['user'],$config['groupview']))
        return true;
    if ($_FN['user']!= "" && $config['groupinsert']!= "" && FN_UserInGroup($_FN['user'],$config['groupinsert']))
        return true;
    if ($config['groupview']== "")
        return true;
    return false;
}

/**
 *
 * @global array $_FN
 * @param string $user
 * @param array $row
 * @return bool
 */
function FNDBVIEW_UserCanEditField($user,$row)
{
    global $_FN;
//--config-->
    $config=FN_LoadConfig();
    $tables=explode(",",$config['tables']);
    $tablename=$tables[0];
//--config--<
    if ($user== "")
        return false;
    $t=FN_XmlTable($tablename);
    $restr=array();
//    dprint_r($row);
    $restr['table_unirecid']=$row[$t->primarykey];
    $restr['tablename']=$tablename;
    $restr['username']=$user;
    $list_field=FNDBVIEW_GetFieldUserList($row,$tablename,$_FN['database']);
    $unirecid=$row[$t->primarykey];
    if (is_array($list_field))
        foreach($list_field as $field)
        {

            if ($field['username']== $user && $field['table_unirecid']== $row[$t->primarykey] && $field['tablename']== $tablename)
                return true;
        }
    return false;
}

/**
 * @global array $_FN
 */
function FNDBVIEW_WriteSitemap()
{
    global $_FN;
//--config-->
    $config=FN_LoadConfig();
    $tables=explode(",",$config['tables']);
    $tablename=$tables[0];
    $titlef=explode(",",$config['titlefield']);
    $titlef=$titlef[0];
//--config--<

    if ($config['generate_googlesitemap'])
    {
        $sBasePath=$url="http://".$_SERVER["HTTP_HOST"].DirName($_SERVER['PHP_SELF']);
        $Table=FN_XmlTable($tablename);
        $fieldstoread="$titlef|".$Table->primarykey;
        $data=$Table->GetRecords(false,false,false,false,false,$fieldstoread);
        $handle=fopen("sitemap-$tablename.xml","w");
        fwrite($handle,"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.google.com/schemas/sitemap/0.84\">\n");
        fwrite($handle,"<url>\n\t<loc>$sBasePath/index-$tablename.html</loc>\n</url>\n");
        foreach($data as $row)
        {
            $unirecid=$row[$Table->primarykey];
            fwrite($handle,"<url>\n\t<loc>$sBasePath/".FN_RewriteLink("index.php?mod={$_FN['mod']}&amp;op=view&amp;id=$unirecid")."</loc>\n</url>\n");
        }
        fwrite($handle,"\n</urlset>");
        fclose($handle);
    }
    FNDBVIEW_GenerateRSS();
}

function FNDBVIEW_GenerateRSS()
{
    
}
/**
 *
 * @global array $_FN
 * @param string $unirecid
 */
function FNDBVIEW_Request($unirecid)
{
    global $_FN;
//--config-->
    $config=FN_LoadConfig();
    $tables=explode(",",$config['tables']);
    $tablename=$tables[0];
//--config--<
    $html="";
    if ($_FN['user']== "")
    {
        FN_JsRedirect(FN_RewriteLink("index.php?mod={$_FN['mod']}&op=view&id=$unirecid"));
        return "";
    }
    if (isset($_POST['message']))
    {
        $Table=FN_XmlForm($tablename);
        $row=$Table->xmltable->GetRecordByPrimaryKey($unirecid);
        if (!empty($row['username']))
        {
            $user_record=FN_GetUser($row['username']);
        }
        else
        {

            $user_record=array("username"=>"");
        }
        $Table=FN_XmlTable($tablename);
        $rname=$row[$Table->primarykey];
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
        $user=FN_GetUser($user_record['username']);

        $subject="[{$_FN['sitename']}] ".$rname;
        $message=$_FN['user']." ".FN_Translate("has requested to modify this content","aa")." \"".$rname."\"\n\n\n";
        $message.=FN_Translate("to allow editing do login","aa")." ".$_FN['siteurl']."index.php?mod=login\n";
        $message.=FN_Translate("and login as user","aa").": \"".$user_record['username']."\"\n\n\n";
        $message.=FN_Translate("go to edit this content or log in","aa")." :\n".$_FN['siteurl']."index.php?mod={$_FN['mod']}&op=edit&id=$unirecid\n";
        $message.=FN_Translate("then click on -user allowed to edit- and manage the permissions","aa")." "."\"{$_FN['user']}\"";
        $message.="\n\n----------------------\n";
        $message.="\n".FN_StripPostSlashes($_POST['message']);
        if (!empty($user['email']) && FN_SendMail($user['email'],$subject,$message))
        {
            $html.="<br />".FN_Translate("request sent")."<br />";
        }
        else
        {
            $html.="<br />".FN_Translate("you can not send your request, please contact the administrator of the website")."<br />";
        }
        FN_Log("{$_FN['mod']}",$_SERVER['REMOTE_ADDR']."||".$_FN['user']."||request ".$rname." in table $tablename.");
    }
    else
    {
        $html.=FN_Translate("the creator of the object will be contacted to request you to be allowed. You can add comments in the box below.")."<br />";
        $html.="<form method=\"post\" action=\"index.php?mod={$_FN['mod']}&amp;op=request&amp;id=$unirecid\">";
        $html.="<textarea name=\"message\" cols=\"60\" rows=\"5\"></textarea><br />";
        $html.="<input type=\"submit\"  name=\"send\" value=\"".FN_Translate("demand modification")."\" class=\"submit\" />";
        $link=FNDBVIEW_MakeLink(array("op"=>null),"&");
        $html.="\n<input type=\"button\" onclick=\"window.location='$link'\" class=\"button\" value=\"".FN_Translate("cancel")."\" />";
        $html.="</form>";
    }
    $link=FNDBVIEW_MakeLink(array("op"=>null),"&");
    $tit=FN_Translate("back");
    $html.="<br /><br /><button onclick=\"window.location='$link'\"><img border=\"0\" style=\"vertical-align:middle\" src=\"".FN_FromTheme("images/left.png")."\" alt=\"\">&nbsp;".FN_Translate("go to the contents list")."</button>";
    return $html;
}


/**
 * ricava il rank di una scheda
 *
 * @param int $id
 * @return int
 */
function FNDBVIEW_GetRank($id,&$n,$tablename)
{
    global $_FN;
//--config-->
    $config=FN_LoadConfig();
    if ($tablename== "")
    {
        $tables=explode(",",$config['tables']);
        $tablename=$tables[0];
    }
//--config--<
    $table=FN_XmlTable($tablename."_ranks");
    $res=$table->GetRecords(array("unirecidrecord"=>"$id"));
    $total=0;
    if (!is_array($res))
        $res=array();
    $n=count($res);
    if ($n== 0)
        return -1;
    foreach($res as $r)
    {
        $total+=$r['rank'];
    }
    $m=round(($total / $n),0);
    return $m;
}


/**
 *
 * @global array $_FN
 * @global <type> $databasename
 * @param <type> $id
 * @param <type> $rank
 * @param <type> $tablename
 * @return <type> 
 */
function FNDBVIEW_SetRank($id,$rank,$tablename)
{
    global $_FN;
    $rank=intval($rank);
    if ($rank > 5 || $rank < 0)
        return;
    $table=FN_XmlTable("{$tablename}_ranks");

    $table->InsertRecord(array("unirecidrecord"=>"$id","rank"=>$rank));
}


function FNDBVIEW_CanEditRecord($id,$tablename)
{
    global $_FN;
    if (FN_IsAdmin())
        return true;
    $config=FN_LoadConfig();
    //----if inner table is in other section----------------------------------->
    if ($config['tables']!= $tablename)
    {
        foreach($_FN['sections'] as $section)
        {
            if ($section['type']== "navigator" || $section['type']== "dbview")
            {
                $configTmp=FN_LoadConfig("",$section['id']);
                if ($configTmp['tables']== $tablename)
                {
                    $config=$configTmp;
                    if (!FN_UserCanViewSection($section['id']))
                    {
                        return false;
                    }
                    break;
                }
            }
        }
    }
    //----if inner table is in other section-----------------------------------<
    return true;
}

/**
 * return true if user can view record
 * @global array $_FN
 * @param string $id
 * @param string $tablename
 * @return boolean
 */
function FNDBVIEW_CanViewRecord($id,$tablename)
{
    global $_FN;
    if (FN_IsAdmin())
        return true;
    $config=FN_LoadConfig();
    //----if inner table is in other section----------------------------------->
    if ($config['tables']!= $tablename)
    {
        foreach($_FN['sections'] as $section)
        {
            if ($section['type']== "navigator" || $section['type']== "dbview")
            {
                $configTmp=FN_LoadConfig("",$section['id']);
                if ($configTmp['tables']== $tablename)
                {
                    $config=$configTmp;
                    if (!FN_UserCanViewSection($section['id']))
                    {
                        return false;
                    }
                    break;
                }
            }
        }
    }
    //----if inner table is in other section-----------------------------------<
    $table=FN_XmlTable($tablename);
    $record=$table->GetRecordByPrimaryKey($id);
    //--------visualizzazione solo per il creatore----------------------------->
    if (!empty($config['viewonlycreator']))
    {
        if ($_FN['user']== "" && $record['username']!= "")
        {
            return false;
        }
        elseif ($_FN['user']== $record['username'])
        {
            return true;
        }
    }

    if ($config['viewonlycreator'])
    {
        $listusers=FN_XmlTable("fieldusers");
        $list_field=FNDBVIEW_GetFieldUserList($record,$tablename);
        if (is_array($list_field))
            foreach($list_field as $field)
            {
                if ($field['username']== $_FN['user'] && $field['table_unirecid']== $record[$table->primarykey] && $field['tablename']== $tablename)
                    return true;
            }
    }

    //--------visualizzazione solo per il creatore-----------------------------<
    //permessi per ogni record------------------------------------------------->
    else
    {
        if (!empty($config['enable_permissions_each_records']) && $config['enable_permissions_each_records']== 1)
        {
            if (empty($record['groupview']))
            {
                return true;
            }
            else
            {
                if ($_FN['user']== "")
                    return false;
                $uservalues=FN_GetUser($_FN['user']);
                $usergroups=explode(",",$uservalues['group']);
                $groupsview=explode(",",$record['groupview']);
                $groupinsert=explode(",",$config['groupinsert']);
                $groupadmin=explode(",",$config['groupadmin']);
                foreach($usergroups as $group)
                {
                    if (in_array($group,$groupsview) || in_array($group,$groupinsert) || in_array($group,$groupadmin))
                    {
                        return true;
                    }
                }
                return false;
            }
        }
        else
        {

            if (empty($record['groupview']) && empty($config['viewonlycreator']))
            {
                return true;
            }
        }
    }
    //permessi per ogni record-------------------------------------------------<
    return true;
}


?>