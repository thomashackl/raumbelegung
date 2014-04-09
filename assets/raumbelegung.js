$(document).ready(function() {
    $('#date').datepicker({
        onSelect: function(date) {
            $('#dateform').submit();
        }});
    
    $('#buildings').change(function(date) {
        $('#dateform').submit();
    });
    $('#datehint').hide();
    $('[name="submitButton"]').hide();

    // Work all the datepickers
    $('.datepicker').datepicker();

    // Make settings sortable
    $(function() {
        $("ul.sortable").sortable();
    });
});