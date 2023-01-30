<?php

use Hal\Report\Html\ViewHelper;

require __DIR__ . '/_header.php';
/** @var ViewHelper $viewHelper */
$viewHelper = $this->viewHelper;

$lcom = [];
foreach ($this->sharedMetrics->classes as $c) {
    if (!$c['interface']) {
        array_push($lcom, $c['lcom']);
    }
}
if (count($lcom) > 0) {
    $lcom = round(array_sum($lcom) / count($lcom), 2);
} else {
    $lcom = 0;
}
?>


    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">classes <?php echo $this->getTrend('sum', 'nbClasses'); ?></div>
                <div class="number">
                    <?php echo $this->sharedMetrics->sum->nbClasses; ?>
                    <small> (<?php echo (count($this->sharedMetrics->classes) ? round($this->sharedMetrics->sum->nbClasses / count($this->sharedMetrics->classes) * 100) : '0'); ?> %)</small>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">interfaces <?php echo $this->getTrend('sum', 'nbInterfaces'); ?></div>
                <div class="number"><?php echo $this->sharedMetrics->sum->nbInterfaces; ?>
                    <small> (<?php echo (count($this->sharedMetrics->classes) ? round($this->sharedMetrics->sum->nbInterfaces / count($this->sharedMetrics->classes) * 100) : '0'); ?> %)</small>
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
                <div class="number"><?php echo $this->sharedMetrics->sum->nbClasses ? round($this->sharedMetrics->sum->lloc / $this->sharedMetrics->sum->nbClasses) : '-'; ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">logical lines of code by method</div>
                <div class="number"><?php echo $this->sharedMetrics->sum->nbMethods ? round($this->sharedMetrics->sum->lloc / $this->sharedMetrics->sum->nbMethods) : '-'; ?></div>
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
                    foreach ($this->sharedMetrics->classes as $class) { ?>
                        <tr>
                            <td><span class="path"><?php echo $class['name']; ?></span></td>
                            <?php foreach (['lcom', 'volume', 'ccn', 'ccnMethodMax', 'bugs', 'difficulty'] as $attribute) {?>
                                <td>
                                    <span class="badge" <?php echo $viewHelper->gradientStyleFor($this->sharedMetrics->classes, $attribute, $class[$attribute]);?>>
                                    <?php echo isset($class[$attribute]) ? $class[$attribute] : ''; ?>
                                    </span>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>


<?php require __DIR__ . '/_footer.php'; ?>
