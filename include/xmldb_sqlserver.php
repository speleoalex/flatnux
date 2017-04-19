<?php
/**
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2003-2014
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @package xmldb
 *
 */
/**
 * xmldb_sqlite.php created on 13/feb/2014
 * driver sqlite per xmldb
 * permette di inserire i dati in una tabella sqlite
 * il descrittore della tabella deve contenere:
 *
 * <driver>sqlite</driver>
 * <host>sqliteserverhost</host>
 * <user>sqliteusername</user>
 * <password>sqlitepassword</password>
 *
 *
 *
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 */
ini_set('mssql.textlimit','65536');
ini_set('mssql.textsize','65536');

class XMLTable_sqlserver
{

    function __construct(& $xmltable,$params=false)
    {
        $this->xmltable=&$xmltable;
        $this->tablename=& $xmltable->tablename;
        $this->databasename=& $xmltable->databasename;
        $this->fields=& $xmltable->fields;
        $this->path=& $xmltable->path;
        $this->numrecords=& $xmltable->numrecords;
        $this->primarykey=&$xmltable->primarykey;
        $this->xmldescriptor=&$xmltable->xmldescriptor;
        $this->sqlfields=array();
        $this->nullfields=false;
        if (is_array($params))
        {
            foreach($params as $k=> $v)
            {
                $this->$k=$v;
            }
        }
        $path=$this->path;
        $databasename=$this->databasename;
        $this->sqldatabasename=$this->databasename;
        $this->sqltablename=$this->tablename;
        //------------------------sql table name------------------------------->
        $sqltablename=get_xml_single_element("sqltablename",$this->xmldescriptor);
        if ($sqltablename)
        {
            $this->sqltablename=$sqltablename;
        }
        //------------------------sql table name-------------------------------<
        //------------------------db name-------------------------------------->
        global $xmldb_mssqldatabase,$xmldb_mssqlusername,$xmldb_mssqlpassword,$xmldb_mssqlhost,$xmldb_mssqlport;
        //database name
        if (!empty($xmldb_mssqldatabase))
        {
            $this->sqldatabasename=$xmldb_mssqldatabase;
        }
        else
        {
            $element=get_xml_single_element("database",$this->xmldescriptor);
            if ($element == "")
            {
                $this->sqldatabasename=$this->databasename;
            }
            else
            {
                $this->sqldatabasename=$element;
            }
        }
        //host
        if (!empty($xmldb_mssqlhost))
        {
            $this->sqlhost=$xmldb_mssqlhost;
        }
        else
        {
            $element=get_xml_single_element("host",$this->xmldescriptor);
            if ($element == "")
            {
                $this->sqlhost="localhost";
            }
            else
            {
                $this->sqlhost=$element;
            }
        }
        //port
        if (!empty($xmldb_mssqlport))
        {
            $this->sqlport=$xmldb_mssqlport;
        }
        else
        {
            $element=get_xml_single_element("port",$this->xmldescriptor);
            if ($element == "")
            {
                $this->sqlport=1433;
            }
            else
            {
                $this->sqlport=$element;
            }
        }
        //user and password
        if (!empty($xmldb_mssqlhost))
        {
            $this->sqlusername=$xmldb_mssqlusername;
            $this->sqlpassword=$xmldb_mssqlpassword;
        }
        else
        {
            $element=get_xml_single_element("user",$this->xmldescriptor);
            if ($element == "")
            {
                $this->sqlusername="sa";
            }
            else
            {
                $this->sqlusername=$element;
            }
            $this->sqlpassword=get_xml_single_element("password",$this->xmldescriptor);
        }
        //------------------------db name--------------------------------------<
        //---da variabili globali che sostituiscono xml------------------------>
        global $xmldb_mssqldatabase;
        if ($xmldb_mssqldatabase)
        {
            $this->sqldatabasename=$xmldb_mssqldatabase;
        }
        //---da variabili globali che sostituiscono xml------------------------<
        //verifico se esiste il db--------------------------------------------->
        $query="SELECT name FROM master.sys.databases WHERE name = '{$this->sqldatabasename}'";
        $res=$this->dbQuery($query);
        $dbexists=false;
        if (isset($res[0]['name']))
            $dbexists=true;
        //verifico se esiste il db---------------------------------------------<
        //--------------creo il db--------------------------------------------->
        if (!$dbexists)
        {
            $query="CREATE DATABASE {$this->sqldatabasename}";
            $res=$this->dbQuery($query);
        }
        //--------------creo il db---------------------------------------------<
        //verifico se esiste la tabella---------------------------------------->

        $query="SELECT * FROM information_schema.tables  WHERE TABLE_TYPE='BASE TABLE'  AND TABLE_NAME='{$this->sqltablename}'";
        //dprint_r($query);
        $res=$this->dbQuery($query);
        $exists=false;
        if (isset($res[0]) && is_array($res[0]))
        {
            $exists=true;
        }


        //verifico se esiste la tabella----------------------------------------<
        /* CREATE TABLE [dbo].[WhatsUpIn](
          [IDMessaggio] [int] IDENTITY(3500000,1) NOT NULL,
          [NumberApplication] [varchar](32) NULL,
          [Telefono] [varchar](32) NULL,
          [Data] [datetime] NULL,
          [Messaggio] [text] NULL,
          [FProcessato] [int] NOT NULL,
          [Reportizated] [int] NULL,
          [DataArrivo] [datetime] NULL
          ) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY] */


        //crea la tabella----->
        if (!$exists)
        {
            //die ("ccc");
            $fields=$this->fields;
            //dprint_r($fields);
            $query="CREATE TABLE {$this->sqltablename} (";
            $n=count($fields);
            foreach($fields as $field)
            {
                $field=get_object_vars($field);
                if (!isset($field['type']) || $field['type'] == "string")
                    $field['type']="varchar";
                $query .= "[".$field['name']."]";
                $field['size']=isset($field['size']) ? $field['size'] : "";
                switch($field['type'])
                {
                    case "innertable" :
                        break;
                    case "text" :
                    case "html" :
                        $query .= " TEXT";
                        break;
                    case "int" :
                        $query .= " INT";
                        break;
                    default : //forzo tutto a varchar
                        $query .= " VARCHAR";
                        $field['size']="255";
                        break;
                }
                if ($field['size'] != "")
                    $query .= "(".$field['size'].")";
                $query .= " ";
                if (isset($field['primarykey']) && $field['primarykey'] == "1")
                {
                    $query .= "  PRIMARY KEY ";
                }
                if (isset($field['extra']) && $field['extra'] == "autoincrement")
                {
                    if ($field['type'] == "int")
                    {
                        $query .= " IDENTITY(1,1) ";
                    }
                }
                $query .= " NOT NULL DEFAULT('')"; //NOT NULL
                if ($n-- > 1)
                    $query .= ",";
            }
            $query .= ") ;";

            if (!$this->dbQuery($query))
            {
                die("sqlservererror".__LINE__);
            }
            $dbcache['tables'][$this->sqldatabasename]=$this->dbQuery("SELECT * FROM information_schema.tables");

            //transfert xml data into sql
            $tmpRecords=xmldb_readDatabase("$path/".$databasename."/".$this->tablename,$this->tablename,false,false);
            foreach($tmpRecords as $rec)
            {
                $this->InsertRecord($rec);
            }
        }
        //crea la tabella-----<
        //--sincronizzo i campi --->
        if (empty($dbcache[$this->sqldatabasename][$this->sqltablename]['describe']))
            $dbcache[$this->sqldatabasename][$this->sqltablename]['describe']=$this->dbQuery("exec sp_columns  ".$this->sqltablename);
        $xmlfield=$this->fields;
        $result=$dbcache[$this->sqldatabasename][$this->sqltablename]['describe'];
        $exists=false;
        //dprint_r($result);
        if ($result)
        {
            foreach($result as $tmp)
            {
                if (!is_array($tmp))
                    return true;
                $sql_fields[$tmp['COLUMN_NAME']]=$tmp;
                //dprint_r($tmp);
                if ($tmp['NULLABLE'] != "NO")
                {
                    $this->nullfields[$tmp['COLUMN_NAME']]=$tmp['COLUMN_NAME'];
                }
            }
        }
        else
        {
            //echo sql_error();
            return false;
        }
        $flag_tablechanged=false;
        foreach($xmlfield as $fieldname=> $fieldvalues)
        {
            if (!isset($sql_fields[$fieldname]) && $fieldvalues->type != "innertable")
            {
                $field=get_object_vars($fieldvalues);
                echo "add field $fieldname";
                $query="ALTER TABLE ".$this->sqltablename." ADD $fieldname ";
                $field['size']=isset($field['size']) ? $field['size'] : "";
                switch($field['type'])
                {
                    case "text" :
                    case "html" :
                        $query .= " TEXT";
                        break;
                    case "int" :
                        $query .= " INT";
                        break;
                    default : //forzo tutto a varchar
                        $query .= " VARCHAR";
                        $field['size']="255";
                        break;
                }
                if ($field['size'] != "")
                    $query .= "(".$field['size'].")";
                if ($field['type'] != "int")
                    $query .= " ";
                $query .= " ";
                if (isset($field['extra']) && $field['extra'] == "autoincrement")
                {
                    if ($field['type'] == "int")
                        $query .= " AUTO_INCREMENT ";
                }
                $query .= " NOT NULL  DEFAULT('')";
                //dprint_r($query);
                if (!$this->dbQuery($query))
                {
                    die("sqlservererror".__LINE__);
                    return false;
                }
                $flag_tablechanged=true;
            }
        }
        if ($flag_tablechanged)
            $dbcache[$this->sqldatabasename][$this->sqltablename]['describe']=$this->dbQuery("exec sp_columns  ".$this->sqltablename);

        $this->sqlfields=$sql_fields;
        //--sincronizzo i campi ---<
    }

    /**
     * 
     * @param type $query
     * @return boolean
     */
    function dbQuery($query)
    {
        //dprint_r($query);
        $db=array();
        $db['server']=$this->sqlhost;
        $db['dbname']=$this->sqldatabasename;
        $db['user']=$this->sqlusername;
        $db['password']=$this->sqlpassword;
        $db['port']=$this->sqlport;


        $result=false;
        $rows=false;
        //versione con le funzioni ms
        if (!function_exists("mssql_query"))
        {
            if (empty($this->conn))
            {
                if (!function_exists("sqlsrv_connect"))
                    die("sqlsrv_connect not exists");
                //$db['server'] = $dbserver;
                $serverName=$db['server'];
                $connectionInfo=array("UID"=>$db['user'],
                    "PWD"=>$db['password'],
                    "Database"=>$db['dbname'],
                    "ReturnDatesAsStrings"=>true
                );
                /* Connect using Windows Authentication. */

                $conn=sqlsrv_connect($serverName,$connectionInfo);
                if ($conn === false)
                {
                    echo "Unable to connect {$db['server']}.</br>";
                    die(print_r(sqlsrv_errors(),true));
                }

                $this->conn=$conn;
            }

            /* Query SQL Server for the login of the user accessing the
              database. */
            $tsql=$query;
            $result=sqlsrv_query($this->conn,$tsql);
            $error=sqlsrv_errors();
            if ($result === false && $error != "")
            {
                echo "Error in executing query.\n$query\n";
                echo "Error:";
                print_r($error);
                return false;
            }

            $rows=array();
            while(false !== ($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)))
            {
                if (is_array($row) && count($row) > 0)
                    $rows[]=$row;
            }
            /* Free statement and connection resources. */
            sqlsrv_free_stmt($result);
            //sqlsrv_close($conn);
            $result=true;
        }
        else
        {
            if (empty($this->conn))
            {
                $connectionstring="{$db['server']}:{$db['port']}";
                //versione con le funzioni php native
                $link=mssql_connect($connectionstring,$db['user'],$db['password']);
                if (!$link)
                {
                    die('Connection to server failed');
                }
                $this->conn=$link;
            }
            // solo su select,update,delete
            mssql_select_db($db['dbname'],$this->conn);
            $result=mssql_query($query);
            if (!$result)
            {
                dprint_r($query);
                return false;
            }
            if (is_resource($result))
            {
                $rows=array();
                while(false !== ($row=mssql_fetch_array($result,MSSQL_ASSOC)))
                {
                    $rows[]=$row;
                }
            }
        }
        if (!$rows && $result)
        {
            return true;
        }
        return $rows;
    }
    function AddSlashes($str)
    {
//        $str=str_replace('"','""',$str);
        $str=str_replace("'","''",$str);
        
        return $str;
    }
    /**
     * get records in table
     *
     * @param array $restr
     * @param int $min
     * @param int $length
     * @param string $order
     * @param bool $reverse
     * @param array $fields
     * @return array
     */
    function GetRecords($restr=false,$min=false,$length=false,$order=false,$reverse=false,$fields=false)
    {

        $tablename=$this->tablename;
        if (!$fields)
        {
            foreach($this->fields as $ff=> $vv)
            {
                $fields[]=$ff;
            }
        }
        if (is_array($fields))
            $fields=implode("|",$fields);
        $fields='	['.str_replace("|","],[",$fields).']';
        $query="SELECT $fields FROM {$this->sqltablename}";
        if (is_array($restr) && count($restr > 0))
        {
            
            $query .= " WHERE ";
            $and="";
            foreach($restr as $h=> $v)
            {
                $query .= " $and [$h] LIKE '".$this->AddSlashes($v)."' ";
                $and="AND";
            }
        }
        if (is_string($restr) && trim($restr)!="")
        {
            $query.=" WHERE $restr";
           
        }
        if ($order !== false && $order !== "" && isset($this->fields[$order]))
        {
            $query .= " ORDER BY  [$order]";
        }
        if ($reverse)
            $query .= " DESC";


        $res=$this->dbQuery($query);

        if ($res && $min !== false)
        {
            $tmp=array();
            for($a=$min; $a < count($res); $a++)
            {
                if ($length && $a > ($min + $length))
                {
                    break;
                }
                $tmp[]=$res[$a];
            }
            return $tmp;
        }

        return $res;
    }

    /**
     * get single record
     *
     * @param array $restr
     * @return array
     */
    function GetRecord($restr=false)
    {
        $rec=$this->GetRecords($restr,0,1);

        if (is_array($rec) && isset($rec[0]))
        {
            return $rec[0];
        }
        return null;
    }

    /**
     * alias GetRecordByPk
     *
     * @param string $pvalue
     * @return array
     */
    function GetRecordByPrimaryKey($pvalue)
    {
        return $this->GetRecordByPk($pvalue);
    }

    /**
     * 
     * @param type $pvalue
     * @return type
     */
    function MakeQueryPk($pvalue)
    {
        if (is_array($this->primarykey))
        {
            $query="";
            $sep="";
            foreach($pvalue as $k=> $p)
            {

                /* [$pkey] LIKE";
                  if ($this->fields[$pkey]->type == "int")
                  $query .= " $pvalue ";
                  else
                  $query .= " '$pvalue' "; */
                $query .= " $sep [$k] LIKE ";
                if ($this->fields[$k]->type == "int")
                    $query .= " $p ";
                else
                    $query .= " '$p' ";
                $sep="AND";
            }
        }
        else
        {
            $pkey=$this->primarykey;
            $query="[$pkey] LIKE ";
            if ($this->fields[$pkey]->type == "int")
                $query .= " $pvalue ";
            else
                $query .= " '$pvalue' ";
        }
        return $query;
    }

    /**
     * GetRecordByPk
     * torna il record passandogli la chiave primaria
     * @param string $pvalue valore chiave
     */
    function GetRecordByPk($pvalue)
    {
        $tablename=$this->tablename;
        // se i dati sono su database --->
        $query="SELECT * FROM {$this->sqltablename} WHERE ".$this->MakeQueryPk($pvalue);
        $result=$this->dbQuery($query);
        if (!isset($result[0]))
        {
            return false;
        }
        $res=$this->fix_null($result[0]);
        return $res;
    }

    /**
     * convert NULL in ""
     * @param $res
     */
    function fix_null($res)
    {
        if (is_array($this->nullfields) && is_array($res))
        {
            foreach($res as $k=> $v)
            {
                if ($res[$k] === NULL)
                    $res[$k]="";
            }
        }
        return $res;
    }

    /**
     * DelRecord
     * Elimina un record.
     * @param string $unirecid
     * <b>$values[$this->primarykey] deve essere presente</b>
     * @return array record appena inserito o null
     * */
    function DelRecord($pkvalue)
    {
        $path=$this->path;
        $databasename=$this->databasename;
        $tablename=$this->tablename;
        $pkey=$this->primarykey;
        $query="DELETE FROM {$this->sqltablename} WHERE ".$this->MakeQueryPk($pkvalue);


        $result=$this->dbQuery($query);
        if (!$result)
        {
            return false;
        }
        if (!is_array($pkvalue) && !strpos($pkvalue,"..") !== false && file_exists("$path/$databasename/$tablename/$pkvalue/") && is_dir("$path/$databasename/$tablename/$pkvalue/"))
            xmldb_remove_dir_rec("$path/$databasename/$tablename/$pkvalue");
        return true;
    }

    /**
     * truncate table
     *
     * @return unknown
     */
    function Truncate()
    {
        $result=$this->dbQuery("truncate ".$this->sqltable);
        if (!$result)
        {
            return false;
        }
        return true;
    }

    function encode($str)
    {
        $str=str_replace("'","''",$str);
        $str=str_replace("\\","\\\\",$str);

        return $str;
    }

    /**
     * InsertRecord
     * Aggiunge un record
     *
     * @param array $values
     * */
    function InsertRecord($values)
    {

        if ($this->conn)
        {
            $seldb=true;
           // dprint_r($this->fields[$this->primarykey]);
            $query="INSERT INTO ".$this->sqltablename." (";
            if (!is_array($this->primarykey) && !isset($values[$this->primarykey]) && empty($this->fields[$this->primarykey]->autoincrement_db_side))
                $values[$this->primarykey]="";
            $n=count($values);
            $tf=array();
            foreach($values as $k=> $v)
            {
                if (isset($this->fields[$k]))
                {
                    //------autoincrement--->
                    if (empty($this->fields[$k]->autoincrement_db_side) && isset($this->fields[$k]->extra) && $this->fields[$k]->extra == "autoincrement")
                    {
                        if (!isset($this->fields[$k]->nativeautoincrement) || $this->fields[$k]->nativeautoincrement != 1)
                        {
                            if (!isset($values[$k]) || $values[$k] == "")
                            {
                                $newid=$this->GetAutoincrement($k);
                                $values[$k]=$newid;
                                $v=$newid;
                                $this->maxautoincrement[$k]=$newid;
                            }
                        }
                    }
                    //------autoincrement---<
                    $tf[]="$k";
                }
            }
            $query .= "[".implode("],[",$tf)."]";
            $query .= ") VALUES (";
            $tf=array();
            foreach($values as $k=> $v)
            {
                if (isset($this->fields[$k])) // 'IF' ADDED BY DANIELE FRANZA 28/03/2009
                {
                    if (isset($this->sqlfields[$k]['IS_NULLABLE']) && $this->sqlfields[$k]['IS_NULLABLE'] == "YES" && $v == "")
                    {
                        $tf[]="NULL";
                    }
                    else
                    {
                        if ($this->fields[$k]->type == "int" && $v !== '' && $v !== NULL)
                            $tf[]=$v;
                        else
                        {
                            //$v = str_replace('\\',"\\\\",$v);
                            //$tf[] = "'".str_replace("'","\\'",$v)."'";
                            $tf[]="'".$this->encode($v)."'";
                        }
                    }
                }
            }
            $query .= implode(",",$tf);
            $query .= ");";
        }
        $ret=false;
        //dprint_r($query);
        $ret=$this->dbQuery($query);
        if (!$ret)
        {
            return false;
        }

        if (!is_array($this->primarykey) && (!isset($values[$this->primarykey]) || $values[$this->primarykey] == ""))
        {
            $lastid=$this->dbQuery("SELECT IDENT_CURRENT('{$this->sqltablename}') AS i;"); //IDENT_CURRENT('MyTable')
            if (isset($lastid[0]['i']))
                $values=$this->GetRecordByPk($lastid[0]['i']);
        }
        else
        {
            if (is_array($this->primarykey))
            {
                $pkvalues=array();
                foreach($this->primarykey as $pv)
                {
                    $pkvalues[$pv]=$values[$pv];
                }
            }
            else
            {
                $pkvalues=$values[$this->primarykey];
            }
            $values=$this->GetRecordByPk($pkvalues);
        }
        $this->gestfiles($values);
        return $values;
    }

    /**
     * UpdateRecordBypk
     * aggiorna il record passandogli la chiave primaria
     * @param array $values
     * @param string $pkey
     * @param string $pvalue
     */
    function UpdateRecordBypk($values,$pkey,$pvalue)
    {
                  
        if ($this->conn)
        {
            $existsvalues=$this->GetRecordByPk($pvalue);
            if (!$existsvalues || !is_array($existsvalues) || count($existsvalues) == 0)
                return false;
            // $oldvalues = ($values[$pkey] != $pvalue ) ? $existsvalues : null;
            $oldvalues=$existsvalues;
            $query="UPDATE {$this->sqltablename} SET ";

            $values2=array();
            foreach($values as $k=> $value)
            {
                if (isset($this->fields[$k]))
                {
                    if ($values[$k] != $existsvalues[$k])//accorcio la query
                    {
                        $values2[$k]=$values[$k];
                    }
                }
            }
            $n=count($values2);
            if ($n == 0) //se non c'e' nulla da aggiornare
                return $existsvalues;

            foreach($values2 as $k=> $value)
            {
                if (isset($this->fields[$k]))
                {
                    $query .= "[$k]=";
                    if ($this->sqlfields[$k]['IS_NULLABLE'] == "YES" && $value == "")
                    {
                        $query .= "NULL";
                    }
                    else
                    {
                        if ($this->fields[$k]->type == "int")
                            $query .= $this->encode($value);
                        else
                            $query .= "'".$this->encode($value)."'";
                    }
                    if ($n-- > 1)
                        $query .= ",";
                }
            }
            $query .= " WHERE ".$this->MakeQueryPk($pvalue);
            //die($query);
            $ret=$this->dbQuery($query);
            $this->gestfiles($values,$oldvalues);
            if (!$ret)
            {
                return false;
            }
            $newvalues=$this->GetRecordByPk($pvalue);
        }
        else
            return false;
        return $newvalues;
    }

    /**
     * GetNumRecords
     * return records count
     * 
     * @param type $restr
     * @return type 
     */
    function GetNumRecords($restr=null)
    {
        $query="SELECT COUNT(*) AS C FROM ".$this->sqltablename;
        if (is_array($restr) && count($restr > 0))
        {
            $query .= " WHERE ";
            $and="";
            foreach($restr as $h=> $v)
            {
                $query .= " $and [$h] LIKE '$v' ";
                $and="AND";
            }
        }
        if (is_string($restr) && $restr!=="")
        {
            $query.=" WHERE $restr";
        }

        $ret=$this->dbQuery($query);
        if (isset($ret[0]['C']))
            return $ret[0]['C'];
        return 0;
    }

    /**
     *
     * @param type $values
     * @param type $oldvalues 
     */
    function gestfiles($values,$oldvalues=null)
    {
        $this->xmltable->gestfiles($values,$oldvalues);
    }

    /**
     *
     * @param type $recordvalues
     * @param type $recordkey
     * @return type 
     */
    function get_thumb($recordvalues,$recordkey)
    {
        $databasename=$this->databasename;
        $tablename=$this->tablename;
        $path=realpath($this->path);
        $unirecid=$recordvalues[$this->primarykey];
        if (!isset($recordvalues[$recordkey]))
            $recordvalues=$this->GetRecord($recordvalues);
        $value=$recordvalues[$recordkey];
        if (file_exists("$path/$databasename/$tablename/$unirecid/$recordkey/thumbs/$value.jpg"))
        {
            $php_self=isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : "";
            $dirname=dirname($php_self);
            if ($dirname == "/" || $dirname == "\\")
            {
                $dirname="";
            }
            $protocol="http://";
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")
                $protocol="https://";
            $siteurl="$protocol".$_SERVER['HTTP_HOST'].$dirname;
            if (substr($siteurl,strlen($siteurl) - 1,1) != "/")
            {
                $siteurl=$siteurl."/";
            }
            return "$siteurl".$this->path."/$databasename/$tablename/$unirecid/$recordkey/thumbs/$value.jpg";
        }
        return false;
    }

    /**
     * GetAutoincrement
     *
     * gestisce l' autoincrement di un campo della tabella
     *
     * @param string nome del campo
     * @return indice disponibile
     */
    function GetAutoincrement($field)
    {
        //die ("vvvv");
        static $last="";
        if (isset($this->maxautoincrement[$field]))
        {
            return $this->maxautoincrement[$field] + 1;
        }
        //todo autoincrement offset SHOW VARIABLES;
        //dprint_r ("SELECT MAX(CAST($field AS UNSIGNED)) AS $field FROM {$this->sqltablename} WHERE $field NOT LIKE '%[a-z]%' ");
        //dprint_r($record);
        $record=$this->dbQuery("SELECT MAX(CAST($field AS BIGINT)) AS $field FROM {$this->sqltablename} WHERE [$field] NOT LIKE '%[a-z]%' ");
        if (!isset($record[0][$field]))
            return 1;
        $max=$record[0][$field];
        //dprint_r($max);
        return $max + 1;
    }

}

?>