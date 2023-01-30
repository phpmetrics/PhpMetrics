<?php require __DIR__ . '/_header.php'; ?>

<?php
$relations = [];
$classesCp = [];
foreach ($this->sharedMetrics->classes as $class) {

    $class['name'] = '\\' . $class['name'];
    $classesCp[$class['name']] = $class;
    $classesCp[$class['name']]['externals'] = [];


    foreach ($class['externals'] as &$ext) {
        $ext = '\\' . $ext;
        if (!isset($this->sharedMetrics->classes[$ext])) {
            $classesCp[$ext] = [
                'name' => $ext,
                'externals' => [],
            ];
        }
        $classesCp[$class['name']]['externals'][] = $ext;
    }
}
foreach ($classesCp as $class) {
    array_push($relations, (object)[
        'name' => $class['name'],
        'size' => 3000,
        'relations' => (array)array_values(array_unique($class['externals'])),
    ]);
}
?>


<div class="row">
    <div class="column">
        <div class="bloc">
            <h4>Object relations</h4>
            <div id="chart-relations"></div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>


<script>


    var relations = <?php echo json_encode($relations, JSON_PRETTY_PRINT); ?>;

    /**
     * Thanks to http://bl.ocks.org/mbostock/raw/7607999/
     */
    function updateRelationsChart() {


        var diameter = document.getElementById('chart-relations').offsetWidth,
            radius = diameter / 2,
            innerRadius = radius - 120;

        var cluster = d3.layout.cluster()
            .size([360, innerRadius])
            .sort(null)
            .value(function (d) {
                return d.size;
            });

        var bundle = d3.layout.bundle();

        var line = d3.svg.line.radial()
            .interpolate("bundle")
            .tension(.85)
            .radius(function (d) {
                return d.y;
            })
            .angle(function (d) {
                return d.x / 180 * Math.PI;
            });

        var svg = d3.select("#chart-relations").append("svg")
            .attr("width", diameter)
            .attr("height", diameter)
            .append("g")
            .attr("transform", "translate(" + radius + "," + radius + ")");

        var link = svg.append("g").selectAll(".link"),
            node = svg.append("g").selectAll(".node");


        var nodes = cluster.nodes(packageHierarchy(relations)),
            links = packageImports(nodes);
        link = link
            .data(bundle(links))
            .enter().append("path")
            .each(function (d) {
                d.source = d[0], d.target = d[d.length - 1];
            })
            .attr("class", "link")
            .attr("d", line);

        node = node
            .data(nodes.filter(function (n) {
                return !n.children;
            }))
            .enter().append("text")
            .attr("class", "node")
            .attr("dy", ".31em")
            .attr("transform", function (d) {
                return "rotate(" + (d.x - 90) + ")translate(" + (d.y + 8) + ",0)" + (d.x < 180 ? "" : "rotate(180)");
            })
            .style("text-anchor", function (d) {
                return d.x < 180 ? "start" : "end";
            })
            .text(function (d) {
                return d.key;
            })
            .on("mouseover", mouseovered)
            .on("mouseout", mouseouted);


//        d3.data(relations);

        function mouseovered(d) {
            node
                .each(function (n) {
                    n.target = n.source = false;
                });

            link
                .classed("link--target", function (l) {
                    if (l.target === d) return l.source.source = true;
                })
                .classed("link--source", function (l) {
                    if (l.source === d) return l.target.target = true;
                })
                .filter(function (l) {
                    return l.target === d || l.source === d;
                })
                .each(function () {
                    this.parentNode.appendChild(this);
                });

            node
                .classed("node--target", function (n) {
                    return n.target;
                })
                .classed("node--source", function (n) {
                    return n.source;
                });
        }

        function mouseouted(d) {
            link
                .classed("link--target", false)
                .classed("link--source", false);

            node
                .classed("node--target", false)
                .classed("node--source", false);
        }

        d3.select(self.frameElement).style("height", diameter + "px");

        // Lazily construct the package hierarchy from class names.
        function packageHierarchy(classes) {
            var map = {};

            function find(name, data) {
                name = (data ? name + ' ' : name);
                var node = map[name], i;
                if (!node) {
                    node = map[name] = data || {name: name, children: []};
                    if (name.length) {
                        node.parent = find(name.substring(0, i = name.lastIndexOf("\\")));
                        if (!node.parent.children) {
                            node.parent.children = []; // fix anomalies
                        }
                        node.parent.children.push(node);
                        node.key = name;//name.substring(i + 1);
                    }
                }
                return node;
            }

            classes.forEach(function (d) {
                find(d.name, d);
            });

            return map[""];
        }

        // Return a list of imports for the given array of nodes.
        function packageImports(nodes) {
            var map = {},
                imports = [];

            // Compute a map from name to node.
            nodes.forEach(function (d) {
                map[d.name] = d;
            });

            // For each import, construct a link from the source to target node.
            nodes.forEach(function (d) {
                if (d.relations) d.relations.forEach(function (i) {
                    imports.push({source: map[d.name], target: map[i]});
                });
            });

            return imports;
        }


    }

    document.onreadystatechange = function () {
        if (document.readyState === 'complete') {
            updateRelationsChart();
        }
    };

</script>
