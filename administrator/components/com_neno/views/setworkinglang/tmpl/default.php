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
<h2>Please select the language you would like to work with</h2>

<div class="clearfix">
    <?php foreach ($this->langs as $lang): ?>
        <a class="btn btn-large span2" href="index.php?option=com_neno&task=setworkinglang&lang=<?php echo $lang->lang_code; ?>"><h2><img src="../media/mod_languages/images/<?php echo $lang->image; ?>.gif" /> <?php echo $lang->title_native; ?></h2></a>
    <?php endforeach; ?>
</div>
<?php

