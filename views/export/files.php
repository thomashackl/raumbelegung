<?php if ($topFolder && count($topFolder->getFiles()) > 0) : ?>
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
            <?php foreach ($topFolder->getFiles()->orderBy('mkdate DESC') as $file) : ?>
                <?php
                    $split = explode(' - ', $file->name);
                    $start = strtotime($split[0]);
                    $end = strtotime($split[1]);
                    $startDate = date('Y-m-d', $start);
                    $endDate = date('Y-m-d', $end);
                    $filename = 'raumbelegungen-' . $startDate . '-' . $endDate . '.csv';
                ?>
                <tr>
                    <td>
                        <a href="<?= URLHelper::getURL('sendfile.php?type=0&file_id=' . $file->id . '&file_name=' . $filename) ?>">
                            <?= Icon::create('download', 'clickable',
                                ['title' => dgettext('roomplanplugin', 'Datei herunterladen')]) ?>
                            <?= htmlReady($file->name) ?>
                        </a>
                    </td>
                    <td><?= htmlReady($file->description) ?></td>
                    <td>
                        <a href="<?= $controller->url_for('export/delete', $file->id) ?>"
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
<?php elseif ($topFolder && count($topFolder->getFiles()) == 0) : ?>
    <?= PageLayout::postInfo(dgettext('roomplanplugin', 'Es sind keine Dateien vorhanden.')) ?>
<?php endif;
