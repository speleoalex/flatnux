<?php
global $xmldb_mysqlhost,$xmldb_mysqldatabase,$xmldb_mysqlusername,$xmldb_mysqlpassword,$_FN_default_auth_method;


//display error
$_FN_display_errors = "on";
//max upload file size
$_FN_upload_max_filesize = "20M";
//xmlphp,sqlite3,sqlite,csv,serialize,mysql,mssql
$_FN_default_database_driver = "";
//
$_FN_default_auth_method = "local";

//specific options for the mysql driver:
$xmldb_mysqlhost = "localhost";
$xmldb_mysqldatabase = "fndatabase";
$xmldb_mysqlusername = "root";
$xmldb_mysqlpassword = "";

?>