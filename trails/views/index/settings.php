<form method="post">
    <?= \Studip\Button::create(_('Speichern'), 'save') ?>
    <ul class="resources">
        <? foreach ($resources as $resource): ?>
            <?= $this->render_partial('index/settings_tree', array('resource' => $resource)) ?>
        <? endforeach; ?>
    </ul>
</form> 