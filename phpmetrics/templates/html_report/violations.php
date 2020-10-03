<?php require __DIR__ . '/_header.php'; ?>

<?php
$map = [
    \Hal\Violation\Violation::INFO => 'information',
    \Hal\Violation\Violation::WARNING => 'warning',
    \Hal\Violation\Violation::ERROR => 'error',
    \Hal\Violation\Violation::CRITICAL => 'critical',
];
?>

    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Violations</div>
                <div class="number"><?php echo $sum->violations->total; ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Information</div>
                <div class="number"><?php echo $sum->violations->information; ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Warnings</div>
                <div class="number"><?php echo $sum->violations->warning; ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Errors</div>
                <div class="number"><?php echo $sum->violations->error; ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Criticals</div>
                <div class="number"><?php echo $sum->violations->critical; ?></div>
            </div>
        </div>
    </div>

<?php if ($sum->violations->total > 0) { ?>
    <div class="row">
        <div class="column">
            <div class="bloc">
                <h4>Class Violations</h4>
                <table class="table-pagerank table-small">
                    <thead>
                    <tr>
                        <th>Class</th>
                        <th>Violations</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($classes as $class) {
                        if (count($class['violations']) > 0) {
                            $currentId = 'bloc-' . uniqid('', true);
                            ?>

                            <tr>
                                <td>
                                    <a onclick="return toggle('<?php echo $currentId; ?>');">
                                        <span class="path"><?php echo $class['name']; ?></span>
                                    </a>
                                    <div class="violation-list" id="<?php echo $currentId; ?>">
                                        <?php foreach ($class['violations'] as $violation) { ?>
                                            <div class="violation">
                                                <div class="name">
                                                    <?php echo $violation->getName(); ?>
                                                    <span
                                                        class="badge level level-<?php echo $map[$violation->getLevel()]; ?>"><?php echo $map[$violation->getLevel()]; ?></span>

                                                </div>
                                                <div
                                                    class="description"><?php echo nl2br($violation->getDescription()); ?></div>

                                            </div>
                                        <?php } ?>
                                    </div>
                                </td>

                                <td valign="top">
                                    <?php foreach ($class['violations'] as $violation) { ?>
                                        <span
                                            class="badge level level-<?php echo $map[$violation->getLevel()]; ?>"> <?php echo $violation->getName(); ?></span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php }
                    } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="column">
            <div class="bloc">
                <h4>Package Violations</h4>
                <table class="table-pagerank table-small">
                    <thead>
                    <tr>
                        <th>Package</th>
                        <th>Violations</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($packages as $package) {
                        if (count($package['violations']) > 0) {
                            $currentId = 'bloc-' . uniqid();
                            ?>

                            <tr>
                                <td>
                                    <a onclick="return toggle('<?php echo $currentId; ?>');">
                                        <span class="path"><?php echo substr($package['name'], 0, -1); ?></span>
                                    </a>
                                    <div class="violation-list" id="<?php echo $currentId; ?>">
                                        <?php foreach ($package['violations'] as $violation) { ?>
                                            <div class="violation">
                                                <div class="name">
                                                    <?php echo $violation->getName(); ?>
                                                    <span
                                                            class="badge level level-<?php echo $map[$violation->getLevel()]; ?>"><?php echo $map[$violation->getLevel()]; ?></span>

                                                </div>
                                                <div
                                                        class="description"><?php echo nl2br($violation->getDescription()); ?></div>

                                            </div>
                                        <?php } ?>
                                    </div>
                                </td>

                                <td valign="top">
                                    <?php foreach ($package['violations'] as $violation) { ?>
                                        <span
                                                class="badge level level-<?php echo $map[$violation->getLevel()]; ?>"> <?php echo $violation->getName(); ?></span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php }
                    } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>

    <script type="text/javascript">
        function toggle(id) {
            var div = document.getElementById(id);
            if (div.style.display === 'block') {
                div.style.display = 'none';
            }
            else {
                div.style.display = 'block';
            }
        }
    </script>
<?php require __DIR__ . '/_footer.php'; ?>
