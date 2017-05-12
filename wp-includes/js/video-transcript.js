jQuery('document').ready(function () {
   jQuery('.script_title').on('click', function() {
        var $txt_view = jQuery(this).html();
        if($txt_view == 'View Transcript') {
            jQuery(this).html('Hide Transcript');
            jQuery('#script_container_updated').css('height','auto');
        } else {
            jQuery(this).html('View Transcript');
            jQuery('#script_container_updated').css('height','0px');
        }
   });
});