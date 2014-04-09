
<?

use Studip\Button;
?>
<form id="dateform" method="get">
    <?= dgettext('roomplanplugin', 'Belegungsplan für Datum: ') ?>
    <input name="date" id="date" value="<?= $date ?>"></input>
    <?= Button::create(dgettext('roomplanplugin', "Anzeigen"), 'submitButton') ?>
    <span id="datehint"><?= dgettext('roomplanplugin', 'Datumsformat tt.mm.yyyy'); ?></span>
</form>
<script>
    $('#date').datepicker( {
        onSelect: function(date) {
            $('#dateform').submit();
        }});
        $('#datehint').hide();
    $('[name="submitButton"]').hide();
</script>
<ul class="belegungsplan">
    <?= print_list($room) ?>
</ul>

<?

function print_list($room) {
    ?>
    <li><?= $room->name ?> (<?= $room->getDate() ?>)</li>
    <ul>
        <? foreach ($room->termine as $termin): ?>
            <li><?= $termin->display ?></li>
        <? endforeach; ?>
    </ul>
    <ul id="sortable">
        <? foreach ($room->children as $child): ?>
            <? print_list($child) ?>
        <? endforeach; ?>
    </ul>
    <?
}
?>
