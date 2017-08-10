<?php
/**
 *
 * @global array $_FN
 * @staticvar int $i
 * @param array $item
 * @param object $newsobject
 *    [unirecid] => 2
  [txtid] => asa
  [title] => asa
  [argument] => 1
  [status] => 1
  [summary] =>

  asasa


  [body] =>

  asasasa


  [photo1] =>
  [username] => speleoalex
  [tags] =>
  [date] => 2013-07-17 09:39:44
  [startdate] =>
  [enddate] =>
  [locktop] =>
  [guestnews] =>
  [idimport] =>
  [title_en] =>
  [summary_en] =>
  [body_en] =>
  [title_de] =>
  [title_es] =>
  [title_fr] =>
  [summary_de] =>
  [summary_es] =>
  [summary_fr] =>
  [body_de] =>
  [body_es] =>
  [body_fr] =>
  [op] =>
  [accesskey_READ] => 1
  [news_USER] => speleoalex
  [txt_READ] => Leggi tutto
  [link_READ] => news-asa.html
  [accesskey_PRINT] => s
  [txt_PRINT] => Stampa
  [link_PRINT] => print.php?mod=news&op=asa
  [link_MODIFY] => news.html?opt=fnc_ccnf_section_news&mode=edit&pk___xdb_news=2&op___xdb_news=insnew
  [txt_MODIFY] => Modifica
  [socialnetwork_buttons] =>
  [facebook_button_i_like] =>
  [txt_POSTED] => Postato
  [txt_DATE] => 17 Luglio 2013 - 09:39:44
  [news_SUMMARY] =>

  asasa

  [news_BODY] =>

  asasasa

  [news_TITLE] => asa
  [news_VIEWS] => 13
  [txt_VIEWS] => Letto 13 volte
  [img_argument] => http://localhost/speleoalex/flatnux/misc/fndatabase/news_arguments/1/icon/news.png
  [argument_values] => Array
  (
  [unirecid] => 1
  [title] => News
  [icon] => news.png
  [idimport] =>
  [title_it] =>
  [title_en] =>
  [title_de] =>
  [title_es] =>
  [title_fr] =>
  )

  [img_argument_thumb] => http://localhost/speleoalex/flatnux/misc/fndatabase/news_arguments/1/icon/thumbs/news.png.jpg
  [title_argument] => News
  [img_news] =>
  [img_news_thumb] =>
  [COMMENTS] => Array
  (
  [0] => Array
  (
  [unirecid] => 3
  [insert] => 2013-07-22 00:00:00
  [username] => speleoalex
  [comment] => qweqeqeq
  [idimport] =>
  [unirecidrecord] => 2
  [txt_FROM] => Sconosciuto
  [html_USER] => speleoalex
  [img_USER] => images/user.png
  [html_img_USER] =>
  [txt_DATE] => Data
  [html_DATE] => Lunedì 22 Luglio 2013 - 00:00
  [html_COMMENT] => qweqeqeq
  [html_DELCOMMENT] => Elimina
  )

  [1] => Array
  (
  [unirecid] => 4
  [insert] => 2013-07-22 00:00:00
  [username] => speleoalex
  [comment] => wwwwwwwwwwwwwww
  [idimport] =>
  [unirecidrecord] => 2
  [txt_FROM] => Sconosciuto
  [html_USER] => speleoalex
  [img_USER] => images/user.png
  [html_img_USER] =>
  [txt_DATE] => Data
  [html_DATE] => Lunedì 22 Luglio 2013 - 00:00
  [html_COMMENT] => wwwwwwwwwwwwwww
  [html_DELCOMMENT] => Elimina
  )

  [2] => Array
  (
  [unirecid] => 5
  [insert] => 2013-07-22 00:00:00
  [username] => speleoalex
  [comment] => zz
  [idimport] =>
  [unirecidrecord] => 2
  [txt_FROM] => Sconosciuto
  [html_USER] => speleoalex
  [img_USER] => images/user.png
  [html_img_USER] =>
  [txt_DATE] => Data
  [html_DATE] => Lunedì 22 Luglio 2013 - 00:00
  [html_COMMENT] => zz
  [html_DELCOMMENT] => Elimina
  )

  [3] => Array
  (
  [unirecid] => 6
  [insert] => 2013-07-22 00:00:00
  [username] => speleoalex
  [comment] => gggggggggg
  [idimport] =>
  [unirecidrecord] => 2
  [txt_FROM] => Sconosciuto
  [html_USER] => speleoalex
  [img_USER] => images/user.png
  [html_img_USER] =>
  [txt_DATE] => Data
  [html_DATE] => Lunedì 22 Luglio 2013 - 00:00
  [html_COMMENT] => gggggggggg
  [html_DELCOMMENT] => Elimina
  )

  )

  [txt_NUMCOMMENTS] => 4 Commenti
  [link_COMMENTS] => news-asa.html?mode=comment
  [txt_COMMENTS] => Commenti
  [accesskey_COMMENTS] => e
  [txt_WRITECOMMENT] => Aggiungi commento
  [accesskey_WRITECOMMENT] => u
  [link_WRITECOMMENT] => news-asa.html?mode=comment
  [txt_PDF] =>
  [link_PDF] => pdf.php?mod=news&op=asa
  [txt_SENDEMAIL] => Invia
  [link_SENDEMAIL] => mailto:?body=http%3A%2F%2Flocalhost%2Fspeleoalex%2Fflatnux%2Fnews-asa.html
  [news_CATEGORY] => News
  [txt_TAGS] =>
 */
function FNNEWS_PrintNews_summary($item,$newsobject)
{
	static $i = 0;
	$_MODIFY = "";
	$_IMAGENEWS = "";
	$_IMAGEARGUMENT = "";
	$_COMMENTS = "";
	$_TAGS = "";
	$_NEWS_USER = "";
	$_MAILTO = "mailto:?body={$item['link_READ']}";
	if (!empty($item['news_USER']))
	{
		$_NEWS_USER = "<a title=\"Posts by {$item['news_USER']}\" href=\"#\">{$item['news_USER']}</a>";
	}

	if (!empty($item['txt_TAGS']))
	{
		$_TAGS = "| <span class=\"art-posttagicon\">{$item['txt_TAGS']}</span>";
	}

	if (!empty($item['txt_NUMCOMMENTS']))
	{
		$_COMMENTS = "| <span class=\"art-postcommentsicon\"><a title=\"Comments\" href=\"{$item['link_COMMENTS']}\">{$item['txt_NUMCOMMENTS']} »</a></span>";
	}

	if ($item['img_news_thumb'] != "")
	{
		$_IMAGENEWS = "<img id=\"preview-image\" alt=\"\" style=\"border:0px;\" src=\"{$item['img_news_thumb']}\" />";
	}
	if ($item['img_argument'] != "")
	{
		$_IMAGEARGUMENT = "<img  class=\"preview-cms-logo\" yalt=\"\" style=\"border:0px;\" src=\"{$item['img_argument']}\" />";
	}
	if ($item['link_MODIFY'])
		$_MODIFY = " | <span class=\"art-postediticon\"><a href=\"{$item['link_MODIFY']}\" title=\"{$item['txt_MODIFY']}\" >{$item['txt_MODIFY']}</a></span>";
	$_READBUTTON = "<p class=\"readallbutton\">
		<span class=\"art-button-wrapper\">
			<span class=\"art-button-l\"> </span>
			<span class=\"art-button-r\"> </span>
			<a class=\"readon art-button\" accesskey=\"{$item['accesskey_READ']}\" href=\"{$item['link_READ']}\">".FN_Translate("read all")."</a>
		</span>
  </p>";
	$htmlout = "<article class=\"art-post art-article\">
                                <h2 class=\"art-postheader\"><a accesskey=\"{$item['accesskey_READ']}\" href=\"{$item['link_READ']}\">{$item['title']}</a>
                                </h2>
                                                <div class=\"art-postheadericons art-metadata-icons\"><span class=\"art-postdateicon\">{$item['txt_DATE']}</span> | <span class=\"art-postauthoricon\"> $_NEWS_USER</span>  | <span class=\"art-postpdficon\" onclick=\"window.open('{$item['link_PDF']}');\"></span> | <span class=\"art-postprinticon\" onclick=\"window.open('{$item['link_PRINT']}');\"></span> | <span class=\"art-postemailicon\" onclick=\"window.open('$_MAILTO');\"></span> $_MODIFY</div>
                <div class=\"art-postcontent art-postcontent-0 clearfix\"> {$_IMAGENEWS}{$_IMAGEARGUMENT}{$item['news_SUMMARY']}{$_READBUTTON}{$item['facebook_button_i_like']} </div>
                                <div class=\"art-postfootericons art-metadata-icons\"> <span class=\"art-postcategoryicon\">{$item['news_CATEGORY']}</span> $_TAGS $_COMMENTS </div>
                

</article>";
	echo $htmlout;
	$i++;
}

function FN_HtmlOpenTableTitle($title="")
{
	$htmlout = "<article class=\"art-post art-article\">
                                <div class=\"art-postmetadataheader\"><h2 class=\"art-postheader\">$title</h2></div>
		<div class=\"art-postcontent clearfix\">
		";
	return $htmlout;
}

function FN_HtmlCloseTableTitle($title="")
{
	return "</div></article>";
}
function FN_HtmlOpenTable()
{
	$htmlout = "<div class=\"art-post art-article\">
		<div class=\"art-postcontent clearfix\">
		";
	
	return $htmlout;
}

function FN_HtmlCloseTable($title="")
{
	return "</div></div>";
}


function FN_HtmlOpenSection($title=false)
{
	global $_FN;
	$htmlout = "";
	if (!empty($_FN['not_open_section']) || !empty($_FN['sectionvalues']['type']) && $_FN['sectionvalues']['type'] == "news")
	{
		return "";
	}
	if ($title !== false){
		$htmlout .= "<div class=\"art-postmetadataheader\"><h2 class=\"art-postheader\">$title</h2></div>
		";
	}
	$htmlout .= "<div class=\"art-postcontent clearfix\">";
	return $htmlout;
}

function FN_HtmlCloseSection($title=false)
{
	global $_FN;
	if (!empty($_FN['not_open_section']) || !empty($_FN['sectionvalues']['type']) && $_FN['sectionvalues']['type'] == "news")
	{
		return "";
	}
	return "</div>";
}

function FNNEWS_HtmlRss($rss)
{

	return "<a href=\"{$rss['path']}\" class=\"art-rss-tag-icon\" ></a>";
}

?>
