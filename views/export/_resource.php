<li>
    <?php if ($resource['children'] && count($resource['children']) > 0) : ?>
        <input type="checkbox" id="<?= $prefix . htmlReady($resource['resource_id']) ?>">
        <label for="<?= $prefix . htmlReady($resource['resource_id']) ?>" class="undecorated">
            <?= htmlReady($resource['name']) ?>
        </label>
    <?php else : ?>
        <label class="undecorated">
            <input type="checkbox" name="selected[]" value="<?= htmlReady($resource['resource_id']) ?>"
                   style="display: inline" <?= in_array($resource['resource_id'], $selected) ? ' checked' : ''?>>
            <?= htmlReady($resource['name']) ?>
        </label>
    <?php endif ?>
    <?php if ($resource['children'] && count($resource['children']) > 0) : ?>
    <ul>
        <?php foreach ($resource['children'] as $child) : ?>
            <?= $this->render_partial('export/_resource', ['resource' => $child, 'selected' => $selected]) ?>
        <?php endforeach ?>
    </ul>
    <?php endif ?>
</li>
