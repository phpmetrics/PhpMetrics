<?php require __DIR__ . '/_header.php'; ?>

<div class="row">
    <div class="column">
        <div class="bloc">
            <h4>Coupling</h4>

            <div class="help">
                <strong>Afferent coupling (AC)</strong> is the number of classes affected by given class.
                <br/><strong>Efferent coupling (EC)</strong> is the number of classes from which given class receives
                effects.
            </div>

            <table id="table-relations" class="js-sort-table">
                <thead>
                <tr>
                    <th>Class</th>
                    <th class="js-sort-number">Afferent coupling</th>
                    <th class="js-sort-number">Efferent coupling</th>
                    <th class="js-sort-number">Instability</th>
                    <th class="js-sort-number">ClassRank</th>
                </thead>
                <tbody>
                <?php foreach ($classes as $class) { ?>
                    <tr>
                        <td><span class="path"><?php echo $class['name']; ?></span></td>
                        <?php foreach (['afferentCoupling', 'efferentCoupling', 'instability', 'pageRank'] as $attribute) {?>
                            <td>
                                <span class="badge" <?php echo gradientStyleFor($classes, $attribute, $class[$attribute]);?>);">
                                <?php echo isset($class[$attribute]) ? $class[$attribute] : ''; ?>
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


<script type="text/javascript">
    sortTable(document.getElementById('table-relations'), 1, -1);
</script>
