<style>
    .belegungsplan {font-size: 20px; font-weight: bolder}
    .belegungsplan ul {font-size: 18px; font-weight: bold}
    .belegungsplan ul ul {font-size: 16px; font-weight: normal}
    .belegungsplan ul ul ul{font-size: 14px;}
</style>
<?

use Studip\Button;
?>
<form id="dateform" method="get">
    <?= _('Belegungsplan für Datum: ') ?>
    <input name="date" id="date" value="<?= $date ?>"></input>
    <?= Button::create(_("Anzeigen"), 'submitButton') ?>
    <span id="datehint"><?= _('Datumsformat tt.mm.yyyy'); ?></span>
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
