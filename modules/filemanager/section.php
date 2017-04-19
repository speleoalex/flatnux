<?php
/**
 * @package Flatnux_module_filemanager
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
defined('_FNEXEC') or die('Restricted access');
$browse_all_filesystem = 0;

//-------------start filemanager ------------->
define("_SIZE1",110);
define("_SIZE2",50);
define("_SIZE3",77);
define("_SIZE4",135);
define("_SIZE5",70);
global $_FN;


if (FN_IsAdmin() || $_FN['user'] != "")
{
	strstr(PHP_OS,"WIN") ? $slash = "\\" : $slash = "/";
	$_FN['slash'] = $slash;


	//---------get request dir------------------------------------------------->
	$dir = FN_GetParam("dir",$_POST);
	if ($dir == "")
		$dir = FN_GetParam("dir",$_GET);
	//---------get request dir-------------------------------------------------<
	$scriptfolder = dirname($_SERVER['SCRIPT_FILENAME']);
	if ($dir == "")
	{
		$dir = $scriptfolder;
	}
	else
	{
		if (isset($dir[0]))
		{
			if ($dir[0] != "/" && false == strpos($dir,":\\"))
			{
				$dir = fm_absolutepath($scriptfolder.$_FN['slash'].$dir);
			}
		}
	}

	if ($browse_all_filesystem == 0)
	{
		if (!preg_match("/^".str_replace('/','\\/',str_replace("\\","\\\\",$scriptfolder))."/s",$dir))
		{
			$dir = $scriptfolder;
		}
	}

	$file = isset($_GET['ffile']) ? $_GET['ffile'] : null;
	if ($file == null)
		$file = isset($_POST['ffile']) ? $_POST['ffile'] : null;
	$opmod = isset($_GET['opmod']) ? $_GET['opmod'] : null;
	if ($opmod == null)
		$opmod = isset($_POST['opmod']) ? $_POST['opmod'] : null;
	//$dir = fm_absolutepath($dir);
	if (!$dir)
		$dir = "$slash";
	echo "<div class=\"fnfilemanager\"  >";
	switch($opmod)
	{
		case "save" :
			fm_SaveFile($dir,$file);
			fm_Browse($dir);
			break;
		case "open" :
			fm_OpenFile($dir,$file);
			break;
		case "rename" :
			fm_RenameFile($dir,$file);
			break;
		case "newfolder" :
			fm_NewFolder($dir);
			fm_Browse($dir);
			break;
		case "newfile" :
			fm_NewFile($dir);
			fm_Browse($dir);
			break;
		case "dorename" :
			fm_DoRenameFile($dir,$file);
			fm_Browse($dir);
			break;
		case "delfile" :
			fm_DoDelFile($dir,$file);
			fm_Browse($dir);
			break;
		case "upload" :
			fm_UploadFile($dir);
			fm_Browse($dir);
			break;
		default :
		case "browse" :
		case "" :
			fm_Browse($dir);
			break;
	}
	echo "</div>";
}
else
{
	echo FN_HtmlLoginForm();
}
//-------------start filemanager -------------<
/**
 * upload a file
 * @param string $dir
 */
function fm_UploadFile($dir)
{
	global $_FN;
	$file_clean = FN_StripPostSlashes($_FILES['filename']['name']);
	if (file_exists($dir."/".$file_clean))
	{
		echo FN_i18n("the file already exists");
		return;
	}
	if (!FN_CanModifyFile($_FN['user'],$dir."/".$file_clean))
	{ // se non e' un file valido
		FN_Alert(FN_i18n("operation is not permitted")." - ".FN_i18n("file not created"));
	}
	else
	{
		if (!move_uploaded_file($_FILES['filename']['tmp_name'],$dir."/".$file_clean))
		{
			echo "".FN_i18n("file not created")."<br />";
		}
	}
}

/**
 * create folder
 *
 * @param string $dir
 */
function fm_NewFolder($dir)
{
	if (!FN_IsAdmin())
		return;
	$folder = FN_GetParam("newfolder",$_POST);
	if ($folder == "")
		return;
	if (file_exists("$dir/$folder"))
		return;
	if (!is_writable("$dir"))
		return;
	mkdir("$dir/$folder");
}

/**
 * create file
 *
 * @param string $dir
 */
function fm_NewFile($dir)
{
	global $_FN;
	$file = FN_GetParam("newfile",$_POST);
	if ($file == "")
		return;
	if (file_exists("$dir{$_FN['slash']}$file"))
	{
		FN_Alert(FN_i18n("the file already exists"));
		return;
	}
	if (!is_writable("$dir"))
	{
		FN_Alert("folder is not wrietable");
		return;
	}
	if (!FN_CanModifyFile($_FN['user'],"$dir{$_FN['slash']}$file"))
	{
		FN_Alert(FN_i18n("operation is not permitted"));
		return;
	}
	FN_Alert("the file was created");
	$fp = fopen("$dir{$_FN['slash']}$file","a+");
	fclose($fp);
}

/**
 * Delete file
 *
 * @param string $dir
 * @param string $file
 */
function fm_DoDelFile($dir,$file)
{
	global $_FN;
	if ($file == "")
		return;
	if (!FN_CanModifyFile($_FN['user'],"$dir{$_FN['slash']}$file"))
		return;
	if (is_dir("$dir{$_FN['slash']}$file"))
	{
		FN_RemoveDir("$dir{$_FN['slash']}$file");
	}
	else
	{
		if (!FN_IsAdmin())
		{
			FN_BackupFile("$dir{$_FN['slash']}$file");
		}
		unlink("$dir{$_FN['slash']}$file");
		FN_Alert(FN_i18n("file deleted"));
	}
}

/**
 * fm_RenameFile
 * Apre un file
 * @param string $dir
 * @param string $file
 */
function fm_RenameFile($dir,$file)
{
	global $_FN;
	$sess_filemanager_editor = FN_GetParam("filemanager_editor",$_GET);
	$op = FN_GetParam("opt",$_GET);
	$mime = FN_GetParam("mime",$_GET,"html");
	$from = fm_Link(array("opmod"=>"","ffile"=>""),"&");
	echo "<div style=\"display:block;text-align:center;margin-top:auto;padding:10px;background-color:transparent;\">";
	echo "\n<form method=\"post\" action=\"?mod={$_FN['mod']}&amp;filemanager_editor=$sess_filemanager_editor&amp;mime=$mime&amp;opt=$op&amp;dir=$dir&amp;ffile=$file&amp;opmod=dorename\">";
	echo "\n<input size=\"40\" name=\"newname\" value=\"".($file)."\" /><br />";
	echo "\n<input type=\"submit\" class=\"submit\" value=\"".FN_i18n("rename")."\" />";
	echo "\n<input type=\"button\" onclick=\"window.location='$from'\" class=\"button\" value=\"".FN_i18n("cancel")."\" />";
	echo "\n</form>";
	echo "</div>\n";
}

/**
 *
 * @global array $_FN
 * @param string $dir
 * @param string $file
 */
function fm_DoRenameFile($dir,$file)
{
	global $_FN;
	$newname = FN_GetParam("newname",$_POST);
	if ($newname == "")
		return;
	if (!FN_CanModifyFile($_FN['user'],"$dir{$_FN['slash']}$newname"))
	{
		FN_Alert(FN_i18n("operation is not permitted"));
		return;
	}
	if (file_exists("$dir{$_FN['slash']}$newname"))
	{
		FN_Alert(FN_i18n("the file already exists"));
		return;
	}
	if (!is_writable($dir) || !is_writable("$dir{$_FN['slash']}$file"))
	{
		FN_Alert("file is readonly");
		return;
	}
	FN_Alert("file has been renamed".":\n$file => $newname");
	//FN_BackupFile("$dir{$_FN['slash']}$file", "$dir{$_FN['slash']}$newname");
	FN_Rename("$dir{$_FN['slash']}$file","$dir{$_FN['slash']}$newname");
}

/**
 * fm_OpenFile
 * Apre un file
 * @param string $dir
 * @param string $file
 */
function fm_OpenFile($dir,$file)
{
	$sess_filemanager_editor = FN_GetParam("filemanager_editor",$_GET);
	global $_FN;
	$mime = FN_GetParam("mime",$_GET,"html");
	$filetype = fm_MimeContentType(fm_absolutepath("$dir{$_FN['slash']}$file"));
	$scriptfilename = basename(FN_GetParam('SCRIPT_FILENAME',$_SERVER));
	$op = FN_GetParam("opt",$_GET);
	$local = FN_GetParam("local",$_GET);
	$abs = FN_GetParam("abs",$_GET);
	$lind = fm_Link(array("opmod"=>"","dir"=>$dir,"ffile"=>""),"&");
	$image_http_link = fm_absolutepath(dirname($_SERVER['SCRIPT_FILENAME'])).$_FN['slash'];
	$image_http_link = str_replace($image_http_link,"{$_FN['siteurl']}","$dir/$file");
	$image_http_link = str_replace($_FN['slash'],"/",$image_http_link);
	if ($local == "")
	{
		$url_link = $image_http_link;
	}
	else
	{
		$url_link = "$dir/$file";
		if ($abs == "")
		{
			$url_link = fm_absolutepath(dirname($_SERVER['SCRIPT_FILENAME'])).$_FN['slash'];
			$url_link = str_replace($url_link,"","$dir/$file");
		}
	}
	$image_fullpath = fm_absolutepath(dirname($_SERVER['SCRIPT_FILENAME'])).$_FN['slash'].$file;
	if (FN_CanModifyFile($_FN['user'],"$dir{$_FN['slash']}$file") && !empty($_GET['edit']))
	{
		fm_EditFileForm($dir,$file);
	}
	else
	{
		echo "<div style=\"position:relative;display:block;text-align:center;margin-top:auto;padding:10px;background-color:transparent;\">";
		echo "<img style=\"vertical-align:middle;position:absolute;right:10px\"src=\"".FN_GetIconByFilename($file)."\" alt=\"\" />";
		echo ("\n<span style=\"font-size:20px\">$file</span>");
		echo ("\n<br />"."$image_fullpath");
		echo ("\n<br />"."URL"."\t: <a ".fm_StyleA()."onclick=\"window.open(this.href);return false;\" href=\"$image_http_link\" >$image_http_link</a>");
		echo ("\n<br />".FN_i18n("type")."\t: $filetype ");
		echo ("\n<br />".FN_i18n("size")."\t: ".fm_ByteConvert(@filesize("$dir{$_FN['slash']}$file"))."");
		echo ("\n<br />".FN_i18n("permissions")."\t: ".fm_GetPerms("$dir{$_FN['slash']}$file"));
		echo ("\n<br />".FN_i18n("data")."\t: ".date("Y-m-d h:i:s",filemtime("$dir{$_FN['slash']}$file")));
		//echo "$image_http_link";
		echo "<div style=\"text-align:left;background-color:transparent;\">".FN_i18n("preview").":</div>";

		if (preg_match('/^text\\//s',$filetype) == 1 || filesize("$dir{$_FN['slash']}$file") == 0) //text
		{
			if (FN_CanModifyFile($_FN['user'],"$dir{$_FN['slash']}$file"))
			{
				$strfile = "";
				if (false !== ($handle = fopen("$dir{$_FN['slash']}$file","r")))
				{
					$t = true;
					for ($i = 0; $i < 100; $i++)
					{
						$t = fread($handle,10);
						if ($t === false)
						{
							break;
						}
						$strfile.=$t;
					}
					fclose($handle);
				}
				$strprev = str_replace("\n","<br />",htmlspecialchars($strfile,ENT_QUOTES));
				$strprev = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;",$strprev);
				if ($t != false)
					$strprev.="<br /><br /><div style=\"color:red;background-color:#dddddd\">(...)</div>\n";
				echo "<div style=\"background-color:#ffffff;color:#606060;height:200px;overflow:auto;border:1px inset #000000;padding:6px\"><div style=\"white-space:normal;text-align:left;\">".$strprev."</div></div>";
			}
			else
				echo "<div style=\"font-family: monospace;background-color:#ffffff;height:200px;overflow:auto;border:1px inset #000000;padding:5px\">".FN_i18n("preview not avaiable")."</div>";
		}
		elseif (preg_match("/^image\\//s",$filetype) == 1) //images
		{
			if (!function_exists("imagecreatetruecolor"))
			{
				$icon = $image_http_link;
			}
			else
			{
				$icon = "{$_FN['siteurl']}modules/filemanager/thumb.php?f=$dir{$_FN['slash']}$file&amp;h=185&amp;w=480";
			}
			echo "<div style=\"background-color:transparent;background-image:url('images/transparent.png');height:200px;overflow:auto;border:1px inset #000000;padding:5px\"><img style=\"background-color:transparent;\" src=\"$icon\" border=\"0\"  alt=\"\" /></div>";
		}
		else //others
		{
			echo "<div style=\"background-color:transparent;height:200px;overflow:auto;border:1px inset #000000;padding:5px\">".FN_i18n("preview not avaiable")."</div>";
		}

		echo "<button  onclick=\"window.location='$lind'\"  >&lt;&lt;".FN_i18n("back")."</button>";
		if ($sess_filemanager_editor != "")
		{
			if (!empty($_GET['linklocalfs']))
			{
				echo "&nbsp;<button onclick=\"insertElement('{$dir}/{$file}"."')\" >".FN_i18n("insert")."</button>";
			}
			else
				echo "&nbsp;<button onclick=\"insertElement('$url_link"."')\" >".FN_i18n("insert")."</button>";
		}
		else
		{
			echo "&nbsp;<button onclick=\"window.location='modules/filemanager/download.php?file=".urlencode($dir)."/".urlencode(basename($file))."'\"  >".FN_i18n("download")."</button>";
			echo "&nbsp;<button onclick=\"window.location='".fm_Link(array("opmod"=>"rename","dir"=>$dir,"ffile"=>$file),"&")."'\" >".FN_i18n('rename')." </button>";
			if (preg_match('/^text\\//s',$filetype) == 1 && FN_CanModifyFile($_FN['user'],"$dir{$_FN['slash']}$file"))
			{
				echo "&nbsp;<button onclick=\"window.location='".fm_Link(array("edit"=>1,"opmod"=>"open","dir"=>$dir,"ffile"=>$file),"&")."'\" >".FN_i18n("modify")." $file</button>";
			}
		}
		echo "</div>";
	}
}

/**
 * fm_SaveFile
 * Salva un file
 * @param string $dir
 * @param string $file
 */
function fm_SaveFile($dir,$file)
{
	global $_FN;
	if (!isset($_POST['body']) || $file == "")
	{
		return;
	}
	$body = FN_GetParam("body",$_POST);
	$filetowrite = "$dir{$_FN['slash']}$file";
	if (is_dir($filetowrite))
		echo "is directory";
	elseif (!file_exists($filetowrite))
		echo "$filetowrite file not exists";
	elseif (!is_writable($filetowrite))
		echo FN_i18n("file is readonly");
	else
	{
		$handle = fopen($filetowrite,"w");
		fwrite($handle,$body);
		fclose($handle);
		echo "<br />".FN_Alert("$filetowrite saved");
	}
}

/**
 *
 * @global array $_FN
 * @param string $dir
 * @param string $file
 * @return 
 */
function fm_EditFileForm($dir,$file)
{
	global $_FN;
	$sess_filemanager_editor = FN_GetParam("filemanager_editor",$_GET);



	$mime = FN_GetParam("mime",$_GET,"html");
	$op = FN_GetParam("opt",$_GET);
	$link = fm_Link(array("edit"=>""),"&");
	$linkcancel = fm_Link(array("edit"=>""),"&");
	//--- modifica del file ----
	if ($file !== null && file_exists($dir."{$_FN['slash']}".$file))
	{
		$readonly = $enable = "";
		$lhl = strtolower(FN_GetFileExtension($file));
		if (!FN_CanModifyFile($_FN['user'],"$dir{$_FN['slash']}$file"))
		{
			return;
		}
		echo "<form style=\"display:block;position:relative;width:100%;height:100%;margin:0px;padding:0px;\" method='post' action='?mod={$_FN['mod']}&amp;opt=$op&amp;filemanager_editor=$sess_filemanager_editor&amp;mime=$mime'>";

		if (!is_writable("$dir{$_FN['slash']}$file"))
		{
			$readonly = "readonly='readonly' ";
			$enable = " disabled=\"true\" ";
		}
		//---top bar --->
		echo "<div id=\"fmedit_top\" style=\"height:5%;overflow:hidden;background-color:#888888;color:#ffffff;text-align:center;\" >
			<b>".FN_i18n("modify").":</b>".("$dir{$_FN['slash']}$file").
		"</div>";
		//top bar -----<
		echo "<textarea style=\"height:88%;width:100%;border:1px inset;overflow:auto;\" id=\"fmedit_center\" cols=\"80\" rows=\"20\" id=\"body\" name=\"body\"
		$readonly wrap=\"off\" 
		>".htmlspecialchars(file_get_contents($dir."/".$file))."</textarea>";
		//---save bar --->
		echo "<div id=\"fmedit_bottom\" style=\"height:5%;overflow:hidden;padding:2px;background-color:#000000;color:#ffffff;text-align:right;\" >";
		echo "\n<input type=\"hidden\" name=\"opmod\" value=\"save\" />";
		echo "\n<input type=\"hidden\" name=\"ffile\" value=\"$file\" />";
		echo "\n<input type=\"hidden\" name=\"dir\" value=\"$dir\" />";
		echo "<button title=\"".FN_i18n("save")."\"  style=\"height:90%;font-size:10px\" $enable type=\"submit\">".FN_i18n("save")."</button>&nbsp;";
		echo "<button title=\"".FN_i18n("cancel")."\" style=\"height:90%;font-size:10px\" onclick='window.location=\"$linkcancel\";return false;'>".FN_i18n("cancel")."</button>";
		echo "</div>";
		//save bar -----<
		echo "
			<script>
			

</script>
";
		echo "</form>";
	}
}

/**
 * make filemanager link
 *
 * @global array $_FN
 * @param array $params
 * @param string $sep
 * @return string
 */
function fm_Link($params = false,$sep = "&amp;")
{
	global $_FN;
	$sess_filemanager_editor = FN_GetParam("filemanager_editor",$_GET);
	$link = array();
	foreach ($_GET as $key=> $value)
	{
		if (isset($_GET[$key]))
		{
			$link[$key] = "$key=".FN_GetParam("$key",$_GET);
			if (FN_GetParam("$key",$_GET) == "")
				unset($link[$key]);
		}
	}
	if (is_array($params))
	{
		foreach ($params as $key=> $value)
		{
			$link[$key] = "$key=".urlencode($params[$key]);
			if ($params[$key] == "")
				unset($link[$key]);
		}
	}
	$link = "?".implode($sep,$link);
	return $link;
}

/**
 * browse
 * visualizza elenco cartelle e files
 * @param string dir
 * 
 */
function fm_Browse($dir)
{
	global $_FN;
	$sess_filemanager_editor = FN_GetParam("filemanager_editor",$_GET);
	$op = FN_GetParam("opt",$_GET);
	$mime = FN_GetParam("mime",$_GET,"html");
	//dprint_r($dir);
	$_dir = $dir;
	$_dir = FN_RelativePath($_dir);
	$exdir = explode($_FN['slash'],$_dir);
	$strdir = "";
	$pd = "";
	foreach ($exdir as $ld)
	{
		$pd .= $ld;
		if (FN_CanModify($_FN['user'],$pd))
			$strdir .= "<a title=\"$dir\" href=\"".fm_Link(array("dir"=>$pd,"opmod"=>""))."\">$ld</a>{$_FN ['slash']}";
		else
			$strdir .= "$ld{$_FN ['slash']}";
		$pd .= $_FN['slash'];
	}

	$op = FN_GetParam("opt",$_GET);
	$mime = FN_GetParam("mime",$_GET,"html");
	$order = FN_GetParam("order",$_GET);
	$viewmode = FN_GetParam("mode",$_GET);
	if ($order == "")
		$order = "name";
	//$viewmode="t";
	//---  creazione elenco ---
	// BARRA NAVIGAZIONE -------------------->
	echo "<div style=\"height:22px;overflow:hidden;white-space:nowrap\">";
	echo "<a href=\"javascript:history.back()\"><img src=\"images/left.png\" style=\"vertical-align:middle;height:16px;width:16px;border:0px\" alt=\"".FN_i18n("back")."\" title=\"".FN_i18n("back")."\"></a>";
	echo "<a href=\"javascript:history.forward()\"><img src=\"images/right.png\" style=\"vertical-align:middle;height:16px;width:16px;border:0px\" alt=\"".FN_i18n("next")."\" title=\"".FN_i18n("next")."\"></a>";
	if (FN_CanView($_FN['user'],dirname($dir)))
	{
		$l = fm_Link(array("dir"=>dirname("$dir"),"opmod"=>""));
		echo "&nbsp;<a href=\"$l\"><img src=\"images/folder-up.png\" style=\"vertical-align:middle;height:16px;width:16px;border:0px\" alt=\"up\" /></a>";
	}
	$l = fm_Link(array("dir"=>".","opmod"=>""));
	if (FN_CanView($_FN['user'],"."))
	{
		echo "&nbsp;|&nbsp;<a href=\"$l\"><img src=\"images/home.png\" style=\"vertical-align:middle;height:16px;width:16px;border:0px\" alt=\"home\"></a>";
	}
	if ($viewmode == "")
	{
		$l = fm_Link(array("mode"=>"t","opmod"=>""));
		echo "&nbsp;|&nbsp;&nbsp;<a title=\"".FN_i18n("preview")."\" href=\"$l\"><img src=\"images/mime/image.png\" style=\"vertical-align:middle;height:16px;width:16px;border:1px outset transparent\" alt=\"home\"></a>";
	}
	else
	{
		$l = fm_Link(array("mode"=>"","opmod"=>""));
		echo "&nbsp;|&nbsp;&nbsp;<a title=\"".FN_i18n("preview")."\" href=\"$l\"><img src=\"images/mime/image.png\" style=\"vertical-align:middle;height:16px;width:16px;border:1px inset #dadada\" alt=\"home\"></a>";
	}
	echo "&nbsp;&nbsp;&nbsp;$strdir</div>";
	// BARRA NAVIGAZIONE --------------------<
	// TITOLI -------------------->
	$down = "&nbsp;<img src=\"images/down.png\" style=\"border:0px;height:10px;vertical-align:middle\"/>";
	$bgcolorselect = "#f0f0f0";
	$bgcolor = "#dadada";
	$minwidth = 25 + _SIZE1 + _SIZE2 + _SIZE3 + _SIZE4 + _SIZE5;
	echo "\n<table style=\"width:{$minwidth}px;padding:0px;margin:0px;\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"margin-top:10px;\">";
	echo "\n\t<tr style=\"\">";
	$a = $order == "name" ? $down : "";
	$cl = $order == "name" ? $bgcolorselect : $bgcolor;
	echo "<td style=\"width:".(_SIZE1 - 1)."px;background-color:$cl;color:#000000;border-left:1px solid #000000\"><a href=\"".fm_Link(array("order"=>"name"))."\" ".fm_StyleDiv(_SIZE1 - 1)." >";
	echo FN_i18n("name").$a;
	echo "</div></td>";
	$a = $order == "size" ? $down : "";
	$cl = $order == "size" ? $bgcolorselect : $bgcolor;
	echo "<td style=\"width:".(_SIZE2 - 1)."px;background-color:$cl;color:#000000;border-left:1px solid #000000\"><a href=\"".fm_Link(array("order"=>"size"))."\"".fm_StyleDiv(_SIZE2 - 1)." >";
	echo FN_i18n("size").$a;
	echo "\n\t\t</td>";
	$a = $order == "perm" ? $down : "";
	$cl = $order == "perm" ? $bgcolorselect : $bgcolor;
	echo "\n\t\t<td style=\"width:".(_SIZE3 - 1)."px;background-color:$cl;color:#000000;border-left:1px solid #000000\"><a href=\"".fm_Link(array("order"=>"perm"))."\"".fm_StyleDiv(_SIZE3 - 1)." >";
	echo FN_i18n("permissions").$a;
	echo "</div></td>";
	$a = $order == "data" ? $down : "";
	$cl = $order == "data" ? $bgcolorselect : $bgcolor;
	echo "<td style=\"width:".(_SIZE4 - 1)."px;background-color:$cl;color:#000000;border-left:1px solid #000000\"><a href=\"".fm_Link(array("order"=>"data"))."\"".fm_StyleDiv(_SIZE4 - 1)." >";
	echo FN_i18n("data").$a;
	echo "</div></td>";
	echo "<td style=\"width:".(_SIZE5 - 1)."px;background-color:#efef52;color:#000000;border-left:1px solid #000000\"><div ".fm_StyleDiv(_SIZE5 - 1)." >";
	echo "</div></td>";
	echo "\n\t</tr>";
	echo "\n</table>";
	// TITOLI --------------------<
	// CONTENUTO -------------------->
	echo "<div style=\"background-color:#f1f1e3;border:1px inset;height:300px;overflow:auto\">";
	if (!is_readable($dir))
		die("<br />accesso negato");
	$handle = opendir($dir);

	$listdir = array();
	$listfiles = array();
	while(false !== ($f = readdir($handle)))
	{
		if ($f != ".." && $f != ".")
		{
			$dirname = "$dir{$_FN['slash']}$f";
			if (is_dir($dir."{$_FN['slash']}".$f) && FN_CanViewFile($_FN['user'],"$dir{$_FN['slash']}$f"))
			{
				//dprint_r(glob("$dir/$f/*"));
				$ncontents = count(glob("$dirname{$_FN['slash']}*"));
				$listdir[] = array("fullpath"=>$dirname,"name"=>basename($f),"size"=>$ncontents,"perm"=>fm_GetPerms($dirname));
			}
			else
			{
				if ($mime == "all" || $mime == "" || preg_match("/".str_replace('/','\\/',$mime)."/s",fm_MimeContentType("$dir/$f")))
				{
					if (FN_CanViewFile($_FN['user'],"$dir{$_FN['slash']}$f"))
					{
						$listfiles[] = array("fullpath"=>$dirname,"name"=>basename($f),"size"=>@filesize($dirname),"perm"=>fm_GetPerms($dirname));
					}
				}
			}
		}
	}
	//dprint_r($listdir);
	$listdir = xmldb_array_natsort_by_key($listdir,$order);
	$listfiles = xmldb_array_natsort_by_key($listfiles,$order);
	if ($viewmode == "")
	{
		echo "
<script type=\"text/javascript\">
fmtrh = function (over,elem){
	var old;
	if (over){
		this.old = elem.style.backgroundColor ;
		elem.style.backgroundColor='yellow';
	}
	else{
		elem.style.backgroundColor=this.old;
	}
}
</script>
";
		echo "\n<table  border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"\">";
		$color[0] = "#ffffff";
		$color[1] = "#eaeaea";
		$i = 0;
		foreach ($listdir as $d)
		{

			$bgcolor = $color[$i % 2];
			echo "<tr style=\"background-color:$bgcolor\" onmouseover=\"fmtrh(1,this)\" onmouseout=\"fmtrh(0,this)\">";
			fm_DrawFolder($d['fullpath'],$viewmode);
			echo "</tr>";
			$i++;
		}
		foreach ($listfiles as $f)
		{
			$bgcolor = $color[$i % 2];
			echo "<tr style=\"background-color:$bgcolor\" onmouseover=\"fmtrh(1,this)\" onmouseout=\"fmtrh(0,this)\">";
			fm_DrawFile($f['fullpath'],$viewmode);
			echo "<tr>";
			$i++;
		}
		echo "\n</table>";
	}
	else
	{
		foreach ($listdir as $d)
		{
			fm_DrawFolder($d['fullpath'],$viewmode);
		}
		foreach ($listfiles as $f)
		{
			fm_DrawFile($f['fullpath'],$viewmode);
		}
	}
	echo "</div>";
	// CONTENUTO --------------------<
	// FORM -------------------->
	if (is_writable($dir) && FN_UserCanEditFolder($dir))
	{
		$ac = fm_Link(array("opmod"=>"upload"));
		echo "<fieldset><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" >";
		if ($mime != "folder")
		{

			echo "<tr >";
			echo "<td >";
			echo "".FN_i18n("upload file").":";
			echo "</td>";
			echo "<td >";
			//echo "<form enctype=\"multipart/form-data\" method=\"post\" action=\"?mod={$_FN['mod']}&amp;opt=$op&amp;opmod=upload&amp;filemanager_editor=$sess_filemanager_editor&amp;mime=$mime\" >";
			echo "<form enctype=\"multipart/form-data\" method=\"post\" action=\"$ac\" >";
			echo "<input title=\"".FN_i18n("upload file here")."\" type=\"file\" name=\"filename\" value=\"upload\" />";
			echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"90000000\" />";
			echo "<input type=\"submit\" class=\"submit\" value=\"".FN_i18n("send")."\" name=\"send\" />";
			echo "<input type=\"hidden\" value=\"$dir\" name=\"dir\" />";
			echo "</form>";
			echo "</td>";
			echo "</tr>";
		}
		if (FN_IsAdmin())
		{
			echo "<tr>";
			echo "<td>";
			echo FN_i18n('new folder')." :";
			echo "</td>";
			echo "<td>";

			echo "<form method=\"post\" action=\"".fm_Link(array("opmod"=>"newfolder"))."\" >";
			echo "<input size=\"20\" name=\"newfolder\" value=\"\" />";
			echo "<input type=\"submit\" class=\"submit\" value=\"".FN_i18n("create")."\" name=\"send\" />";
			echo "</form>";
			echo "</td>";
			echo "</tr>";
		}
		if ($mime != "folder")
		{
			echo "<tr>";
			echo "<td>";
			echo "".FN_i18n("new file")." :";
			echo "</td>";
			echo "<td>";
			echo "<form method=\"post\" action=\"".fm_Link(array("opmod"=>"newfile"))."\" >";
			echo "<input size=\"20\" name=\"newfile\" value=\"\" />";
			echo "<input type=\"submit\" class=\"submit\" value=\"".FN_i18n("create")."\" name=\"send\" />";
			echo "</form>";
			echo "</td>";
			echo "</tr>";
		}
		else
		{
			
		}
		echo "\n</table></fieldset>";
	}
	// FORM --------------------<
}

/**
 *
 * @param string $size
 * @param string $extra
 * @return string
 */
function fm_StyleDiv($size,$extra = "")
{
	return "style=\"font-size:10px;color:#000000;font-family:Verdana, Arial;white-space:nowrap;width:$size"."px;overflow:hidden;margin:0px;padding:0px;$extra\"";
}

/**
 *
 * @return string
 */
function fm_StyleA()
{
	return "style=\"text-decoration:none;font-weight:normal;padding:0px;font-size:10px;color:#000000;font-family:Verdana,'Lucida Grande';border:0px;margin:0px\"";
}

/**
 *
 * @return string
 */
function fm_StyleF()
{
	return "style=\"background-color:#ffffff;font-weight:normal ; padding:0px ; font-size:10px ; color:#000000 ; font-family: Verdana , 'Lucida Grande' ; border:0px ; margin:0px\"";
}

/**
 * fm_DrawFolder
 * disegna una cartella
 */
function fm_DrawFolder($dir,$mode)
{
	global $_FN;
	static $i = 0;
	$color2[0] = "#f3e49c";
	$color2[1] = "#f3e4c8";
	$_FN['colorline'] = isset($_FN['colorline']) ? $_FN['colorline'] : 1;
	$bgcolor2 = $color2[($_FN['colorline'] ++) % 2];
	$bgcolor = "transparent";
	$sess_filemanager_editor = FN_GetParam("filemanager_editor",$_GET);
	$op = FN_GetParam("opt",$_GET);
	$mime = FN_GetParam("mime",$_GET,"html");
	if (basename($dir) == ".")
		return;
	$ncontents = count(glob("$dir/*"));
	$pdir = $dir;
	if (basename($pdir) == "..")
		$pdir = fm_absolutepath($pdir);
	//$pdir = $pdir;
	$l = fm_Link(array("edit"=>"","dir"=>$pdir,"opmod"=>""));
	$l2 = fm_Link(array("edit"=>"","dir"=>$pdir,"opmod"=>""),"&");
	$tit = basename($dir);
	if ($mode == "")
		$icon = FN_FromTheme("images/mime/dir.png");
	else
		$icon = FN_FromTheme("modules/filemanager/folder.png");
	$size = $ncontents;
	$perm = fm_GetPerms($dir);
	$date = date("Y-m-d h:i:s",filemtime($dir));
	if ($mode == "")
	{
		echo "<td style=\"background-color:transparent;width:"._SIZE1."px\"><div ".fm_StyleDiv(_SIZE1)." >";
		echo "<a  title=\"".FN_i18n("open")." ".htmlentities(basename($dir),ENT_QUOTES)."\"  ".fm_StyleA()."href=\"$l\""."style=\"text-decoration: none;font-family: sans-serif,helvetica, arial;\" >";
		echo "<img style=\"vertical-align:middle\" src=\"$icon\" border='0' />&nbsp;";
		echo basename($dir)."</a>";
		echo "</div>\n\t\t</td>";
		echo "\n\t\t<td style=\"background-color:transparent;width:"._SIZE2."px\"><div ".fm_StyleDiv(_SIZE2,"text-align:right")." >";
		echo "($ncontents)";
		echo "</div></td>";
		echo "<td style=\"background-color:transparent;width:"._SIZE3."px\"><div ".fm_StyleDiv(_SIZE3,"text-align:right")." >";
		echo fm_GetPerms($dir);
		echo "</div></td>";
		echo "<td style=\"background-color:transparent;width:"._SIZE4."px\"><div ".fm_StyleDiv(_SIZE4,"text-align:right")." >";
		echo date("Y-m-d h:i:s",@filemtime($dir));
		echo "</div></td>";
		if (is_writable($dir) && basename($dir) != "..")
		{
			$ldel = fm_Link(array("opmod"=>"delfile","dir"=>dirname($dir),"ffile"=>basename($dir)),"&");
			$lmod = fm_Link(array("opmod"=>"rename","dir"=>dirname($dir),"ffile"=>basename($dir)));
			echo "\n\t\t<td style=\"background-color:$bgcolor2;width:"._SIZE5."px\"><div ".fm_StyleDiv(_SIZE5)." >&nbsp;";
			echo "<a title=\"".FN_i18n("delete")."\" ".fm_StyleA()." href=\"javascript:check('?mod={$_FN['mod']}&opt=$op&opmod=delfile&dir=".dirname($dir)."&ffile=".basename($dir)."&filemanager_editor=$sess_filemanager_editor&mime=$mime');\"><img style=\"border:0px\" src=\"".FN_FromTheme("images/delete.png")."\" alt=\"".FN_i18n("delete")."\"  /></a>";
			echo "&nbsp;<a title=\"".FN_i18n("rename")."\" ".fm_StyleA()." href=\"$lmod\" ><img style=\"border:0px;\" src=\"".FN_FromTheme("images/rename.png")."\" alt=\"".FN_i18n("rename")."\" /></a>";
			echo "</div></td>";
		}
		else
		{
			echo "\n\t\t<td style=\"width:140px\">&nbsp;</td>";
		}
	}
	else
	{
		echo "<div onmouseout=\"this.style.borderColor='#dadada'\" onmouseover=\"this.style.borderColor='#1010ff'\" onclick=\"window.location='$l2'\" style=\"float:left;background-color:#f0f0f0;margin:4px;border:1px solid #dadada;padding:0px;height:133px;width:112px;text-align:center;overflow:hidden\">";
		echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" title=\"".FN_i18n("open")." $tit\" onclick=\"window.location='$l2'\" style=\"cursor:pointer;background-color:#ffffff;margin:0px;line-height:80px;height:80px;width:100%;text-align:center;overflow:hidden\"><tr><td valign=\"center\">";
		echo "<img style=\"line-height:80px;margin:0px;border:0px;vertical-align:middle;\" src='$icon' alt=\"\" />";
		echo "</td></tr></table>";
		echo "<div style=\"font-weight:bold;font-size:12px;height:14px;overflow:hidden\">$tit</div>";
		echo "<div style=\"font-size:10px;height:12px;overflow:hidden\">($size)</div>";
		echo "<div style=\"font-size:10px;height:12px;overflow:hidden\">$perm</div>";
		echo "<div style=\"font-size:10px;height:12px;overflow:hidden\">$date</div>";
		echo "</div>";
	}
}

/**
 *
 * @global array $_FN
 * @param string $file
 * @param string $mode
 */
function fm_DrawFile($file,$mode)
{
	global $_FN;
	$color[0] = "#ffffff";
	$color[1] = "#eaeaea";
	$color2[0] = "#f3e49c";
	$color2[1] = "#f3e4c8";
	$_FN['colorline'] = isset($_FN['colorline']) ? $_FN['colorline'] : 1;
	;
	$bgcolor = $color[($_FN['colorline']) % 2];
	$bgcolor2 = $color2[($_FN['colorline'] ++) % 2];
	$bgcolor = "transparent";
	$sess_filemanager_editor = FN_GetParam("filemanager_editor",$_GET);
	$mime = FN_GetParam("mime",$_GET,"html");
	$op = FN_GetParam("opt",$_GET);
	$pdir = dirname($file);
	$pfile = basename($file);
	$icon = FN_GetIconByFilename($file);
	$ldel = fm_Link(array("opmod"=>"delfile","dir"=>dirname($file),"ffile"=>basename($file)),"&");
	$lmod = fm_Link(array("opmod"=>"rename","dir"=>dirname($file),"ffile"=>basename($file)));
	$l = fm_Link(array("edit"=>"","ffile"=>basename($pfile),"dir"=>$pdir,"opmod"=>"open"));
	$l2 = fm_Link(array("edit"=>"","ffile"=>basename($pfile),"dir"=>$pdir,"opmod"=>"open"),"&");
	$tit = basename($file);
	$size = fm_ByteConvert(@filesize($file));
	$perm = fm_GetPerms($file);
	$date = date("Y-m-d h:i:s",filemtime($file));
	if ($mode == "")
	{
		echo "<td style=\"background-color:$bgcolor;width:"._SIZE1."px\"><div ".fm_StyleDiv(_SIZE1)." >";
		echo "<a title=\"".FN_i18n("open")." ".htmlentities(basename($file),ENT_QUOTES)."\" ".fm_StyleA()." href=\"$l"."\" >";
		echo "<img style=\"vertical-align:middle;width:16px;height:16px;\" src='$icon' border='0' />&nbsp;";
		echo basename($file)."</a>";
		echo "</div></td>";
		echo "<td style=\"background-color:$bgcolor;width:"._SIZE2."px\"><div ".fm_StyleDiv(_SIZE2,"text-align:right")." >";
		echo $size;
		echo "</div></td>";
		echo "<td style=\"background-color:$bgcolor;width:"._SIZE3."px\"><div ".fm_StyleDiv(_SIZE3,"text-align:right")." >";
		echo $perm;
		echo "</div></td>";
		echo "<td style=\"background-color:$bgcolor;width:"._SIZE4."px\"><div ".fm_StyleDiv(_SIZE4,"text-align:right")." >";
		echo $date;
		echo "</div></td>";
		echo "<td style=\"background-color:$bgcolor2;width:"._SIZE5."px\"><div ".fm_StyleDiv(_SIZE5)." >&nbsp;";
		if (is_writable($file))
		{

			echo "<a title=\"".FN_i18n("delete")."\" ".fm_StyleA()."href='#' onclick=\"check('$ldel');\"><img style=\"border:0px\" src=\"".FN_FromTheme("images/delete.png")."\" alt=\"".FN_i18n("delete")."\" /></a>";
			echo "&nbsp;<a title=\"".FN_i18n("rename")."\" ".fm_StyleA()." href=\"$lmod\" ><img style=\"border:0px\" src=\"".FN_FromTheme("images/rename.png")."\" alt=\"".FN_i18n('rename')."\" /></a>&nbsp;";
		}
		echo "<a title=\"".FN_i18n("download")."\" ".fm_StyleA()." href=\"modules/filemanager/download.php?file=$pdir/".basename($file)."\" ><img style=\"border:0px\" src=\"".FN_FromTheme("images/download.png")."\" alt=\"".FN_i18n("download")."\" /></a>";
		echo "</div></td>";
	}
	else
	{
		$filetype = fm_MimeContentType($file);
		if (preg_match("/^image\\//s",$filetype) == 1)
		{
			$icon = "{$_FN['siteurl']}modules/filemanager/thumb.php?f=$file&amp;h=80&amp;w=110";
		}
		echo "<div onmouseout=\"this.style.borderColor='#dadada'\" onmouseover=\"this.style.borderColor='#1010ff'\" onclick=\"window.location='$l2'\" style=\"float:left;background-color:#f0f0f0;margin:4px;border:1px solid #dadada;padding:0px;height:133px;width:112px;text-align:center;overflow:hidden\">";
		echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" title=\"".FN_i18n("open")." $tit\" onclick=\"window.location='$l2'\" style=\"cursor:pointer;background-color:#ffffff;margin:0px;line-height:80px;height:80px;width:100%;text-align:center;overflow:hidden\"><tr><td valign=\"center\">";
		echo "<img style=\"line-height:80px;margin:0px;border:0px;vertical-align:middle;\" src='$icon' alt=\"\" />";
		echo "</td></tr></table>";
		echo "<div style=\"font-weight:bold;font-size:12px;height:14px;overflow:hidden\">$tit</div>";
		echo "<div style=\"font-size:10px;height:12px;overflow:hidden\">$size</div>";
		echo "<div style=\"font-size:10px;height:12px;overflow:hidden\">$perm</div>";
		echo "<div style=\"font-size:10px;height:12px;overflow:hidden\">$date</div>";
		echo "</div>";
	}
}

/**
 * 
 * @param string $filename
 */
function fm_GetPerms($filename)
{
	if (!file_exists($filename))
	{
		return;
	}
	$perms = fileperms($filename);
	if (($perms & 0xC000) == 0xC000)
	{
		// Socket
		$info = 's';
	}
	elseif (($perms & 0xA000) == 0xA000)
	{
		// Symbolic Link
		$info = 'l';
	}
	elseif (($perms & 0x8000) == 0x8000)
	{
		// Regular
		$info = '-';
	}
	elseif (($perms & 0x6000) == 0x6000)
	{
		// Block special
		$info = 'b';
	}
	elseif (($perms & 0x4000) == 0x4000)
	{
		// Directory
		$info = 'd';
	}
	elseif (($perms & 0x2000) == 0x2000)
	{
		// Character special
		$info = 'c';
	}
	elseif (($perms & 0x1000) == 0x1000)
	{
		// FIFO pipe
		$info = 'p';
	}
	else
	{
		// Unknown
		$info = 'u';
	}
	// Owner
	$info .= ( ($perms & 0x0100) ? 'r' : '-');
	$info .= ( ($perms & 0x0080) ? 'w' : '-');
	$info .= ( ($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));
	// Group
	$info .= ( ($perms & 0x0020) ? 'r' : '-');
	$info .= ( ($perms & 0x0010) ? 'w' : '-');
	$info .= ( ($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));
	// World
	$info .= ( ($perms & 0x0004) ? 'r' : '-');
	$info .= ( ($perms & 0x0002) ? 'w' : '-');
	$info .= ( ($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));
	return $info;
}

/**
 * 
 * @param int $bytes
 */
function fm_ByteConvert($bytes)
{
	if ($bytes <= 0)
		return '0 Byte';
	$convention = 1000; //[1000->10^x|1024->2^x]
	$s = array('B','kB','MB','GB','TB','PB','EB','ZB');
	$e = floor(log($bytes,$convention));
	return round($bytes / pow($convention,$e),2).' '.$s[$e];
}

/**
 *
 * @param string $f
 * @return string
 */
function fm_MimeContentType($f)
{
	$ext = strtolower(FN_GetFileExtension($f));
	switch($ext)
	{
		case "js" :
		case "inc" :
		case "xhtml" :
		case "xml" :
		case "html" :
		case "htm" :
		case "php" :
		case "css" :
		case "txt" :
		case "sh" :
		case "" :
			return "text/plain";
			break;
		case "png" :
		case "bmp" :
		case "jpg" :
		case "jpeg" :
		case "ico" :
		case "gif" :
			return "image/$ext";
			break;
		default :
			return "binary/$ext";
			break;
	}
}

/**
 *
 * @param string $path
 * @return string 
 */
function fm_absolutepath($path)
{
	// dprint_r($path);
	$out = array();
	foreach (explode('/',$path) as $i=> $fold)
	{
		if ($fold == '' || $fold == '.')
			continue;
		if ($fold == '..' && $i > 0 && end($out) != '..')
			array_pop($out);
		else
			$out[] = $fold;
	} $path = ($path{0} == '/' ? '/' : '').join('/',$out);
	// dprint_r($path);
	return $path;
}

?>