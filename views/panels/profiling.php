<?php
/**
 * @var Yii2ProfilingPanel
 * @var array              $items
 * @var string             $time
 * @var string             $memory
 */
?>
<p>Total processing time: <b><?php echo $time; ?></b>; Peak memory: <b><?php echo $memory; ?></b>.</p>

<table id="myTable" class="table table-condensed table-bordered table-striped table-hover table-filtered" style="table-layout:fixed">
    <thead>
    <tr>
        <th style="width:80px"><a href="#" class="sortable">Time</a></th>
        <th style="width:220px"><a href="#" class="sortable">Category</a></th>
        <th><a href="#" class="sortable">Procedure</a></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td style="width:80px"><?php echo $item['time']; ?></td>
            <td style="width:220px"><?php echo CHtml::encode($item['category']); ?></td>
            <td><?php echo \str_repeat(
                '<span class="indent">→</span>',
                $item['indent']
            ) . CHtml::encode($item['procedure']); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', (event) => {
    const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;
    const comparer = (idx, asc) => (a, b) => {
        const v1 = getCellValue(asc ? a : b, idx);
        const v2 = getCellValue(asc ? b : a, idx);
        if (idx === 0) { // If it's the first column (which is a time in ms), parse as float
            return parseFloat(v1) - parseFloat(v2);
        } else { // Otherwise, sort as string
            return v1.toString().localeCompare(v2);
        }
    };
    document.querySelectorAll('th a.sortable').forEach(th => th.addEventListener('click', () => {
        const table = th.closest('table');
        Array.from(table.querySelectorAll('tr'))
            .sort(comparer(Array.from(th.parentNode.parentNode.children).indexOf(th.parentNode), this.asc = !this.asc))
            .forEach(tr => table.appendChild(tr) );
    }));
});
</script>
