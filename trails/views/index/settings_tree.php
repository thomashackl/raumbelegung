<li>
    <?= $resource->name ?>
    <ul>
        <? foreach ($resource->getFilteredChildren() as $resource): ?>
            <?= $this->render_partial('index/settings_tree', array('resource' => $resource)) ?>
        <? endforeach; ?>
    </ul>
</li>