/**
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


jQuery(document).ready(function () {

    // Load strings on results scroll
    jQuery('#elements-wrapper').scroll(function(){
        var wrapper = jQuery(this);
        if(wrapper.scrollTop() + wrapper.innerHeight()>=wrapper[0].scrollHeight && wrapper.innerHeight() > 10) {
            document.adminForm.limitstart.value = parseInt(document.adminForm.limitstart.value) + 30;
            loadStrings();
        }
    });

    // Bind keyboard events
    jQuery('body').on('keydown', function (e) {
        var ev = e || window.event;

        // Ctrl+S
        if (ev.keyCode == 83 && e.ctrlKey) {
            ev.preventDefault();
            saveDraft();
        }

        // Ctrl+Enter
        if (ev.keyCode == 13 && e.ctrlKey) {
            ev.preventDefault();
            saveTranslationAndNext();
        }

        // Ctrl+Space
        if (ev.keyCode == 32 && e.ctrlKey) {
            ev.preventDefault();
            loadNextTranslation();
        }

        // Ctrl+→
        if (ev.keyCode == 39 && e.ctrlKey && !e.shiftKey) {
            ev.preventDefault();
            copyOriginal();
        }
    });

    // Load string passed by the URL
    var params = document.location.search.replace('?', '');
    var paramsArray = params.split('&');
    for (var i=0; i<paramsArray.length; i++) {
        if (paramsArray[i].indexOf('stringId=') != -1) {
            var stringId = paramsArray[i].split('=')[1];
            loadTranslation(stringId);
            break;
        }
    }

    // If there are no filter options, select "Manual", "Source has changed" and "Not translated"
    if (document.location.href == document.location.origin + document.location.pathname + '?option=com_neno&view=editor') {
        jQuery('.multiselect input[type=checkbox]').prop('checked', false);
        jQuery('#input-method-1').prop('checked', true);
        jQuery('#input-status-3').prop('checked', true);
        jQuery('#input-status-4').prop('checked', true);
        loadStrings(true);
    }

    // Bind event to search button
    jQuery('.submit-form').off('click').on('click', function (e) {
        loadStrings(true);
    });

    // Fit filters inside the sidebar
    jQuery(window).resize(function(){
       jQuery('#filter_search').width(jQuery('#j-sidebar-container').innerWidth() - jQuery('.submit-form').width() - 57);
       jQuery('.multiselect-wrapper').width(jQuery('#j-sidebar-container').innerWidth() - 45);
    });

    // Set results wrapper height
    setResultsWrapperHeight();

    // Bind click event to close multiselects
    jQuery('html').click(function(e){
        var ev = e || window.event;
        if(jQuery(ev.target).parents('.js-stools-container-filters').length == 0 && !jQuery(ev.target).hasClass('icon-arrow-down-3') && !jQuery(ev.target).hasClass('icon-arrow-right-3')) {
            jQuery('.js-stools-container-filters .btn-toggle').each(function (e) {
                if (jQuery(this).hasClass('open')){
                    jQuery('#' + jQuery(this).attr('data-toggle')).slideToggle('fast');
                    jQuery(this).toggleClass('open');
                    jQuery(this).blur();
                }
            });
        }
    });
});
