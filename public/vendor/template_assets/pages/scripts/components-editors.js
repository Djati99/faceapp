var ComponentsEditors = function () {
    
    var handleWysihtml5 = function () {
        if (!jQuery().wysihtml5) {
            return;
        }

        if ($('.wysihtml5').size() > 0) {
            $('.wysihtml5').wysihtml5({
                "stylesheets": ["../assets/global/plugins/bootstrap-wysihtml5/wysiwyg-color.css"]
            });
        }
    }

    var handleSummernote = function () {
        $('.summernote').summernote({
            height: 300,
        });

        $('.summernote_nostyle').summernote({
            height: 300,
            toolbar: [
                // [groupName, [list of button]]
                ['style', ['bold', 'italic', 'underline']],
               // ['font', ['strikethrough', 'superscript', 'subscript']],
               // ['fontsize', ['fontsize']],
              //   ['color', ['color']],
              //   ['para', ['ul', 'ol', 'paragraph']],
              //   ['height', ['height']]
              ]
        });
        //API:
        //var sHTML = $('#summernote_1').code(); // get code
        //$('#summernote_1').destroy(); // destroy
    }

    return {
        //main function to initiate the module
        init: function () {
            handleWysihtml5();
            handleSummernote();
        }
    };

}();

jQuery(document).ready(function() {    
   ComponentsEditors.init(); 
});