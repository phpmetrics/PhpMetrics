<?php require __DIR__ . '/_header.php'; ?>
    <div class="row">
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number"><?php echo $sum->loc; ?></div>
                <div class="label">lines of code <?php echo $this->getTrend('sum', 'loc'); ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number"><?php echo $sum->nbClasses; ?></div>
                <div class="label">classes <?php echo $this->getTrend('sum', 'nbClasses'); ?></div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div
                    class="number"><?php echo $sum->nbClasses ? round($sum->nbMethods / $sum->nbClasses) : '-'; ?></div>
                <div class="label">methods by class</div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number"><?php echo $sum->nbClasses ? round($sum->lloc / $sum->nbClasses) : '-'; ?></div>
                <div class="label">logical lines of code by class</div>
            </div>
        </div>
        <div class="column">
            <div class="bloc bloc-number">
                <div class="number"><?php echo $sum->nbMethods ? round($sum->lloc / $sum->nbMethods) : '-'; ?></div>
                <div class="label">logical lines of code by method</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="column">
            <div class="bloc">
                <h4>Maintainability / complexity</h4>
                <div id="svg-maintainability"></div>
                <div class="help">
                    <p>Each file is symbolized by a circle. Size of the circle represents the Cyclomatic complexity.
                        Color
                        of the circle represents the Maintainability Index.</p>

                    <p>Large red circles will be probably hard to maintain.</p>
                </div>
            </div>


        </div>

        <div class="column column-75">
            <div class="bloc">
                <h4>Top 10 ClassRank</h4>
                <table id="table-pagerank">
                    <thead>
                    <tr>
                        <th>Class</th>
                        <th>ClassRank</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $classesS = $classes;
                    usort($classesS, function ($a, $b) {
                        return strcmp($b['pageRank'], $a['pageRank']);
                    });
                    $classesS = array_slice($classesS, 0, 10);
                    foreach ($classesS as $class) { ?>
                        <tr>
                            <td><?php echo $class['name']; ?> <span class="badge"
                                                                    title="Maintainability Index"><?php echo isset($class['mi']) ? $class['mi'] : ''; ?></span>
                            </td>
                            <td><?php echo $class['pageRank']; ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <div class="help">
                    PageRank applied to relations beetwen classes.
                </div>
            </div>

        </div>
    </div>
    </div>
    </div>


    <script type="text/javascript">
        function chartMaintainability() {

            var diameter = document.getElementById('svg-maintainability').offsetWidth;

            var json = {
                name: 'chart',
                children: classes
            };

            var svg = d3.select('#svg-maintainability').append('svg')
                .attr('width', diameter)
                .attr('height', diameter);


            var bubble = d3.layout.pack()
                .size([diameter, diameter])
                .padding(3)
                .value(function (d) {
                    return d.ccn;
                });

            var nodes = bubble.nodes(json)
                .filter(function (d) {
                    return !d.children;
                }); // filter out the outer bubble*

            var vis = svg.selectAll('circle')
                .data(nodes, function (d) {
                    return d.name;
                });

            vis.enter().append('circle')
                .attr('transform', function (d) {
                    return 'translate(' + d.x + ',' + d.y + ')';
                })
                .attr('r', function (d) {
                    return d.r;
                })
                .style("fill", function (d) {
                    if (d.mi > 85) {
                        return '#8BC34A';
                    } else if (d.mi > 69) {
                        return '#FFC107';
                    } else {
                        return '#F44336';
                    }
                })
                .attr("transform", function (d) {
                    return "translate(" + d.x + "," + d.y + ")";
                })
                .on('mouseover', function (d) {
                    var text = '<strong>' + d.name + '</strong>'
                        + "<br />Cyclomatic Complexity : " + d.ccn
                        + "<br />Maintainability Index: " + d.mi;
                    d3.select('.tooltip').html(text);
                    d3.select(".tooltip").style("opacity", 1);
                })
                .on('mousemove', function () {
                    d3.select(".tooltip")
                        .style("left", (d3.event.pageX + 5) + "px")
                        .style("top", (d3.event.pageY + 5) + "px");
                })
                .on('mouseout', function () {
                    d3.select(".tooltip").style("opacity", 0);
                });

            d3.select("body")
                .append("div")
                .attr("class", "tooltip")
                .style("opacity", 0);

        }


        document.onreadystatechange = function () {
            if (document.readyState === 'complete') {
                chartMaintainability();
            }
        };
    </script>

<?php require __DIR__ . '/_footer.php'; ?>