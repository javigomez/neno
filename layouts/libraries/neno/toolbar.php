<?php

/**
 * @package     Neno
 * @subpackage  Helpers
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('JPATH_NENO') or die;

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . '/media/neno/css/toolbar.css');

?>
	<a href="index.php?option=com_neno&view=dashboard">
		<img src="<?php echo JUri::root(); ?>/media/neno/images/admin_top_neno_logo.png" width="80" height="30"
		     alt="Neno logo"/>
	</a>
<?php

if (!empty($displayData['view']))
{
	$default_lang_constant = 'COM_NENO_TITLE_' . strtoupper($displayData['view']);

	if (JText::_($default_lang_constant) != $default_lang_constant)
	{
		?>
		<?php
		// If the JText text is different from the constant then it actually exists and should be used
		echo ': ' . JText::_($default_lang_constant);
	}
}

// If there is any working language
if (!empty($displayData['workingLanguage']))
{
	$workingLanguageTitleNative = $displayData['targetLanguages'][$displayData['workingLanguage']]->title_native;
	$workingLanguageImage       = JUri::root() . '/media/mod_languages/images/' . $displayData['targetLanguages'][$displayData['workingLanguage']]->image . '.gif';
	unset($displayData['targetLanguages'][$displayData['workingLanguage']]);

	// If we have more than one target languages left then allow changing, if not only show the name
	if (count($displayData['targetLanguages']) > 0)
	{
		$next = empty($displayData['view']) ? 'dashboard' : $displayData['view'];
		?>
		<ul id="workingLangSelect">
			<li class="dropdown">
				<?php echo JText::_('COM_NENO_TOOLBAR_TRANSLATING_TITLE'); ?>
				<a class="dropdown-toggle" data-toggle="dropdown" href="#">
					<img src="<?php echo $workingLanguageImage; ?>"/>
					<?php echo $workingLanguageTitleNative; ?>
					<span class="caret"></span>
				</a>
				<ul class="dropdown-menu">

					<?php
					foreach ($displayData['targetLanguages'] as $targetLanguage):
						?>
						<li>
							<a href="index.php?option=com_neno&task=setworkinglang&lang=<?php echo $targetLanguage->lang_code; ?>&next=<?php echo $next; ?>">
								<img src="../media/mod_languages/images/<?php echo $targetLanguage->image; ?>.gif"/>
								<?php echo $targetLanguage->title_native; ?>
							</a>
						</li>
					<?php
					endforeach
					?>
				</ul>
			</li>
		</ul>
	<?php
	}
	else
	{
		?>
		<ul id="workingLangSelect">
			<li class="dropdown">
				<?php echo JText::_('COM_NENO_TOOLBAR_TRANSLATING_TITLE'); ?>
				[<?php echo $workingLanguageTitleNative; ?>]
			</li>
		</ul>
	<?php
	}
}
