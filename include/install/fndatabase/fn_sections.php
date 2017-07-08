<?xml version="1.0" encoding="UTF-8"?>
<?php exit(0);?>
<tables>
	<field>
		<name>id</name>
		<frm_required>1</frm_required>
		<primarykey>1</primarykey>
		<size>128</size>
		<frm_show>0</frm_show>
	</field>
	<field>
		<name>type</name>
		<frm_i18n>page type</frm_i18n>
		<frm_type>select</frm_type>
		<frm_show>1</frm_show>
		<foreignkey>fn_sectionstypes</foreignkey>
		<fk_link_field>name</fk_link_field>
		<fk_show_field>title</fk_show_field>
	</field>
	<field>
		<name>parent</name>
		<foreignkey>fn_sections</foreignkey>
		<fk_link_field>id</fk_link_field>
		<fk_show_field>id</fk_show_field>
	</field>
	<field>
		<name>position</name>
	</field>
	<field>
		<name>title</name>
		<frm_i18n>title</frm_i18n>
		<type>varchar</type>
		<frm_multilanguages>auto</frm_multilanguages>
	</field>
	<field>
		<name>description</name>
		<frm_i18n>description</frm_i18n>
		<type>varchar</type>
		<frm_multilanguages>auto</frm_multilanguages>
	</field>
	<field>
		<name>startdate</name>
		<frm_i18n>publication start date</frm_i18n>
		<frm_en>Publication start date</frm_en>
		<frm_it>Data inizio pubblicazione</frm_it>
		<frm_type>datetime</frm_type>
		<frm_dateformat>y-mm-dd</frm_dateformat>
	</field>
	<field>
		<name>enddate</name>
		<frm_i18n>Publication end date</frm_i18n>
		<frm_en>Publication end date</frm_en>
		<frm_it>Data fine pubblicazione</frm_it>
		<frm_type>datetime</frm_type>
		<frm_dateformat>y-mm-dd</frm_dateformat>
	</field>
	<field>
		<name>status</name>
		<frm_i18n>status</frm_i18n>
		<frm_en>Status</frm_en>
		<frm_it>Stato</frm_it>
        <frm_type>radio</frm_type>
        <frm_options>1,0</frm_options>
		<frm_options_i18n>published,not published</frm_options_i18n>
	</field>
	<field>
		<name>hidden</name>
		<frm_type>check</frm_type>
		<frm_i18n>section hidden</frm_i18n>
	</field>
	<field>
		<name>level</name>
		<frm_type>select</frm_type>
		<frm_i18n>minimum level for viewing</frm_i18n>
		<frm_options>0,1,2,3,4,5,6,7,8,9,10</frm_options>
	</field>
	<field>
		<name>accesskey</name>
		<frm_i18n>accesskey</frm_i18n>
		<type>varchar</type>
		<size>1</size>
		<frm_size>2</frm_size>
	</field>
	<field>
		<name>keywords</name>
		<frm_i18n>keywords</frm_i18n>
		<type>varchar</type>
	</field>
	<field>
		<name>sectionpath</name>
		<frm_type>varchar</frm_type>
		<frm_show>0</frm_show>
	</field>
	<field>
		<name>group_view</name>
		<foreignkey>fn_groups</foreignkey>
		<fk_link_field>groupname</fk_link_field>
		<fk_show_field>groupname</fk_show_field>
		<frm_type>multicheck</frm_type>
		<frm_i18n>groups for viewing</frm_i18n>
	</field>
	<field>
		<name>group_edit</name>
		<foreignkey>fn_groups</foreignkey>
		<fk_link_field>groupname</fk_link_field>
		<fk_show_field>groupname</fk_show_field>
		<frm_type>multicheck</frm_type>
		<frm_i18n>groups for editing</frm_i18n>
	</field>
	<field>
		<name>blocksmode</name>
        <frm_group>blocks</frm_group>
        <frm_group_i18n>blocks</frm_group_i18n>
        <frm_i18n>view blocks</frm_i18n>
        <frm_type>radio</frm_type>
        <frm_options>,show,hide</frm_options>
        <frm_options_i18n>view all,displays only selected,hide selected</frm_options_i18n>
	</field>
    <field>
        <name>blocks</name>
        <frm_i18n>selected blocks</frm_i18n>
        <foreignkey>fn_blocks</foreignkey>
        <fk_link_field>id</fk_link_field>
        <fk_show_field>title</fk_show_field>
        <frm_type>multicheck</frm_type>
        <frm_endgroup></frm_endgroup>
    </field>    
	<filename>sections</filename>
</tables>