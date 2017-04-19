<?php
#[it]Abilita invio email in html {true=SI,false=NO}
#[en]Enable html emails {true=SI,false=NO}
$config['htmlnewsletter'] = true;
#[it]Editor html{0=Disabilitato,include/htmleditors}
#[en]Editor html{0=Disable,include/htmleditors}
$config['htmleditornewsletter'] = "ckeditor4";
#[it]Tempo di attesa tra una email e un altra in milliseconds
#[en]Sleep time ms
$config['newsletter_sleep_time'] = 3000;
#[it]Tentativi massimi di invio email
#[en]Max retry
$config['newslettermaxretry']=2;
#[it]Gruppo di utenti abilitato a pubblicare le newsletter  {fn_groups}
#[en]Qualified group of users to publish the newsletter  {fn_groups}
#[i18n]qualified group of users to publish the newsletter  {fn_groups}
$config['group_newsletters'] = "newsletter";

?>