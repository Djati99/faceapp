var FormRepeater = function () {

    return {
        //main function to initiate the module
        init: function () {
        	$('.mt-repeater').each(function(){
                $(this).repeater({
					initEmpty: true,
        			show: function () {
	                	$(this).slideDown();
                        $('.date-picker').datepicker({
                            rtl: App.isRTL(),
                            orientation: "left",
                            autoclose: true
						});
						$('.select2-container').remove();
						$('.select2').select2({
							placeholder: "Select an option...",
							allowClear: true,
							
						});
						$('.select2-container').css('width','100%');
						change_uom();
		            },

		            hide: function (deleteElement) {
		                if(confirm('Are you sure you want to delete this purchase request item?')) {
		                    $(this).slideUp(deleteElement);
		                }
					},
				
		            ready: function (setIndexes) {

		            }

        		});
        	});
        }

    };

}();

jQuery(document).ready(function() {
    FormRepeater.init();
});
