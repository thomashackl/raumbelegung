<form action="<?= $controller->url_for('index/save_settings') ?>" method="post">
    <?= \Studip\Button::createAccept(dgettext('roomplanplugin', 'Speichern'), 'save') ?>
    <ul class="sortable resources">
        <? foreach ($resources as $resource): ?>
            <?= $this->render_partial('index/settings_tree', array('resource' => $resource)) ?>
        <? endforeach; ?>
    </ul>
    <?= \Studip\Button::createAccept(dgettext('roomplanplugin', 'Speichern'), 'save') ?>
</form>