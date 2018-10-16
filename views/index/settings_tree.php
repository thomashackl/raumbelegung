<li>
    <label for="<?= $resource->id ?>"><?= htmlReady($resource->name) ?></label>
    <input type="hidden" name="resources[<?= $resource->id ?>]" value="0">
    <input name="resources[<?= $resource->id ?>]" id="<?= $resource->id ?>" type="checkbox" value="1" <?= $resource->checked ?>>
    <ul class="can-be-sorted">
        <? foreach ($resource->children->orderBy('priority') as $resource): ?>
            <?= $this->render_partial('index/settings_tree', array('resource' => $resource)) ?>
        <? endforeach; ?>
    </ul>
</li>
