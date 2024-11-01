jQuery(document).ready(function () {
    if(Modernizr.getusermedia == false){
        if(Modernizr.datachannel == false && Modernizr.peerconnection == false){
            jQuery( "#alert_browser_support").addClass('show');
        }
        if( Modernizr.datachannel == true ||  Modernizr.peerconnection == true){
            jQuery( ".wp-hourly_page_wph-my-workspace #alert_no_screenshots").addClass('show');
        }
    }
});
jQuery(document).ready(function () {
    

    
    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: dashboard Wp HOURLY PLUS slider
    */

    jQuery('.unpaid-time-record').change(updateUnpaidTotals);

    function updateUnpaidTotals() {
        var markedHours = 0;
        var totalHours = 0;
        var markedCost = 0;
        var totalCost = 0;
        jQuery('.unpaid-time-record').each(function() {
            totalHours += parseFloat(jQuery(this).data('hours'));
            totalCost += parseFloat(jQuery(this).data('cost'));
            if (jQuery(this).is(':checked')) {
                markedHours += parseFloat(jQuery(this).data('hours'));
                markedCost += parseFloat(jQuery(this).data('cost'));
            }
        });
        if (markedHours < 0.05) { //work around the 0.09999 for floats
            jQuery('#mark-unpaid-hours').attr('disabled', true);
        } else {
            jQuery('#mark-unpaid-hours').attr('disabled', false);
        }
        jQuery('#total-marked-unpaid-hours').text(markedHours.toFixed(2));
        jQuery('#total-unpaid-hours').text(totalHours.toFixed(2));
        jQuery('#total-marked-unpaid-hours-cost').text(markedCost.toFixed(2));
        jQuery('#total-unpaid-hours-cost').text(totalCost.toFixed(2));
    }
    // make sure ui is updated
    updateUnpaidTotals();

    jQuery('#mark-unpaid-hours').click(function() {
        jQuery(this).attr('disabled', true);
        var timeRecordsToMark = jQuery('.unpaid-time-record:checked');
        var ids = [];
        timeRecordsToMark.each(function() {
            ids.push(parseInt(jQuery(this).val()));
        });

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'mark-time-records-as-paid',
                records: ids,
                'mark-type': jQuery('#mark-type').val(),
            },
            success: function(response) {
                timeRecordsToMark.parents('tr').remove();
                updateUnpaidTotals();
            }
        });
    });

    setInterval(function () {
        moveRight();
    }, 4000);

    var slideCount = jQuery('#slider ul li').length;
    var slideWidth = jQuery('#slider ul li').width();
    var slideHeight = jQuery('#slider ul li').height();
    var sliderUlWidth = slideCount * slideWidth;

    jQuery('#slider').css({ width: slideWidth, height: slideHeight });

    jQuery('#slider ul').css({ width: sliderUlWidth, marginLeft: - slideWidth });

    jQuery('#slider ul li:last-child').prependTo('#slider ul');

    function moveLeft() {
        jQuery('#slider ul').animate({
            left: + slideWidth
        }, 200, function () {
            jQuery('#slider ul li:last-child').prependTo('#slider ul');
            jQuery('#slider ul').css('left', '');
        });
    }

    function moveRight() {
        jQuery('#slider ul').animate({
            left: - slideWidth
        }, 200, function () {
            jQuery('#slider ul li:first-child').appendTo('#slider ul');
            jQuery('#slider ul').css('left', '');
        });
    }

    jQuery('a.control_prev').click(function () {
        moveLeft();
    });

    jQuery('a.control_next').click(function () {
        moveRight();
    });

    jQuery('body').on('click', '.wph-addon-action', function(e) {
        e.preventDefault();
        var license_key = jQuery('#wph_tracker_license_key').val();
        var action = jQuery(this).data('wph-addon-action');
        var addon = jQuery(this).data('wph-addon-action');
        jQuery.ajax({
            type : "post",
            url : ajaxurl,
            data : {
                action: action,
                addon : addon,
                license_key : license_key
            },
            success: function(response) {
                jQuery('#actmsg').html(response);
            }
        });
    });
    var report_empty = true;
    jQuery('.report-form form[name="run-report-form"]').on('submit',function(e){
        var select_user_elem = jQuery(this).find('#report_select_user');

        if (select_user_elem.length){
            if(select_user_elem.is('select')){
                var select_user = jQuery('#report_select_user').val();
                if(!select_user){
                    jQuery('#select_client').addClass('err_empty');
                    e.preventDefault();
                }
            }
        }
    });
    jQuery(document).on("change", ".report-form #select_project_list", function() {
        jQuery('#select_project').removeClass('err_empty');
    });
    jQuery(document).on("change", ".report-form #report_select_user", function() {
        jQuery('#select_client').removeClass('err_empty');
        
        var userId = jQuery(this).val();
        jQuery('#select_project>*:not(#select_project_loader)').remove();
        jQuery('#select_project_loader').show();

        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                "action" : "wph_get_projects_dropdown",
                "user_id" : userId
            },
            beforeSend: function() {
                // console.log(jQuery(this).val());
            },
            success:function(data) {
                var result_holder = jQuery("#select_project");
                result_holder.append(data);
                // result_holder.append('<div class="err_empty_tooltip">Please Select a Project</div>');

                if (jQuery.fn.select2) {
                    jQuery('#select_project_list').select2({
                        placeholder: 'Select project',
                        width: 200,
                    });
                }
                jQuery('#select_project_loader').hide();
                // var return_view_holder = jQuery("#select_return_view");
                // return_view_holder.show();
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });
        



    });

    if (jQuery.fn.select2) {
        jQuery('#report_select_user').select2({
            placeholder: 'Select user',
            width: 200,
        });

        // task list
        jQuery('#task-list-client').select2({
            placeholder: 'Select Client',
            width: '200',
        });
        jQuery('#task-list-assignee').select2({
            placeholder: 'Select Assignee',
            width: '200',
        });
        jQuery('#task-list-project').select2({
            placeholder: 'Select Project',
            width: '200',
        });

        // tr list
        jQuery('#tr-list-task').select2({
            placeholder: 'Select Task',
            width: '200',
        });

        jQuery('#tr-list-assignee').select2({
            placeholder: 'Select Assignee',
            width: '200',
        });
    }

    var mediaUploaderProfileImage;
    jQuery('#wph-user-profile-image-upload-button').on('click', function(e) {
        e.preventDefault();
        if (mediaUploaderProfileImage) {
            mediaUploaderProfileImage.open();

            return;
        }

        mediaUploaderProfileImage = wp.media({
            title: 'Choose a profile image',
            button: {
                text: 'Choose image',
            },
            library: {
                type: 'image',
            },
            multiple: false,
        });

        mediaUploaderProfileImage.on('select', function() {
            var attachment = mediaUploaderProfileImage.state().get('selection').first().toJSON();

            jQuery('#wph-user-profile-image').val(attachment.id);
            jQuery('#wph-user-profile-image-preview').html('<img src="' + attachment.url + '" style="max-width: 400px; max-height: 400px" /><br/>');
        });

        mediaUploaderProfileImage.open();
    });

    jQuery('#wph-user-profile-image-remove-button').on('click', function(e) {
        e.preventDefault();

        jQuery('#wph-user-profile-image').val('');
        jQuery('#wph-user-profile-image-preview').html('');
    })
});

function saveError(message) {
    alert(message);

    jQuery('#major-publishing-actions .spinner').hide();
    jQuery('#major-publishing-actions').find(':button, :submit, a.submitdelete, #post-preview').removeClass('disabled');
    jQuery("#title").focus();
}
