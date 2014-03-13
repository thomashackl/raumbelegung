<form id="dateform" method="get">
    <?= _('Wochenbelegungsplan') ?> 
    <input name="date" id="date" value="<?= $date ?>"></input>
    <?= \Studip\Button::create(_("Anzeigen"), 'submitButton') ?>
    <span id="datehint"><?= _('Datumsformat tt.mm.yyyy'); ?></span>
</form>