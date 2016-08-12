<?php require __DIR__ . '/_header.php'; ?>

<?php
$violations = [
    'total' => 0,
    'information' => 0,
    'warning' => 0,
    'error' => 0,
    'critical' => 0,
];
$map = [
    \Hal\Violation\Violation::INFO => 'information',
    \Hal\Violation\Violation::WARNING => 'warning',
    \Hal\Violation\Violation::ERROR => 'error',
    \Hal\Violation\Violation::CRITICAL => 'critical',
];
foreach ($classes as $class) {
    foreach ($class['violations'] as $violation) {

        $violations['total']++;

        $name = $map[$violation->getLevel()];
        $violations[$name]++;
    }
}
$violations = (object)$violations;
?>

    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number"><?php echo $violations->total; ?></div>
                <div class="label">Violations</div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number"><?php echo $violations->information; ?></div>
                <div class="label">Information</div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number"><?php echo $violations->warning; ?></div>
                <div class="label">Warnings</div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number"><?php echo $violations->error; ?></div>
                <div class="label">Errors</div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number"><?php echo $violations->critical; ?></div>
                <div class="label">Criticals</div>
            </div>
        </div>
    </div>

<?php if ($violations->total > 0) { ?>
    <div class="row">
        <div class="column">
            <div class="bloc">
                <h4>Violations</h4>
                <table class="table-pagerank table-small">
                    <thead>
                    <tr>
                        <th>Component</th>
                        <th>Violations</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($classes as $class) {
                        if (sizeof($class['violations']) > 0) {
                            $currentId = 'bloc-' . uniqid();
                            ?>

                            <tr>
                                <td>
                                    <a onclick="return toggle('<?php echo $currentId; ?>');"><?php echo $class['name']; ?></a>
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