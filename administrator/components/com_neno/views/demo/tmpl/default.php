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

JHtml::_('behavior.keepalive');

?>

<style type="text/css">
	#translate_btn{float:right}	
</style>

<script type="text/javascript">
    // function to get translation using ajax	
	function translate(apiMethod)
	{
     	var source = jQuery("#"+apiMethod+"Source").val();

		if(source !== "")
		 { 
                jQuery.ajax({
				url: "index.php?option=com_neno&task=demo.ajaxTranslate",			 
				data: {'api':apiMethod ,'source':source},
                 type:'post',
				success: function(msg){                 
                                 jQuery("#"+apiMethod+"Translate").val(jQuery.trim(msg));
			        }				 
			      });
		 }	 
	}
</script>

<!-- (start) Google translation Block -->
<div class="row-fluid">
    
    <div class="span1">Source Text</div>
    
    <div class="span4">
        <form>
            <textarea id="googleSource" class="span12" rows="8" cols="200" placeholder="English"></textarea>
        </form>
    </div>   
   
    
    <div class="span1">Translated Text</div>

    <div class="span4">

        <form action="" method="post" enctype="multipart/form-data">

            <textarea id="googleTranslate" class="span12" rows="8" cols="200" placeholder="French"></textarea>

            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>

        </form>

    </div>  
    
     <div class="span5">
     <button onclick="translate('google');" class="btn btn-primary">Google Translate</button>
    </div> 
    
</div>
<!-- (end) Google translation Block -->

<br/>

<!-- (start) Yandex translation Block -->
<div class="row-fluid">

    <div class="span1">Source Text</div>

    <div class="span4">
        <form>
            <textarea id="yandexSource" class="span12" rows="8" cols="200" placeholder="English"></textarea>
        </form>
    </div>


    <div class="span1">Translated Text</div>

    <div class="span4">

        <form action="" method="post" enctype="multipart/form-data">

            <textarea id="yandexTranslate" class="span12" rows="8" cols="200" placeholder="French"></textarea>

            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>

        </form>

    </div>

    <div class="span5">
        <button onclick="translate('yandex');" class="btn btn-primary">Yandex Translate</button>
    </div>

</div>
<!-- (end) Yandex translation Block -->