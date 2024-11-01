/*
//    ---        SUPER WP HEROES
//    ---        COMMENT: EDIT IN PLACE INPUTS
*/




const addProject = (e) => {
    e.preventDefault();


    var params = {
        title: jQuery('#form-project-title').val(),
        client_id: jQuery('#wph-selected-form-project-client-id').val(),
        status: 'active',
        is_billable: jQuery('[name="is_billable"]').is(':checked') ? 1 : 0,
        action: 'wph_live_edit',
        entity: 'project',
    };
    var fields = ['title', 'client_id', 'status', 'is_billable'];

    if (jQuery('#form-project-estimated-hours').val().trim().length > 0) {
        params.estimated_hours = jQuery('#form-project-estimated-hours').val().trim();
        fields.push('estimated_hours');
    }
    if (jQuery('#form-project-ex-link').val()) {
        params.external_url = jQuery('#form-project-ex-link').val();
        fields.push('external_url');
    }
    if (jQuery('#form-project-deadline').val()) {
        params.deadline = jQuery('#form-project-deadline').val();
        fields.push('deadline');
    }
    if (jQuery('#form-project-hourly-rate').val()) {
        params.hourlyRate = jQuery('#form-project-hourly-rate').val();
        fields.push('hourlyRate');
    }
    if (jQuery('#form-project-description').val()) {
        params.description = jQuery('#form-project-description').val();
        fields.push('description');
    }

    params.fields = fields;

    var spinner = jQuery(this).siblings('.fa-spinner');
    spinner.addClass('d-inline-block');

    if(jQuery('#wph-selected-form-project-client-id').val() == ''){
        jQuery('#wph-selected-form-project-client-id').parent().addClass("form-control is-invalid");
    }

    var forms = document.getElementsByClassName('add-validation');

    var validation = Array.prototype.filter.call(forms, function(form) {
        if (form.checkValidity() === true) {
            jQuery.ajax({
                method: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: params,
                success: function(response) {
                    if (response.success) {
                        // const url = window.location.protocol + "//" + window.location.host + "/" + window.location.pathname.split('/')[1];
                        var arr_split = window.location.pathname.split('/'); 
                        var reconstruct_url = window.location.protocol + "//" + window.location.host ;
                        jQuery.each(arr_split,function(key, value){
                            if(value != '' ){
                                reconstruct_url = reconstruct_url +'/'+ value;
                            }
                            if(value === "wp-admin") {
                                return false;
                            }
                        });
                        var final_url = reconstruct_url+ '/admin.php?page=wph-dashboard&project=' + response.id;
                        spinner.removeClass('d-inline-block');
                        // window.location = url + "/wp-admin/admin.php?page=wph-dashboard&project=" + response.id;
                        window.location = final_url;
        
                        return;
                    }
        
                }
            });
        }
        form.classList.add('was-validated');
    });
}


function updateEntityDetails(element) {
    var input = jQuery(element);

        var value = input.val();



        var spinnerIcon = input.siblings(".fa-spinner");
        var editIcon = input.siblings(".fa-edit");
        var errorIcon = input.siblings(".fa-exclamation-triangle");

        var entityID = input.data('entity-id');

        var entity = input.data('entity');

        var update = input.data('update');

        /*
        //    ---        SUPER WP HEROES
        //    ---        COMMENT: project fields
        */
        if(entity == 'project') {
            if(update == 'title') {
                var params = {
                        id: entityID,
                        title: value,
                        action: 'wph_live_edit',
                        entity: entity,
                    };

                var fields = ['id', 'title' ];
            }
            if(update == 'hourlyRate') {
                var params = {
                        id: entityID,
                        hourlyRate: value,
                        action: 'wph_live_edit',
                        entity: entity,
                    };

                var fields = ['id', 'hourlyRate' ];
            }

            if(update == 'estimated_hours') {
                var params = {
                        id: entityID,
                        estimated_hours: value,
                        action: 'wph_live_edit',
                        entity: entity,
                    };

                var fields = ['id', 'estimated_hours' ];
            }

            if(update == 'deadline') {
                var params = {
                        id: entityID,
                        deadline: value,
                        action: 'wph_live_edit',
                        entity: entity,
                    };

                var fields = ['id', 'deadline' ];
            }

            if(update == 'client_id') {
                var params = {
                        id: entityID,
                        client_id: value,
                        action: 'wph_live_edit',
                        entity: entity,
                    };

                var fields = ['id', 'client_id' ];
            }

            if(update == 'description') {
                var params = {
                        id: entityID,
                        description: value,
                        action: 'wph_live_edit',
                        entity: entity,
                    };

                var fields = ['id', 'description' ];
            }

            if(update == 'external_url') {
                var params = {
                        id: entityID,
                        external_url: value,
                        action: 'wph_live_edit',
                        entity: entity,
                    };

                var fields = ['id', 'external_url' ];
            }
        } // end project entity check

        if(entity == 'task') {

            if (update == 'project_id') {
                var params = {
                    id: entityID,
                    project_id: value,
                    action: 'wph_live_edit',
                    entity: entity,
                };

                var fields = ['id', 'project_id' ];
            }

            if(update == 'assignee_id') {
                var params = {
                        id: entityID,
                        assignee_id: value,
                        action: 'wph_live_edit',
                        entity: entity,
                    };

                var fields = ['id', 'assignee_id' ];
            }

            if(update == 'title') {
                var params = {
                        id: entityID,
                        title: value,
                        action: 'wph_live_edit',
                        entity: entity,
                    };

                var fields = ['id', 'title' ];
            }

            if(update == 'description') {
                var params = {
                        id: entityID,
                        description: value,
                        action: 'wph_live_edit',
                        entity: entity,
                    };

                var fields = ['id', 'description' ];
            }

            if(update == 'external_url') {
                var params = {
                        id: entityID,
                        external_url: value,
                        action: 'wph_live_edit',
                        entity: entity,
                    };

                var fields = ['id', 'external_url' ];
            }

            // if(update == 'external_url') {
            //     var params = {
            //             id: entityID,
            //             external_url: value,
            //             action: 'wph_live_edit',
            //             entity: entity,
            //         };

            //     var fields = ['id', 'external_url' ];
            // }

            if(update == 'is_billable') {
                var params = {
                        id: entityID,
                        is_billable: value,
                        action: 'wph_live_edit',
                        entity: entity,
                    };

                var fields = ['id', 'is_billable' ];
            }

            if(update == 'hourlyRate') {
                var params = {
                        id: entityID,
                        hourlyRate: value,
                        action: 'wph_live_edit',
                        entity: entity,
                    };

                var fields = ['id', 'hourlyRate' ];
            }

            if(update == 'estimated_hours') {
                var params = {
                        id: entityID,
                        estimated_hours: value,
                        action: 'wph_live_edit',
                        entity: entity,
                    };

                var fields = ['id', 'estimated_hours' ];
            }

            if(update == 'deadline') {
                var params = {
                        id: entityID,
                        deadline: value,
                        action: 'wph_live_edit',
                        entity: entity,
                    };

                var fields = ['id', 'deadline' ];
            }

        } // end task entity check

        params.fields = fields;

        //console.log(params);

        jQuery.ajax({
            method: 'POST',
            url: ajaxurl,
            dataType: 'json',
            data: params,
            beforeSend: function() {
                spinnerIcon.show();
                editIcon.hide();
            },
            success: function(response) {
                if(response.success == false) {
                    errorIcon.show();
                    editIcon.hide();
                    console.log(response);
                } else {
                    errorIcon.hide();
                    editIcon.show();
                    if (update == 'project_id') {
                        window.location.reload();
                    }

                    if (entity == 'task' && params.fields.includes('title')) {
                        jQuery('.task-title-container[data-task="' + entityID + '"]').text(value);
                    }
                }
            },
            error: function(){
                errorIcon.show();
                editIcon.hide();
                console.log(response);
            },
            complete: function(){
                spinnerIcon.hide();
                editIcon.removeAttr("style");
            }
        });
}

jQuery(document).ready(function () {

/*
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    *******
//    *******
//    *******
//    *******
//    *******        SUPER WP HEROES
//    *******        SECTION NAME: MISC
//    *******        DESCRIPTION:
//    *******
//    *******
//    *******
//    *******
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
*/  
    
    
    jQuery('<div id="wph_task_modal_loading"><div class="wph-modal-loading_centered"><div class="lds-ellipsis "><div></div><div></div><div></div><div></div></div></div></div>').appendTo('body');
    

    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: enable bootstrap tooltips
    */
    jQuery('[data-toggle="tooltip"]').tooltip();


    /*
    //  ---     SUPER WP HEROES
    //  ---     COMMENT: filter out sortable elements
    */

    jQuery('.searchable').each(function () {
        jQuery(this).attr('data-search-term', jQuery(this).text().toLowerCase());
    });

    jQuery('body').on('keyup', '.live-search-box', function () {
        //jQuery('.live-search-box').on('keyup', function(){
        
        var searchTerm = jQuery(this).val().toLowerCase();
        var searchTarget = jQuery(this).attr('data-search-target');

        jQuery('[data-search-result="' + searchTarget + '"]').each(function () {

            if (jQuery(this).filter('[data-search-term *= "' + searchTerm + '"]').length > 0 || searchTerm.length < 1) {
                jQuery(this).show();
            } else {
                jQuery(this).hide();
            }

        });
    });

    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: all elements in a list / column (ex. tasks, users, etc)
    */

    jQuery("body").on("click", ".wph-show-all", function () {
        var showTarget = jQuery(this).data("show-all-target");
        console.log(showTarget);
        jQuery('[data-show-all-result="' + showTarget + '"').toggle();

    });


    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: LOAD TASK MODAL
    */

    // jQuery('.btn-action').click(function(){
    //     var url = $(this).data("url");
    //     jQuery.ajax({
    //         type: "GET",
    //         url: url,
    //         dataType: 'json',
    //         success: function(res) {

    //             // get the ajax response data
    //             var data = res.body;
    //             // update modal content
    //             jQuery('.modal-body').text(data.someval);
    //             // show modal
    //             jQuery('#myModal').modal('show');

    //         },
    //         error:function(request, status, error) {
    //             console.log("ajax call went wrong:" + request.responseText);
    //             console.log(error);
    //         }
    //     });
    // });



/*
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    *******
//    *******
//    *******
//    *******
//    *******        SUPER WP HEROES
//    *******        SECTION NAME:  GLOBAL INPUTS MANAGEMENT
//    *******        DESCRIPTION:
//    *******
//    *******
//    *******
//    *******
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ******************************** ****************************************************************************************************
*/

    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: UPDATE FIELD VALUE
    */


    jQuery(document).on('blur', '#ddd', function() {
        e.preventDefault();

        var field = jQuery(this).data('field');
    });

    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: CHECKBOX UPDATE
    */
    jQuery(document).on('click', 'label.switch span.switch-label', function() {

        //e.preventDefault();

        var is_ajax = jQuery(this).data("is-ajax");

        if(is_ajax == 'yes') {

            var checkbox = jQuery(this).siblings(".switch-input");
            var spinner = jQuery(this).siblings(".fa-spinner");
            var entityID = checkbox.data('id');
            var entity = checkbox.data('entity');
            var update = checkbox.data('update');

            spinner.show();

            if(update == "status") {
                if(checkbox.is(':checked')) {
                    var status = 'archived';
                } else {
                    var status = 'active';
                }
                var params = {
                    id: entityID,
                    status: status,
                    action: 'wph_live_edit',
                    entity: entity,
                };


                var fields = ['id','status'];
            }

            if(update == "is_billable") {
                if(checkbox.is(':checked')) {
                    var is_billable = 0;
                } else {
                    var is_billable = 1;
                }
                var params = {
                    id: entityID,
                    is_billable: is_billable,
                    action: 'wph_live_edit',
                    entity: entity,
                };


                var fields = ['id','is_billable'];
            }



            params.fields = fields;

            jQuery.ajax({
                method: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: params,
                success: function(response) {
                    spinner.hide();
                    console.log(response);
                    if(response.success == false) {
                        spinner.hide();
                        alert('Could not update project; please check console.log for errors');
                        console.log(response);

                        return;
                    }

                    if (fields.includes('status')) {
                        jQuery('.add-task, #wph-project-delete').toggle();
                    }
                },
                error: function(){
                    spinner.hide();
                    alert('Could not update project; please check console.log for errors');
                    console.log(response);
                }
            });
        } // end ajax check
    });

    jQuery('#wph-project-delete-approval').on('keyup', function() {
        jQuery('#delete-project-button').prop('disabled', jQuery(this).val().toLowerCase() !== 'delete project');
    });

    jQuery('#delete-project-button').on('click', function(e) {
        e.preventDefault();

        if (!confirm(pw_script_vars.project_err03)) {
            return;
        }

        jQuery.ajax({
            method: 'POST',
            url: ajaxurl,
            dataType: 'json',
            data: {
                action: 'wph_delete_project',
                id: jQuery(this).data('id'),
            },
            success: function(response) {
                if (!response.success) {
                    // alert('Could not delete project; please check console.log for errors');
                    alert(pw_script_vars.project_err01);
                    console.log(e);
                }

                window.location.search = window.location.search.replace(/project=\d*/, '');
            },
            error: function(e){
                 if(e.responseText =='working'){
                    // alert('Cannot delete, someone is working on a task');
                    alert(pw_script_vars.project_err02);

                }else{
                    // alert('Could not delete project; please check console.log for errors');
                    alert(pw_script_vars.project_err01);
                }
                
                console.log(e);
            }
        });
    })

    // trigger edit in place actions
    jQuery(document).on('keypress blur change','.eip, .sip', function(e) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        var change =  (event.type);
        var element = jQuery(this);
        var elementType = element.attr('type');
        console.log(elementType);
        if((keycode == '13' && elementType != 'textarea') || change == 'change') {
            updateEntityDetails(element);
        }
    });

    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: update selected element (user / project / task etc)
    */
    jQuery("body").on("click", ".wph-user-select .wph-update-user-select", function (e) {

        var $selectedUser = jQuery(this);
        var element = $selectedUser.parents('.dropdown').children('.wph-selected-user');
        var is_ajax = element.data('is-ajax');

        var entity = element.data('entity');
        var update = element.data('update');
        if (entity == 'task' && update == 'project_id' && !confirm(pw_script_vars.project_err03)) {
            return;
        }

        var userID = $selectedUser.data('select-user-id');
        var userNN = $selectedUser.data('select-user-nicename');
        var userAV = $selectedUser.data('select-user-avatar');
        console.log($selectedUser);

        window.le = e;
        // selectedUser.parents('.dropdown').addClass('akm');
        element.val(userID);
        console.log(element);
        $selectedUser.parents('.dropdown').find('.wph-selected-user-nicename').html(userNN);

        $selectedUser.parents('.dropdown').find('.wph-selected-user-avatar').attr("src", userAV);
        $selectedUser.parents('.dropdown').removeClass('form-control is-invalid');
        jQuery('#wph-selected-form-project-client-id').parent().removeClass("form-control is-invalid");


        if (is_ajax == 'yes') {
            updateEntityDetails(element);
        }
    })



/*
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    *******
//    *******
//    *******
//    *******
//    *******        SUPER WP HEROES
//    *******        SECTION NAME:  PROJECTS MANAGEMENT
//    *******        DESCRIPTION:
//    *******
//    *******
//    *******
//    *******
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
*/

    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: ADD PROJECT MODAL
    */
   jQuery('#wph-add-project-holder').hide();
    jQuery("#wph-open-add-project-modal").click(function () {
        jQuery('#wph-add-project-holder').modal('show');
    });
    jQuery("#wp-admin-bar-add-project").click(() => {
        jQuery('#wph-add-project-holder').modal('show');
    })
    jQuery('#add-project-button').on('click', e => addProject(e));

    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: enable sorting tasks in columns (change status, reassign)
    */
    jQuery('.sortable').sortable({
        items: "li:not(.ui-state-disabled)",
        stop: function (event, ui) {
            // do stuff
        },
        receive: function(event, ui) {
            var task = ui.item;
            var entity = ui.item.data('entity');
            var entityID = ui.item.data('entity-id');
            var newEntityStatus = task.closest('ul').data('update-task-status');

             var params = {
                id: entityID,
                status_id: newEntityStatus,
                action: 'wph_live_edit',
                entity: entity,
            };


            var fields = ['id','status_id'];

            params.fields = fields;

            jQuery.ajax({
                method: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: params,
                success: function(response) {
                    //spinner.hide();
                    console.log(response);
                    if(response.success == false) {
                        //spinner.hide();
                        // alert('Could not update project; please check console.log for errors');
                        alert(pw_script_vars.project_err04);
                        console.log(response);
                    }
                },
                error: function(){
                    //spinner.hide();
                    // alert('Could not update project; please check console.log for errors');
                    alert(pw_script_vars.project_err04);
                    console.log(response);
                }
            });
        },
        connectWith: ".sortable"
    });





/*
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    *******
//    *******
//    *******
//    *******
//    *******        SUPER WP HEROES
//    *******        SECTION NAME:  TASKS MANAGEMENT
//    *******        DESCRIPTION:
//    *******
//    *******
//    *******
//    *******
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
*/

    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: ADD TASK
    */


    jQuery(document).on('click', '#add-task-button-menu', (e) => {
        e.preventDefault();
        const project_id = jQuery('[data-entity-id="form-select-project"]').val();
        console.log(jQuery(`#project_id_${project_id}`));
        var params = {
            title: jQuery('#form-task-title-menu').val(),
            project_id: project_id,
            client_id: jQuery(`#project_id_${project_id}`).val(),
            status_id: jQuery('#task-add-status-id-menu').val(),
            is_billable: jQuery('[name="is_billable"]').is(':checked') ? 1 : 0,
            action: 'wph_live_edit',
            entity: 'task',
        };
        var fields = ['title', 'project_id', 'client_id', 'status_id', 'is_billable'];

        if (jQuery('#form-task-estimated-hours-menu').val().trim().length > 0) {
            params.estimated_hours = jQuery('#form-task-estimated-hours-menu').val().trim();
            fields.push('estimated_hours');
        }
        if (jQuery('#wph-selected-task-assignee-menu').val()) {
            params.assignee_id = jQuery('#wph-selected-task-assignee-menu').val().trim();
            fields.push('assignee_id');
        }
        if (jQuery('#form-task-deadline-menu').val()) {
            params.deadline = jQuery('#form-task-deadline-menu').val();
            fields.push('deadline');
        }
        if (jQuery('#form-task-hourly-rate-menu').val()) {
            params.hourlyRate = jQuery('#form-task-hourly-rate-menu').val();
            fields.push('hourlyRate');
        }
        if (jQuery('#form-task-description-menu').val()) {
            params.description = jQuery('#form-task-description-menu').val();
            fields.push('description');
        }
        if (jQuery('#form-task-ex-link-menu').val()) {
            params.external_url = jQuery('#form-task-ex-link-menu').val();
            fields.push('external_url');
        }

        params.fields = fields;

        var forms = document.getElementsByClassName('add-task-modal-validation');

        if(jQuery('#wph-selected-task-assignee-menu').val() == 0){
            jQuery('#wph-selected-task-assignee-menu').parent().addClass("form-control is-invalid");
        }

        
        var validation = Array.prototype.filter.call(forms, function(form) {
            if (form.checkValidity() === true) {
                jQuery('#add-task-button-menu').prop('disabled', true);
                jQuery.ajax({
                    method: 'POST',
                    url: ajaxurl,
                    dataType: 'json',
                    data: params,
                    success: function(response) {
                            if(response.success == false) {
                                jQuery('#add-task-button-menu').prop('disabled', false);

                                // alert('Could not create the task; please check console.log for errors');
                                console.log(response);
                            }
                        if (response.success) {
                            var arr_split = window.location.pathname.split('/'); 
                            var reconstruct_url = window.location.protocol + "//" + window.location.host ;
                            jQuery.each(arr_split,function(key, value){
                                if(value != '' ){
                                    reconstruct_url = reconstruct_url +'/'+ value;
                                }
                                if(value === "wp-admin") {
                                    return false;
                                }
                            });
                            
                            var final_url = reconstruct_url+ '/admin.php?page=wph-dashboard&project=' + project_id;
                            // const url = window.location.protocol + "//" + window.location.host + "/" + window.location.pathname.split('/')[1];
                            // window.location = url + "/wp-admin/admin.php?page=wph-dashboard&project=" + project_id;
                            window.location = final_url;
        
                            return;
                        }
        
                        // do something with `response.errors` (array of errors)
                    }
                });
            }
            form.classList.add('was-validated');
        });
    });
    jQuery(document).on('click', '#add-task-button', function(e) {
        e.preventDefault();

        var params = {
            title: jQuery('#form-task-title').val(),
            project_id: jQuery('#task-add-project-id').val(),
            client_id: jQuery('#task-add-client-id').val(),
            status_id: jQuery('#task-add-status-id').val(),
            is_billable: jQuery('[name="is_billable"]').is(':checked') ? 1 : 0,
            action: 'wph_live_edit',
            entity: 'task',
        };
        var fields = ['title', 'project_id', 'client_id', 'status_id', 'is_billable'];

        if (jQuery('#form-task-estimated-hours').val().trim().length > 0) {
            params.estimated_hours = jQuery('#form-task-estimated-hours').val().trim();
            fields.push('estimated_hours');
        }
        if (jQuery('#wph-selected-task-assignee').val()) {
            params.assignee_id = jQuery('#wph-selected-task-assignee').val().trim();
            fields.push('assignee_id');
        }
        if (jQuery('#form-task-deadline').val()) {
            params.deadline = jQuery('#form-task-deadline').val();
            fields.push('deadline');
        }
        if (jQuery('#form-task-hourly-rate').val()) {
            params.hourlyRate = jQuery('#form-task-hourly-rate').val();
            fields.push('hourlyRate');
        }
        if (jQuery('#form-task-description').val()) {
            params.description = jQuery('#form-task-description').val();
            fields.push('description');
        }
        if (jQuery('#form-task-ex-link').val()) {
            params.external_url = jQuery('#form-task-ex-link').val();
            fields.push('external_url');
        }

        params.fields = fields;

        var forms = document.getElementsByClassName('add-task-validation');

        if(jQuery('#wph-selected-task-assignee').val() == 0){
            jQuery('#wph-selected-task-assignee').parent().addClass("form-control is-invalid");
        }

        var validation = Array.prototype.filter.call(forms, function(form) {
            if (form.checkValidity() === true) {
                jQuery('#add-task-button').prop('disabled', true);
                jQuery.ajax({
                    method: 'POST',
                    url: ajaxurl,
                    dataType: 'json',
                    data: params,
                    success: function(response) {
                            if(response.success == false) {
                                jQuery('#add-task-button').prop('disabled', false);
                                // alert('Could not create the task; please check console.log for errors');
                                console.log(response);
                            }
                        if (response.success) {
                            window.location.reload();
        
                            return;
                        }
        
                        // do something with `response.errors` (array of errors)
                    }
                });
            }
            form.classList.add('was-validated');
        });
    });

    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: ADD TASK ???
    */



    jQuery('#time-select-project').on('change', (e) => {
        console.log(jQuery('#time-select-project').val());
    })
    jQuery('.add-new-task').on('click', (e) => {
        e.preventDefault();

        jQuery('#wph-add-task-holder').modal('show');
    })

    jQuery('#wp-admin-bar-add-time-records').on('click', (e) => {
        e.preventDefault();


        jQuery('#wph-add-manual-time').modal('show');
    })

    jQuery('.add-task').on('click', function(e) {
        e.preventDefault();
        jQuery('#task-details').html('<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>');

        jQuery('#add-task-modal-title').css('display','block');
        jQuery('#task-tabs').css('display','none');

        jQuery('#wph-task-holder').modal('show');

        var projectId = jQuery(this).data('project-id');
        var status = jQuery(this).data('status-id');
        jQuery.ajax({
            method: 'GET',
            url: ajaxurl,
            dataType: 'html',
            data: {
                action: 'wph_load_task_add_form',
                project_id: projectId,
                status_id: status,
            },
            success: function(response) {
                jQuery('#task-details').html(response);
            }
        });
    });

    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: LOAD TASK
    */



    jQuery(document).on('click', '.load-task', function () {
        jQuery('#task-details').html('<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>');
        jQuery('#wph_task_modal_loading').show();

        jQuery('#add-task-modal-title').css('display','none');
        jQuery('#task-tabs').css('display','flex');

        jQuery('#wph-task-holder').modal('show');

        //remove if it's already there (convert to a function and remove existing events?)
        jQuery('#hourly-breakdown').remove();

        var taskId = jQuery(this).data('task');
        var projectId = jQuery(this).data('project');
        jQuery.ajax({
            method: 'GET',
            url: ajaxurl,
            dataType: 'html',
            data: {
                action: 'wph_load_task_edit_form',
                task: taskId,
            },
            success: function(response) {
                jQuery('#wph_task_modal_loading').hide();
                jQuery('#task-details').html(response);
                jQuery('[data-toggle="tooltip"]').tooltip();
                jQuery('#hourly-breakdown').appendTo(jQuery('body'));




                /*change new url location to new one that contains sub-folders*/
                var arr_split = window.location.pathname.split('/'); 
                // var reconstruct_url = window.location.protocol + "//" + window.location.host ;
                var reconstruct_url = '';
                var arr_pos = arr_split.indexOf('wp-admin') - 1;

                jQuery.each(arr_split,function(key, value){
                    if(value != '' ){
                        reconstruct_url = reconstruct_url +'/'+ value;
                    }
                    if(key === arr_pos) {
                        return false;
                    }
                });
                var final_url = reconstruct_url+ '/';
                
                /*END change new url location to new one sub-folders*/
                

                var url = new URL(window.location);
                
                // var url = new URL(window.location);
                url.pathname = reconstruct_url + '/wp-admin/admin.php';
                url.searchParams.set('page', 'wph-dashboard');
                url.searchParams.set('project', projectId);
                url.searchParams.set('task', taskId);

                window.history.pushState({}, null, url.toString());
                window.wphWentBack = false;
            }
        });
    });

    setupBackHandlers();

    jQuery(document).on('click', '#hourly-breakdown-left', function() {
        var activeBreakdown = jQuery('#hourly-breakdown > .row:visible');
        var previousBreakdown = activeBreakdown.prev('.row');
        if (!previousBreakdown.length) {
            previousBreakdown = jQuery('#hourly-breakdown .row:last');
        }

        activeBreakdown.hide();
        previousBreakdown.show();

        loadBreakdownScreenshots(previousBreakdown);
    });

    function loadBreakdownScreenshots(container) {
        container.find('.wph-time-record-thumbnail[data-bg-image]').each(function() {
            var img = document.createElement('img');
            var placeholder = this;
            img.onload = function() {
                jQuery(placeholder)
                    .css('background-image', 'url(' + img.src + ')')
                    .removeAttr('data-bg-image')
                    .find('.time-record-holder')
                    .html('');
            }
            img.src = jQuery(this).data('bg-image');
        });
    }

    jQuery(document).on('click', '#hourly-breakdown-right', function() {
        var activeBreakdown = jQuery('#hourly-breakdown > .row:visible');
        var nextBreakdown = activeBreakdown.next('.row');
        if (!nextBreakdown.length) {
            nextBreakdown = jQuery('#hourly-breakdown .row:first');
        }

        activeBreakdown.hide();
        nextBreakdown.show();

        loadBreakdownScreenshots(nextBreakdown);
    });

    jQuery(document).on('click', '.hourly-breakdown-trigger', function() {
        jQuery('#hourly-breakdown').show();
        jQuery('#hourly-breakdown .row[data-index="' + jQuery(this).data('index') + '"]').show();
        loadBreakdownScreenshots(jQuery('#hourly-breakdown .row[data-index="' + jQuery(this).data('index') + '"]'));
    });

    jQuery(document).on('click', '#hourly-breakdown-close', function() {
        jQuery('#hourly-breakdown').hide();
        jQuery('#hourly-breakdown > .row').hide();
    });

    //time record selection
    jQuery(document).on('change', '.hourly-select-all', function() {
        var isChecked = jQuery(this).is(':checked');
        jQuery('.time-record-breakdown-checkbox[data-index="' + jQuery(this).data('index') + '"]')
            .prop('checked', isChecked);

        if (isChecked && !jQuery('.hourly-select-all').not(':checked').length) {
            jQuery('#select-all-time-records').prop('checked', true);
        } else {
            jQuery('#select-all-time-records').prop('checked', false);
        }

        if (isChecked) {
            jQuery('.selected-tr-warning[data-index=' + jQuery(this).data('index') + ']').show();
        } else {
            jQuery('.selected-tr-warning[data-index=' + jQuery(this).data('index') + ']').hide();
        }

        jQuery('#tr-selection-go').prop(
            'disabled',
            !(jQuery('#tr-selection-action').val() && jQuery('.time-record-breakdown-checkbox:checked').length)
        );
    });
    jQuery(document).on('change', '.time-record-breakdown-checkbox', function() {
        var isChecked = jQuery(this).is(':checked');
        if (isChecked &&
            !jQuery('.time-record-breakdown-checkbox[data-index="' + jQuery(this).data('index') + '"]').not(':checked').length) {
            jQuery('.hourly-select-all[data-index="' + jQuery(this).data('index') + '"]').prop('checked', true);
            if (!jQuery('.hourly-select-all').not(':checked').length) {
                jQuery('#select-all-time-records').prop('checked', true);
            }
        } else if (!isChecked) {
            jQuery('.hourly-select-all[data-index="' + jQuery(this).data('index') + '"]').prop('checked', false);
            jQuery('#select-all-time-records').prop('checked', false);
        }

        if (jQuery('.time-record-breakdown-checkbox[data-index="' + jQuery(this).data('index') + '"]:checked').length) {
            jQuery('.selected-tr-warning[data-index=' + jQuery(this).data('index') + ']').show();
        } else  {
            jQuery('.selected-tr-warning[data-index=' + jQuery(this).data('index') + ']').hide();
        }

        jQuery('#tr-selection-go').prop(
            'disabled',
            !(jQuery('#tr-selection-action').val() && jQuery('.time-record-breakdown-checkbox:checked').length)
        );
    });
    jQuery(document).on('change', '#select-all-time-records', function() {
        jQuery('.hourly-select-all, .time-record-breakdown-checkbox').prop('checked', jQuery(this).is(':checked'));
        if (jQuery(this).is(':checked')) {
            jQuery('.selected-tr-warning').show();
        } else {
            jQuery('.selected-tr-warning').hide();
        }

        jQuery('#tr-selection-go').prop(
            'disabled',
            !(jQuery('#tr-selection-action').val() && jQuery('.time-record-breakdown-checkbox:checked').length)
        );
    });

    jQuery(document).on('change', '#tr-selection-action', function() {
        if (jQuery('.time-record-breakdown-checkbox:checked').length) {
            jQuery('#tr-selection-go').prop('disabled', false);
        }

        if (jQuery(this).val() == 'delete') {
            jQuery('#tr-selection-go').removeClass('btn-primary').addClass('btn-danger');
        } else {
            jQuery('#tr-selection-go').removeClass('btn-danger').addClass('btn-primary');
        }
    });

    jQuery(document).on('click', '#tr-selection-go', function(e) {
        e.preventDefault();

        let confirmation = 0;
        var confirmTextOptions = {
            'paid-in': 'Are you sure you want to mark as paid in %d time record%s?',
            'paid-out': 'Are you sure you want to mark as paid out %d time record%s?',
            'unpaid-in': 'Are you sure you want to mark as unpaid in %d time record%s?',
            'unpaid-out': 'Are you sure you want to mark as unpaid out %d time record%s?',
            'delete': 'Are you sure you want to delete %d time record%s?',
        };
        var selectedTimeRecordsCount = jQuery('.time-record-breakdown-checkbox:checked').length;
        var confirmText = confirmTextOptions[jQuery('#tr-selection-action').val()]
            .replace('%d', selectedTimeRecordsCount)
            .replace('%s', selectedTimeRecordsCount == 1 ? '' : 's');


        Swal.fire({
            icon: 'question',
            title: pw_script_vars.confirm_1,
            // title:'Are you sure?',
            text: confirmText,
            showDenyButton: true,
            // confirmButtonText: `Save`,
            confirmButtonText: pw_script_vars.confirm_2,
            // denyButtonText: `Don't save`,
            denyButtonText: pw_script_vars.confirm_3,
        }).then((result) => {
            if(result.isConfirmed) {
                var params = {
                    action: 'wph_' + jQuery('#tr-selection-action').val(),
                    'time-records': jQuery('.time-record-breakdown-checkbox:checked').map(function() {
                        return this.value;
                    }).get(),
                }
                console.log(params);
        
                jQuery.ajax({
                    method: 'POST',
                    url: ajaxurl,
                    dataType: 'json',
                    data: params,
                    beforeSend: function() {
                        // jQuery('#add-manual-time-response').html('<i class="fa fa spinner fa-spin"></i>');
                    },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                icon: 'success',
                                // title: 'Succes!',
                                title: pw_script_vars.confirm_5,
                                // text: __('The record has been updated successfully!','wphourly')
                                text: pw_script_vars.reccord_msg_1
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                // title: 'Error!',
                                title: pw_script_vars.confirm_6,
                                // text: __('Something went wrong! Check the console.','wphourly')
                                text: pw_script_vars.reccord_msg_2
                            });
                            console.log(response);
                        }
                    },
                    error: function(){
                        // jQuery('#add-manual-time-response').html('<p class="alert alert-danger">Cound not add time record. please check console.log!</p>');
                        // console.log(response);
                        // return;
                    },
                    complete: function() {
        
                    }
                });
            }
        });

        // if (!confirm(confirmText)) {
        //     return;
        // }

    });

    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: RESET ACTIVE TAB WHEN CLOSING TASK MODAL
    */

    jQuery('#wph-task-holder').on('hide.bs.modal', function () {
      jQuery('#task-tabs li:first-child a', this).trigger('click');
    })



/*
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    *******
//    *******
//    *******
//    *******
//    *******        SUPER WP HEROES
//    *******        SECTION NAME:  TIME RECORDS MANAGEMENT
//    *******        DESCRIPTION:
//    *******
//    *******
//    *******
//    *******
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
//    ************************************************************************************************************************************
*/

    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: LOAD TIME RECORDS
    */

    jQuery(document).on('click', '.nav-link', function() {

        var is_ajax = jQuery(this).data('is-ajax');
        if(is_ajax == 'yes') {
            loadBreakdownScreenshots(jQuery('#wph-time-records'));
        }
    });

    /*
    //    ---        SUPER WP HEROES
    //    ---        COMMENT: ADD TIME RECORD
    */
    jQuery(document).on('change', '#form-time-record-add-hours , #form-time-record-add-date , #form-time-record-add-time ', function(e) {
        e.preventDefault();
        jQuery(this).removeClass('wph-err-invalid');
        var data_attr = jQuery(this).data('err');
        jQuery('.swph-manual-time-err[data-err="'+data_attr+'"]').slideUp();

    });
    jQuery(document).on('click', '#form-time-record-add', function(e) {
        e.preventDefault();

        var ts = jQuery('#form-time-record-add-date').val() + ' ' + jQuery('#form-time-record-add-time').val();

        
        var params = {
            title: 'Manual time ' + ts,
            task_id: jQuery('#form-time-record-add-task-id').val(),
            client_id: jQuery('#form-time-record-add-client-id').val(),
            hours: jQuery('#form-time-record-add-hours').val(),
            assignee_id: jQuery('#wph-selected-task-assignee-add-time').val(),
            timestamp: ts,
            tr_type: 'manual',
            action: 'wph_live_edit',
            entity: 'timeRecord',
        };
        
        var fields = ['title', 'task_id', 'client_id', 'timestamp', 'hours', 'assignee_id', 'tr_type'];
        
        params.fields = fields;
        
        var forms = document.getElementsByClassName('add-time-record-validation');

        var add_hours = jQuery('#form-time-record-add-hours').val();
        var add_date = jQuery('#form-time-record-add-date').val();
        var add_time = jQuery('#form-time-record-add-time').val();
        var errors = [];
        if(add_hours == ''){
            errors.push('inv_hours');
        }
        if(add_date ==''){
            errors.push('inv_date');
        }
        if(add_time ==''){
            errors.push('inv_time');
        }
        
        if(errors.length  > 0 ){
            jQuery('#form-time-record-add-hours').removeClass('wph-err-invalid');    
            jQuery('#form-time-record-add-date').removeClass('wph-err-invalid');   
            jQuery('#form-time-record-add-time').removeClass('wph-err-invalid');   
            
            jQuery.each(errors,function(e,val){
                
                var err = '';
                if(val == 'inv_hours'){
                    err = jQuery('<div class="swph-manual-time-err" data-err="inv_hours">Hours field is required</div>');
                    jQuery('#form-time-record-add-hours').addClass('wph-err-invalid');    
                    jQuery('#form-time-record-add-hours').data('err', 'inv_hours');    
                }
                if(val == 'inv_date'){
                    err = jQuery('<div class="swph-manual-time-err" data-err="inv_date">Date field is required</div>');
                    jQuery('#form-time-record-add-date').addClass('wph-err-invalid');   
                    jQuery('#form-time-record-add-date').data('err', 'inv_date');    
            
                }
                if(val == 'inv_time'){
                    err = jQuery('<div class="swph-manual-time-err" data-err="inv_time">Time field is required</div>');
                    jQuery('#form-time-record-add-time').addClass('wph-err-invalid');   
                    
                    jQuery('#form-time-record-add-time').data('err', 'inv_time');     
                }
                jQuery('#add-manual-time-response').append(err);
            });

            
        }
        
        if(errors.length  == 0){
            var validation = Array.prototype.filter.call(forms, function(form) {
                console.log(form);
                if (form.checkValidity() === true) {

                    jQuery.ajax({
                        method: 'POST',
                        url: ajaxurl,
                        dataType: 'json',
                        data: params,
                        beforeSend: function() {
                             jQuery('#add-manual-time-response').html('<i class="fa fa spinner fa-spin"></i>');
                        },
                        success: function(response) {
                            if(response.success) {
                                // jQuery('#add-manual-time-response').html('<p class="alert alert-success">Manual time added succesfuly!</p>');
                                jQuery('#add-manual-time-response').html('<p class="alert alert-success">'+pw_script_vars.manual_time_msg_1+'</p>');
                                return;
                            } else {
                                // jQuery('#add-manual-time-response').html('<p class="alert alert-danger">Cound not add time record. please check console.log!</p>');
                                jQuery('#add-manual-time-response').html('<p class="alert alert-danger">'+pw_script_vars.manual_time_msg_2+'</p>');
                                console.log(response);
                                return;
                            }
                        },
                        error: function(){
                            jQuery('#add-manual-time-response').html('<p class="alert alert-danger">'+pw_script_vars.manual_time_msg_1+'</p>');
                            console.log(response);
                            return;
                        },
                        complete: function(){
                        }
                    });
                }
                form.classList.add('was-validated');

            });
        }
    });

    jQuery(document).on('keyup', '#wph-task-delete-approval', function() {
        jQuery('#wph-delete-task-button').prop('disabled', jQuery(this).val().toLowerCase() != 'delete task');
    });

    jQuery(document).on('click', '#wph-delete-task-button', function(e) {
        e.preventDefault();

        jQuery.ajax({
            method: 'POST',
            url: ajaxurl,
            dataType: 'json',
            data: {
                'task_id': jQuery(this).data('task-id'),
                'action': 'wph-remove-task'
            },
            beforeSend: function() {
                // jQuery('#add-manual-time-response').html('<i class="fa fa spinner fa-spin"></i>');
            },
            success: function(response) {
                // console.log('here in succes');
                if(response.success) {
                    window.location.reload();
                } else {
                    alert(response.error);
                }
            },
            error: function(response){
                // console.log(response);
                // console.log('here in error');
            },
            complete: function(response){
            }
        });
    });

});

function setupBackHandlers() {
    if (window.wphDidSetupBackHandlers || !jQuery('#wph-task-holder').length) {
        return;
    }

    jQuery('#wph-task-holder').on('hidden.bs.modal', function (e) {
        if (window.wphWentBack) {
            return;
        }

        window.wphWentBack = true;
        window.history.back();
    });

    window.addEventListener('popstate', (event) => {
        if (window.wphWentBack) {
            return;
        }

        window.wphWentBack = true;

        // var url = new URL(window.location);
        // if (url.searchParams.get('project')) {
        jQuery('#wph-task-holder').modal('hide');
        // }
    });

    // open modal if task id is in url
    var urlOnLoad = new URL(window.location);
    if (urlOnLoad.searchParams.get('task')) {
        var taskIdOnLoad = urlOnLoad.searchParams.get('task');
        jQuery('.load-task[data-task=' + taskIdOnLoad + ']').click();
    }

    window.wphDidSetupBackHandlers = true;
}
