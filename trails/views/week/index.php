<form id="dateform" class="studip_form" method="get">
    <label>
        <?= _('Datum') ?> 
        <input name="date" id="date" value="<?= $date ?>"></input>
    </label>

    <label>
        <?= _('Geb�ude oder Raum') ?>
        <select name="building" id="buildings" class="multilevel">
            <? foreach ($buildings as $building): ?>
                <option value="<?= $building->id ?>" class="building" <?= $building->id == Request::get('building') ? 'selected' : '' ?>><?= htmlReady($building->name) ?></option>
                <? foreach ($building->children as $child): ?>
                    <option value="<?= $child->id ?>" <?= $child->id == Request::get('building') ? 'selected' : '' ?>><?= htmlReady($child->name) ?> <?= htmlReady($child->description) ?></option>
                <? endforeach; ?>
            <? endforeach; ?>
        </select>
    </label>

    <?= \Studip\Button::create(_("Anzeigen"), 'submitButton') ?>
</form>
<? if (false): ?>
    <? foreach ($request as $room): ?>
        <?= IntelecBelegungsplan::display(Request::get('date'), $room) ?>
    <? endforeach; ?>
<? endif; ?>

<? foreach ($timetables as $timetable): ?>
<?= $this->render_partial('week/timetable', array('table' => $timetable)); ?>
<? endforeach; 