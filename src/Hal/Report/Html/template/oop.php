<?php
require __DIR__ . '/_header.php'; ?>

<?php

$lcom = [];
foreach ($classes as $c) {
    if (!$c['interface']) {
        array_push($lcom, $c['lcom']);
    }
}
if (sizeof($lcom) > 0) {
    $lcom = round(array_sum($lcom) / sizeof($lcom), 2);
} else {
    $lcom = 0;
}
?>


    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">classes <?php echo $this->getTrend('sum', 'nbClasses'); ?></div>
                <div class="number">
                    <?php echo $sum->nbClasses; ?>
                    <small> (<?php echo (sizeof($classes) ? round($sum->nbClasses / sizeof($classes) * 100) : '0'); ?> %)</small>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">interfaces <?php echo $this->getTrend('sum', 'nbInterfaces'); ?></div>
                <div class="number"><?php echo $sum->nbInterfaces; ?>
                    <small> (<?php echo (sizeof($classes) ? round($sum->nbInterfaces / sizeof($classes) * 100) : '0'); ?> %)</small>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">average LCOM <?php echo $this->getTrend('avg', 'lcom', true); ?></div>
                <div class="number"><?php echo $lcom ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">logical lines of code by class</div>
                <div class="number"><?php echo $sum->nbClasses ? round($sum->lloc / $sum->nbClasses) : '-'; ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">logical lines of code by method</div>
                <div class="number"><?php echo $sum->nbMethods ? round($sum->lloc / $sum->nbMethods) : '-'; ?></div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="column">
            <div class="bloc">
                <table class="js-sort-table" id="table-length">
                    <thead>
                    <tr>
                        <th>Class</th>
                        <th class="js-sort-number">LCOM</th>
                        <th class="js-sort-number">Volume</th>
                        <th class="js-sort-number">Class cycl.</th>
                        <th class="js-sort-number">Max method cycl.</th>
                        <th class="js-sort-number">Bugs</th>
                        <th class="js-sort-number">Difficulty</th>
                    </tr>
                    </thead>
                    <?php
                    foreach ($classes as $class) { ?>
                        <tr>
                            <td><?php echo $class['name']; ?></td>
                            <td><?php echo isset($class['lcom']) ? $class['lcom'] : ''; ?></td>
                            <td><?php echo isset($class['volume']) ? $class['volume'] : ''; ?></td>
                            <td><?php echo isset($class['ccn']) ? $class['ccn'] : ''; ?></td>
                            <td><?php echo isset($class['ccnMethodMax']) ? $class['ccnMethodMax'] : ''; ?></td>
                            <td><?php echo isset($class['bugs']) ? $class['bugs'] : ''; ?></td>
                            <td><?php echo isset($class['difficulty']) ? $class['difficulty'] : ''; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>


<?php require __DIR__ . '/_footer.php'; ?>