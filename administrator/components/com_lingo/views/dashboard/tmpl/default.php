<?php
/**
 * @version     1.0.0
 * @package     com_lingo
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Soren Beck Jensen <soren@notwebdesign.com> - http://www.notwebdesign.com
 */
// no direct access
defined('_JEXEC') or die;
?>


<h1>Lingo Dashboard</h1>


<form action="index.php" method="get">
	<input type="hidden" name="option" value="com_lingo"/>
	<input type="hidden" name="view" value="langfilesimport"/>
	<button type="submit" class="btn btn-info">
		<span class="icon-download "></span>
		Import language files
	</button>
</form>