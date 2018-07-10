<?php require __DIR__ . '/_header.php'; ?>
    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="violations.html">Violations</a> (<?php echo $sum->violations->critical; ?>
                    criticals, <?php echo $sum->violations->error; ?> errors)
                </div>
                <div class="number"><?php echo $sum->violations->total; ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="loc.html">Lines of code</a></div>
                <div class="number"><?php echo $sum->loc; ?></div>
                <?php echo $this->getTrend('sum', 'loc'); ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="oop.html">Classes</a></div>
                <div class="number"><?php echo $sum->nbClasses; ?></div>
                <?php echo $this->getTrend('sum', 'nbClasses'); ?>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="complexity.html">Average cyclomatic complexity by class</a></div>
                <div class="number"><?php echo $avg->ccn; ?></div>
                <?php echo $this->getTrend('avg', 'ccn', true); ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">
                    <a href="junit.html">Assertions in tests</a>
                </div>
                <div class="number">
                    <?php echo isset($project['unitTesting']) ? $project['unitTesting']['assertions'] : '--'; ?>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="complexity.html">Average bugs by class</a></div>
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

        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">ClassRank
                    <small>(Google's page rank applied to relations between classes)</small>
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
                            $packages = isset($project['composer'], $project['composer']['packages']) ? $project['composer']['packages'] : [];
                            usort($packages, function ($a, $b) {
                                return strcmp($a->name, $b->name);
                            });
                            foreach ($packages as $package) { ?>
                                <tr<?php if (null !== $package->installed && version_compare($package->installed, $package->latest) === -1) { echo ' style="color:orangered"'; }?>>
                                    <td><?php echo $package->name; ?></td>
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
                <?php if(0 === sizeof($packages)) { ?>
                    <div>No composer.json file found</div>
                <?php } ?>
                <div id="svg-licenses"></div>
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

                new Clusterize({
                    scrollId: 'clusterizePackages',
                    contentId: 'contentPackages'
                });

                // prepare json for packages pie
                <?php
                $json = [];
                $packages = isset($project['composer'], $project['composer']['packages']) ? $project['composer']['packages'] : [];
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
