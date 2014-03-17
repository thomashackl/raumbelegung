<table class="intelec_roomtable">
    <colgroup>
        <col width="1%">
        <col width="16%">
        <col width="16%">
        <col width="16%">
        <col width="16%">
        <col width="16%">
        <col width="1%">
        <col width="16%">
    </colgroup>
    <thead>
        <tr>
            <td colspan="3">
                <span class="headline"><?= $table->headline ?></span><br>
                <?= $table->adress ?>
            </td>
            <td colspan="2">
                Plätze: <?= $table->places ?><br>
                Fläche: <?= $table->area ?>
            </td>
            <td colspan="3">
                Zeitraum: <?= $table->timespan ?><br>
                Stand: <?= $table->timestamp ?>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td></td>
            <th>Montag</th>
            <th>Dienstag</th>
            <th>Mittwoch</th>
            <th>Donnerstag</th>
            <th>Freitag</th>
            <td></td>
            <th>Samstag&thinsp;/&thinsp;Sonntag</th>
        </tr>
        <? foreach ($table->hour as $hour): ?>
            <tr>
                <? foreach ($hour as $day): ?>
                    <? if (isset($day['title'])): ?>
                        <td>
                            <?= $day['title'] ?>
                        </td>
                    <? endif; ?>
                    <? if ($day['content']): ?>
                        <td rowspan="<?= $day['content']['timeslots'] ?>">
                            <?= $this->render_partial('week/entry', array('entry' => $day['content'])) ?>
                        </td>
                    <? endif; ?>
                <? endforeach; ?>
            </tr>
        <? endforeach; ?>
    </tbody>
</table>