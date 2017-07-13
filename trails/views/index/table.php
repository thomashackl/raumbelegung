<form id="dateform" class="studip_form" method="get">
    <label>
        <?= dgettext('roomplanplugin', 'Datum') ?>:
        <input name="date" id="date" value="<?= $date ?>"></input>
    </label>
    <?= Studip\Button::create(dgettext('roomplanplugin', "Anzeigen"), 'submitButton') ?>
    <span id="datehint"><?= dgettext('roomplanplugin', 'Datumsformat tt.mm.yyyy'); ?></span>
</form>
<br>

<?= print_table($room) ?>

<?

function print_table($room) {
    ?>
    <table class="zim_daytable" border="1" width="800">
        <tr>
            <th>
                <?= $room->name ?><br> 
                (<?= $room->getDate() ?>)
            </th>
            <? foreach ($room->termine as $termin): ?>
            <tr>
                <td>
                    <?= $termin->display ?>
                    <?php if ($termin->info) : ?>
                        <br>
                        <?= formatReady($termin->info) ?>
                    <?php endif ?>
                </td>
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
