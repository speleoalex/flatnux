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
        <name>title</name>
        <frm_it>Titolo</frm_it>
        <frm_en>Title</frm_en>
        <type>string</type>
        <frm_required>1</frm_required>
    </field>
    <field>
        <name>description</name>
        <frm_cols>auto</frm_cols>
        <frm_rows>auto</frm_rows>
        <frm_help_i18n>Insert description here</frm_help_i18n>
        <frm_i18n>description</frm_i18n>
        <type>text</type>
        <frm_multilanguages>auto</frm_multilanguages>
    </field>
    <field>
        <name>photo1</name>
        <frm_i18n>image</frm_i18n>
        <frm_showinlist>0</frm_showinlist>
        <view_hiddentitle>1</view_hiddentitle>
        <thumb_listheight>64</thumb_listheight>
        <type>image</type>
        <thumbsize>250</thumbsize>
        <gridicononexists>images/mime/image.png</gridicononexists>
    </field>
    <field>
        <name>file</name>
        <frm_i18n>file</frm_i18n>
        <type>file</type>
        <gridicononexists>images/download.png</gridicononexists>
    </field>
    <!--  system fields -->
    <field>
        <name>recordinsert</name>
        <frm_i18n>insertion date</frm_i18n>
        <type>string</type>
        <frm_show>0</frm_show>ù
        <view_show>1</view_show>
    </field>
    <field>
        <name>recordupdate</name>
        <frm_i18n>date updated</frm_i18n>
        <type>string</type>
        <frm_show>0</frm_show>
        <view_show>1</view_show>
    </field>
    <field>
        <name>view</name>
        <frm_i18n>number of views</frm_i18n>
        <type>string</type>
        <foreignkey>fn_files_stat</foreignkey>
        <fk_link_field>unirecid</fk_link_field>
        <fk_show_field>view</fk_show_field>
        <frm_show>0</frm_show>
        <frm_showinlist>1</frm_showinlist>
    </field>
    <field>
        <name>groupview</name>
        <frm_i18n>limits the display of the content in these groups</frm_i18n>        
        <foreignkey>fn_groups</foreignkey>
        <fk_link_field>groupname</fk_link_field>
        <fk_show_field>groupname</fk_show_field>        
        <frm_type>multicheck</frm_type>
    </field>    
    <field>
        <name>username</name>
        <type>string</type>
        <frm_show>0</frm_show>
    </field>
</tables>
