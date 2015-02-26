<form method="post">
    <?= \Studip\Button::create(dgettext('roomplanplugin', 'Speichern'), 'save') ?>
    <ul class="sortable resources">
        <? foreach ($resources as $resource): ?>
            <?= $this->render_partial('index/settings_tree', array('resource' => $resource)) ?>
        <? endforeach; ?>
    </ul>
</form>