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

?>

<button type="button" class="btn btn-link" data-language="<?php echo $displayData['language']; ?>"
        data-issue="<?php echo $displayData['issue']; ?>">
	<?php echo JText::_('COM_NENO_FIX_IT_BUTTON_FIX_IT_TEXT'); ?>
</button>
