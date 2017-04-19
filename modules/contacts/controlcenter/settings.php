<?php
/**
 * @package Flatnux_module_contacts
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 *
 */

$csv =FN_GetParam("csv",$_GET,"html");
$opt =FN_GetParam("opt",$_GET,"html");
$order =FN_GetParam("order___xdb_contact_message",$_GET,"html");
if ($order=="")
{
    $order="id";
}
if ($csv)
{
    $t =FN_XmlTable("contact_message");
    $records = $t->GetRecords(false,false,false,"id",false,/*$fields = */false);
    SaveToCSV($records,"contacts.csv");
    exit;
}

$params['enableview']= true;
$params['layout_view'] = "table";
$params['fields'] = "name|contact|telephone|subject|message|date";

FN_XmltableEditor("contact_message",$params);

$link = "controlcenter.php?opt=$opt&amp;csv=1";
echo "<div><hr /><a href=\"$link\"><img alt=\"download csv\" src=\"".FN_FromTheme("images/mime/xls.png")."\" /></a></div>";

/**
 *
 * @param type $data 
 */
function SaveToCSV($data,$filename)
{
	$sep = ",";
	$str = "";
	foreach ($data as $row)
	{
		$arraycols = array();
		foreach ($row as $cell)
		{
			$arraycols[] = "\"".str_replace("\"","\"\"",$cell)."\"";
		}
		$str.=implode($sep,$arraycols)."\n";
	}
	FN_SaveFile($str,$filename,"application/vnd.ms-excel");
}

?>