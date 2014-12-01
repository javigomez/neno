<?php
/**
 * @package     Neno
 * @subpackage  Views
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;
?>


<h1>Neno Dashboard</h1>

<a href="<?php echo JRoute::_('index.php?option=com_neno&view=langfilesimport'); ?>" class="btn btn-info">
	<span class="icon-download "></span>
	Import language files
</a>
<a href="<?php echo JRoute::_('index.php?option=com_neno&view=translations'); ?>" class="btn">
	<span class="icon-list-2 "></span>
	Manage translation items
</a>
