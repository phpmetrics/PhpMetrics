<?php
require __DIR__ . '/_header.php'; ?>

    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Average weighted method count by class <small>(CC)</small></div>
                <div class="number">
                    <?php echo $avg->wmc; ?>
                </div>
                <?php echo $this->getTrend('avg', 'wmc', true); ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Average cyclomatic complexity by class</div>
                <div class="number">
                    <?php echo $avg->ccn; ?>
                </div>
                <?php echo $this->getTrend('avg', 'ccn', true); ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Average relative System complexity</div>
                <div class="number">
                    <?php echo $avg->relativeSystemComplexity; ?>
                </div>
                <?php echo $this->getTrend('avg', 'relativeSystemComplexity', true); ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">Average bugs by class<small>(Halstead)</small></div>
                <div class="number">
                    <?php echo $avg->bugs; ?>
                </div>
                <?php echo $this->getTrend('avg', 'bugs', true); ?>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="label">average defects by class <small>(Kan)</small></div>
                <div class="number">
                    <?php echo $avg->kanDefect; ?>
                </div>
                <?php echo $this->getTrend('avg', 'kanDefect', true); ?>
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
                        <th class="js-sort-number">WMC</th>
                        <th class="js-sort-number">Class cycl.</th>
                        <th class="js-sort-number">Max method cycl.</th>
                        <th class="js-sort-number">Relative system complexity</th>
                        <th class="js-sort-number">Relative data complexity</th>
                        <th class="js-sort-number">Relative structural complexity</th>
                        <th class="js-sort-number">Bugs</th>
                        <th class="js-sort-number">Defects</th>
                        <?php if ($config->has('junit')) { ?>
                            <th class="js-sort-number">Unit testsuites calling it</th>
                        <?php } ?>
                    </tr>
                    </thead>
                    <?php foreach ($classes as $class) {
                        $shortClassName = substr($class['name'], strrpos($class['name'], '\\') + 1);
                    ?>
                        <tr>
                            <td class="className">
                                <a onclick="return toggle('<?php echo $shortClassName; ?>');">
                                    <span class="path"><?php echo $class['name']; ?></span>
                                </a>
                            </td>
                            <?php foreach (['wmc', 'ccn', 'ccnMethodMax', 'relativeSystemComplexity', 'relativeDataComplexity', 'relativeStructuralComplexity', 'bugs', 'kanDefect'] as $attribute) {?>
                                <td>
                                    <span class="badge" <?php echo gradientStyleFor($classes, $attribute, $class[$attribute]);?>>
                                    <?php echo isset($class[$attribute]) ? $class[$attribute] : ''; ?>
                                    </span>
                                </td>
                            <?php } ?>
                            <?php if ($config->has('junit')) { ?>
                                <td><?php echo isset($class['numberOfUnitTests']) ? $class['numberOfUnitTests'] : ''; ?></td>
                            <?php } ?>
                            <td>
                                <div class="details" id="<?php echo $shortClassName; ?>">
                                    <div class="table">
                                        <?php foreach ($class['methods'] as $method) { ?>
                                            <div class="methods-list">
                                                <span><?php echo $class['name'] . '::' . $method->getName(); ?></span>
                                                <span></span>
                                                <span></span>
                                                <span><span class="badge" <?php echo gradientStyleFor($classes, 'ccnMethodMax', $method->get('ccn'));?>><?php echo $method->get('ccn'); ?></span></span>
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                                <?php if ($config->has('junit')) { ?>
                                                    <span></span>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        window.onload = window.onresize = function() {
            initDetails();
        };

        function initDetails() {
            var methods = document.getElementsByClassName('methods-list');
            var measures = extractMeasures(methods[0].parentNode.parentNode.parentNode.parentNode.querySelector('.className'));
            for (var i = 0; i < methods.length; i++) {
                methods[i].children[0].style.width = measures('className') + "px";
                methods[i].children[1].style.width = measures('classWmc') + "px";
                methods[i].children[2].style.width = measures('classCcn') + "px";
                methods[i].children[3].style.width = measures('classMax') + "px";
            }
        }

        function extractMeasures(element) {
            if (element == null) throw Error('No element provided.');
            var cache = {};

            return function (key) {
                var measurers = {
                    'className': function () {return element.clientWidth},
                    'classWmc': function () {return element.nextElementSibling.clientWidth},
                    'classCcn': function () {return element.nextElementSibling.nextElementSibling.clientWidth},
                    'classMax': function () {return element.nextElementSibling.nextElementSibling.nextElementSibling.clientWidth},
                }

                if (measurers[key] === undefined) {
                    throw Error(`${key} is not supported`)
                }

                var result = cache[key];
                result || (result = cache[key] = measurers[key]())
                return result;
            };
        }

        function toggle(id) {
            var el = document.getElementById(id);
            if (el.style.display === 'block') {
                el.parentNode.parentNode.style.height = el.parentNode.parentNode.clientHeight - el.clientHeight + "px";
                el.style.display = 'none';
            } else {
                el.style.display = 'block';
                el.parentNode.parentNode.style.height = el.parentNode.parentNode.clientHeight + el.clientHeight + "px";
            }
        }
    </script>
<?php require __DIR__ . '/_footer.php'; ?>
