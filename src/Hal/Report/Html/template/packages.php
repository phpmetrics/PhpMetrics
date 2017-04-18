<?php
require __DIR__ . '/_header.php'; ?>

    <div class="row">
        <div class="column">
            <div class="bloc">
                <table class="js-sort-table" id="table-length">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th class="js-sort-number">Classes</th>
                        <th class="js-sort-number">Abstraction</th>
                        <th class="js-sort-number">Instability</th>
                        <th class="js-sort-number">Outgoing class dependencies</th>
                        <th class="js-sort-number">Outgoing package dependencies</th>
                        <th class="js-sort-number">Incoming class dependencies</th>
                        <th class="js-sort-number">Incoming package dependencies</th>
                    </tr>
                    </thead>
                    <?php
                    foreach ($packages as $package) { ?>
                        <tr>
                            <td><?= $package['name'] === '\\' ? 'global' : substr($package['name'], 0, -1); ?></td>
                            <td><?= $package['classes'] ? count($package['classes']) : 0; ?></td>
                            <td><?= isset($package['abstraction']) ? round($package['abstraction'], 3) : ''; ?></td>
                            <td><?= isset($package['instability']) ? round($package['instability'], 3) : ''; ?></td>
                            <td><?= isset($package['outgoing_class_dependencies']) ? count($package['outgoing_class_dependencies']) : 0; ?></td>
                            <td><?= isset($package['outgoing_package_dependencies']) ? count($package['outgoing_package_dependencies']) : 0; ?></td>
                            <td><?= isset($package['incoming_class_dependencies']) ? count($package['incoming_class_dependencies']) : 0; ?></td>
                            <td><?= isset($package['incoming_package_dependencies']) ? count($package['incoming_package_dependencies']) : 0; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>


<?php require __DIR__ . '/_footer.php'; ?>