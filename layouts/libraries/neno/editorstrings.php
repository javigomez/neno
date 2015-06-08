<?php
/**
 * @package     Neno
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_NENO') or die;

$translationStatesClasses                                                                   = array ();
$translationStatesClasses[NenoContentElementTranslation::TRANSLATED_STATE]                  = 'translated';
$translationStatesClasses[NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE] = 'queued';
$translationStatesClasses[NenoContentElementTranslation::SOURCE_CHANGED_STATE]              = 'changed';
$translationStatesClasses[NenoContentElementTranslation::NOT_TRANSLATED_STATE]              = 'not-translated';

$translationStatesText                                                                   = array ();
$translationStatesText[NenoContentElementTranslation::TRANSLATED_STATE]                  = JText::_('COM_NENO_STATUS_TRANSLATED');
$translationStatesText[NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE] = JText::_('COM_NENO_STATUS_QUEUED');
$translationStatesText[NenoContentElementTranslation::SOURCE_CHANGED_STATE]              = JText::_('COM_NENO_STATUS_CHANGED');
$translationStatesText[NenoContentElementTranslation::NOT_TRANSLATED_STATE]              = JText::_('COM_NENO_STATUS_NOT_TRANSLATED');

if (isset($displayData->translations))
{
	$translations = $displayData->translations;
}
else
{
	$translations = $displayData;
}
?>

<script>
	jQuery(document).ready(function () {
		jQuery('.string').unbind('click').bind('click', function () {
			loadTranslation(jQuery(this));
		});
	});
</script>

<?php if (!empty($translations)): ?>
	<?php /* @var $translation stdClass */ ?>
	<?php foreach ($translations as $translation): ?>
		<div class="string" data-id="<?php echo $translation->id; ?>">
			<div class="status <?php echo $translationStatesClasses[$translation->state]; ?>"
			     alt="<?php echo $translationStatesText[$translation->state]; ?>"
			     title="<?php echo $translationStatesText[$translation->state]; ?>">
			</div>
			<div class="string-text"
			     title="<?php echo NenoHelper::html2text($translation->original_text, 300); ?>">
				<?php echo JHtmlString::truncate(strip_tags($translation->string), 50, false); ?>
			</div>
		</div>
		<div class="clearfix"></div>
	<?php endforeach; ?>
<?php endif; ?>




