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
                Pl�tze: <?= $table->places ?><br>
                Fl�che: <?= $table->area ?>
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
                    <? if (!is_array($day)): ?>
                        <td>
                            <?= $day ?>
                        </td>
                    <? endif; ?>
                    <? if ($day['content']): ?>
                        <td>
                            <?= $this->render_partial('semester/entry', array('entry' => $day['content'])) ?>
                        </td>
                    <? endif; ?>
                    <? if (isset($day['weekend'])): ?>
                        <td class="weekend" rowspan="<?= count($table->hour) ?>">
                            <? foreach ($day['weekend'] as $entry): ?>
                                <?= $entry ?><br>
                            <? endforeach; ?>
                        </td>
                    <? endif; ?>
                <? endforeach; ?>
            </tr>
        <? endforeach; ?>
        <? if ($table->dayassigns): ?>
            <tr>
                <td></td>
                <? for ($day = 1; $day <= 5; $day++): ?>
                    <td class="non-cyclic">
                        <? foreach ($table->dayassigns[$day] as $entry): ?>
                            <?= $entry ?><br>
                        <? endforeach; ?>
                    </td>
                <? endfor; ?>
                <td colspan="100">
                </td>
            </tr>
        <? endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="100">
                <span class="headline">Hinweis f�r Notf�lle:</span><br>
                Dreizeiliges Bla Bla Bla, das sich alle 3-4 Monate �ndert. Daher w�re es toll, wenn die Raumvergabe diesen Text in Stud.IP editieren k�nnte!?Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ...
            </td>
        </tr>
    </tfoot>
</table>