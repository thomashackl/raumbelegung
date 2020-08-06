<li>
    <?php if ($resource['children'] && count($resource['children']) > 0) : ?>
        <input type="checkbox" id="<?= $prefix . htmlReady($resource['id']) ?>">
        <label for="<?= $prefix . htmlReady($resource['id']) ?>" class="undecorated">
            <?= htmlReady($resource['name']) ?>
            (
                <a href="" class="select-all"><?php echo dgettext('roomplanplugin', 'alle') ?></a>
                |
                <a href="" class="select-none"><?php echo dgettext('roomplanplugin', 'keine') ?></a>
            )
        </label>
    <?php else : ?>
        <label class="undecorated">
            <input type="checkbox" name="selected[]" value="<?= htmlReady($resource['id']) ?>"
                   style="display: inline" <?= in_array($resource['id'], $selected) ? ' checked' : ''?>>
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
