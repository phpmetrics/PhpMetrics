<?php
require __DIR__ . '/_header.php'; ?>

<?php
if (!isset($project['unitTesting'])) {
    echo '<div class="row"><div class="column">Please use the <code>--junit</code> option to enable this report</div></div>';
    return;
}

$unit = $project['unitTesting'];
$getMetricForClass = function ($classname, $metric) use ($classes) {
    foreach ($classes as $class) {
        if ($classname !== $class['name']) {
            continue;
        }

        return $class[$metric];
    }

    return '-';
};
?>


<div class="row">
    <div class="column">
        <div class="bloc bloc-number">
            <div class="label">Test suites</div>
            <div class="number">
                <?php echo $unit['nbSuites']; ?>
            </div>
        </div>
    </div>
    <div class="column">
        <div class="bloc bloc-number">
            <div class="label">Assertions</div>
            <div class="number">
                <?php echo $unit['assertions']; ?>
            </div>
        </div>
    </div>
    <div class="column">
        <div class="bloc bloc-number">
            <div class="label">
                classes never called by tests
                <small>(<?php echo $unit['percentUncoveredClasses']; ?> %)</small>
            </div>
            <div class="number">
                <?php echo $unit['nbUncoveredClasses']; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="column">
        <div class="bloc">
            <h4>These classes are never called by tests</h4>
            <div class="help">
                <div class="help-inner">
                    Tests with high Cyclomatic number or high probability of bugs should be covered by unit tests.
                </div>
            </div>
            <div class="clusterize small">
                <div id="scrollAreaJunitNeverCalled" class="clusterize-scroll">
                    <table>
                        <thead>
                        <tr>
                            <th>Class</th>
                            <th class="js-sort-number">Cyclomatic</th>
                            <th class="js-sort-number">Bugs</th>
                        </tr>
                        </thead>
                        <tbody id="contentAreaJunitNeverCalled" class="clusterize-content">
                        <?php
                        foreach ($classes as $class) {
                            if ($class['numberOfUnitTests'] > 0 || $class['interface']) {
                                continue;
                            }
                            ?>
                            <tr>
                                <td><span class="path"><?php echo $class['name']; ?></span></td>
                                <?php foreach (['ccn', 'bugs'] as $attribute) {?>
                                    <td>
                                        <span class="badge" <?php echo gradientStyleFor($classes, $attribute, $class[$attribute]);?>>
                                        <?php echo isset($class[$attribute]) ? $class[$attribute] : ''; ?>
                                        </span>
                                    </td>
                                <?php } ?>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="column">
        <div class="bloc">
            <h4>Execution time of tests</h4>

            <div id="svg-unit-time"></div>
        </div>
    </div>
</div>

<div class="row">

    <div class="column">
        <div class="bloc">
            <h4>These classes are called by tests</h4>

            <div class="clusterize small">
                <div id="scrollAreaJunitCalled" class="clusterize-scroll">
                    <table>
                        <thead>
                        <tr>
                            <th>TestSuite</th>
                            <th class="js-sort-number">Called by these classes</th>
                        </tr>
                        </thead>
                        <tbody id="contentAreaJunitCalled" class="clusterize-content">
                        <?php foreach ($unit['tests'] as $suite) { ?>
                            <tr>
                                <td valign="top"><span class="path"><?php echo $suite->classname; ?></span></td>
                                <td style="padding-bottom: 1em;">
                                    <?php
                                    foreach ($suite->externals as $index => $external) { ?>
                                        <?php echo ($index === 0) ? '' : '<br />'; ?>
                                        <span class="badge" title="Cyclomatic complexity of class">
                                            <?php echo $getMetricForClass($external, 'ccn'); ?>
                                        </span>
                                        <span class="path"><?php echo $external; ?></span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/_footer.php'; ?>

    <script type="text/javascript">
        var clusterize = new Clusterize({
            scrollId: 'scrollAreaJunitNeverCalled',
            contentId: 'contentAreaJunitNeverCalled'
        });

        var clusterize = new Clusterize({
            scrollId: 'scrollAreaJunitCalled',
            contentId: 'contentAreaJunitCalled'
        });
    </script>


    <script type="text/javascript">

        function chartTreeUnit() {
            // from http://bl.ocks.org/masakick/04ad1502068302abbbcb
            var w = document.getElementById('svg-unit-time').offsetWidth,
                h = 400,
                x = d3.scale.linear().range([0, w]),
                y = d3.scale.linear().range([0, h]),
                color = d3.scale.category20c(),
                root,
                node;

            var treemap = d3.layout.treemap()
                .round(false)
                .size([w, h])
                .sticky(true)
                //    .value(function(d) { return d["好き度"]; });
                .value(function (d) {
                    return d.time;
                });

            var svg = d3.select('#svg-unit-time').append('div')
                .attr("class", "chart")
                .style("width", w + "px")
                .style("height", h + "px")
                .append("svg:svg")
                .attr("width", w)
                .attr("height", h)
                .append("svg:g")
                .attr("transform", "translate(.5,.5)");


            <?php
            // prepare json
            $unitTimeJson = new stdClass;
            $unitTimeJson->name = 'Execution time';
            $unitTimeJson->children = [];
            foreach ($unit['tests'] as $test) {
                array_push($unitTimeJson->children, [
                    'name' => $test->classname,
                    'time' => (float)$test->time,
                ]);
            }
            ?>

            var json = <?php echo json_encode($unitTimeJson);?>;
            root = json;
            var nodes = treemap.nodes(json)
                .filter(function (d) {
                    return d.time;
                    //return d.children;
                });

            var cell = svg.selectAll("g")
                    .data(nodes)
                    .enter().append("svg:g")
                    .attr("class", "cell")
                    .attr("transform", function (d) {
                        return "translate(" + d.x + "," + d.y + ")";
                    })
                    .on("click", function (d) {
                        return zoom(node == root ? d : root);
                        return zoom(node == d.parent ? root : d.parent);
                    })
                ;

            cell.append("svg:rect")
                .attr("width", function (d) {
                    return d.dx - 1;
                })
                .attr("height", function (d) {
                    return d.dy - 1;
                })
                .style("fill", function (d) {
                    return '#AED581';
                })
            ;

            cell.append("svg:text")
                .attr("x", function (d) {
                    return d.dx / 2;
                })
                .attr("y", function (d) {
                    return d.dy / 2;
                })
                .attr("dy", ".35em")
                .attr("text-anchor", "middle")
                .text(function (d) {
                    var long = d.name + "\n" + '(' + d.time + ' secs)';
                    var short = d.name.substr(0, 20) + "...";
                    return d.dx > d.w ? short : long;
                })
                .style("opacity", function (d) {
                    d.w = this.getComputedTextLength();
                    return d.dx > d.w ? 1 : 0.2;
                })
                .attr('fill', function (d) {
                    return '#333'
                });

            //d3.select(window).on("click", function () {
            //    zoom(root);
            //});
            function zoom(d) {
                var kx = w / d.dx, ky = h / d.dy;
                x.domain([d.x, d.x + d.dx]);
                y.domain([d.y, d.y + d.dy]);

                var t = svg.selectAll("g.cell").transition()
                    .duration(d3.event.altKey ? 7500 : 750)
                    .attr("transform", function (d) {
                        return "translate(" + x(d.x) + "," + y(d.y) + ")";
                    });

                t.select("rect")
                    .attr("width", function (d) {
                        return kx * d.dx - 1;
                    })
                    .attr("height", function (d) {
                        return ky * d.dy - 1;
                    });

                t.select("text")
                    .attr("x", function (d) {
                        return kx * d.dx / 2;
                    })
                    .attr("y", function (d) {
                        return ky * d.dy / 2;
                    })
                    .style("opacity", function (d) {
                        return kx * d.dx > d.w ? 1 : 0;
                    });

                node = d;
                d3.event.stopPropagation();
            }
        }
        chartTreeUnit();
    </script>
