<?php

/**
 * @package Flatnux_functions
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 */
defined('_FNEXEC') or die('Restricted access');

/**
 *
 * @param string $fromDir
 * @param string $toDir
 * @param bool $verbose
 * @return bool
 */
function FN_CopyDir($fromDir,$toDir,$verbose=false)
{
    //FN_ListDir($path = "sections", $showhidden = false, $recursive = false, $reset = true)
    $dirs=FN_ListDir($fromDir,true,true);
    if ($verbose)
        dprint_r("$fromDir => $toDir");
    $to_create=false;
    if (!is_dir($toDir))
    {
        if (!FN_MkDir($toDir))
            return false;
        $to_create=true;
    }
    if (!is_writable($toDir))
        return false;
    if ($to_create == false && is_dir($fromDir) && is_dir($toDir))
    {
        if (!is_writable($toDir))
            return false;
        if (file_exists($toDir."/".basename($fromDir)))
        {
            if ($verbose)
                echo "<br />".$toDir."/".basename($fromDir)." exists";
            return false;
        }
        if (!mkdir($toDir."/".basename($fromDir)))
            return false;
        if ($verbose)
            echo 'Directory created: '.$toDir;
        $toDir=$toDir."/".basename($fromDir);
    }
    $handlesource=opendir("$fromDir");
    while(false!== ($file_hs=readdir($handlesource)))
    {
        if (!is_dir("$fromDir/$file_hs") && $file_hs!= "." && $file_hs!= "...")
        {
            if ($verbose)
                echo "<br /><b>copy file $fromDir/$file_hs => $toDir/$file_hs</b>";
            if (!FN_Copy("$fromDir/$file_hs","$toDir/$file_hs"))
                return false;
        }
    }
    foreach($dirs as $dir)
    {
        mkdir("$toDir/$dir");
        if ($verbose)
            echo "<br /><pre>mkdir: $toDir/$dir</pre>";
        $handlesource=opendir("$fromDir/$dir");
        while(false!== ($file_hs=readdir($handlesource)))
        {
            if (!is_dir("$fromDir/$dir/$file_hs") && $file_hs!= "." && $file_hs!= "...")
            {
                if ($verbose)
                    echo "<br /><b>copy file $fromDir/$dir/$file_hs => $toDir/$dir/$file_hs</b>";
                if (!FN_Copy("$fromDir/$dir/$file_hs","$toDir/$dir/$file_hs"))
                    return false;
            }
        }
    }
    return true;
}

/**
 *
 * @param string $path
 * @return array
 */
function FN_ListDir($path="sections",$showhidden=false,$recursive=false,$reset=true)
{
    static $modlist=array();
    static $basepath=null;
    static $cache=array();
    if (isset($cache[$path.$showhidden.$recursive.$reset]))
    {
        return $cache[$path.$showhidden.$recursive.$reset];
    }
    while(strstr($path,'//'))
    {
        $path=str_replace('//','/',$path);
    }
    if ($basepath == null || $reset == true)
        $basepath=$path;
    if (!file_exists($path) || !is_dir($path))
        return array();
    $handle=opendir($path);
    if ($recursive == false || $path == "$basepath") //se e' la prima chiamata o non e' ricorsiva resetto la lista
        $modlist=array();
    while(false!= ($file=readdir($handle)))
    {
        if ($file!= "." && $file!= ".." && is_dir("$path/$file"))
        {
            if ((!preg_match("/^\./si",$file) && !preg_match("/^none_/si",$file)) || $showhidden == true)
            {
                if ($recursive!= false)
                {
                    $modlist[]=str_replace("$basepath/","",$path."/".$file);
                    FN_ListDir($path."/".$file,$showhidden,true,false);
                }
                else
                {
                    $modlist[]=str_replace("$basepath/","",$file);
                }
            }
        }
    }
    closedir($handle);
    if ($reset)
    {
        $cache[$path.$showhidden.$recursive.$reset]=$modlist;
    }
    return $modlist;
}

/**
 *
 * @param string $s
 * @param string $d
 * @return bool
 */
function FN_Copy($s,$d)
{
    global $_FN;
    if (!file_exists($s) || is_dir($s))
    {
        return false;
    }
    $contents=file_get_contents($s);
    if (is_dir($d))
    {
        $d.=$_FN['slash'].basename($s);
    }
    //dprint_r($s." ".$d);
    $h=fopen($d,"wb");
    if ($h === false)
        return false;
    fwrite($h,$contents);
    fclose($h);
    if ($contents!= file_get_contents($d))
    {
        @ unlink($d);
        return false;
    }
    return true;
}

/**
 *
 * @param string $string
 * @param string $file
 * @param string $mode
 * @return bool
 */
function FN_Write($string,$file,$mode="w")
{
    if (false!== ($fp=fopen($file,$mode)))
    {
        fwrite($fp,$string);
        fclose($fp);
        return true;
    }
    return false;
}

/**
 *
 * @param string $oldname
 * @param string $newname
 * @return bool 
 */
function FN_Rename($oldname,$newname)
{
    return rename($oldname,$newname);
}

/**
 *
 * @param string $file
 * @return bool
 */
function FN_IsWritable($file)
{
    if (is_writable($file))
    {
        return true;
    }
    return false;
}

/**
 *
 * @param string $pathname
 * @return bool
 */
function FN_MkDir($pathname)
{
    is_dir(dirname($pathname)) || FN_MkDir(dirname($pathname));
    return is_dir($pathname) || @mkdir($pathname);
}

/**
 *
 * @param string $folder
 */
function FN_RemoveDir($dirtodelete)
{
    if (!$dirtodelete)
        return;
    if (strpos($dirtodelete,"../")!== false)
        die("error");
    if (false!= ($objs=glob($dirtodelete."/.*")))
    {
        foreach($objs as $obj)
        {
            if (!is_dir($obj))
            {
                unlink($obj);
            }
            else
            {
                if (basename($obj)!= "." && basename($obj)!= "..")
                {
                    FN_RemoveDir($obj);
                }
            }
        }
    }
    if (false!== ($objs=glob($dirtodelete."/*")))
    {
        foreach($objs as $obj)
        {
            is_dir($obj) ? FN_RemoveDir($obj) : unlink($obj);
        }
    }
    rmdir($dirtodelete);
}

/**
 *
 * @global array $_FN
 * @param string $file 
 */
function FN_BackupFile($file)
{
    if (file_exists($file) && file_get_contents($file)!== "")
    {
        global $_FN;
        $time=time();
        $user=$_FN['user'];
        if ($user == "")
            $user="_@CMS@_";
        $dateFile=filemtime($file);
        while(file_exists("$file.$dateFile.".date("YmdHis",$time).".{$_FN['user']}.bak~"))
            $time++;
        FN_Copy($file,"$file.$dateFile.".date("YmdHis",$time).".{$_FN['user']}.bak~");
    }
}

/**
 *
 * @param string $filename 
 */
function FN_Unlink($filename)
{
    unlink($filename);
}

/**
 *
 * @param string $path
 * @return string 
 */
function FN_AbsolutePath($path)
{
    // dprint_r($path);
    $out=array();
    foreach(explode('/',$path) as $i=> $fold)
    {
        if ($fold == '' || $fold == '.')
            continue;
        if ($fold == '..' && $i > 0 && end($out)!= '..')
            array_pop($out);
        else
            $out[]=$fold;
    }
    if (isset($path[0]))
        $path=($path[0] == '/' ? '/' : '').(join("/",$out));
    return $path;
}

/**
 *
 * @param string $path
 * @return string 
 */
function FN_RelativePath($path)
{
    //"/var/www/html/flatnux/misc/fndatabase TO misc/fndatabase/"
    $path=FN_AbsolutePath($path);
    $scriptfolder=dirname($_SERVER['SCRIPT_FILENAME']).DIRECTORY_SEPARATOR;
    $path=preg_replace("/^".str_replace('/','\\/',str_replace("\\","\\\\",$scriptfolder))."/s","",$path);
    return $path;
}

/**
 * 
 * @return type
 */
function FN_FileIsLocked($file)
{
    global $_FN;
    $filelock="{$_FN['datadir']}/_cache/".md5(($file)).".lock";
    if (file_exists($filelock))
    {
        return true;
    }
    return false;
}

/**
 * 
 * @return type
 */
function FN_LockFile($file)
{
    global $_FN;
    $filelock="{$_FN['datadir']}/_cache/".md5(($file)).".lock";
    if (false!== ($fp=@fopen($filelock,"x")))
    {
        fclose($fp);
        return true;
    }
    return false;
}

/**
 * 
 * @return type
 */
function FN_UnlockFile($file)
{
    global $_FN;
    $filelock="{$_FN['datadir']}/_cache/".md5(($file)).".lock";
    $r=unlink($filelock);
    return $r;
}

?>