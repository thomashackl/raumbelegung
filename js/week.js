$(document).ready(function() {
    $('#date').datepicker({
        onSelect: function(date) {
            $('#dateform').submit();
        }});
    $('#datehint').hide();
    $('[name="submitButton"]').hide();
});