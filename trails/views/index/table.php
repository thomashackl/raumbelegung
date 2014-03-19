<form id="dateform" method="get">
    <input name="date" id="date" value="<?= $date ?>"></input>
<?= Studip\Button::create(dgettext('roomplanplugin', "Anzeigen"), 'submitButton') ?>
    <span id="datehint"><?= dgettext('roomplanplugin', 'Datumsformat tt.mm.yyyy'); ?></span>
</form>

<?= print_table($room) ?>

<?

function print_table($room) {
    ?>
    <table class="intelec_daytable" border="1" width="800">
        <tr>
            <th>
    <?= $room->name ?><br> 
                (<?= $room->getDate() ?>)
            </th>
    <? foreach ($room->termine as $termin): ?>
            <tr>
                <td><?= $termin->display ?></td>
            </tr>
    <? endforeach; ?>
    </tr>
    <? foreach ($room->children as $child): ?>
        <tr>
            <td>
        <? print_table($child) ?>
            </td>
        </tr>
    <? endforeach; ?>
    </table>
    <?
}
