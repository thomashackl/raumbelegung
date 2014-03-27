<table class="intelec_roomtable">
    <colgroup>
        <col>
        <col width="16%">
        <col width="16%">
        <col width="16%">
        <col width="16%">
        <col width="16%">
        <col>
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
                        <td class="weekend" rowspan="<?= count($table->hour) + ($table->dayassigns ? 1 : 0)?>">
                            <div class="cell_wrapper" style="height: <?= count($table->hour) * IntelecSemesterBelegungsplan::SLOTSIZE ?>px">
                                <? foreach ($day['weekend'] as $entry): ?>
                                    <p class="other"><?= $entry ?></p>
                                <? endforeach; ?>
                            </div>
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
                <td></td>
            </tr>
        <? endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="8">
                <span class="headline">Hinweis für Notfälle:</span><br>
                Montag bis Freitag können Sie in Notfällen von 07.00 Uhr bis 20.00 Uhr einen Mitarbeiter der Hauswerkstatt unter der Tel.Nr. 0851 509-1232 oder unter der Mobilfunknummer 0173 8638666 anrufen. Außerdem ist ein Wachmann zu folgenden Zeiten in der Universität eingesetzt und unter der Mobilfunknummer 0175 1861348 erreichbar: Mo. - So. (kalendertäglich) von 19.30 Uhr - 07.00 Uhr, Samstag und Sonntag von 07.00 Uhr - 19.30 Uhr
            </td>
        </tr>
    </tfoot>
</table>