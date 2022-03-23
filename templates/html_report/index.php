<?php
require __DIR__ . '/_header.php'; ?>
    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="violations.html">Violations</a> (<?php echo $sum->violations->critical; ?>
                    criticals, <?php echo $sum->violations->error; ?> errors)
                </div>
                <div class="number"><?php echo number_format($sum->violations->total, 0); ?></div>
                <div class="bloc-action">
                    <a href="violations.html">View details &gt;</a>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <?php echo $this->getTrend('sum', 'loc'); ?>
                <div class="label"><a href="loc.html">Lines of code</a></div>
                <div class="number"><?php echo number_format($sum->loc, 0); ?></div>
                <div class="bloc-action">
                    <a href="loc.html">View details &gt;</a>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <?php echo $this->getTrend('sum', 'nbClasses'); ?>
                <div class="label"><a href="oop.html">Classes</a></div>
                <div class="number"><?php echo number_format($sum->nbClasses, 0); ?></div>
                <div class="bloc-action">
                    <a href="loc.html">View details &gt;</a>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <?php echo $this->getTrend('avg', 'ccn', true); ?>
                <div class="label"><a href="complexity.html">Average cyclomatic complexity by class</a></div>
                <div class="number"><?php echo number_format($avg->ccn, 2); ?></div>
                <div class="bloc-action">
                    <a href="complexity.html">View details &gt;</a>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">
                    <a href="junit.html">Assertions in tests</a>
                </div>
                <?php if(isset($project['unitTesting'])) { ?>
                    <div class="number">
                        <?php echo $project['unitTesting']['assertions']; ?>
                    </div>
                    <div class="bloc-action">
                        <a href="junit.html">View details &gt;</a>
                    </div>
                <?php } else { ?>
                    <div class="help number-alternate">
                        <div class="help-inner">
                            No JUnit report found. Use the --junit=&lt;junit.xml&gt; option to analyse your unit tests.
                            See <a href="https://phpunit.readthedocs.io/fr/latest/textui.html" target="_blank">documentation of PHPUnit if needed</a>
                        </div>
                    </div>
                    <div class="bloc-action">
                        No details
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <?php echo $this->getTrend('avg', 'bugs', true); ?>
                <div class="label"><a href="complexity.html">Average bugs by class</a></div>
                <div class="number">
                    <?php echo number_format($avg->bugs, 2); ?>
                </div>
                <div class="bloc-action">
                    <a href="complexity.html">View details &gt;</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="column column-help">
            <div class="bloc" style="min-height: 540px;">
                <div class="column column-help-inner">
                    <div class="row">
                        <div class="column with-help">
                            <div>
                                <div class="label">
                                    Maintainability / complexity
                                    <small><a
                                            data-current="with-comments"
                                            onclick="toggleChartMaintainability(this)">
                                                (with comments)
                                    </a></small>
                                </div>
                                <div id="svg-maintainability" class="svg-container"></div>
                            </div>
                        </div>
                        <div class="column help">
                            <div class="help-inner">
                                <p>Each file is symbolized by a circle. Size of the circle represents the Cyclomatic
                                    complexity.
                                    Color of the circle represents the Maintainability Index.</p>
                                <p>Large red circles will be probably hard to maintain.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="column">
            <div class="bloc bloc-number" style="min-height: 540px;">
                <div class="label">ClassRank
                    <small>(<a href="https://en.wikipedia.org/wiki/PageRank" target="_blank">Google's page rank applied to relations between classes)</a></small>
                </div>
                <div class="help">
                    <div class="help-inner">
                        <p>
                            Page Rank is a way to measure the importance of a class. There is no "good" or "bad" page rank. This metric reflects interactions in your code.
                        </p>
                    </div>
                </div>
                <div class="clusterize small">
                    <div id="clusterizeClassRank" class="clusterize-scroll">
                        <table class="table-small">
                            <thead>
                            <tr>
                                <th>ClassRank</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody id="contentClassRank" class="clusterize-content">
                            <?php
                            $classesS = $classes;
                            usort($classesS, static function ($a, $b) {
                                return strcmp($b['pageRank'], $a['pageRank']);
                            });
                            //$classesS = array_slice($classesS, 0, 10);
                            foreach ($classesS as $class) { ?>
                                <tr>
                                    <td>
                                        <span class="badge" <?php echo gradientStyleFor($classes, 'pageRank', $class['pageRank']);?>>
                                        <?php echo $class['pageRank']; ?>
                                    </td>
                                    </td>
                                    <td>
                                        <span class="path"><?php echo $class['name']; ?></span>
                                        <?php
                                            $badgeTitleMIWOC = 'Maintainability Index (w/o comments)';
                                            $mIwoC = isset($class['mIwoC']) ? $class['mIwoC'] : '';
                                            $badgeTitleMI = 'Maintainability Index';
                                            $mi = isset($class['mi']) ? $class['mi'] : '';
                                        ?>
                                        <span class="badge" title="<?php echo $badgeTitleMI;?>"><?php echo $mi;?></span>
                                        <span class="badge" title="<?php echo $badgeTitleMIWOC;?>"><?php echo $mIwoC;?></span>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php if($this->isHomePage()) {?>
            <div class="column">
                <div class="bloc bloc-number">
                    <div class="label">
                        <a href="composer.html">Composer</a>
                    </div>
                    <?php
                    $packages = isset($project['composer']['packages']) ? $project['composer']['packages'] : [];
                    $packagesInstalled = isset($project['composer']['packages-installed']) ? $project['composer']['packages-installed'] : [];
                    if ([] === $packages) { ?>
                        <div class="help number-alternate"><div class="help-inner">No composer.json file found</div></div>
                    <?php } else {?>
                        <div class="number">
                            <?php echo count($packages);?> dependencies
                        </div>
                    <?php } ?>

                    <div id="svg-licenses" class="chart-in-number"></div>
                    <div class="bloc-action">
                        <a href="composer.html">View details &gt;</a>
                    </div>
                </div>
            </div>
        <?php }?>
        <div class="column"></div>
        <div class="column"></div>
    </div>


    <script type="text/javascript">
        document.onreadystatechange = function () {
            if (document.readyState === 'complete') {
                chartMaintainability();

                new Clusterize({
                    scrollId: 'clusterizeClassRank',
                    contentId: 'contentClassRank'
                });

                // prepare json for packages pie
                <?php
                $json = [];
                $packages = isset($project['composer']['packages']) ? $project['composer']['packages'] : [];
                foreach ($packages as $package) {
                    foreach ($package->license as $license) {
                        if (!isset($json[$license])) {
                            $json[$license] = new stdClass();
                            $json[$license]->name = $license;
                            $json[$license]->value = 0;
                        }
                        $json[$license]->value++;
                    }
                }
                ?>
                chartLicenses(<?php echo json_encode(array_values($json));?>);
            }
        };
    </script>

<?php require __DIR__ . '/_footer.php'; ?>
