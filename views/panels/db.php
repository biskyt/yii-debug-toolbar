<?php
/**
 * @var Yii2DbPanel
 * @var array       $queries
 * @var int         $queriesCount
 * @var array       $resume
 * @var int         $resumeCount
 * @var array       $connections
 * @var int         $connectionsCount
 */
?>
<ul class="nav nav-tabs">
    <li class="active">
        <a href="#queries" data-toggle="tab">
            Queries
            <span class="badge badge-info"><?php echo $queriesCount; ?></span>
        </a>
    </li>
    <li>
        <a href="#resume" data-toggle="tab">
            Resume
            <?php if ($queriesCount > $resumeCount): ?>
                <span class="badge badge-warning"
                      title="Repeated queries: <?php echo $queriesCount - $resumeCount; ?>">
					<?php echo $resumeCount; ?>
				</span>
            <?php else: ?>
                <span class="badge badge-info">
					<?php echo $resumeCount; ?>
				</span>
            <?php endif; ?>
        </a>
    </li>
    <li>
        <a href="#connections" data-toggle="tab">
            Connections
            <span class="badge badge-info"><?php echo $connectionsCount; ?></span>
        </a>
    </li>
</ul>
<div class="tab-content">
    <div id="queries" class="tab-pane active">
        <table id="tbl-db" class="table table-condensed table-bordered table-filtered" style="table-layout:fixed">
            <thead>
            <tr>
                <th style="width:100px"><a href="#" class="sortable">Time</a></th>
                <th style="width:80px"><a href="#" class="sortable">Duration</a></th>
                <th><a href="#" class="sortable">Query</a></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($queries as $num => $query): ?>
                <tr>
                    <td style="width:100px"><?php echo $query['time']; ?></td>
                    <td style="width:80px"><?php echo $query['duration']; ?></td>
                    <td>
                        <?php echo $this->highlightCode ? $this->highlightSql($query['procedure']) : CHtml::encode($query['procedure']); ?>
                        <?php if ($this->canExplain && \count($explainConnections = $this->getExplainConnections($query['procedure'])) > 0): ?>
                            <div class="pull-right">
                                <?php if (\count($explainConnections) > 1): ?>
                                    <div class="btn-group">
                                        <button class="btn btn-link btn-small" data-toggle="dropdown">
                                            Explain <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu pull-right">
                                            <?php foreach ($explainConnections as $name => $info): ?>
                                                <li>
                                                    <?php echo CHtml::link("${name} - {$info['driver']}", [
                                                        'explain',
                                                        'tag'        => $this->tag,
                                                        'num'        => $num,
                                                        'connection' => $name,
                                                    ], ['class' => 'explain']); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($explainConnections as $name => $info): ?>
                                        <?php echo CHtml::link('Explain', [
                                            'explain',
                                            'tag'        => $this->tag,
                                            'num'        => $num,
                                            'connection' => $name,
                                        ], ['class' => 'explain btn btn-link btn-small']); ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div><!-- queries -->
    <div id="resume" class="tab-pane">
        <table id="tbl-resume" class="table table-condensed table-bordered table-striped table-hover table-filtered"
               style="table-layout:fixed">
            <thead>
            <tr>
                <th style="width:30px;">#</th>
                <th>Query</th>
                <th style="width:50px;"><a href="#" class="sortable">Count</a></th>
                <th style="width:70px;"><a href="#" class="sortable">Total</a></th>
                <th style="width:70px;"><a href="#" class="sortable">Avg</a></th>
                <th style="width:70px;"><a href="#" class="sortable">Min</a></th>
                <th style="width:70px;"><a href="#" class="sortable">Max</a></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($resume as $num => $query): ?>
                <tr>
                    <td style="width:30px;"><?php echo $num + 1; ?></td>
                    <td>
                        <?php echo $this->highlightCode ? $this->highlightSql($query['procedure']) : CHtml::encode($query['procedure']); ?>
                    </td>
                    <td style="width:50px;"><?php echo $query['count']; ?></td>
                    <td style="width:70px;"><?php echo $query['total']; ?></td>
                    <td style="width:70px;"><?php echo $query['avg']; ?></td>
                    <td style="width:70px;"><?php echo $query['min']; ?></td>
                    <td style="width:70px;"><?php echo $query['max']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div><!-- resume -->
    <div id="connections" class="tab-pane">
        <?php
        foreach ($connections as $id => $info) {
            $caption = 'Component: ';
            if ($this->owner->showConfig) {
                $caption .= CHtml::link($id, ['config', '#' => 'components-' . $id]);
            } else {
                $caption .= $id;
            }
            $caption .= ' (' . $info['class'] . ')';
            unset($info['class']);
            echo $this->render(\dirname(__FILE__) . '/_detail.php', [
                'caption' => $caption,
                'values'  => $info,
            ]);
        }
        ?>
    </div><!-- connections -->
</div>
<?php
Yii::app()->getClientScript()->registerScript(__CLASS__ . '#explain', <<<JS
$('a.explain').click(function(e){
	if (e.altKey || e.ctrlKey || e.shiftKey) return;
	e.preventDefault();
	var block = $(this).data('explain-block');
	if (!block) {
		block = $('<tr>').insertAfter($(this).parents('tr').get(0));
		var div = $('<div class="explain">').appendTo($('<td colspan="3">').appendTo(block));
		div.text('Loading...');
		div.load($(this).attr('href'), function(response, status, xhr){
			if (status == "error") {
				div.text(xhr.status + ': ' + xhr.statusText);
				block.addClass('error');
			}
		});
		$(this).data('explain-block', block);
	} else {
		block.toggle();
	}
});
JS
);
?>
<script>
document.addEventListener('DOMContentLoaded', (event) => {
    const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;
    const comparer = (idx, asc, tableId) => (a, b) => {
        const v1 = getCellValue(asc ? a : b, idx);
        const v2 = getCellValue(asc ? b : a, idx);
        if (tableId === 'tbl-db') {
            if (idx === 1) { // If it's the first column (which is a time in ms), parse as float
                return parseFloat(v1) - parseFloat(v2);
            } else { // Otherwise, sort as string
                return v1.toString().localeCompare(v2);
            }
        } else if (tableId === 'tbl-resume') {
            if (idx >= 2 && idx <= 5) { // Columns 3, 4, 5, and 6 should be parsed as float
                return parseFloat(v1) - parseFloat(v2);
            } else { // Otherwise, sort as string
                return v1.toString().localeCompare(v2);
            }
        }
    };
    document.querySelectorAll('th a.sortable').forEach(th => th.addEventListener('click', () => {
        const table = th.closest('table');
        const tableId = table.id;
        Array.from(table.querySelectorAll('tr'))
            .sort(comparer(Array.from(th.parentNode.parentNode.children).indexOf(th.parentNode), this.asc = !this.asc, tableId))
            .forEach(tr => table.appendChild(tr) );
    }));
});
</script>