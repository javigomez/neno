/**
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


jQuery(document).ready(function () {

    jQuery('.js-stools-field-filter select').addClass('btn dropdown-toggle');
    setTimeout(function() {
        jQuery('.js-stools-field-filter .dropdown-toggle').removeClass('active');
    }, 100);

    jQuery('#elements-wrapper').scroll(function(){
        var wrapper = jQuery(this);
        if(wrapper.scrollTop() + wrapper.innerHeight()>=wrapper[0].scrollHeight && wrapper.innerHeight() > 10) {
            document.adminForm.limitstart.value = parseInt(document.adminForm.limitstart.value) + 20;
            loadStrings(jQuery('.multiselect input[type=checkbox]').first());
        }
    });

});
