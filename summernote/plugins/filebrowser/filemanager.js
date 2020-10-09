(function (factory) {
    /* global define */
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        // Node/CommonJS
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals
        factory(window.jQuery);
    }
}(function ($) {

    // Extends plugins for adding readmore.
    //  - plugin is external module for customizing.
    $.extend($.summernote.plugins, {
        /**
         * @param {Object} context - context object has status of editor.
         */

        'filebrowser': function (context) {
            var self = this;
            var ui = $.summernote.ui;

            // add  button
            context.memo('button.filebrowser', function () {
                // create button
                var button = ui.button({
                    contents: '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-file-earmark-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0H4zm5.5 1.5v2a1 1 0 0 0 1 1h2l-3-3z"/></svg>',
                    tooltip: 'File manager',
                    click: function () {
                        app(),
                        $('#FileModal').modal('show')
                    }
                });

                // create jQuery object from button instance.
                var $btn = button.render();
                return $btn;
            });

            // This methods will be called when editor is destroyed by $('..').summernote('destroy');
            // You should remove elements on `initialize`.
            this.destroy = function () {
                this.$panel.remove();
                this.$panel = null;
            };
        }


    });
    }));

// create modal
function app() {
    // change iframe path for file browser location
    var modalcode = '<div class=\"modal fade\" id=\"FileModal\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"fileModalTitle\" aria-hidden=\"true\"><div class=\"modal-dialog  modal-xl\" role=\"document\"><div class=\"modal-content\"><div class=\"modal-header\"><h4 class="modal-title">File manager</h4><button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;<\/span><\/button><\/div><div class=\"modal-body\"><iframe width=100% height=450px style=\"border:0\" id=\"iframe\" src=\"\summernote\/plugins\/filebrowser\/filemanager.php\" id=\"editoverlayiframe\" class=\"editoverlayiframe\"><\/iframe><\/div><\/div><\/div><\/div>'

    $(modalcode).appendTo("body");

};


function AddFile(imagepath, returnid) {
    // function to add either an image or link to page
    if (imagepath.endsWith("jpg") || imagepath.endsWith("png") || imagepath.endsWith("gif") || imagepath.endsWith("svg")) {
      if (returnid=="false"){
        $('#summernote').summernote('editor.insertImage', imagepath);
      }
      else{
        $('#download_'+returnid).attr("href", imagepath);
        $('#download_'+returnid).removeClass('disabled');
        $('#'+returnid).val(imagepath);
        $('#'+returnid).trigger("change");
        $('#'+returnid+"_preview").attr('src', imagepath).show();
      }
    } else {
      $('#download_'+returnid).attr("href", imagepath);
      $('#download_'+returnid).removeClass('disabled');
      $('#'+returnid).val(imagepath);
      $('#'+returnid).trigger("change");
        $('#summernote').summernote('createLink', {
            text: imagepath,
            url: imagepath,
            isNewWindow: false
        });
    }


    $('#FileModal').modal('hide');
    $('#FileModalSelect').remove();
    $('.modal-backdrop').remove();


}
