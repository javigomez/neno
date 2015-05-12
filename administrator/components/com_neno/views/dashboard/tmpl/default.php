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

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

//Include the CSS file
JHtml::stylesheet('media/neno/css/admin.css');

// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}

$workingLanguage = NenoHelper::getWorkingLanguage();

?>

<script type="text/javascript">

	jQuery(document).ready(function () {

		jQuery('.configuration-button').on('click', function () {
			jQuery(this).siblings('.language-configuration').slideToggle('fast');
		});

		jQuery(".radio").on('change', function () {
			jQuery.ajax({
				beforeSend: onBeforeAjax,
				url: 'index.php?option=com_neno&task=dashboard.toggleLanguage&language=' + jQuery(this).data('language')
			});
		})

		jQuery('.method-1').change(toggleMethodSelect);

		jQuery("[data-issue]").off('click').on('click', fixIssue);

	});
</script>


<form action="<?php echo JRoute::_('index.php?option=com_neno&view=groupselements'); ?>" method="post" name="adminForm"
      id="adminForm">

	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<div class="languages-holder">
			<?php
			foreach ($this->items as $item):
				$item->placement = 'dashboard';
				echo JLayoutHelper::render('languageconfiguration', $item, JPATH_NENO_LAYOUTS);
			endforeach;
			?>
		</div>
	</div>
</form>


