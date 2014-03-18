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
                        <td>
                            <?= $this->render_partial('week/entry', array('entry' => $day['content'])) ?>
                        </td>
                    <? endif; ?>
                    <? if ($day['weekend']): ?>
                        <td class="weekend" rowspan="<?= count($table->hour) ?>">
                            <? foreach ($day['weekend'] as $entry):?>
                                <?= $entry['realname'] ?><br>
                            <? endforeach; ?>
                        </td>
                    <? endif; ?>
                <? endforeach; ?>
            </tr>
        <? endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="100">
                Dreizeiliges Bla Bla Bla, das sich alle 3-4 Monate ändert. Daher wäre es toll, wenn die Raumvergabe diesen Text in Stud.IP editieren könnte!?Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ...
            </td>
        </tr>
    </tfoot>
</table>