<?php
/**
 * @package Flatnux_theme_Sugar
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
defined('_FNEXEC') or die('Restricted access');
global $_FN;
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">";
echo "\n<html lang=\"{$_FN['lang']}\">
<head>
<link href=\"themes/{$_FN['theme']}/navigati.css\" rel=\"stylesheet\" type=\"text/css\" >
";
echo "<script type=\"text/javascript\" src=\"themes/{$_FN['theme']}/sugar_30.js\"></script>
<script type=\"text/javascript\" src=\"themes/{$_FN['theme']}/cookie00.js\"></script>
";
echo "
<script type=\"text/javascript\" language=\"javascript\" src=\"themes/{$_FN['theme']}/menu0000.js\"></script>
<script type=\"text/javascript\" language=\"javascript\" src=\"themes/{$_FN['theme']}/cookie01.js\"></script>
";
echo "<script type=\"text/javascript\" language=\"javascript\">
function hideLeftCol(id){

	if(this.document.getElementById( id).style.display=='none'){
		this.document.getElementById( id).style.display='inline';

		Set_Cookie('showLeftCol','true',30,'/','','');
		var show = Get_Cookie('showLeftCol');
		document['HideHandle'].src = 'themes/{$_FN['theme']}/images/hide.gif';
	}else{
		this.document.getElementById(  id).style.display='none';

		Set_Cookie('showLeftCol','false',30,'/','','');
		var show = Get_Cookie('showLeftCol');
		document['HideHandle'].src = 'themes/{$_FN['theme']}/images/show.gif';

	}
}

function showSubMenu(id){
	if(this.document.getElementById( id).style.display=='none'){
		tbButtonMouseOver('HideHandle',122,'',10);
	}
}
</script>

";
?>
<!--[if lt IE 7]>
<script language="JavaScript">
function correctPNG() // correctly handle PNG transparency in Win IE 5.5 & 6.
{
   try{
   var arVersion = navigator.appVersion.split("MSIE")
   var version = parseFloat(arVersion[1])
   var dil;
   if ((version >= 5.5) && (document.body.filters))
   {
   	  dil=document.images.length;
   
      for(var i=0; i<dil ; i++)
      {
         var img = document.images[i]
         var imgName = img.src.toUpperCase()
         if (imgName.substring(imgName.length-3, imgName.length) == "PNG")
         {
			
            var imgID = (img.id) ? "id='" + img.id + "' " : ""
            var imgClass = (img.className) ? "class='" + img.className + "' " : ""
            var imgTitle = (img.title) ? "title='" + img.title + "' " : "title='" + img.alt + "' "
            var imgStyle = "display:inline-block;" + img.style.cssText
            if (img.align == "left") imgStyle = "float:left;" + imgStyle
            if (img.align == "right") imgStyle = "float:right;" + imgStyle
            if (img.parentElement.href) imgStyle = "cursor:hand;" + imgStyle
            var strNewHTML = "<span " + imgID + imgClass + imgTitle
            + " style=\"" + "width:" + img.width + "px; height:" + img.height + "px;" + imgStyle + ";"
            + "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader"
            + "(src=\'" + img.src + "\', sizingMethod='scale');\"></span>"
            img.outerHTML = strNewHTML
            i = i-1
         }
		 
		 
      }
   }
    }catch(e)
    {
		
   }
   
}
window.attachEvent("onload", correctPNG);
</script>
<![endif]-->
<?php
echo FN_HtmlHeader();
echo "</head>";
?>
<body onMouseOut="closeMenus();">
	<div id="myaccountMenu" class="menu"></div>
	<div id="employeesMenu" class="menu"></div>
	<div id="adminMenu" class="menu"></div>
	<div id="usersMenu" class="menu"></div>
	<div id="aboutMenu" class="menu"></div>
	<?php
	echo createsubmenu1();
	?>
	<div id="HideMenu" class="subDmenu">
		<table cellpadding="0" cellspacing="0" border="0" width="180"
			   class="leftColumnModuleHead" onMouseOver="hiliteItem(this,'no');">
			<tr>
				<th width="5" valign="top"><img
						src="themes/<?php echo $_FN ['theme']?>/images/moduleTb.gif"
						alt="Scorciatoie" width="5" height="23" border="0"></th>
				<th width="100%" class="leftColumnModuleName">Menu</th>
				<th width="7" valign="top"><img
						src="themes/<?php echo $_FN ['theme']?>/images/moduleTc.gif"
						alt="Scorciatoie" width="7" height="23" border="0"></th>
			</tr>
		</table>
		<table cellpadding="0" cellspacing="0" border="0" class="subMenu"
			   width="180" onMouseOver="hiliteItem(this,'no');">
				   <?php
				   MyCreateMenu2();
				   ?>
		</table>
	</div>

	<table width="100%" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td colspan="3" style="background-image:url(themes/<?php echo $_FN ['theme']?>/images/header_bg.gif); background-repeat: repeat-x;">
				<table cellspacing="0" cellpadding="0" border="0" width="100%">
					<tr>
						<td width="80%" height="40" rowspan="2" valign="top"><img
								src="themes/<?php echo $_FN ['theme']?>/images/company_.png"
								width="212" height="40" alt="Company Logo" border="0"
								style="margin-left: 12px; margin-bottom: 3px;">
								<?php
								$listlanguages=$_FN['listlanguages'];
								if (count($listlanguages) > 1 && !file_exists("blocks/sx/00_Lingua.php"))
								{
									foreach ($listlanguages as $l)
									{
										$ll="";
										$a=FN_GetAccessKey($ll,"lang=$l");
										$ll=$l;
										$ak="";
										if ($_FN ['showaccesskey'] == 1)
											$ak="[$a]";
										$getvars="";
										foreach ($_GET as $key=>$value)
										{
											if ($key !== "mod" && $key != "lang")
											{
												$getvars .= "&amp;" . htmlspecialchars($key) . "=" . htmlentities(( FN_StripPostSlashes($value)),ENT_QUOTES,$_FN['charset_page']);
												//$getvars .= "&$key=".strippostpslashes($value);
											}
										}
										$image=FN_FromTheme("images/flags/$l.png");
										$link=FN_RewriteLink("index.php?mod={$_FN['mod']}&amp;lang=$l&amp;$getvars");

										//$link = htmlspecialchars($link);
										echo "\n<a accesskey=\"$a\" href=\"$link\" $ak><img style=\"vertical-align: 15px;border:0px;height:10px;\"  src=\"$image\" alt=\"$l\" /></a>";
									}
								}
								?>
						</td>
						<td rowspan="2" align="right" valign="top" style="border: none;"><img
								src="themes/<?php echo $_FN ['theme']?>/images/myAreaSe.gif"
								width="20" height="20" alt="My Area Links:" border="0"></td>
						<td style="height: 20px" align="right" valign="middle" nowrap
							class="myAreaBg">
								<?php
								if ($_FN ['user'] !== "")
								{
								?>
								<a href="<?php echo FN_RewriteLink("index.php?mod=login&op=modprof")?>" class="myAreaLink"><?php echo FN_Translate("profile settings");?></a>&nbsp;|&nbsp;
								<a href="<?php echo FN_RewriteLink("index.php?mod={$_FN['mod']}&fnlogin=logout")?>" class="myAreaLink"><?php echo FN_Translate("logout");?></a>&nbsp;|&nbsp;
								<?php
							}
							else
							{
								?>
								<a href="<?php echo FN_RewriteLink("index.php?mod=login")?>" class="myAreaLink"><?php echo FN_Translate("login");?></a>&nbsp;|&nbsp;
								<?php
							}
							?>
							<a href="<?php echo FN_RewriteLink("index.php?mod=search")?>" class="myAreaLink" ><?php echo FN_Translate("search")?></a>&nbsp;|&nbsp;
							<a href="<?php echo FN_RewriteLink("index.php?mod=sitemap")?>" class="myAreaLink"><?php echo FN_Translate("site map");?></a></td>
					</tr>
					<tr>
						<td align="right" style="height: 20px"><img
								src="themes/<?php echo $_FN ['theme']?>/images/sugarsal.png"
								alt="Sugar Suite" width="140" height="15" border="0"
								style="margin-top: 6px; margin-right: 6px;"></td>
					</tr>
					<tr>
						<td colspan="3" ><table style="background-color:transparent" cellspacing="0" cellpadding="0" border="0" width="100%">
								<tr>
									<td style="padding-left:7px; background-image :url(themes/<?php echo $_FN ['theme']?>/images/emptyTabSpace.gif);">&nbsp;</td>
									<?php
									MyCreateMenuH();
									$st="";
									if (!FN_GetSections($_FN['mod']))
									{
										$st="style=\"display:none\"";
									}
									?>
									<td>
										<table <?php echo $st?> cellspacing="0" cellpadding="0" border="0" width="100%">
											<tr style="height: 25px">
												<td valign="bottom" style="background-image : url(themes/<?php echo $_FN ['theme']?>/images/emptyTabSpace.gif);"><img
														src='themes/<?php echo $_FN ['theme']?>/images/more0000.gif'
														alt='' id='MoreHandle'
														style='vertical-align: middle; margin-left: 2px; cursor: pointer; cursor: pointer; vertical-align: middle;'
														onmouseover='tbButtonMouseOver(this.id,66,"",0);'></td>
											</tr>
										</table>
									</td>
									<td width="100%" style="background-image : url(themes/<?php echo $_FN ['theme']?>/images/emptyTabSpace.gif);"><img
											src="themes/<?php echo $_FN ['theme']?>/images/blank000.gif"
											width="1" height="1" border="0" alt=""></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr style="height: 20px">
						<td class="subTabBar" colspan="3">
							<form name='UnifiedSearch' action="<?php FN_RewriteLink("?mod=search");?>" method="get">
								<table width="100%" cellspacing="0" cellpadding="0" border="0"
									   style="height: 20px">
									<tr>
										<td id="welcome" class="welcome" width="100%"><?php echo $_FN ['user']?></td>
										<td class="welcome" style="padding: 0px" align="right" width="20"><img
												src="themes/<?php echo $_FN ['theme']?>/images/searchSe.gif"
												width="20" height="20" border="0" alt="<?php echo FN_Translate("search")?>"></td>
										<td class="search" style="padding: 0px" align="right">&nbsp;<b><nobr><?php echo FN_Translate("search")?></nobr></b></td>
										<td class="search" nowrap>&nbsp; <input type="hidden" name="mod"
																				value="search"> <input type="text" class="searchField"
																				name="q" id="query_string" size="14" value="">&nbsp;<input
																				type="image" value="<?php echo FN_Translate("search")?>"
																				src="themes/<?php echo $_FN ['theme']?>/images/searchBu.gif"
																				alt="" align="top" style="width: 25px; height: 17px;"
																				class="searchButton"></td>
									</tr>
								</table>
							</form>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr style="height: 20px">
			<td class="lastView" nowrap colspan="3"><b><?php echo FN_Translate("position")?>:&nbsp;&nbsp;</b>
				<?php
				echo navbar();
				?>
			</td>
		</tr>
		<tr style="height: 11px">
			<td colspan="3" style="padding-left: 10px;"></td>
		</tr>
		<tr>
			<td colspan="3">
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr>
						<td valign="top" style="width: 10px;"><img
								style="cursor: pointer; " id="HideHandle"
								name="HideHandle"
								src="themes/<?php echo $_FN ['theme']?>/images/hide.gif" alt="h"
								onclick='hideLeftCol("leftCol");closeMenus();'
								onmouseover="showSubMenu('leftCol')"></td>
						<td id="left" valign="top" style="width: 160px;">
							<div style="display: none;" id="leftCol">
								<table cellpadding="0" cellspacing="0" border="0" width="160">
									<tr>
										<td style="padding-right: 10px;">


											<?php
											create_menu();
											echo FN_HtmlBlocks("left");
											echo FN_HtmlBlocks("right");
											?>

											<img src="themes/<?php echo $_FN ['theme']?>/images/blank001.gif" alt=""
												 width="160" height="1" border="0"></td>
									</tr>
								</table>
							</div>
						</td>
						<td style="padding-right: 10px; vertical-align: top; width: 100%;"
							id="flopt">


							<?php
							echo FN_HtmlBlocks("top");
							if ($_FN['mod'] != $_FN['home_section'])
							{
								FN_OpenSection($_FN['sectionvalues']['title']);
								echo FN_HtmlSection();
								FN_CloseSection();
							}
							else
							{

								echo FN_HtmlSection();
							}
							echo "<br />";
							echo FN_HtmlBlocks("bottom");
							?>
							<!--end body panes--></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="3" class="aboveFooter"><img alt=""
													 src="themes/<?php echo $_FN['theme']?>/images/blank000.gif" width="1"
													 height="5" border="0"></td>
		</tr>
		<tr>
			<td colspan="3" align="center" class="footer">
				<?php echo my_create_menu_h();?>
		</tr>
	</table>
	<div style="text-align:center"><small>
			<a href="http://flatnux.sugarforge.org" onclick="window.open(this.href);return false;"><img border="0" alt="" src="themes/<?php echo $_FN['theme']?>/images/poweredb.png" /></a>
			<br />
			<small>
				<?php
				echo FN_HtmlCredits();
				echo "<br />Page generated in " . FN_GetExecuteTimer() . " seconds. "
				?>
			</small>
	</div>
</body>
</html>