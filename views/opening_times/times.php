<?php if (count($buildings) > 0) : ?>
    <form class="default" action="<?= $controller->url_for('openingtimes/store_times') ?>" method="post">
        <table class="default">
            <colgroup>
                <col>
                <col>
                <col>
                <col>
            </colgroup>
            <thead>
                <tr>
                    <th><?= dgettext('roomplanplugin', 'Gebäude') ?></th>
                    <th><?= dgettext('roomplanplugin', 'Montag - Freitag') ?></th>
                    <th><?= dgettext('roomplanplugin', 'Samstag') ?></th>
                    <th><?= dgettext('roomplanplugin', 'Sonntag') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($buildings as $building) : ?>
                <tr>
                    <td><?= htmlReady($building->name) ?></td>
                    <td>
                        <input type="time" name="times[<?= $building->id ?>][weekdays_start]"
                               value="<?= $building->opening_times ? $building->opening_times->weekdays_start : ''?>">
                        -
                        <input type="time" name="times[<?= $building->id ?>][weekdays_end]"
                               value="<?= $building->opening_times ? $building->opening_times->weekdays_end : ''?>">
                    </td>
                    <td>
                        <input type="time" name="times[<?= $building->id ?>][saturday_start]"
                               value="<?= $building->opening_times ? $building->opening_times->saturday_start : ''?>">
                        -
                        <input type="time" name="times[<?= $building->id ?>][saturday_end]"
                               value="<?= $building->opening_times ? $building->opening_times->saturday_end : ''?>">
                    </td>
                    <td>
                        <input type="time" name="times[<?= $building->id ?>][sunday_start]"
                               value="<?= $building->opening_times ? $building->opening_times->sunday_start : ''?>">
                        -
                        <input type="time" name="times[<?= $building->id ?>][sunday_end]"
                               value="<?= $building->opening_times ? $building->opening_times->sunday_end : ''?>">
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
        <footer data-dialog-button>
            <?= CSRFProtection::tokenTag() ?>
            <?= Studip\Button::createAccept(dgettext('roomplanplugin', 'Speichern'), 'store') ?>
        </footer>
    </form>
<?php else: ?>
    <?= MessageBox::info(dgettext('roomplanplugin', 'Es wurden keine Gebäude gefunden.')) ?>
<?php endif;
