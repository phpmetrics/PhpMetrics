<?php require __DIR__ . '/_header.php'; ?>
    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="violations.php">Violations</a> (<?php echo $sum->violations->critical;?> criticals, <?php echo $sum->violations->error;?> errors)</div>
                <div class="number"><?php echo $sum->violations->total; ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="loc.php">Lines of code</a></div>
                <?php echo $this->getTrend('sum', 'loc'); ?>
                <div class="number"><?php echo $sum->loc; ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="oop.php">Classes</a></div>
                <div class="number"><?php echo $sum->nbClasses; ?></div>
                <?php echo $this->getTrend('sum', 'nbClasses'); ?>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="complexity.php">Average cyclomatic complexity by class</a></div>
                <div class="number"><?php echo $avg->ccn; ?></div>
                <?php echo $this->getTrend('avg', 'ccn', true); ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">
                    <a href="junit.php">Assertions in tests</a>
                </div>
                <div class="number">
                    <?php echo isset($project['unitTesting']) ? $project['unitTesting']['assertions'] : '--'; ?>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="complexity.php">Average bugs by class</a></div>
                <div class="number">
                    <?php echo $avg->bugs; ?>
                </div>
                <?php echo $this->getTrend('avg', 'bugs', true); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="column column-help">
            <div class="column column-help-inner">
                <div class="row">
                    <div class="column with-help">
                        <div class="bloc">
                            <div class="label">Maintainability / complexity</div>
                            <div id="svg-maintainability"></div>
                        </div>
                    </div>
                    <div class="column help">
                        <div class="help-inner">
                            <p>Each file is symbolized by a circle. Size of the circle represents the Cyclomatic
                                complexity.
                                Color
                                of the circle represents the Maintainability Index.</p>

                            <p>Large red circles will be probably hard to maintain.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="column column-50 bloc">
            <div class="label">ClassRank
                <small>(Google's page rank applyed to relations between classes)</small>
            </div>
            <div class="clusterize small">
                <div id="clusterizeClassRank" class="clusterize-scroll">
                    <table>
                        <thead>
                        <tr>
                            <th>Class</th>
                            <th>ClassRank</th>
                        </tr>
                        </thead>
                        <tbody id="contentClassRank" class="clusterize-content">
                        <?php
                        $classesS = $classes;
                        usort($classesS, function ($a, $b) {
                            return strcmp($b['pageRank'], $a['pageRank']);
                        });
                        //$classesS = array_slice($classesS, 0, 10);
                        foreach ($classesS as $class) { ?>
                            <tr>
                                <td><?php echo $class['name']; ?> <span class="badge"
                                                                        title="Maintainability Index"><?php echo isset($class['mi']) ? $class['mi'] : ''; ?></span>
                                </td>
                                <td><?php echo $class['pageRank']; ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <script type="text/javascript">
        document.onreadystatechange = function () {
            if (document.readyState === 'complete') {
                chartMaintainability();

                new Clusterize({
                    scrollId: 'clusterizeClassRank',
                    contentId: 'contentClassRank'
                });

            }
        };
    </script>

<?php require __DIR__ . '/_footer.php'; ?>