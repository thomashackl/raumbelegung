jQuery(function (event) {
    var info = $('div#assign-info');
    var tr = $('<tr>');
    var td = $('<td>');
    tr.append(td.attr('align', 'left').append(info.show())).append($('<td>'));
    var target = $('textarea[name="comment_internal"]').closest('tr');
    tr.insertAfter(target);
});

