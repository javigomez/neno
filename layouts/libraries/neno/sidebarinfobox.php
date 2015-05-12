<?php
/**
 * Created by PhpStorm.
 * User: victor
 * Date: 21/04/15
 * Time: 16:09
 */

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

