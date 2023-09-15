<?php

use Hal\Report\Html\ViewHelper;

require __DIR__ . '/_header.php';
/** @var ViewHelper $viewHelper */
$viewHelper = $this->viewHelper;
?>

    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Average weighted method count by class <small>(CC)</small></div>
                <div class="number">
                    <?php echo $this->sharedMetrics->avg->wmc; ?>
                </div>
                <?php echo $this->getTrend('avg', 'wmc', true); ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Average cyclomatic complexity by class</div>
                <div class="number">
                    <?php echo $this->sharedMetrics->avg->ccn; ?>
                </div>
                <?php echo $this->getTrend('avg', 'ccn', true); ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Average relative System complexity</div>
                <div class="number">
                    <?php echo $this->sharedMetrics->avg->relativeSystemComplexity; ?>
                </div>
                <?php echo $this->getTrend('avg', 'relativeSystemComplexity', true); ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Average bugs by class<small>(Halstead)</small></div>
                <div class="number">
                    <?php echo $this->sharedMetrics->avg->bugs; ?>
                </div>
                <?php echo $this->getTrend('avg', 'bugs', true); ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">average defects by class <small>(Kan)</small></div>
                <div class="number">
                    <?php echo $this->sharedMetrics->avg->kanDefect; ?>
                </div>
                <?php echo $this->getTrend('avg', 'kanDefect', true); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="column">
            <div class="bloc">
                <table class="js-sort-table nested-table-top" id="table-length">
                    <thead>
                    <tr>
                        <th>Class</th>
                        <th class="js-sort-number" title="Weight Method Count">WMC</th>
                        <th class="js-sort-number" title="Class Cyclomatic complexity">CC</th>
                        <th class="js-sort-number" title="Highest Method cyclomatic complexity">Max MC</th>
                        <th class="js-sort-number" title="Relative system complexity">System comp.</th>
                        <th class="js-sort-number" title="Relative data complexity">Data comp.</th>
                        <th class="js-sort-number" title="Relative structural complexity">Structural comp.</th>
                        <th class="js-sort-number" title="Delivered bugs (Halstead)">Bugs</th>
                        <th class="js-sort-number" title="Rate defects (Kan)">Defects</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($this->sharedMetrics->classes as $class) {
                      $classHash = md5($class['name']);
                    ?>
                        <tr>
                            <td>
                              <?php if ([] !== $class['methods']) { ?>
                              <a href="javascript:" class="toggle-complexity" onclick="toggleNestedTable(this, 'table-complexity-class-<?php echo $classHash; ?>')"><i>►</i> <span class="path"><?php echo $class['name']; ?></span></a>
                              <div id="table-complexity-class-<?php echo $classHash; ?>">
                                <table class="js-sort-table">
                                  <thead>
                                  <tr>
                                    <th>Method</th>
                                    <th class="js-sort-number" title="Method Cyclomatic complexity">Cyclomatic complexity</th>
                                  </tr>
                                  </thead>
                                  <tbody>
                                  <?php foreach ($class['methods'] as $method) { ?>
                                    <?php if ($method->get('isAccessor')) { ?>
                                    <tr>
                                      <td><span class="path ignored"><?php echo $class['name'] . '::' . $method->getName(); ?></span> <small>(accessors are ignored)</small></td>
                                      <td><span>-</span></td>
                                    </tr>
                                    <?php } else { ?>
                                    <tr>
                                      <td><span class="path"><?php echo $class['name'] . '::' . $method->getName(); ?></span></td>
                                      <td><span><?php echo $method->get('ccn'); ?></span></td>
                                    </tr>
                                  <?php } ?>
                                  <?php } ?>
                                  </tbody>
                                </table>
                              </div>
                              <?php } else { ?>
                              <i style="visibility:hidden">►</i> <span class="path"><?php echo $class['name']; ?></span>
                              <?php } ?>
                            </td>
                            <?php foreach (['wmc', 'ccn', 'ccnMethodMax', 'relativeSystemComplexity', 'relativeDataComplexity', 'relativeStructuralComplexity', 'bugs', 'kanDefect'] as $attribute) {?>
                                <td>
                                    <span class="badge" <?php echo $viewHelper->gradientStyleFor($this->sharedMetrics->classes, $attribute, $class[$attribute]);?>>
                                    <?php echo $class[$attribute] ?? ''; ?>
                                    </span>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


<?php require __DIR__ . '/_footer.php'; ?>
