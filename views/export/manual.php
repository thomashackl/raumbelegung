<form class="default" action="<?= $controller->url_for('export/do') ?>" method="post">
    <fieldset>
        <legend>
            <?= dgettext('roomplanplugin', 'Welcher Zeitraum soll exportiert werden?') ?>
        </legend>
        <section>
            <label>
                <?= dgettext('roomplanplugin', 'Start') ?>:
                <input class="has-date-picker" type="text" name="start" id="export-start" value="<?= $start ?>">
            </label>
            <label>
                <?= dgettext('roomplanplugin', 'Ende') ?>:
                <input class="has-date-picker" type="text" name="end" id="export-end" value="<?= $end ?>"
                       data-date-picker='{">=":"#export-start"}'>
            </label>
        </section>
    </fieldset>
    <fieldset>
        <legend>
            <?= dgettext('roomplanplugin', 'Welche Räume sollen exportiert werden?') ?>
        </legend>
        <section>
            <?= $this->render_partial('export/_resourcelist', compact('resources', 'selected')) ?>
        </section>
    </fieldset>
    <footer data-dialog-button>
        <?= Studip\Button::create(dgettext('roomplanplugin', 'Exportieren'), 'do_export') ?>
    </footer>
</form>
