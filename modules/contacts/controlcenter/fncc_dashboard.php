<?php

/**
 * @package Flatnux_module_contacts
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 *
 */
defined('_FNEXEC') or die('Restricted access');

$messages=FN_XMLQuery("SELECT * FROM contact_message ORDER BY id DESC LIMIT 1,5");
if (is_array($messages))
	foreach ($messages as $message)
	{
		//echo $message['contact']." ";
        if (!empty($message['name']))
		echo $message['name'] . " ";
        if (!empty($message['subject']))
		echo $message['subject'];
		echo "<a href=\"?op___xdb_contact_message=view&pk___xdb_contact_message={$message['id']}&mod={$_FN['mod']}&opt=fnc_ccnf_section_{$_FN['mod']}\">&nbsp;&nbsp;" . FN_Translate("read all") . "</a>";
		echo "<br />";
	}
else
	echo FN_Translate("no result");
?>