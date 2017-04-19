<?xml version="1.0" encoding="UTF-8"?>
<?php exit(0);?>
<tables>
	<field>
		<name>id</name>
		<primarykey>1</primarykey>
		<extra>autoincrement</extra>
		<frm_show>0</frm_show>
	</field>
	<field>
		<name>newsletters_id</name>
		<type>select</type>
		<frm_i18n>newsletter</frm_i18n>
		<frm_required>1</frm_required>
		<foreignkey>newsletter_newsletters</foreignkey>
		<fk_link_field>id</fk_link_field>
		<fk_show_field>title</fk_show_field>
		<frm_show>1</frm_show>
	</field>
	<field>
		<name>subject</name>
		<frm_it>Titolo</frm_it>
		<frm_en>Title</frm_en>
		<frm_required>1</frm_required>
	</field>
	<field>
		<name>body</name>
		<frm_it>Corpo messaggio</frm_it>
		<frm_en>Message body</frm_en>
		<frm_cols>80</frm_cols>
		<frm_rows>10</frm_rows>
		<type>text</type>
		<frm_type>html</frm_type>
	</field>
	<field>
		<name>date</name>
		<frm_it>Data</frm_it>
		<frm_en>Date</frm_en>
		<frm_type>datetime</frm_type>
		<frm_dateformat>y/mm/dd</frm_dateformat>
		<frm_show>1</frm_show>
	</field>
	<field>
		<name>status</name>
		<frm_it>Stato messaggio</frm_it>
		<frm_en>Message status</frm_en>
		<type>varchar</type>
		<frm_options>unsended,processing,sended</frm_options>
		<frm_options_it>non spedito,in fase di spedizione,spedito</frm_options_it>
		<frm_show>0</frm_show>
		<frm_type>select</frm_type>
	</field>
</tables>