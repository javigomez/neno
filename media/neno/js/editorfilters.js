/**
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


jQuery(document).ready(function () {

    jQuery('#elements-wrapper').scroll(function(){
        var wrapper = jQuery(this);
        if(wrapper.scrollTop() + wrapper.innerHeight()>=wrapper[0].scrollHeight && wrapper.innerHeight() > 10) {
            document.adminForm.limitstart.value = parseInt(document.adminForm.limitstart.value) + 20;
            loadStrings();
        }
    });

    jQuery('body').on('keydown', function (e) {
        var ev = e || window.event;

        // Ctrl-S
        if (ev.keyCode == 83 && e.ctrlKey) {
            ev.preventDefault();
            saveDraft();
        }

        // Ctrl-Enter
        if (ev.keyCode == 13 && e.ctrlKey) {
            ev.preventDefault();
            saveTranslationAndNext();
        }
    });

    var params = document.location.search.replace('?', '');
    var paramsArray = params.split('&');
    for (var i in paramsArray) {
        if (paramsArray[i].indexOf('stringId=') != -1) {
            var stringId = paramsArray[i].split('=')[1];
            loadTranslation(stringId);
            break;
        }
    }

});
