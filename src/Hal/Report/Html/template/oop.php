<?php
$fullwidth = true;
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
}
?>


    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number">
                    <?php echo $sum->nbClasses; ?>
                    <small> (<?php echo round($sum->nbClasses / sizeof($classes) * 100); ?> %)</small>
                </div>
                <div class="label">classes</div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number"><?php echo $sum->nbInterfaces; ?>
                    <small> (<?php echo round($sum->nbInterfaces / sizeof($classes) * 100); ?> %)</small>
                </div>
                <div class="label">interfaces</div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div
                    class="number"><?php echo $lcom ?></div>
                <div class="label">average LCOM</div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number"><?php echo $sum->nbClasses ? round($sum->lloc / $sum->nbClasses) : '-'; ?></div>
                <div class="label">logical lines of code by class</div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number"><?php echo $sum->nbMethods ? round($sum->lloc / $sum->nbMethods) : '-'; ?></div>
                <div class="label">logical lines of code by method</div>
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
                        <th class="js-sort-number">Cyclomatic</th>
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
                            <td><?php echo isset($class['bugs']) ? $class['bugs'] : ''; ?></td>
                            <td><?php echo isset($class['difficulty']) ? $class['difficulty'] : ''; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>


<?php require __DIR__ . '/_footer.php'; ?>