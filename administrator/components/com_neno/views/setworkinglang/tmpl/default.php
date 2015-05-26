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

$next = JFactory::getApplication()->input->getString('next', 'dashboard');

?>
<h2><?php echo JText::_('COM_NENO_SETTINGS_SET_WORKING_LANGUAGE'); ?></h2>

<div class="clearfix">
	<?php foreach ($this->langs as $lang): ?>
		<a class="btn btn-large span2 <?php echo $lang->isInstalled == false ? 'not-ready' : ''; ?>"
		   href="index.php?option=com_neno&task=setworkinglang&lang=<?php echo $lang->lang_code; ?>&next=<?php echo $next; ?>">
			<h4>
				<?php
				if (file_exists(JPATH_SITE . '/media/mod_languages/images/' . $lang->image . '.gif')): ?>
					<img src="<?php echo JUri::root() . 'media/mod_languages/images/' . $lang->image . '.gif'; ?>"/>
				<?php endif; ?>
				<?php echo $lang->title_native; ?>
			</h4>
		</a>
	<?php endforeach; ?>
</div>

<script>
	jQuery(document).ready(function () {
		jQuery('.not-ready').off('click').on('click', function (e) {
			e.preventDefault();
			alert('<?php echo JText::_('COM_NENO_LANGUAGE_IS_NOT_READY_YET_MESSAGE'); ?>');
		});
	});
</script>
