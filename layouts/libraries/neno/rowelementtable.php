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

if ($displayData === null): ?>

    <tr><td>NADA</td></tr>
    
<?php else: ?>
    
    
    <?php
    echo '<pre class="debug"><small>' . __file__ . ':' . __line__ . "</small>\n\$displayData = ". print_r($displayData, true)."\n</pre>";
    ?>

<?php endif; ?>

