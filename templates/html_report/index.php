<?php
require __DIR__ . '/_header.php'; ?>
    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="violations.html">Violations</a> (<?php echo $sum->violations->critical; ?>
                    criticals, <?php echo $sum->violations->error; ?> errors)
                </div>
                <div class="number"><?php echo number_format($sum->violations->total, 0); ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="loc.html">Lines of code</a></div>
                <div class="number"><?php echo number_format($sum->loc, 0); ?></div>
                <?php echo $this->getTrend('sum', 'loc'); ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="oop.html">Classes</a></div>
                <div class="number"><?php echo number_format($sum->nbClasses, 0); ?></div>
                <?php echo $this->getTrend('sum', 'nbClasses'); ?>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="complexity.html">Average cyclomatic complexity by class</a></div>
                <div class="number"><?php echo number_format($avg->ccn, 0); ?></div>
                <?php echo $this->getTrend('avg', 'ccn', true); ?>
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
                <?php } else { ?>
                    <div class="help">
                        <div class="help-inner">
                            No JUnit report found. Use the --junit=&lt;junit.xml&gt; option to analyse your unit tests.
                            See <a href="https://phpunit.readthedocs.io/fr/latest/textui.html" target="_blank">documentation of PHPUnit if needed</a> (look for the --log-junit parameter)
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="complexity.html">Average bugs by class</a></div>
                <div class="number">
                    <?php echo number_format($avg->bugs, 0); ?>
                </div>
                <?php echo $this->getTrend('avg', 'bugs', true); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="column column-help">
            <div class="bloc">
                <div class="column column-help-inner">
                    <div class="row">
                        <div class="column with-help">
                            <div class="bloc-graph">
                                <div class="bloc-graph-carousel">
                                    <div class="bloc-graph-items">
                                        <div class="bloc-graph-item first">
                                            <div class="label">Maintainability / complexity</div>
                                            <div id="svg-maintainability" class="svg-container"></div>
                                        </div>
                                        <div class="bloc-graph-item second">
                                            <div class="label">Maintainability without comments / complexity</div>
                                            <div id="svg-maintainability-without-comments" class="svg-container"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="icon-container">
                                    <span class="dot dot-first active" title="Maintainability / complexity"></span>
                                    <span class="dot dot-second" title="Maintainability without comments / complexity"></span>
                                </div>
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
            <div class="bloc bloc-number">
                <div class="label">ClassRank
                    <small>(<a href="https://en.wikipedia.org/wiki/PageRank" target="_blank">Google's page rank applied to relations between classes)</a></small>
                </div>
                <div class="help">
                    <div class="help-inner">
                        <p>
                            Page Rank is a way to measure de importance of a class. There is no "good" or "bad" page rank. This metrics reflects interactions in your code.
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
                                        <span class="badge" <?php echo gradientStyleFor($classes, 'pageRank', $class['pageRank']);?>);">
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
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Composer dependencies</div>
                <div class="clusterize small">
                    <div id="clusterizePackages" class="clusterize-scroll">
                        <table>
                            <?php
                            $packagesInstalled = isset($project['composer']['packages-installed']) ? $project['composer']['packages-installed'] : [];
                            ?>
                            <thead>
                            <tr>
                                <th>Package</th>
                                <th>Required</th>
                                <?php if (0 !== count($packagesInstalled)) {?><th>Installed</th><?php } ?>
                                <th>Latest</th>
                                <th>License</th>
                            </tr>
                            </thead>
                            <tbody id="contentPackages" class="clusterize-content">
                            <?php
                            $packages = isset($project['composer']['packages']) ? $project['composer']['packages'] : [];
                            usort($packages, function ($a, $b) {
                                return strcmp($a->name, $b->name);
                            });
                            foreach ($packages as $package) if( !preg_match('!(^php$|^ext\-)!', $package->name)) { ?>
                                <tr>
                                    <td>
                                        <a target="_blank" href="https://packagist.org/packages/<?php echo $package->name; ?>">
                                            <?php echo $package->name; ?>
                                        </a>
                                    </td>
                                    <td><?php echo $package->required; ?></td>
                                    <?php if (0 !== count($packagesInstalled)) {?><td><?php echo $package->installed; ?></td><?php } ?>
                                    <td><?php echo $package->latest; ?></td>
                                    <td><?php foreach($package->license as $license) { ?>
                                            <a target="_blank" href="https://spdx.org/licenses/<?php echo $license;?>.html"><?php echo $license;?></a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                        <?php if(0 === count($packages)) { ?>
                            <div>No composer.json file found</div>
                        <?php } ?>
                        <?php if(0 === count($packagesInstalled)) { ?>
                            <div>No composer.lock file found</div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Licences of Composer dependencies</div>
                <?php if(0 === count($packages)) { ?>
                    <div>No composer.json file found</div>
                <?php } ?>
                <div id="svg-licenses"></div>
            </div>
        </div>
    </div>


    <script type="text/javascript">
        document.onreadystatechange = function () {
            if (document.readyState === 'complete') {
                chartMaintainability(true);
                chartMaintainability(false);

                new Clusterize({
                    scrollId: 'clusterizeClassRank',
                    contentId: 'contentClassRank'
                });

                new Clusterize({
                    scrollId: 'clusterizePackages',
                    contentId: 'contentPackages'
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
