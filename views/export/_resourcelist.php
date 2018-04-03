<ul class="collapsable css-tree">
    <?php foreach ($resources as $resource) : ?>
        <?= $this->render_partial('export/_resource', compact('resource', 'selected', 'prefix')) ?>
    <?php endforeach ?>
</ul>
