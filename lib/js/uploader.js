/**
 * Natural Uplader
 * Depends on ajaxupload.js plugin
 */
/**
 * Ajax Uploader Module
 * Using jquery plugin (http://valums.com/wp-content/uploads/ajax-upload/demo-jquery.htm)
 */
function uploader_add_file(field_id) {
  var preview = '';
  var button = $('#uploader-button-' + field_id), interval;
  new AjaxUpload(button, {
    action: 'modules/uploader/uploader_add_file.php?field_id=' + field_id,
    name: 'myfile',
    onSubmit: function(file) {
      // If you want to allow uploading only 1 file at time,
      this.disable();
      // Uploding -> Uploading. -> Uploading...
      interval = window.setInterval(function() {
        var text = button.text();
      }, 2000);
    },
    onComplete: function(file, response) {
      process_messages();
      preview = '';
      window.clearInterval(interval);
      // enable upload button
      this.enable();
      if (response) {
        response = jQuery.parseJSON(response);
        if (response.id > 0) {
          // Decode html_entities
          var file_item = $('<div/>').html(response.file_item).text();
          // Add file to the list
          $(file_item).appendTo('#uploaded-files-' + field_id)
          // We hide upload button when file limit is reached
          if (response.limit >= $('#uploaded-files-' + field_id + ' .file-item').length) {
            $(button).addClass('hide');
          }
          else {
            $(button).removeClass('hide');
          }
        }
      }
    }
  });
}

/**
 * Ajax Uploader
 * Deleting Uploaded files
 */
function uploader_remove_file(id, field_id) {
  $.ajax({
    url: 'modules/uploader/uploader_remove_file.php?id=' + id,
    dataType: 'json',
    success: function(data) {
      if (data['removed'] == true) {
        $('#file-item-' + id).parent().remove();
        $('#uploader-button-' + field_id).removeClass('hide');
      }
    },
    complete: function() {
      process_messages();
    }
  });
}