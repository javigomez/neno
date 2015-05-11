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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
?>

<h2>Confirm</h2>

<form action="<?php echo JRoute::_('index.php?option=com_neno&view=groupselements'); ?>" method="post" name="adminForm" id="adminForm">
    
    
    <?php 
    echo '<pre class="debug"><small>' . __file__ . ':' . __line__ . "</small>\n\$this = ". print_r($this, true)."\n</pre>";
    ?>
        
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo JHtml::_('form.token'); ?>

</form>


