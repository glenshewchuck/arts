(function($, Drupal) {
  $(document).ready(function() {
    $('.show-pass').change(function() {
      if (this.checked) {
        $('#edit-pass').prop("type", "text");
      } else {
        $('#edit-pass').prop("type", "password");
      }
    });
  });
})(jQuery, Drupal);
