<?php
$fullwidth = true;
require __DIR__ . '/_header.php'; ?>

<?php

$classesC = [];
foreach ($classes as $c) {
    if (!$c['interface']) {
        unset($c['interface']);
        $classesC[$c['name']] = $c;
    }
}

?>

    <div class="row">
        <div class="column">
            <div class="bloc">
                <table class="js-sort-table" id="table-length">
                    <thead>
                    <tr>
                        <?php foreach ((array) current($classesC) as $k => $v) { ?>
                            <th class="js-sort-number"><?php echo $k; ?></th>
                        <?php } ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($classesC as $class) { ?>
                        <tr>
                            <?php foreach ($class as $k => $v) { ?>
                                <td class="js-sort-number"><?php echo is_array($v) ? sizeof($v) : $v; ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                    </tbody>
            </div>
        </div>
    </div>
<?php require __DIR__ . '/_footer.php'; ?>