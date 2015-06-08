<?php
/**
 * @package    Neno
 *
 * @author     Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright  Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_NENO') or die;

$view = $displayData;

?>

<?php if ($view == 'groupselements'): ?>
    <br />
    <div class="alert alert-info">
        <?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_INFOBOX'); ?>
        <br />
        <br />
        <?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_INFOBOX2'); ?>
    </div>
<?php endif; ?>

