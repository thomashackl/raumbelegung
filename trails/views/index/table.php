<style>
    table {border-style: solid; border-width: 6px; border-color: #999999;}
    table th {background-color: #999999}
    table table th {background-color: #7d92ce;border-color: #7d92ce;}
    table table{border-color: #7d92ce;}
    table table table th {background-color: #8fd08a; border-color: #8fd08a; }
    table table table{border-color: #8fd08a; }
    table table table table th {background-color: #d78888; border-color:  #d78888; }
    table table table table {border-color:  #d78888;}
</style>
<?
Navigation::activateItem('/tools/raumbelegung/tableview');

use Studip\Button;
?>
<form id="dateform" method="get">
    <input name="date" id="date" value="<?= $date ?>"></input>
    <?= Button::create(dgettext('roomplanplugin', "Anzeigen"), 'submitButton') ?>
    <span id="datehint"><?= dgettext('roomplanplugin', 'Datumsformat tt.mm.yyyy'); ?></span>
</form>

<script>
    $('#date').datepicker( {
        onSelect: function(date) {
            $('#dateform').submit();
        }});
        $('#datehint').hide();
    $('[name="submitButton"]').hide();
</script>

<?= print_table($room) ?>

<?

function print_table($room) {
    ?>
    <table border="1" width="800">
        <tr>
            <th>
    <?= $room->name ?><br> 
                (<?= $room->getDate() ?>)
            </th>
    <? foreach ($room->termine as $termin): ?>
            <tr>
                <td><?= $termin->display ?></td>
            </tr>
    <? endforeach; ?>
    </tr>
    <? foreach ($room->children as $child): ?>
        <tr>
            <td>
        <? print_table($child) ?>
            </td>
        </tr>
    <? endforeach; ?>
    </table>
    <?
}
?>
