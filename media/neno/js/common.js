/**
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

function loadTranslation(string) {
    jQuery('.string-activated').removeClass('string-activated');
    string.addClass('string-activated');

    // Get information
    jQuery.get('index.php?option=com_neno&task=editor.getTranslation&id=' + string.data('id'), function (data) {
        jQuery('#editor-wrapper').html(data);
    });
}

function loadNextTranslation() {
    var nextString = jQuery('.string-activated').next('div').next('div');
    if (nextString.length) {
        loadTranslation(nextString);
    }
}

function saveTranslationAndNext() {
    var text = jQuery('.translated-content').val();
    var translationId = jQuery('#save-next-button').data('id');
    var statuses = ['', 'translated', 'queued', 'changed', 'not-translated'];
    jQuery.post(
        'index.php?option=com_neno&task=editor.saveAsCompleted',
        {
            id: translationId,
            text: text
        }
        , function (data) {
            var row = jQuery('#elements-wrapper .string[data-id=' + data.id + ']');
            if (row) {
                var string = data.string;
                if (string.length > 40) {
                    string = string.substr(0, 35) + '...';
                }
                row.find('.string-text').html(string);
                row.find('.status').removeClass().addClass('status');
                row.find('.status').addClass(statuses[data.state]);
            }
            loadNextTranslation();
        }
        , 'json'
    );
}

function saveDraft() {
    var text = jQuery('.translated-content').val();
    var translationId = jQuery('#draft-button').data('id');
    jQuery.post(
        'index.php?option=com_neno&task=editor.saveAsDraft',
        {
            id: translationId,
            text: text
        }
        , function (data) {

        }
    );
}

function translate() {
    var text = jQuery('.original-text').html().trim();
    jQuery.post(
        'index.php?option=com_neno&task=editor.translate',
        {text: text}
        , function (data) {
            jQuery('.translated-content').val(data);
            jQuery('.translated-by').show();
        }
    );
}

// Check if the user has lost the session
function onBeforeAjax() {
    jQuery.get('index.php?option=com_neno&task=checkSession', function (response) {
        if (response != 'ok') {
            document.location.reload();
        }
    });
}
