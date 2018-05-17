<?php if (count($rooms) > 0) : ?>
    <form class="default" action="<?= $controller->url_for('openingtimes/store_rooms') ?>" method="post">
        <table class="default">
            <colgroup>
                <col>
                <col width="20">
            </colgroup>
            <thead>
                <tr>
                    <th><?= dgettext('roomplanplugin', 'Raum') ?></th>
                    <th><?= dgettext('roomplanplugin', 'Aktion') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rooms as $room) : ?>
                    <tr>
                        <td><?= htmlReady($room->name) ?></td>
                        <td>
                            <a href="<?= $controller->url_for('openingtimes/unassign_room', $room->id) ?>"
                               data-confirm="<?= dgettext('roomplanplugin',
                                   'Möchten Sie die Raumzuordnung wirklich entfernen?') ?>">
                                <?= Icon::create('trash', 'clickable',
                                    ['title' => dgettext('roomplanplugin', 'Raumzuordnung entfernen')]) ?>
                            </a>
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
    <?= MessageBox::info(dgettext('roomplanplugin', 'Kein Raum nutzt die Gebäudeöffnungszeiten.')) ?>
<?php endif ?>
<form class="default" action="<?= $controller->url_for('openingtimes/assign_room') ?>" method="post">
    <h2>
        <?= dgettext('roomplanplugin', 'Raum hinzufügen, der die Gebäudeöffnungszeiten nutzen soll') ?>
    </h2>
    <section>
        <?= QuickSearch::get('resource_id', new ResourceSearch())->render() ?>
    </section>
    <footer data-dialog-button>
        <?= CSRFProtection::tokenTag() ?>
        <?= Studip\Button::createAccept(dgettext('roomplanplugin', 'Zuweisen'), 'assign') ?>
    </footer>
</form>
