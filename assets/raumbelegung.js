// Open nodes with selected children
function openNode(node) {
    $(node).children('li').each(function(index) {
        if ($(this).find('ul li input[type="checkbox"][name="selected[]"]:checked').length > 0) {
            $(this).children('input[type="checkbox"][id*="manual"]').attr('checked', true)
            $(this).children('input[type="checkbox"][id*="auto"]').attr('checked', true)
            openNode($(this).children('ul'))
        }
    })
}

STUDIP.domReady(function() {
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
        $("ul.can-be-sorted").sortable();
    });

    openNode($('ul.css-tree'))

    $(document).on('dialog-open', () => {
        openNode($('ul.css-tree'))
    })
});

