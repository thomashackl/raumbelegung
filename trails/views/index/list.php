<form id="dateform" method="get">
    <?= dgettext('roomplanplugin', 'Belegungsplan f�r Datum: ') ?>
    <input name="date" id="date" value="<?= $date ?>"></input>
    <?= Studip\Button::create(dgettext('roomplanplugin', "Anzeigen"), 'submitButton') ?>
    <span id="datehint"><?= dgettext('roomplanplugin', 'Datumsformat tt.mm.yyyy'); ?></span>
</form>

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
