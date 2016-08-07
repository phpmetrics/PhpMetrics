<?php
require __DIR__ . '/_header.php'; ?>

    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number">
                    <?php echo $avg->ccn; ?>
                </div>
                <div class="label">Average cyclomatic complexity by class <?php echo $this->getTrend('avg', 'ccn', true); ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number">
                    <?php echo $avg->relativeSystemComplexity; ?>
                </div>
                <div class="label">Average relative System complexity <?php echo $this->getTrend('avg', 'relativeSystemComplexity', true); ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number">
                    <?php echo $avg->bugs; ?>
                </div>
                <div class="label">Average bugs by class
                    <small>(Halstead)</small> <?php echo $this->getTrend('avg', 'bugs', true); ?>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number">
                    <?php echo $avg->kanDefect; ?>
                </div>
                <div class="label">average defects by class
                    <small>(Kan)</small> <?php echo $this->getTrend('avg', 'kanDefect', true); ?>
                </div>
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
                        <th class="js-sort-number">Cyclomatic</th>
                        <th class="js-sort-number">Relative system complexity</th>
                        <th class="js-sort-number">Relative data complexity</th>
                        <th class="js-sort-number">Relative structural complexity</th>
                        <th class="js-sort-number">Bugs</th>
                        <th class="js-sort-number">Defects</th>
                    </tr>
                    </thead>
                    <?php
                    foreach ($classes as $class) { ?>
                        <tr>
                            <td><?php echo $class['name']; ?></td>
                            <td><?php echo isset($class['ccn']) ? $class['ccn'] : ''; ?></td>
                            <td><?php echo isset($class['relativeSystemComplexity']) ? $class['relativeSystemComplexity'] : ''; ?></td>
                            <td><?php echo isset($class['relativeDataComplexity']) ? $class['relativeDataComplexity'] : ''; ?></td>
                            <td><?php echo isset($class['relativeStructuralComplexity']) ? $class['relativeStructuralComplexity'] : ''; ?></td>
                            <td><?php echo isset($class['bugs']) ? $class['bugs'] : ''; ?></td>
                            <td><?php echo isset($class['kanDefect']) ? $class['kanDefect'] : ''; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>


<?php require __DIR__ . '/_footer.php'; ?>