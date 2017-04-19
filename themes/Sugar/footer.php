<?php
/************************************************************************/
/* FlatNuke - Flat Text Based Content Management System                 */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2003-2004 by Simone Vellei                             */
/* http://flatnux.sf.net                                      */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/
if (strpos ( strtolower ( $_SERVER ['SCRIPT_NAME'] ), strtolower ( basename ( __FILE__ ) ) ))
	die ();
	
global $_FN;
?>

<!-- FOOTER  -->
<div style="text-align:center"><small>
	<a href="http://flatnux.sugarforge.org" onclick="window.open(this.href);return false;"><img border="0" alt="" src="themes/<?php echo $_FN['theme']?>/images/poweredb.png" /></a>
<br />
	<small>
		<?php ShowCredits(); $time2 = get_microtime();
		echo "<br />Page generated in ".sprintf("%.4f", abs(get_microtime() - $_FN['timestart']))." seconds.";
		
		?>
	</small>
</div>
<!-- END FOOTER -->
