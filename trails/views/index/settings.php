<noscript><h1>Die Administration setzt Javascript vorraus</h1></noscript>
<form id="updateform" method="post">
    <input type="hidden" name="update" id="update" />
    <?= \Studip\Button::create(_('Ändern')) ?>
</form>
<ul id="tt"></ul>  

<script>
$('#tt').tree({  
    data: [<?= $data ?>],
    checkbox: true,
    dnd: true,
    onLoadSuccess: function(node, data) {
$(this).tree('collapseAll', node);
var root = $(this).tree('getRoot');
$(this).tree('expand', root.target);
    }
});

$("#updateform").submit(function() {
    var root = $("#tt").tree('getRoot');
    var nodes = $('#tt').tree("getData", root.target);
    $("#update").val(JSON.stringify(nodes));
});
</script>