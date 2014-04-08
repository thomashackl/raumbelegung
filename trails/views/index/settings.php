 <ul>
    <? foreach ($resources as $resource): ?>
        <?= $this->render_partial('index/settings_tree', array('resource' => $resource)) ?>
    <? endforeach; ?>
</ul>