<?php
/**
 * @package Flatnux_theme_Sugar
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 *
 */
defined("_FNEXEC") or die("Restricted access");
global $_FN;
global $menu_recursive,$menu_recursive_onlyselected,$show_subsections_in_section,$show_section_title_bar;
$menu_recursive_onlyselected=0;
$menu_recursive=0;
$config=FN_LoadConfig("themes/{$_FN['theme']}/config.php");
if ($config['make_vertical_menu_recursive'])
{
	$menu_recursive=1;
}
if ($config['make_vertical_menu_recursive'] > 1)
{
	$menu_recursive_onlyselected=1;
}
$show_subsections_in_section=$config['show_subsections_in_section'];
$show_section_title_bar=$config['show_section_title_bar'];
/**
 *
 * @global array $_FN
 * @param string $title
 * @return string 
 */
function FN_HtmlOpenBlock($title)
{
	global $_FN;
	return "
<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\"
	class=\"leftColumnModuleHead\">
	<tr>
		<th width=\"5\" valign=\"top\"><img
			src=\"{$_FN['siteurl']}themes/{$_FN['theme']}/images/moduleTb.gif\"
			alt=\"$title\" width=\"5\" height=\"23\" border=\"0\"></th>
		<th width=\"100%\" class=\"leftColumnModuleName\">{$title}</th>
		<th width=\"7\" valign=\"top\"><img
			src=\"{$_FN['siteurl']}themes/{$_FN['theme']}/images/moduleTc.gif\"
			alt=\"$title\" width=\"7\" height=\"23\" border=\"0\"></th>
	</tr>
</table>
<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"subMenu\"
	width=\"100%\">
	<tr>
		<td class=\"block\">
	";
}
/**
 *
 * @return string 
 */
function FN_HtmlCloseBlock()
{
	return "</td></tr></table><br />";
}
/**
 *
 * @global array $_FN
 * @return 
 */
function createsubmenu1()
{
	global $_FN;
	$ll="";
	if ($_FN ['lang'] != $_FN ['lang_default'])
		$ll="lang={$_FN['lang']}&amp;";

	if ($_FN['mod'] == "")
		return;
	$modlist=FN_GetSections($_FN['mod']); // return array width title - link
	echo '	<div id="MoreMenu" class="menu">';
	$old="MoreMenu";
	foreach ($modlist as $modl)
	{
		$link=$modl ['link'];
		$linkh=$modl ['id'];
		$title=$modl ['title'];
		$accesskey=$modl ['accesskey'];
		echo "\n<a accesskey=\"{$modl ['accesskey']}\" href=\"" . fn_rewritelink("index.php?mod={$linkh}{$ll}") . "\" class=\"menuItem\" id=\"{$modl ['accesskey']}sdf\" name=\"MoreMenu\"
			onmouseover=\"hiliteItem(this,'yes'); closeSubMenus(this);\"
			onmouseout=\"unhiliteItem(this);\">$title</a>";
	}
	echo '</div>';
}
/**
 *
 * @global array $_FN 
 */
function MyCreateMenuH()
{
	global $_FN;
	$modlist=FN_GetSections();
	$ll="";
	if ($_FN ['lang'] != $_FN ['lang_default'])
		$ll="lang={$_FN['lang']}&amp;";
	foreach ($modlist as $mod)
	{
		$tmp=$mod ['title'];
		$link=$mod ['link'];
		$accesskey=FN_GetAccessKey($tmp,$link);
		$linkh=$mod ['id'];
		$accesskey=$mod ['accesskey'];
		$class=$_FN['mod'] == $mod ['id'] ? "currentTab" : "otherTab";
		$classr=$_FN['mod'] == $mod ['id'] ? "currentTabRight" : "otherTabRight";
		$classl=$_FN['mod'] == $mod ['id'] ? "currentTabLink" : "otherTabLink";
		$image=$_FN['mod'] == $mod ['id'] ? "currentU.gif" : "otherTab.gif";
		echo "<td><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">
	<tr style=\"height: 25px\">
		<td><img
			src=\"{$_FN['siteurl']}themes/{$_FN['theme']}/images/$image\"
			width=\"5\" height=\"25\" border=\"0\" alt=\"Home\"></td>
		<td class=\"$class\" nowrap><a class=\"$classl\" accesskey=\"$accesskey\"
		href=\"" . fn_rewritelink("index.php?{$ll}mod=$linkh") . "\">$tmp</a></td>
		<td class=\"$classr\"><img
			src=\"{$_FN['siteurl']}themes/{$_FN['theme']}/images/blank000.gif\" width=\"2\"
			height=\"1\" border=\"0\" title=\"$tmp\" alt=\"$tmp\"></td>
		<td valign=\"bottom\" style=\"background-image : url({$_FN['siteurl']}themes/{$_FN['theme']}/images/emptyTabSpace.gif);\"></td>
	</tr>
</table>
</td>";
	}
}
/**
 *
 * @global array $_FN
 * @global int $menu_recursive
 * @global int $menu_recursive_onlyselected
 */
function create_menu()
{
	global $_FN;
	global $menu_recursive,$menu_recursive_onlyselected;
	?>

	<table cellpadding="0" cellspacing="0" border="0" width="100%"
		   class="leftColumnModuleHead">
		<tr>
			<th width="5" valign="top"><img
					src="<?php echo $_FN['siteurl'];?>themes/<?php echo $_FN['theme']?>/images/moduleTb.gif" alt="Menu"
					width="5" height="23" border="0"></th>
			<th width="100%" class="leftColumnModuleName">Menu</th>
			<th width="7" valign="top"><img
					src="<?php echo $_FN['siteurl'];?>themes/<?php echo $_FN['theme']?>/images/moduleTc.gif" alt="Menu"
					width="7" height="23" border="0"></th>
		</tr>
	</table>


	<table cellpadding="0" cellspacing="0" border="0" class="subMenu"
		   width="100%">
			   <?php
			   printsection2("",$menu_recursive);
			   ?>
	</table>
	<script type="text/javascript" language="Javascript">
		if (!Get_Cookie('showLeftCol')) {
			Set_Cookie('showLeftCol','true',30,'/','','');
		}
		var show = Get_Cookie('showLeftCol');

		if (show == 'true') {
			this.document.getElementById('leftCol').style.display='inline';
			document['HideHandle'].src = '<?php echo $_FN['siteurl']?>themes/<?php echo $_FN['theme']?>/images/hide.gif';
		} else {
			this.document.getElementById('leftCol').style.display='none';
			document['HideHandle'].src = '<?php echo $_FN['siteurl']?>themes/<?php echo $_FN['theme']?>/images/show.gif';

		}
	</script>

	<br>
	<?php
}
/**
 * Stampa la lista delle sezioni e delle sottosessioni
 *
 * @param string $path dove cercare la sezione (es. sections/01_pippo)
 *
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 *
 */
function printsection2($path,$recursive = true)
{
	global $_FN;
	global $menu_recursive_onlyselected;
	static $slevel=0;
	$modlist=FN_GetSections($path,false,false);
	$slevel++;
	//echo "\n<br /> ";
	$ll="";
	if ($_FN ['lang'] != $_FN ['lang_default'])
		$ll="lang={$_FN['lang']}&amp;";

	foreach ($modlist as $mod)
	{
		$tmp=$mod ['title'];
		$link=$mod ['link'];
		$accesskey=$mod ['accesskey'];
		//$mod = sectionlocation($mod['link']);


		$stile1="";
		if ($mod ['link'] == $_FN['home_section'])
		{

			if (file_exists("themes/{$_FN['theme']}/images/menu.png"))
				$stile1="themes/{$_FN['theme']}/images/menu.png";
		}
		else
		if (file_exists("themes/{$_FN['theme']}/images/section.png"))
			$stile1="themes/{$_FN['theme']}/images/section.png";

		$tmp=$mod ['title'];
		$link=$mod ['link'];
		$accesskey=$mod ['accesskey'];
		if (file_exists('sections/' . $mod ['id'] . '/icon.png'))
			$icon='sections/' . $mod ['id'] . '/icon.png';
		else
			$icon="themes/{$_FN['theme']}/images/CreateMe.gif";
		?>
		<tr>
			<td class="subMenuTDIcon" width="16" style="background-image: url(themes/<?php echo $_FN['theme']?>/images/createBg.gif); background-repeat : repeat-y;"><a
					accesskey="<?php echo $accesskey?>"
					onMouseOver="document.getElementById('CreateMeetings_sh<?php echo $accesskey?>').style.background='#ffffff';"
					onMouseOut="document.getElementById('CreateMeetings_sh<?php echo $accesskey?>').style.background='#eeeeee';"
					class="subMenuLink" href="<?php
		$linkh=$mod ['id'];
		echo fn_rewritelink("index.php?{$ll}mod=$linkh");
		?>">
						<?php
						if ($slevel == 1)
						{
							?>
						<img src='<?php echo $_FN['siteurl'];?><?php echo $icon?>' width='16' height='16'
							 alt='<?php echo $tmp?>' border='0' style="vertical-align: middle;">
							 <?php
						 }
						 ?>
				</a></td>
			<td nowrap id="CreateMeetings_sh<?php echo $accesskey?>"
				class="subMenuTD"
				onMouseOver="this.style.background='#ffffff';this.style.cursor='hand';"
				onMouseOut="this.style.background='#eeeeee';this.style.cursor='auto';"
				onclick="location.href='<?php echo fn_rewritelink("index.php?{$ll}mod=$linkh")?>'">
					<?php
					for ($i=1; $i < $slevel; $i++)
					{
						echo "&nbsp;&nbsp;";
					}
					?>
				<span class="subMenuLink" onMouseOver="this.style.color='#990033';"
					  onMouseOut="this.style.color='#33485c';"><?php echo $tmp?></span></td>
		</tr>

		<?php
		//echo "printsection(\"$path/$link\")";
		$l=basename($link);

		if ($recursive)
		{
			if ($menu_recursive_onlyselected && $_FN['mod'] == $mod ['id'])
			{
				printsection2($mod ['id']);
			}
		}
		//else
		//}
	}

	$slevel--;
	//echo "\n</ul>\n";
}
/**
 * menu verticale a scomparsa, compare quando il
 * menu a sinistra viene minimizzato e si va sopra la freccia
 * con il mouse 
 */
function MyCreateMenu2()
{
	global $_FN;
	$modlist=FN_GetSections();

	$ll="";
	if ($_FN ['lang'] != $_FN ['lang_default'])
		$ll="lang={$_FN['lang']}&amp;";
	foreach ($modlist as $mod)
	{
		$tmp=$mod ['title'];
		$link=$mod ['link'];
		$accesskey=$mod ['accesskey'];
		if (file_exists('sections/' . $link . '/icon.png'))
			$icon='sections/' . $link . '/icon.png';
		else
			$icon="themes/{$_FN['theme']}/images/CreateMe.gif";
		?>
		<tr>
			<td class="subMenuTDIcon" width="16" style="background-image: url(themes/<?php echo $_FN['theme']?>/images/createBg.gif); background-repeat : repeat-y;"><a
					accesskey="<?php echo $accesskey?>"
					onMouseOver="document.getElementById('CreateMeetings_sh<?php echo $accesskey?>').style.background='#ffffff';"
					onMouseOut="document.getElementById('CreateMeetings_sh<?php echo $accesskey?>').style.background='#eeeeee';"
					class="subMenuLink" href="<?php
		$linkh=$mod ['id'];

		echo fn_rewritelink("index.php?{$ll}mod=$linkh");
		?>"><img src='<?php echo $_FN['siteurl'];?><?php echo $icon?>'
					   width='16' height='16' alt='<?php echo $tmp?>' border='0'
					   style="vertical-align: middle;"></a></td>
			<td nowrap id="CreateMeetings_sh<?php echo $accesskey?>"
				class="subMenuTD"
				onMouseOver="this.style.background='#ffffff';this.style.cursor='hand';"
				onMouseOut="this.style.background='#eeeeee';this.style.cursor='auto';"
				onclick="location.href='<?php echo fn_rewritelink("index.php?{$ll}mod=$linkh");?>'">&nbsp; <span class="subMenuLink"
																											 onMouseOver="this.style.color='#990033';"
																											 onMouseOut="this.style.color='#33485c';"><?php echo $tmp?></span></td>
		</tr>

		<?php
	}
}
function FN_CreateSubmenu()
{
	global $_FN;
	$sections=FN_GetSections($_FN['mod']);
	if (count($sections) > 0)
	{
		echo "<br />";
		echo FN_HtmlOpenTable();
		foreach ($sections as $section)
		{
			$img="<img style=\"vertical-align:middle\" alt=\"\" src=\"{$_FN['siteurl']}themes/{$_FN['theme']}/images/subsection.png\" />";
			$accesskey=FN_GetAccessKey($section['title'],$section['link']);
			echo "$img&nbsp;<a accesskey=\"$accesskey\" title=\"{$section['description']}\" href=\"{$section['link']}\">{$section['title']}</a><br />";
		}
		echo FN_HtmlCloseTable();
	}
}
function FN_SectionFooter()
{
	
}
function FN_HtmlOpenTable()
{
	return '
	<table class="tabForm" border="0" cellpadding="0" cellspacing="0"
		   width="100%">
		<tbody>
			<tr>
				<td>';
}
function FN_HtmlCloseTable()
{
	return '
				</td>
			</tr>
		</tbody>
	</table><br />';
}
function OpenTableTitle($title)
{
	global $_FN;
	echo '
	<br />
	<table class="tabDetailView" border="0" cellpadding="4" cellspacing="0"
		   width="100%">
		<tbody>
			<tr>
				<td colspan="20" class="listViewPaginationTdS1"
					style="padding: 0px;">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tbody>
							<tr>
								<td style="text-align: left;" class="tabDetailViewDL">&nbsp;' . $title . '</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>

			<tr>
				<td align="left" bgcolor="ffffff">
	';
}
function FN_OpenNews($title)
{
	?>
	<br />
	<table class="tabDetailView" border="0" cellpadding="0"
		   cellspacing="0" width="100%">
		<tbody>
			<tr>
				<td colspan="20" class="listViewPaginationTdS1"
					style="padding: 0px;">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tbody>
							<tr>
								<td style="text-align: left;" class="tabDetailViewDL">&nbsp;<?php echo $title?></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>

			<tr>
				<td align="left" bgcolor="efefef">
					<?php
				}
				function FN_CloseNews()
				{
					?>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}
function CloseTableTitle()
{
	?>
	</td>
	</tr>
	</tbody>
	</table>
	<?php
}
function FN_OpenSection($title)
{
	global $_FN,$show_section_title_bar;
	$opmod=FN_GetParam("opmod",$_GET,"flat");
	$unirecid=FN_GetParam("unirecid",$_GET,"flat");
	$l="&amp;opmod=$opmod&amp;unirecid=$unirecid";
	if ($show_section_title_bar == 1)
	{
		?>
		<table class="moduleTitle" border="0" cellpadding="0" cellspacing="0"
			   width="100%">
			<tbody>
				<tr>
					<td valign="top"><b><?php echo $title?></b></td>
					<td style="padding-top: 3px; padding-left: 5px;" align="right"
						nowrap="nowrap" valign="top"><a
							href="print.php?mod=<?php echo $_FN['mod'] . $l?>" class="utilsLink"> <img
								src="<?php echo $_FN['siteurl'];?>themes/<?php echo $_FN['theme']?>/images/print.png"
								alt="<?php echo FN_i18n("print")?>" style="vertical-align: middle;"
								border="0" height="13" width="13"></a> &nbsp;<a
							href="print.php?mod=<?php echo $_FN['mod'] . $l?>" onclick="window.open(this.href);return false;"
							class="utilsLink"><?php echo $title ?></a> &nbsp;<a href="<?php echo fn_rewritelink("index.php?mod=help")?>"
							class="utilsLink"><img
								src="<?php echo $_FN['siteurl'];?>themes/<?php echo $_FN['theme']?>/images/help0000.gif"
								alt="<?php echo FN_i18n("help")?>" style="vertical-align: middle;"
								border="0" height="13" width="13"></a>&nbsp;<a href="<?php echo fn_rewritelink("index.php?mod=help");?>"
																	   class="utilsLink"><?php echo FN_i18n("help")?></a></td>
				</tr>
			</tbody>
		</table>
		<?php
	}
	echo "<div>";
}
function FN_CloseSection($title="")
{

	echo "</div>";
	global $show_subsections_in_section;
	FN_SectionFooter();
	if ($show_subsections_in_section)
		FN_CreateSubmenu();
	//CloseTableTitle ();
}
/**
 * menu orizzontale in basso
 */
function my_create_menu_h()
{
	global $_FN,$sectiondefault;
	$ll="";
	if ($_FN ['lang'] != $_FN ['lang_default'])
		$ll="lang={$_FN['lang']}&amp;";

	$modlist=FN_GetSections("sections"); // return array width title - link
	$s="|";

	foreach ($modlist as $modl)
	{
		$link=$modl ['link'];
		$linkh=$mod ['id'];
		$title=$modl ['title'];
		$accesskey=$modl ['accesskey'];
		$s="|";
		echo "<a class=\"footerLink\" href=\"" . fn_rewritelink("index.php?{$ll}mod=$linkh") . "\"  accesskey=\"$accesskey\" >$title</a>  $s";
	}
}
function navbar()
{
	$tree=FN_GetSectionsTree();
	return FN_HtmlNavbar($tree);
}
/**
 *
 * @global array $_FN
 * @staticvar int $i
 * @param array $item
 * @param object $newsobject
 */
function FNNEWS_PrintNews_summary($item,$newsobject)
{
	global $_FN;
	static $i=0;
	echo "
<table class=\"tabDetailView\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
					<tbody>
						<tr>
							<td colspan=\"20\" class=\"listViewPaginationTdS1\" style=\"padding: 0px;\">
							<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
								<tbody>

									<tr>
										<td style=\"text-align: left;\" class=\"tabDetailViewDL\">&nbsp;<img src=\"{$_FN['siteurl']}themes/{$_FN['theme']}/images/news.png\" alt=\"News\">&nbsp;{$item['title']}</td>
									</tr>
								</tbody>
							</table>
							</td>
						</tr>

						<tr>

							<td align=\"left\" bgcolor=\"efefef\">
<table border=\"0\" width=\"100%\"><tbody><tr><td valign=\"top\"><div style=\"float: right;\"><img style=\"border: 0px none;\" alt=\"\" src=\"{$item['img_argument']}\"></div>
{$item['news_SUMMARY']}</td></tr></tbody></table>
		<table style=\"text-align: center;\" width=\"100%\">
			<tbody><tr>
				<td align=\"center\">";
	//footer ------>
	echo "<br /><div class=\"news_footer\" style=\"text-align:center\">",$item['txt_POSTED'] . $item['txt_DATE'] . " (" . $item['txt_VIEWS'] . ")";
	echo "<br />";
	echo "<img src=\"" . FN_FromTheme("images/read.png") . "\" alt=\"Read\" />&nbsp;<a accesskey=\"{$item['accesskey_READ']}\" href=\"" . $item['link_READ'] . "\" title=\"" . $item['txt_READ'] . " " . $item['news_TITLE'] . "\" ><b>" . $item['txt_READ'] . "</b></a>";
	echo " | <img  src=\"" . FN_FromTheme("images/print.png") . "\" alt=\"" . $item['txt_PRINT'] . "\" />&nbsp;<a accesskey=\"{$item['accesskey_PRINT']}\" href=\"{$item['link_PRINT']}" . "\" title=\"" . $item['txt_PRINT'] . " " . $item['news_TITLE'] . "\" >" . $item['txt_PRINT'] . "</a>";
	if ($newsobject->config['enablecomments'])
	{
		echo " | <img  src=\"" . FN_FromTheme("images/comment.png") . "\" alt=\"\"  />&nbsp;<a href=\"{$item['link_COMMENTS']}#newscomments\" >" . $item['txt_NUMCOMMENTS'] . "</a>";
	}
	echo $item['facebook_button_i_like'];
	echo "</div>";
	//footer ------<
	echo "		</td>
			</tr>
		</tbody></table></td>
						</tr>
					</tbody>

				</table><br />			


";
}
?>