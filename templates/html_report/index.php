<?php

use Hal\Report\Html\ViewHelper;

require __DIR__ . '/_header.php';
/** @var ViewHelper $viewHelper */
$viewHelper = $this->viewHelper;
?>
    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label"><a href="violations.html">Violations</a> (<?php echo $this->sharedMetrics->sum->violations->critical; ?>
                    criticals, <?php echo $this->sharedMetrics->sum->violations->error; ?> errors)
                </div>
                <div class="number"><?php echo number_format($this->sharedMetrics->sum->violations->total, 0); ?></div>
                <div class="bloc-action">
                    <a href="violations.html">View details &gt;</a>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <?php echo $this->getTrend('sum', 'loc'); ?>
                <div class="label"><a href="loc.html">Lines of code</a></div>
                <div class="number"><?php echo number_format($this->sharedMetrics->sum->loc, 0); ?></div>
                <div class="bloc-action">
                    <a href="loc.html">View details &gt;</a>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <?php echo $this->getTrend('sum', 'nbClasses'); ?>
                <div class="label"><a href="oop.html">Classes</a></div>
                <div class="number"><?php echo number_format($this->sharedMetrics->sum->nbClasses, 0); ?></div>
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
                <div class="number"><?php echo number_format($this->sharedMetrics->avg->ccn, 2); ?></div>
                <div class="bloc-action">
                    <a href="complexity.html">View details &gt;</a>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <?php echo $this->getTrend('avg', 'bugs', true); ?>
                <div class="label"><a href="complexity.html">Average bugs by class</a></div>
                <div class="number">
                    <?php echo number_format($this->sharedMetrics->avg->bugs, 2); ?>
                </div>
                <div class="bloc-action">
                    <a href="complexity.html">View details &gt;</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="column column-help">
            <div class="bloc" style="min-height: 475px;">
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
        <?php if($this->isHomePage()) {?>
          <div class="column">
            <div class="bloc" style="min-height: 475px;">
              <div class="label">
                <a href="composer.html">Composer</a>
              </div>
                <?php
                $packages = isset($this->sharedMetrics->project['composer']['packages']) ? $this->sharedMetrics->project['composer']['packages'] : [];
                $packagesInstalled = isset($this->sharedMetrics->project['composer']['packages-installed']) ? $this->sharedMetrics->project['composer']['packages-installed'] : [];
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
    </div>

    <script>
        document.onreadystatechange = function () {
            if (document.readyState === 'complete') {
                chartMaintainability();

                // prepare json for packages pie
                <?php
                $json = [];
                $packages = isset($this->sharedMetrics->project['composer']['packages']) ? $this->sharedMetrics->project['composer']['packages'] : [];
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
