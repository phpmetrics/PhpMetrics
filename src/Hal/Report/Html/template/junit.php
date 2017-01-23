<?php
require __DIR__ . '/_header.php'; ?>

<?php
$unit = $project['unitTesting'];
$getMetricForClass = function ($classname, $metric) use ($classes) {
    foreach ($classes as $class) {
        if ($classname !== $class['name']) {
            continue;
        }

        return $class[$metric];
    }

    return '-';
};
?>


    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number">
                    <?php echo $unit['nbTests']; ?>
                </div>
                <div class="label">Test suites</div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number">
                    <?php echo $unit['nbCoveredClasses']; ?>
                </div>
                <div class="label">
                    classes called by tests
                    <small>(<?php echo $unit['percentCoveredClasses']; ?> %)</small>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number">
                    <?php echo $unit['nbUncoveredClasses']; ?>
                </div>
                <div class="label">
                    classes never called by tests
                    <small>(<?php echo $unit['percentUncoveredClasses']; ?> %)</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="column">
            <div class="bloc">
                <table class="js-sort-table" id="table-junit">
                    <thead>
                    <tr>
                        <th>TestSuite</th>
                        <th class="js-sort-number">Called classes</th>
                    </tr>
                    </thead>
                    <?php foreach ($unit['tests'] as $suite) { ?>
                        <tr>
                            <td><?php echo $suite->classname; ?></td>
                            <td>
                                <?php
                                foreach ($suite->externals as $index => $external) { ?>
                                    <?php echo ($index === 0) ? '' : '<br />'; ?>
                                    <span class="badge" title="Cyclomatic complexity of class">
                                        <?php echo $getMetricForClass($external, 'ccn'); ?>
                                    </span>
                                    <?php echo $external; ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>


<?php require __DIR__ . '/_footer.php'; ?>