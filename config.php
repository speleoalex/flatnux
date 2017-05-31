<?php
/**
 * not modify this file
 * all variables are overwritten by values in the database
 * in misc/fndatabase/fn_settings/settings.php
 * this file contains only the default values
 */
#[i18n]site name
$_FN['sitename'] = "Flatnux-NEXT";
#[i18n]site title
$_FN['site_title'] = "Flatnux CMS";
#[it]Sottotitolo del sito
#[en]Site subtitle
$_FN['site_subtitle'] = "www.flatnux.org";
#[i18n]Keywords
$_FN['keywords']="";
#[it]Lista lingue del sito {+languages/*}
#[en]Contents languages {+languages/*}
$_FN['languages'] = "en,it,fr";
#[it]Tema preferito{./themes}
#[en]Default theme{./themes}
$_FN['theme'] = "default";
#[it]Tema preferito pannello di controllo{./controlcenter/themes}
#[en]Default control center theme{./controlcenter/themes}
$_FN['controlcenter_theme'] = "classic";
#[it]Abilita il visitatore a cambiare il tema {1=SI,0=NO}
#[en]Enable theme switch {1=YES,0=NO}
$_FN['switchtheme'] = 1;
#[it]Url del sito (vuoto per autorilevamento)
#[en]Site url (empty = autodetect)
$_FN['siteurl']="";
#[it]Indirizzo email del sito 
#[en]Site email address
$_FN['site_email_address'] = "nobody@site_email_address.set"; //{required}
#[it]Indirizzo email per le notifiche
#[en]Logs email address
$_FN['log_email_address'] = ""; //{required}
#[it]Comprimi pagina con gzip {1=SI,0=NO}
#[en]Compress pages with gzip {1=YES,0=NO}
$_FN['enable_compress_gzip'] = 0;
#[it]Pagina predefinita{0=Disabilitato,./sections}
#[en]Default section{0=Disabilitato,./sections}
$_FN['home_section'] = "home";
#[it]Cartella scrivibile che contiene i dati (default=misc)
#[en]Data folder (default=misc)
$_FN['datadir'] = "misc";
#[it]Fuso orario
#[en]time difference
$_FN['jet_lag'] = 0;
#[it]Visualizza gli accesskey accanto al link {1=SI,0=NO}
#[en]Show access key {1=YES,0=NO}
$_FN['showaccesskey']=0;
#[it]Invia email di log all' amministratore {1=SI,0=NO}
#[en]Enable LOG email to administrator{1=YES,0=NO}
$_FN['enable_log_email']=0;
#[it]Abilita mod_rewrite di Apache {1=SI,0=NO,2=FORZA}
#[en]Enable Apache mod_rewrite {1=YES,0=NO,2=FORCE}
$_FN['enable_mod_rewrite']=1;
#[it]Metodo links{{0=Disabilitato,./include/mod_rewrite/}}
#[en]Links mode {{0=Disabilitato,./include/mod_rewrite/}}
$_FN['links_mode']="html";
#[it]Abilita registrazione utenti{1=SI,0=NO}
#[en]Enable user registration {1=YES,0=NO}
$_FN['enable_registration']=1;
#[it]Utilizza l'indirizzo email come nome utente {1=SI,0=NO}
$_FN['username_is_email']=0;
#[it]Registrazione tramite email {1=SI,0=NO}
#[en]Registration through email {1=YES,0=NO}
$_FN['registration_by_email']=1;
#[i18n]allow users to stay connected {1=YES,0=NO}
$_FN['remember_login'] = 1;
#[i18n]Enable captcha in login form {1=YES,0=NO}
$_FN['enable_captcha']=0;
#[it]Editor html{0=Disabilitato,include/htmleditors}
#[en]Editor html{0=Disable,include/htmleditors}
$_FN['htmleditor']="ckeditor4";
#[i18n]Allows online administration {1=YES,0=NO}
$_FN['enable_online_administration']=1;
#[i18n]credits
$_FN['credits']="Powered by <a href='http://www.flatnux.org'>Flatnux</a>";
#[i18n]site in maintenance {1=yes,0=no}
$_FN['maintenance'] = 0;
#[i18n]url update 
$_FN['url_update'] = 'http://www.flatnux.altervista.org/updates/FLATNUXSTABLE';
#[i18n]enable cache {1=yes,0=no}
$_FN['use_cache'] = 0;
#[i18n]Timezone {""=get from server,timezone_identifiers_list()}
$_FN['timezone'] = "";



?>