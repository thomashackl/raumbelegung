<form id="dateform" class="studip_form" method="post">
    <label>
        <?= dgettext('roomplanplugin', 'Semester') ?>
        <select name="semester">
            <? foreach ($semesters as $semester): ?>
                <option value="<?= $semester->id ?>" <?= $chosenSemester === $semester->id ? 'selected' : '' ?>><?= $semester->name ?></option>
            <? endforeach; ?>
        </select>
    </label>

    <label>
        <input type="checkbox" name="lecture_only" <?= Request::get('lecture_only') ? 'checked' : '' ?>>
        <?= dgettext('roomplanplugin', 'Nur Vorlesungszeiten') ?>
    </label>

    <label>
        <?= dgettext('roomplanplugin', 'Manuell von') ?>
        <input type="text" name="start" class="datepicker" value="<?= Request::get('start') ?>">
    </label>

    <label>
        <?= dgettext('roomplanplugin', 'Manuell bis') ?>
        <input type="text" name="end" class="datepicker" value="<?= Request::get('end') ?>">
    </label>

    <label>
        <input type="checkbox" name="participants" <?= Request::get('participants') ? 'checked' : '' ?>>
        <?= dgettext('roomplanplugin', 'Teilnehmer anzeigen') ?>
    </label>

    <label>
        <input type="checkbox" name="empty_rooms" <?= Request::get('empty_rooms') ? 'checked' : '' ?>>
        <?= dgettext('roomplanplugin', 'Leere R�ume anzeigen') ?>
    </label>

    <label>
        <?= dgettext('roomplanplugin', 'Geb�ude oder Raum') ?>
        <select name="building" id="buildings" class="multilevel">
            <? foreach (RoomUsageResourceObject::getFiltered() as $building): ?>
                <option value="<?= $building->id ?>" class="building" <?= $building->id == Request::get('building') ? 'selected' : '' ?>><?= htmlReady($building->name) ?></option>
                <? foreach ($building->filteredChildren as $child): ?>
                    <option value="<?= $child->id ?>" <?= $child->id == Request::get('building') ? 'selected' : '' ?>><?= htmlReady($child->name) ?> <?= htmlReady($child->description) ?></option>
                <? endforeach; ?>
            <? endforeach; ?>
        </select>
    </label>

    <?= \Studip\Button::create(dgettext('roomplanplugin', "Anzeigen"), 'submit') ?>
</form>
<? if (false): ?>
    <? foreach ($request as $room): ?>
        <?= IntelecBelegungsplan::display(Request::get('date'), $room) ?>
    <? endforeach; ?>
<? endif; ?>

<? foreach ($timetables as $timetable): ?>
    <?= $this->render_partial('semester/timetable', array('table' => $timetable)); ?>
    <?
endforeach;
