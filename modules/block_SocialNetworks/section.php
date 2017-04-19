<?php
/**
 * @package Flatnux_blocks_SocialNetworks
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2003-2005
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
defined('_FNEXEC') or die('Restricted access');
global $_FN;
$id = FN_GetParam("id", $_GET, "html");
$op = FN_GetParam("op", $_GET, "html");
$urlpage = FN_RewriteLink("index.php?mod={$_FN['mod']}&amp;op=$op&amp;id=$id", "&", true);
$strsocial = "";
//facebook----------------------------------------------------->
$strsocial.="<div style=\"margin:5px;\" >
<a name=\"fb_share\" type=\"button_count\" share_url=\"";
$strsocial.= urlencode($urlpage);
$strsocial.= ' href="http://www.facebook.com/sharer.php">share</a>
<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
</div>';
//facebook-----------------------------------------------------<
//google + ---------------------------------------------------->
$strsocial.= "<div style=\"margin:5px;\" >
<!-- Place this tag where you want the share button to render. -->
<div class=\"g-plus\" data-action=\"share\" data-annotation=\"bubble\"></div>

<!-- Place this tag after the last share tag. -->
<script type=\"text/javascript\">
  window.___gcfg = {lang: '{$_FN['lang']}'};

  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>
</div>";
//google + ----------------------------------------------------<
  
//twitter------------------------------------------------------>
  $strsocial.="<div style=\"margin:5px;\" >";
  $strsocial.="
  <a href=\"https://twitter.com/share\" class=\"twitter-share-button\" data-text=\"$urlpage\" data-lang=\"{$_FN['lang']}\">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script> ";
  $strsocial.="</div>";
//twitter------------------------------------------------------<
$strsocial.= "";
echo $strsocial;