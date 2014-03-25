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


    // Adjust width of entries
    /*var width = $('tbody tr th').first().width();
     $('.entry').css('width', width - 2);
     
     $(window).resize(function() {
     var width = $('tbody tr th').first().width();
     $('.entry').css('width', width - 2);
     
     });*/
});