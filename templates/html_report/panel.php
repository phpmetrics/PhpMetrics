<?php
$fullwidth = true;
require __DIR__ . '/_header.php'; ?>

    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number"><?php echo $this->sharedMetrics->sum->loc; ?></div>
                <div class="label">lines of code <?php echo $this->getTrend('sum', 'loc'); ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number">
                    <?php echo $this->sharedMetrics->sum->nbClasses; ?>
                    <small> (<?php echo(count($this->sharedMetrics->classes) ? round($this->sharedMetrics->sum->nbClasses / count($this->sharedMetrics->classes) * 100) : '0'); ?>
                        %)
                    </small>
                </div>
                <div class="label">classes <?php echo $this->getTrend('sum', 'nbClasses'); ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number"><?php echo $this->sharedMetrics->sum->nbInterfaces; ?>
                    <small> (<?php echo(count($this->sharedMetrics->classes) ? round($this->sharedMetrics->sum->nbInterfaces / count($this->sharedMetrics->classes) * 100) : '0'); ?>
                        %)
                    </small>
                </div>
                <div class="label">interfaces <?php echo $this->getTrend('sum', 'nbInterfaces'); ?></div>
            </div>
        </div>


    </div>

    <div class="row">

        <div class="column">
            <div class="bloc bloc-number">
                <div
                    class="number"><?php echo $this->sharedMetrics->sum->nbClasses ? round($this->sharedMetrics->sum->nbMethods / $this->sharedMetrics->sum->nbClasses) : '-'; ?></div>
                <div class="label">methods by class</div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number"><?php echo $this->sharedMetrics->sum->nbClasses ? round($this->sharedMetrics->sum->lloc / $this->sharedMetrics->sum->nbClasses) : '-'; ?></div>
                <div class="label">logical lines of code by class</div>
            </div>
        </div>


        <div class="column">
            <div class="bloc bloc-number">
                <div class="number"><?php echo $this->sharedMetrics->sum->nbMethods ? round($this->sharedMetrics->sum->lloc / $this->sharedMetrics->sum->nbMethods) : '-'; ?></div>
                <div class="label">logical lines of code by method</div>
            </div>
        </div>

        <div class="column">
            <div class="bloc bloc-number">
                <div
                    class="number"><?php echo $this->sharedMetrics->avg->lcom ?></div>
                <div class="label">average LCOM <?php echo $this->getTrend('avg', 'lcom', true); ?></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number">
                    <?php echo $this->sharedMetrics->avg->ccn; ?>
                </div>
                <div class="label">Average cyclomatic complexity by class <?php echo $this->getTrend('avg', 'ccn',
                        true); ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc">
                <div>
                    <h4>Maintainability / complexity</h4>
                    <div id="svg-maintainability"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.onreadystatechange = function () {
            if (document.readyState === 'complete') {
                chartMaintainability();
            }
        };
    </script>

<?php
require __DIR__ . '/_footer.php'; ?>
