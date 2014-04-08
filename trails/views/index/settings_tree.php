<li>
    <label for="<?= $resource->id ?>"><?= htmlReady($resource->name) ?> <?= htmlReady($resource->description) ?></label>
    <input name="resources[]" id="<?= $resource->id ?>" type="checkbox" value="<?= $resource->id ?>" <?= $resource->checked ?>>
    <ul class="sortable">
        <? foreach ($resource->children->orderBy('priority') as $resource): ?>
            <?= $this->render_partial('index/settings_tree', array('resource' => $resource)) ?>
        <? endforeach; ?>
    </ul>
</li>