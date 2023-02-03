<?php
require __DIR__ . '/_header.php'; ?>


<?php
// build d3js json
$json = [];
$history = [];
if (isset($project['git'], $project['git']['history'])) {
    $history = $project['git']['history'];

    // Sort by key (date)
    ksort($history);

    // only last 24 weeks
    $history = array_slice($history, -24);
    $json = [];
    foreach ($history as $date => $values) {
        array_push($json, (object)[
            'date' => $date,
            'key' => 'Additions',
            'value' => abs($values['additions']),
        ]);
        array_push($json, (object)[
            'date' => $date,
            'key' => 'Removes',
            'value' => abs($values['removes']),
        ]);
    }
}

// authors
$authors = [];
if (isset($project['git'], $project['git']['authors'])) {
    $authors = $project['git']['authors'];
}
?>

<div class="row">
    <div class="column">
        <div class="bloc">
            <h4>Git history
                <small>(PHP files only)</small>
            </h4>
            <div id="chart-git-history" style="height:200px"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="column column-60">
        <div class="bloc">
            <h4>Most committed PHP files</h4>
            <table class="js-sort-table table-small" id="table-commits">
                <thead>
                <tr>
                    <th>File</th>
                    <th class="js-sort-number">Commits</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($files as $file) { ?>
                    <tr>
                        <td><?php echo $file['name']; ?></td>
                        <td><?php echo $file['gitChanges']; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="column">
        <div class="bloc">
            <h4>Contributors <small>(PHP only)</small></h4>
            <table class="js-sort-table table-small" id="table-authors">
                <thead>
                <tr>
                    <th>Name</th>
                    <th class="js-sort-number">Commits</th>
                    <th class="js-sort-number">Additions</th>
                    <th class="js-sort-number">Removes</th>
                    <th class="js-sort-number">Files</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($authors as $author => $data) { ?>
                    <tr>
                        <td><?php echo $author; ?></td>
                        <td><?php echo $data['commits']; ?></td>
                        <td><?php echo $data['additions']; ?></td>
                        <td><?php echo $data['removes']; ?></td>
                        <td><?php echo $data['nbFiles']; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>


<script>
    var format = d3.time.format("%Y-%W");

    var margin = {top: 20, right: 0, bottom: 40, left: 40},
        width = document.getElementById('chart-git-history').offsetWidth - margin.left - margin.right,
        height = document.getElementById('chart-git-history').offsetHeight - margin.top - margin.bottom;

    var x = d3.time.scale()
        .range([0, width]);

    var y = d3.scale.linear()
        .range([height, 0]);

    var z = d3.scale.category20c();

    var xAxis = d3.svg.axis()
        .scale(x)
        .orient("bottom")
        .ticks(d3.time.month);

    var yAxis = d3.svg.axis()
        .scale(y)
        .orient("left");

    var stack = d3.layout.stack()
        .offset("zero")
        .values(function (d) {
            return d.values;
        })
        .x(function (d) {
            return d.date;
        })
        .y(function (d) {
            return d.value;
        });

    var nest = d3.nest()
        .key(function (d) {
            return d.key;
        });

    var area = d3.svg.area()
        .interpolate("cardinal")
        .x(function (d) {
            return x(d.date);
        })
        .y0(function (d) {
            return y(d.y0);
        })
        .y1(function (d) {
            return y(d.y0 + d.y);
        });

    var svg = d3.select("#chart-git-history").append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    var data = <?php echo json_encode($json, JSON_PRETTY_PRINT); ?>;

    data.forEach(function (d) {
        d.date = format.parse(d.date);
    });

    var layers = stack(nest.entries(data));

    x.domain(d3.extent(data, function (d) {
        return d.date;
    }));
    y.domain([0, d3.max(data, function (d) {
        return d.y0 + d.y;
    })]);

    svg.selectAll(".layer")
        .data(layers)
        .enter().append("path")
        .attr("class", "layer")
        .attr("d", function (d) {
            return area(d.values);
        })
        .style("fill", function (d, i) {
            return z(i);
        });

    svg.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis);

    svg.append("g")
        .attr("class", "y axis")
        .call(yAxis);


    sortTable(document.getElementById('table-commits'), 1, -1);
    sortTable(document.getElementById('table-authors'), 1, -1);

</script>
