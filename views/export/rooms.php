<?= MessageBox::info(dgettext('roomplanplugin', 'Die hier ausgewählten Räume werden beim automatisch erstellten Export berücksichtigt.')) ?>
<form class="default" action="<?= $controller->url_for('export/save_rooms') ?>" method="post">
    <section>
        <?= $this->render_partial('export/_resourcelist', compact('resources', 'selected')) ?>
    </section>
    <footer data-dialog-button>
        <?= CSRFProtection::tokenTag() ?>
        <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('clients'), ['data-dialog' => 'close']) ?>
    </footer>
</form>