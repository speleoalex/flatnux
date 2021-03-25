<?php

/**
 * @package Flatnux_functions
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
defined('_FNEXEC') or die('Restricted access');

/**
 * Edit config file
 *
 * Sample:
 * #[it]Titolo in italiano {opzione1=1,opzione2=2}
 * #[en]Title 2 in english
 * $nomevariabile // {string}
 * #[it]Titolo 2 in italiano
 * #[en]Title 2 in english
 * $nomevariabile2 // {color}
 *
 *
 * @param string $file
 * @param string $formaction
 * @param string $exit
 * @param array $allow 
 */
function FN_EditConfFile($file,$formaction="",$exit="",$allow=false,$write_to_file=false)
{
    //($file,$formaction="",$exit="",$allow=false,$write_to_file=false)
    echo FN_HtmlEditConfFile($file,$formaction,$exit,$allow,$write_to_file);
}

/**
 * Edit config file
 *
 * Sample:
 * #[it]Titolo in italiano {opzione1=1,opzione2=2}
 * #[en]Title 2 in english
 * $nomevariabile // {string}
 * #[it]Titolo 2 in italiano
 * #[en]Title 2 in english
 * $nomevariabile2 // {color}
 *
 *
 * @param string $file
 * @param string $formaction
 * @param string $exit
 * @param array $allow
 * @param bool $write_to_file
 * @param string $mod
 * @param string $block
 *
 */
function FN_HtmlEditConfFile($file,$formaction="",$exit="",$allow=false,$write_to_file=false,$mod="",$block="",$tableHtmlattibutes="")
{
    global $_FN;
   
    $opt=FN_GetParam("opt",$_GET);
    $filecontents=file_get_contents($file);
    $htmlsaved="";
    if ($mod== "")
        $mod=$_FN['mod'];
    if ($block== "")
        $block=$_FN['block'];
    if ($tableHtmlattibutes== "")
        $tableHtmlattibutes="border=\"0\" cellpadding=\"1\" cellspacing=\"0\"";
    if (!strpos($filecontents,'$_FN') && !strpos($filecontents,'$config'))
    {
        $write_to_file=true;
    }
//die($tableHtmlattibutes);
    $html="";
    if ($file== "config.php")
    {
        $tableconf="fn_settings";
    }
    else
    {
        $path=dirname($file);
        $thispath=realpath(".").$_FN['slash'];
        $file=str_replace($thispath,"",$path.$_FN['slash'].basename($file));
        if (FN_erg("^modules/",$file))
        {
            if ($block!= "")
                $tableconf="fncf_block_{$block}";
            else
                $tableconf="fncf_{$mod}";
        }
        elseif (FN_erg("^sections/",$file))
        {
            $tableconf="fncf_{$mod}";
        }
        else
        {
            $tableconf=dirname($file);
            $tableconf=str_replace("/","_s_",$tableconf);
            $tableconf=str_replace("\\","_b_",$tableconf);
            $tableconf=str_replace(".","_d_",$tableconf);
        }
    }

 //dprint_r($file);
 //dprint_r($tableconf);
 
    echo "<!-- splx $tableconf -->";
    if ($formaction== "")
    {
        $formaction="?mod={$_FN['mod']}&amp;opt=$opt";
    }
    if (!$write_to_file)
    {
        if (!file_exists("{$_FN['datadir']}/{$_FN['database']}/$tableconf.php"))
        {
            $xml="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<?php exit(0);?>
<tables>
	<field>
		<name>varname</name>
		<type>string</type>
		<frm_help_it></frm_help_it>
		<frm_required>1</frm_required>
		<primarykey>1</primarykey>
	</field>
	<field>
		<name>varvalue</name>
		<type>string</type>
	</field>
	<field>
		<name>defaultvalue</name>
		<type>string</type>
		<frm_show>0</frm_show>
	</field>
	<filename>settings</filename>
</tables>";
            FN_Write($xml,"{$_FN['datadir']}/{$_FN['database']}/$tableconf.php");
        }
        $databasevalues=FN_GetVarsFromTable($tableconf);
    }
    $table=FN_XmlTable($tableconf);
    $exitflat=str_replace("&amp;","&",$exit);
    if ($exitflat== "")
    {
        $exitjs="history.back()";
    }
    else
    {
        $exitjs="window.location='$exitflat'";
    }
    if (isset($_POST) && is_array($_POST) && count($_POST) > 0)
    {
        $res=false;
//---------------------------------write to file ------------------------------>
        if ($write_to_file)
        {
            $fd=file($file);
            $new_file="";
            for($i=0; $i < count($fd); $i++)
            {
                $new_line=$fd[$i];
                if (isset($_POST["conf_value_old".$i]))
                {
                    $new_value=FN_GetParam("conf_value_new".$i,$_POST);
                    $old_value=FN_GetParam("conf_value_old".$i,$_POST);
                    $old_value=xmldb_encode_preg($old_value);
                    if ($old_value== "")
                        $new_line=preg_replace('/=(.*?)("")/s','=${1}"'.$new_value.'"',$fd[$i]);
                    else
                        $new_line=preg_replace('/=(.*?)('.$old_value.')/s','=${1}'.$new_value,$fd[$i]);
                }
                $new_file.=$new_line;
            }
            FN_Write($new_file,$file);
            FN_Log("file has been modified:{$file}");
            
        }
       
//---------------------------------write to file ------------------------------<		
        else
        {
            foreach($_POST as $key=> $value)
            {
                $value=FN_GetParam($key,$_POST);
                if (preg_match("/^conf_/s",$key) && !preg_match("/^conf_value_old/s",$key))
                {
                    $varkey=preg_replace("/^conf_/s","",$key);
                    if (!@array_key_exists($varkey,$databasevalues))
                    {
                        $res=$table->InsertRecord(array("varname"=>$varkey,"varvalue"=>$value));
                    }
                    else
                    {
                        $res=$table->UpdateRecord(array("varname"=>$varkey,"varvalue"=>$value));
                    }
                    
                }
            }
            FN_Log("config has been modified: $file");
            //die ($file);
            $values=FN_LoadConfig($file,$mod,false);
        }
        
         
        if ($res && count($_POST) > 1)
        {
            $htmlsaved.="<div style=\"float:right;color:green\">".FN_i18n("the data were successfully updated")."</div>";
            if ($exit!= "")
            {
                $html.="$htmlsaved&nbsp;&nbsp;<button onclick=\"$exitjs\">".FN_i18n("continue")."</button>";
                  
                return $html;
            }
        }
    }
    if (!$write_to_file)
    {
        $databasevalues=FN_GetVarsFromTable($tableconf);
    }
    $lang=$_FN['lang'];
    if (!file_exists($file)) //check file
        return "";

    $html.="
<script type=\"text/javascript\" src=\"include/javascripts/jscolor/jscolor.js\"></script>
<script type=\"text/javascript\">
var synccheck = function (id)
{
	var divitems = document.getElementById('checkconf' + id ).childNodes;
	var sep='';
	var str='';
	for (var i in divitems)
	{
		items=divitems[i].childNodes
		for (var i in items)
		{
			if (items[i].checked == true )
			{
				str = str+ sep + items[i].name
				sep=',';
			}
		}
	}
	document.getElementsByName('conf_'+id)[0].value=str;
}
var moveup = function (node)
{
	var newnode=node;
	var parent=node.parentNode;
	var prevnode=node.previousSibling;
	parent.removeChild(node);
	parent.insertBefore(newnode,prevnode);
}
var movedown = function (node)
{
	var newnode;
	var parent;
	var nextnode;
	newnode = node;
	parent = node.parentNode
	if (node.nextSibling != undefined)
		nextnode = node.nextSibling.nextSibling;
	else
		nextnode = parent.firstChild;

	if (node.nextSibling != null)
	{
		parent.removeChild(node);
		parent.insertBefore(newnode,nextnode);
	}
}
</script>
<form action=\"$formaction\" method=\"post\">
<table   {$tableHtmlattibutes}>
	<tbody>
";
    $ffile=$file;
    $fg=file($file);
    // scansione file alla ricerca delle variabili
    for($i=0; $i < count($fg); $i++)
    {
        if (preg_match('/^\$./s',$fg[$i]) && strpos($fg[$i],"Array();")=== false) // prende solo le righe che iniziano col carattere "$"
        {
            $line_tmp1=explode("=",$fg[$i]);
            //find array key--->
            $varkey=explode("'",$line_tmp1[0]);
            if (isset($varkey [1]))
            {
                $varkey=$varkey [1];
            }
            else
            {
                $varkey=explode("\"",$line_tmp1[0]);
                if (isset($varkey [1]))
                    $varkey=$varkey [1];
                else
                    $varkey=trim(ltrim($varkey [0]));
            }
            //find array key---<
            unset($line_tmp1[0]);
            $line_tmp1=trim(ltrim(implode("=",$line_tmp1)));
            //dprint_r($line_tmp1);
            $varvalue="";
            if ($varkey== "")
                continue;
            if (is_array($allow) && !in_array($varkey,$allow))
                continue;
            //dprint_r($_POST);
            $j=$varkey;
            if ($write_to_file)
                $j="value_new".$i;
            if ($line_tmp1[0]== '$')
            {
                continue;
            }
            if (isset($databasevalues[$varkey]))
            {
                $varvalue=$databasevalues[$varkey];
                eval('$defaultvalue='.$line_tmp1);
                //dprint_r('$defaultvalue=' . $line_tmp1);
            }
            else
            {
                eval('$varvalue='.$line_tmp1);
                $defaultvalue=$varvalue;
            }
            $varkey_ori=$varkey;
            if ($write_to_file)
            {
                $varkey="value_new$i";
            }
            $line_tmp=explode(";",$fg[$i]); // cancella eventuali commenti a dx della variabile
            $type="";
            $onchange="";
            //field type -->
            if (isset($line_tmp[1]))
                preg_match('/[\{].+[\}]/i',$line_tmp[1],$type);
            if (isset($type[0]))
                $type=trim($type[0]);
            else
                $type="";
            //field type --<
            $line=explode("=",$line_tmp[0]); //variable / value
            $title="";
            $options=false;
            // find translate
            $find=1;
            $exists=false;
            $i18n_find=false;
            while(preg_match('/^#./s',$fg[$i - $find]) && !($exists=preg_match('/^#\['.$lang.'\]./s',$fg[$i - $find])))
            {
                if (preg_match('/^#\[i18n\]./s',$fg[$i - $find]))
                {
                    $i18n_find=preg_match('/^#\[i18n\]./s',$fg[$i - $find]);
                }
                $find++;
            }
            if (!$exists)
            {

                $find=1;
                if (false!== $i18n_find)
                {

                    $find=$i18n_find;
                }
            }
            //check options---------------------------------------------------->
            if (preg_match('/^#./s',$fg[$i - $find]))
            {
                $title=preg_replace('/^#/s',"",$fg[$i - $find]);
                $t="";
                preg_match('/[\{].+[\}]/i',$title,$t);
                if (isset($t[0]))
                {
                    $options=explode(',',str_replace("{","",str_replace("}","",$t[0])));
                }
                if (is_array($options))
                {
                    $tmp_options=array();
                    foreach($options as $option)
                    {
                        if (preg_match('/.+\(\)$/',$option))
                        {
                            $fname=str_replace("()","",$option);
                            if (function_exists($fname))
                            {
                                $ext_options=$fname();

                                if (is_array($ext_options))
                                {
                                    foreach($ext_options as $ext_option)
                                    {
                                        $tmp_options[]="$ext_option=$ext_option";
                                    }
                                }
                            }
                        }
                        else
                        {
                            $tmp_options[]=$option;
                        }
                    }
                    $options=$tmp_options;
                }
            }
            //check options----------------------------------------------------<
            if (preg_match('/^\\/\\/./s',$fg[$i - 1]))
            {
                $title=preg_replace('/^\\/\\//s',"",$fg[$i - 1]);
            }
            if (false!== $i18n_find)
            {
                $title=preg_replace('/\{.+\}/s','',$title);
                $title=preg_replace('/\[i18n]/s','',$title);
                $title=FN_Translate(trim(ltrim($title)));
            }
            else
            {
                $title=preg_replace('/\{.+\}/s','',$title);
                $title=htmlentities(trim($title));
            }
            $title=preg_replace('/^\[.+\]/s','',$title);
            $varname=htmlentities(ucfirst(str_replace("_"," ",trim(preg_replace('/^\$/s','',$varkey_ori)))));
            if ($title== "")
                $title=$varname;

            $html.="<tr><td style=\"border-bottom:1px dotted #dadada;text-align:left;\">";
            $html.="\n<label for=\"conf_$varkey\">$title:</label>";
            $html.="</td><td style=\"border-bottom:1px dotted #dadada;text-align:left;\">";
            if ($type== "{color}")
            {

                $html.="<input onchange='try{document.getElementById(\"cc_conf_$i\").style.backgroundColor=\"#\"+this.value}catch(e){}' class=\"color {hash:true,caps:false,adjust:false,styleElement:'cc_conf_$i'}\" title=\"$varname (".FN_i18n("default value").": $defaultvalue)\" type=\"text\" name=\"conf_$varkey\" size=\"15\" maxlength=\"1200\" value=\"".htmlentities($varvalue)."\" />";
                $html.="<span id=\"cc_conf_$i\" style=\"border:#000000 1px solid;background-color:#$varvalue\">&nbsp;&nbsp;&nbsp;&nbsp;</span>";
            }
            elseif ($type== "{password}")
            {
                $html.="<input title=\"$varname (".FN_Translate("default value").": $defaultvalue)\" $onchange type=\"password\" name=\"conf_$varkey\" size=\"30\" maxlength=\"1200\" value=\"".htmlentities($varvalue)."\" />";
            }
            elseif ($type== "{required}")
            {
                $st="";
                if (count($_POST) && $varvalue== "")
                    $st="style=\"border:1px solid red\"";
                $html.="<input $st title=\"$varname (".FN_Translate("default value").": $defaultvalue)\" $onchange type=\"text\" name=\"conf_$varkey\" size=\"40\" maxlength=\"1200\" value=\"".htmlentities($varvalue)."\" />";
            }
            elseif (!$options)
            {
                $html.="<input title=\"$varname (".FN_Translate("default value").": $defaultvalue)\" $onchange type=\"text\" name=\"conf_$varkey\" size=\"40\" maxlength=\"1200\" value=\"".htmlentities($varvalue)."\" />";
            }
            else
            {
                //---------checkbox----------------------->
                if ($options[0][0]== "+")
                {

                    $html.="<div>";
                    $allopt=array();
                    $dirtoopen=$options[0];
                    $dirtoopen=str_replace("+","",$dirtoopen);
                    $html.="<input type=\"hidden\" value=\"".htmlentities($varvalue)."\" name=\"conf_$j\"  />";
                    $is_file=false;
                    $dirtoopenpath="";

                    if (file_exists("{$_FN['datadir']}/{$_FN['database']}/$dirtoopen.php"))
                    {
                        $t=FN_XmlTable($dirtoopen);
                        $items=$t->GetRecords();
                        foreach($items as $item)
                        {
                            $allopt[]=$item[$t->primarykey];
                        }
                    }
                    elseif (preg_match('/\*/s',$dirtoopen))
                    {
                        $allfiles=glob(preg_replace('/^\$fn_datadir\\//s',$_FN['datadir']."/",$dirtoopen));
                        foreach($allfiles as $filename)
                        {
                            $allopt[]=basename($filename);
                        }
                        $is_file=true;
                        $dirtoopenpath=dirname(preg_replace('/^\$fn_datadir\\//s',$_FN['datadir']."/",$dirtoopen));
                    }
                    elseif (is_dir($dirtoopen))
                    {
                        $dirtoopenpath=$dirtoopen;
                    }
                    if (!empty($dirtoopenpath) && is_dir($dirtoopenpath))
                    {

                        $allopt=FN_ListDir($dirtoopenpath);
                    }
                    $html.="<div id=\"checkconf$j\">";
                    $enabledopt=explode(",",$varvalue);
                    $disabledopt=array_diff($allopt,$enabledopt);
                    foreach($enabledopt as $opt)
                    {
                        $imgopt=( file_exists("$dirtoopenpath/$opt/icon.png") ) ? "<img style=\"vertical-align:middle;border:0px;\" src=\"$dirtoopenpath/$opt/icon.png\" alt=\"\"/>" : "";
                        if ($opt== "")
                            continue;
                        //dprint_r($dirtoopenpath . "/$opt");
                        $title=FN_GetFolderTitle($dirtoopenpath."/".$opt);
                        $html.="<div>";
                        $html.="<a href=\"javascript:;\" onclick=\"moveup(this.parentNode);synccheck('$j');\">";
                        $html.="<img style=\"vertical-align:middle;border:0px;\" src=\"".FN_FromTheme("images/up.png")."\" title=\"".FN_Translate("move up")."\" /></a>";
                        $html.="<a href=\"javascript:;\" onclick=\"movedown(this.parentNode);synccheck('$j');\">";
                        $html.="<img style=\"vertical-align:middle;border:0px;\" src=\"".FN_FromTheme("images/down.png")."\" title=\"".FN_Translate("move down")."\" /></a>";
                        $html.="&nbsp;$imgopt&nbsp;$title<input name=\"$opt\" onclick=\"synccheck('$j');\" checked=\"checked\" type=\"checkbox\" />";
                        $html.="</div>";
                    }
                    foreach($disabledopt as $opt)
                    {
                        $imgopt=( file_exists("$dirtoopenpath/$opt/icon.png") ) ? "<img style=\"vertical-align:middle;border:0px;\" src=\"$dirtoopenpath/$opt/icon.png\" alt=\"\" />" : "";
                        $title=FN_GetFolderTitle($dirtoopenpath."/$opt");
                        $html.="<div>";
                        $html.="<a href=\"javascript:;\" onclick=\"moveup(this.parentNode);synccheck('$j');\">";
                        $html.="<img style=\"vertical-align:middle;border:0px;\" src=\"".FN_FromTheme("images/up.png")."\" title=\"".FN_Translate("move up")."\" /></a>";
                        $html.="<a href=\"javascript:;\" onclick=\"movedown(this.parentNode);synccheck('$j');\">";
                        $html.="<img style=\"vertical-align:middle;border:0px;\" src=\"".FN_FromTheme("images/down.png")."\" title=\"".FN_Translate("move down")."\" /></a>";
                        $html.="&nbsp;$imgopt&nbsp;$title<input name=\"$opt\" onclick=\"synccheck('$j');\"  type=\"checkbox\" />";
                        $html.="</div>";
                    }
                    $html.="</div>";
                }
                //---------checkbox-----------------------<
                else
                {
                    $onchange="";
                    $thumbimgselected="";
                    $havethumb=false;
                    $script="onchange=\"this.options[this.selectedIndex].onkeyup()\"";
                    $script.=" onkeyup=\"this.options[this.selectedIndex].onkeyup()\"";
                    $divid="conf_$i";
                    $html.="<select $script $onchange name=\"conf_$varkey\"  >\n";
                    $script="document.getElementById('$divid').innerHTML = ''";
                    foreach($options as $val)
                    {
                        $valdesc=trim($val);
                        if (preg_match("/=/s",$valdesc))
                        {
                            $t=explode("=",$valdesc);
                            $val=trim($t[0]);
                            $valdesc=trim($t[1]);
                            $s=($val== $varvalue) ? "selected=\"selected\"" : "";
                            //font--->
                            if ($type== "{fonts}")
                            {
                                $thumbimgselected="<div style=\"height:20px;line-height:16px;margin:0px;patting:0px;border:0px;font-size:16px;font-family:$varvalue\">".FN_i18n("sample text")."";
                                $thumbimgselected.="</div>";
                                $script="document.getElementById('$divid').innerHTML = ' <div style=\\'height:20px;line-height:16px;margin:0px;patting:0px;border:0px;font-size:16px;font-family:$val\\' >".FN_i18n("sample text")."</div>'";
                            }
                            //font--->
                            if (false!== $i18n_find)
                            {
                                $valdesc=FN_Translate(trim(ltrim(strtolower($valdesc))));
                            }

                            $html.="\n\t<option onkeyup=\"$script\" $s value=\"$val\">$valdesc</option>";
                        }
                        //if is xmltable----->
                        elseif (file_exists("{$_FN['datadir']}/{$_FN['database']}/{$options[0]}.php"))
                        {
                            $t=FN_XmlTable($options[0]);
                            $items=$t->GetRecords();
                            $s=($varvalue== "") ? "selected=\"selected\"" : "";
                            $html.="\n\t<option $s value=\"\">-----</option>";
                            foreach($items as $item)
                            {
                                $s=($item[$t->primarykey]== $varvalue) ? "selected=\"selected\"" : "";
                                $html.="\n\t<option $s value=\"{$item[$t->primarykey]}\">";
                                $html.=(!empty($item['title'])) ? $item[$t->primarykey]."-".$item['title'] : $item[$t->primarykey];
                                $html.="</option>";
                            }
                        }
                        //if is xmltable-----<
                        else
                        {
                            $cdir=$valdesc;
                            if (preg_match('/\*./s',$cdir))
                            {
                                $files=glob(preg_replace('/\$theme/',$_FN['theme'],preg_replace('/^\\$fn_datadir\\//s',$_FN['datadir']."/",$valdesc)));
                                foreach($files as $cf)
                                {
                                    $sv=basename($cf);
                                    //----images ---->
                                    $thumbimg="";
                                    $thumb="$cf";
                                    if (file_exists($thumb) && (false!== strpos("jpg,png,jpeg,gif",strtolower(FN_GetFileExtension($thumb)))))
                                    {
                                        $thumbimg=" <img style='height:64px;vertical-align:middle' src='$thumb' />";
                                        $script="document.getElementById('$divid').innerHTML = '".addslashes(htmlspecialchars($thumbimg))."'";
                                        $havethumb=true;
                                    }
                                    else
                                    {
                                        $script="document.getElementById('$divid').innerHTML = ''";
                                    }
                                    //----images ----<
                                    $ticf=FN_GetFolderTitle($cf);

                                    $s=($sv== $varvalue) ? "selected=\"selected\"" : "";
                                    if ($s)
                                        $thumbimgselected=$thumbimg;

                                    $html.="\n\t<option onkeyup=\"$script\" $s value=\"$sv\">";
                                    if ($ticf!= "")
                                        $html.="$ticf";
                                    else
                                        $html.="$sv";
                                    $html.="</option>";
                                }
                            }
                            elseif (preg_match('/.php$/s',$cdir))
                            {
                                $files=glob(preg_replace('/^\\$fn_datadir\\//s',$_FN['datadir']."/",$valdesc));
                                foreach($files as $cf)
                                {
                                    $sv=preg_replace('/.php$/s','',basename($cf));
                                    //----images ---->
                                    $thumbimg="";
                                    $thumb="$cf.png";
                                    if (file_exists($thumb))
                                    {
                                        $thumbimg="<img alt='' style='vertical-align:middle' src='$thumb' />";
                                        $script="document.getElementById('$divid').innerHTML = '".addslashes(htmlspecialchars($thumbimg))."'";
                                        $havethumb=true;
                                    }
                                    else
                                    {
                                        $script="document.getElementById('$divid').innerHTML = ''";
                                    }
                                    //----images ----<
                                    $ticf=FN_GetFolderTitle($cf);
                                    $s=($sv== $varvalue) ? "selected=\"selected\"" : "";
                                    if ($s)
                                        $thumbimgselected=$thumbimg;
                                    $html.="\n\t<option onkeyup=\"$script\" $s value=\"$sv\">";
                                    if ($ticf!= "")
                                        $html.="$ticf [$sv]";
                                    else
                                        $html.="$sv";
                                    $html.="</option>";
                                }
                            }
                            elseif (is_dir($cdir))
                            {
                                $handle=opendir($cdir);
                                $options=array();
                                while(false!== $file=readdir($handle))
                                {
                                    $val1="";
                                    if ($file!= "." && $file!= ".." && is_dir($cdir."/".$file))
                                    {
                                        //----images ---->
                                        $thumbimg="";
                                        $thumb=$cdir."/".$file."/screenshot.png";
                                        if (!file_exists($thumb))
                                            $thumb=$cdir."/".$file."/thumb.png";
                                        if (!file_exists($thumb))
                                            $thumb=$cdir."/".$file."/icon.png";
                                        if (file_exists($thumb))
                                        {
                                            $thumbimg="<img alt='' style='vertical-align:middle' src='$thumb' />";
                                            $script="document.getElementById('$divid').innerHTML = '".addslashes(htmlspecialchars($thumbimg))."'";
                                            $havethumb=true;
                                        }
                                        else
                                        {
                                            $script="document.getElementById('$divid').innerHTML = ''";
                                        }
                                        //----images ----<
                                        $val=$file;
                                        $valdesc=FN_GetFolderTitle($cdir."/".$file);

                                        $s="";
                                        if ($val== $varvalue)
                                        {
                                            $thumbimgselected=$thumbimg;
                                            $s="selected=\"selected\"";
                                        }
                                        if ($val1!= "" && $s== "")
                                        {
                                            if ($val1== trim($line[1],"\" "))
                                            {
                                                $s="selected=\"selected\"";
                                                $thumbimgselected=$thumbimg;
                                            }
                                        }
                                        $scopt=" onkeyup = \"$script\" ";
                                        $html.="\n\t<option $scopt $s value=\"$val\">$valdesc</option>";
                                    }
                                }
                                closedir($handle);
                            }
                        }
                    }
                    $html.="</select>";
                    $html.="<div style=\"\" id=\"$divid\">";
                    $html.=$thumbimgselected."</div>";
                    "</div>";
                }
            }
            $v=trim($line[1],"\" ");

            $html.="<input type=\"hidden\" name=\"conf_value_old$i\" value=\"".$varvalue."\" /> ";
            $html.="
</td></tr>";
        }
        else
        {
            
        }
    }
    $html.="<tr><td colspan=\"2\">";
    if (!empty($linkcancel))
    {

        $html.="<button name=\"prev\" onclick=\"window.location=('$linkcancel')\">";
        $html.=FN_Translate("cancel");
        $html.="</button>&nbsp;";
    }
    $html.="<input type=\"hidden\" name=\"savefileconfig\" value=\"1\" /> ";
    $html.="<br /><button type=\"submit\">";
    $html.=FN_Translate("save");
    $html.="</button>";
    if ($exit!= "")
    {
        $html.="&nbsp;<button onclick=\"window.location='$exitflat'\" type=\"button\">";
        $html.=FN_Translate("cancel");
        $html.="</button>";
    }
    $html.="$htmlsaved</td>
		</tr>
</tbody>
</table>
</form><!-- Table:$tableconf -->";
              

    return $html;
}

/**
 *
 * @param string $file
 * @param string $formaction
 * @param string $exit
 * @param array $editor_params
 */
function FN_EditContent($file,$formaction="",$exit="",$editor_params=false)
{
    echo FN_HtmlEditContent($file,$formaction,$exit,$editor_params);
}

/**
 *
 * @global array $_FN
 * @param string $file
 * @param string $formaction
 * @param string $exit
 * @param array $editor_params
 * @return string 
 */
function FN_HtmlEditContent($file,$formaction="",$exit="",$editor_params=false)
{
    global $_FN;
    $filetoget=$file;
    $isdraft=false;
    if (file_exists("$file.draft~"))
    {
        $filetoget="$file.draft~";
        $isdraft=true;
    }
    $html="";
    $dir=dirname($file);
    $savedraft=FN_GetParam("savedraft",$_POST);
    if (!FN_UserCanEditFile($file))
    {
        return;
    }
    if (file_exists($dir))
    {
        if (!file_exists($file))
        {
            if (!FN_IsWritable($dir))
            {
                return "$dir ".FN_i18n("is read only");
            }
            if ($savedraft)
            {
                FN_Write("",$file.".draft~");
            }
            else
                FN_Write("",$file);
        }
        if (!FN_CanModifyFile($_FN['user'],"$file"))
        {
            return "";
        }
        $exitflat=str_replace("&amp;","&",$exit);
        $exitjs="window.location='$exitflat'";
        if (isset($_POST['body']))
        {
            $contents=FN_GetParam("body",$_POST);
            if (!empty($_POST['fn_rewrite_links']))
            {
                $contents=FN_RewriteLinksAbsoluteToLocal($contents,dirname($file));
            }
            if ($savedraft)
            {
                FN_Write($contents,$file.".draft~");
                FN_Log("file has been modified:{$file}.draft~");
            }
            else
            {
                FN_BackupFile($file);
                if (file_exists($file.".draft~"))
                {
                    unlink($file.".draft~");
                }
                FN_Write($contents,$file);
                FN_Log("file has been modified:{$file}");
            }
            $html.=FN_i18n("the file was successfully saved");
            if ($exit!= "")
                $html.="<br /><br /><button onclick=\"$exitjs\">".FN_i18n("next")." &gt;&gt;</button>";
            return $html;
            ;
        }
        else
        {
            //--- modifica del file ----
            $readonly=$enable="";
            $html.="<div class=\"fn_editor\">";
            $html.="<div class=\"fn_editortitle\" >".FN_i18n("modify").":".realpath("$file");
            if ($isdraft)
            {
                $html.=" <b>(".FN_i18n("draft").")</b>";
            }

            $html.="</div>";
            $html.="<form method=\"post\" action=\"$formaction\" >";
            $file_extension=strtolower(FN_GetFileExtension($file));
            $value=file_get_contents($filetoget);
            if (isset($editor_params['force_value']))
            {
                $value=$editor_params['force_value'];
            }
            switch($file_extension)
            {
                case "php":
                    if (basename($file)== "config.php")
                    {
                        return FN_HtmlEditConfFile($file,$formaction,$exit);
                    }
                    else
                    {
                        if (!FN_IsWritable($file))
                        {
                            return "$file ".FN_i18n("is read only");
                        }
                        $html.="<textarea style=\"width:100%;height:90%\" id=\"fn_modcont\" name=\"body\" $readonly cols=\"80\" rows=\"28\" >".htmlspecialchars($value)."</textarea>";
                    }
                    break;
                case "html":
                case "htm":
                    if (!FN_IsWritable($file))
                    {
                        return "$file ".FN_i18n("is read only");
                    }
                    $html.="<input type=\"hidden\" name=\"fn_rewrite_links\" value=\"1\" />";
                    $value=FN_RewriteLinksLocalToAbsolute($value,dirname($file));
                    $editor=$_FN['htmleditor'];
                    if (isset($_FN['force_htmleditor']) && $_FN['force_htmleditor']!= "")
                    {
                        $editor=$_FN['force_htmleditor'];
                    }
                    if ($editor!= "0" && file_exists("include/htmleditors/".$editor."/htmlarea.php"))
                    {
                        require_once ("include/htmleditors/".$editor."/htmlarea.php");
                        $defaultdir=false;
                        if (isset($_FN['editor_folder']))
                        {
                            $defaultdir=$_FN['editor_folder'];
                        }
                        $html.=FN_HtmlHtmlArea("body",0,0,$value,$defaultdir,$editor_params);
                    }
                    else
                    {
                        $html.="<textarea style=\"width:100%;height:90%\" id=\"fn_modcont\" name=\"body\" $readonly cols=\"80\" rows=\"28\" >".htmlspecialchars($value)."</textarea>";
                    }
                    break;
                default:
                    if (!FN_IsWritable($file))
                    {
                        return "$file ".FN_i18n("is read only");
                    }
                    $html.="<textarea style=\"width:100%;height:90%\" id=\"fn_modcont\" name=\"body\" $readonly cols=\"80\" rows=\"28\" >".htmlspecialchars($value)."</textarea>";
                    break;
            }
            $ck=($isdraft== true) ? "checked=\"checked\"" : "";
            $text_save=!empty($editor_params['text_save']) ? $editor_params['text_save'] : FN_i18n("save");
            $html.="<div class=\"fn_editorfooter\">\n<input value=\"1\" $ck type=\"checkbox\" name=\"savedraft\" />&nbsp;".FN_i18n("save as draft")."&nbsp;<button type=\"submit\">".$text_save."</button>";
            if ($exit!= "")
                $html.="<button type=\"button\" class=\"button\" onclick=\"$exitjs\" > ".FN_i18n("cancel")."</button>";
            $html.="</div></form>";
            $html.="</div>";
        }
        return $html;
    }
}

/**
 *
 * @param string $value
 * @param bool $rewritelinkfolder 
 */
function FN_HtmlEditor($value,$rewritelinkfolder=false)
{
    $html="";
    $editor=$_FN['htmleditor'];
    if (isset($_FN['force_htmleditor']) && $_FN['force_htmleditor']!= "")
    {
        $editor=$_FN['force_htmleditor'];
    }
    if ($editor!= "0" && file_exists("include/htmleditors/".$editor."/htmlarea.php"))
    {
        require_once ("include/htmleditors/".$editor."/htmlarea.php");
        $defaultdir=false;
        if (isset($_FN['editor_folder']))
        {
            $defaultdir=$_FN['editor_folder'];
        }
        $value=file_get_contents($file);
        $value=FN_RewriteLinksLocalToAbsolute($value,dirname($file));
        $html.=FN_HtmlHtmlArea("body",0,0,$value,$defaultdir);
    }
}

?>
