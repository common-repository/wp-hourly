jQuery(document).ready(function () {
	/*check if boostrap is loaded*/
	// var bootstrap_version = jQuery.fn.tooltip.Constructor.VERSION;

	var has_boostrap = false;
    if(!jQuery.fn.modal){
    	has_boostrap = false;
    	jQuery('body').addClass('wph-no-bootstrap');
    }else{
    	has_boostrap = true;
    	jQuery('body').addClass('wph-has-bootstrap');
    }

    
    
	jQuery('.wph-project-details .wph-nav-tabs .wph-nav-link a').on('click',function(e){
		var nav_menu = jQuery(this).parent().parent();
		var parent = jQuery(this).closest('.wph-project-details');
		var target_tab = jQuery(this).attr('data-id');
		
		if(jQuery(this).parent().hasClass('active')){
			//do nothing
			e.preventDefault();

		}else{
			nav_menu.find('.wph-nav-link').removeClass('active');
			jQuery(this).parent().addClass('active');	
			e.preventDefault();
			parent.find('.wph-tab-content .wph-tab-pane').removeClass('active show');
			parent.find('.wph-tab-content .wph-tab-pane[data-tab="'+ target_tab+'"]').addClass('show active');
		}

	});

    

});