<?php if ($topFolder) : ?>
    <table class="default">
        <caption>
            <?= dgettext('roomplanplugin', 'CSV-Exporte der Raumbelegungen') ?>
        </caption>
        <colgroup>
            <col>
            <col>
            <col width="16">
        </colgroup>
        <thead>
            <tr>
                <th><?= dgettext('roomplanplugin', 'Exportierter Zeitraum') ?></th>
                <th><?= dgettext('roomplanplugin', 'Datenstand') ?></th>
                <th><?= dgettext('roomplanplugin', 'Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($topFolder->getFiles() as $file) : ?>
                <tr>
                    <td>
                        <?= htmlReady($file->name) ?>
                    </td>
                    <td><?= htmlReady($file->description) ?></td>
                    <td>
                        <a href="<?= $controller->url_for('export/files/delete', $file->id) ?>"
                           data-confirm="<?= dgettext('roomplanplugin',
                               'Soll die Exportdatei wirklich gelöscht werden?') ?>">
                            <?= Icon::create('trash', 'clickable',
                                ['title' => dgettext('roomplanplugin', 'Exportdatei löschen')]) ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif;
