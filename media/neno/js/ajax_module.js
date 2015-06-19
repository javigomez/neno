jQuery(document).ready(function () {
    jQuery.ajax({
        url: 'index.php?option=com_neno&task=processTaskQueue'
    });
});