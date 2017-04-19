<?php
/**
 * @package Flatnux_theme_base
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */

/****   FONTS   ******/

#Font {Arial=Arial,Helvetica=Helvetica,Times New Roman=Times New Roman,Times=Times,Courier=Courier,Courier New=Courier New,Palatino=Palatino,Garamond=Garamond,Bookman=Bookman,Avant Garde=Avant Garde,Verdana=Verdana,Georgia=Georgia,Comic Sans MS=Comic Sans MS,Trebuchet MS=Trebuchet MS,Arial Black=Arial Black,Impact=Impact}
$config['font'] = "Verdana"; //{fonts}
#Background image {=Disabilitato,./themes/$theme/backgrounds/*.*}
$config['background_image'] = "";
$config['background_color_body'] = "#dddddd"; //{color}
$config['border_size_site']="1px"; 
$config['border_color_site'] = "#101010"; //{color}

/****   LAYOUT   ******/

$config['page_margin'] = "10px";
$config['padding'] = "5px";
$config['site_fontsize'] = "12px";
$config['width_site'] = "900px";
$config['width_left'] = "180px";
$config['width_right'] = "180px";

#show blocks right {0=no,1=yes}
$config['show_blocks_right']="1";
#show blocks left {0=no,1=yes}
$config['show_blocks_left']="1";
#show blocks top {0=no,1=yes}
$config['show_blocks_top']="1";
#show blocks bottom {0=no,1=yes}
$config['show_blocks_bottom']="1"; 

/****   HEADER   ******/

$config['header_text_color'] = "#000000";//{color}
$config['background_color_header'] = "#ffffff"; //{color}
$config['header_text_align'] = "left";//{center=center,left=left,right=right,justify,start=start,end=end}

/****   TOP MENU   ******/

$config['topmenu_text_color'] = "#000000";//{color}
$config['topmenu_background_color'] = "#297ddd"; //{color}
$config['topmenu_text_align'] = "right";//{center=center,left=left,right=right,justify,start=start,end=end}
$config['topmenu_link_color']="#ffffff"; //{color}

/****   TOP   ******/

$config['top_background_color'] = "#ffffff"; //{color}
$config['top_separator_color'] = "#ffffff"; //{color}

/****   LEFT   ******/

$config['left_background_color'] = "#ffffff"; //{color}
$config['left_separator_color'] = "#ffffff"; //{color}

/****   RIGHT   ******/

$config['right_background_color'] = "#ffffff"; //{color}
$config['right_separator_color'] = "#ffffff"; //{color}

/****   CENTER PAGE  ******/

$config['page_text_color'] = "#101010"; //{color}
$config['page_background_color'] = "#ffffff"; //{color}
$config['page_link_color']="#297ddd"; //{color}
$config['page_text_align'] = "justify";//{center=center,left=left,right=right,justify,start=start,end=end}

/****   BOTTOM   ******/

$config['bottom_background_color'] = "#ffffff"; //{color}
$config['bottom_separator_color'] = "#ffffff"; //{color}


/****   BLOCK   ******/
$config['block_border_color'] = "#0170ba"; //{color}
$config['block_border_size'] = "1px"; //{color}
$config['block_header_background_color'] = "#0170ba"; //{color}
$config['block_header_text_color'] = "#ffffff"; //{color}
$config['block_body_background_color'] = "#f0f0f0"; //{color}
$config['block_body_text_color'] = "#000000"; //{color}
$config['block_link_text_color'] = "#329c0f"; //{color}
$config['block_text_align'] = "left";//{center=center,left=left,right=right,justify,start=start,end=end}

/****   FOOTER   ******/

$config['footer_text_color'] = "#ffffff"; //{color}
$config['footer_background_color'] = "#000000"; //{color}
$config['footer_link_color'] = "#329c0f"; //{color}
$config['footer_text_align'] = "center";//{center=center,left=left,right=right,justify,start=start,end=end}
$config['footer_separator_color'] = "#080808"; //{color}

?>