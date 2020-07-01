<li>
    <label for="<?= $resource->id ?>"><?= htmlReady($resource->name) ?></label>
    <input type="hidden" name="resources[<?= $resource->id ?>]" value="0">
    <?php $order = ResourceRoomOrder::find([$resource->id, $GLOBALS['user']->id]); ?>
    <input name="resources[<?= $resource->id ?>]" id="<?= $resource->id ?>" type="checkbox"
           value="1"<?= ($order && $order->checked) || $resource->parent_id == '' ? ' checked' : '' ?>>
    <ul class="can-be-sorted">
        <? foreach ($resource->children->orderBy('sort_position, name') as $resource): ?>
            <?= $this->render_partial('index/settings_tree', array('resource' => $resource)) ?>
        <? endforeach; ?>
    </ul>
</li>
