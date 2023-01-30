<?php

use Hal\Report\Html\ViewHelper;

require __DIR__ . '/_header.php';
/** @var ViewHelper $viewHelper */
$viewHelper = $this->viewHelper;

// 1. build an associative array
$logicalLinesOfCodeByClass = array_column($this->sharedMetrics->classes, 'lloc');
$nbStats = count($logicalLinesOfCodeByClass);

// 2. percentile map
$json = [];
/** @var \Hal\Report\Html\ViewHelper $viewHelper */
$viewHelper = $this->viewHelper;
if ($nbStats > 1) {
    sort($logicalLinesOfCodeByClass);
    $range = range(0.5, 1, .05);
    foreach ($range as $percentile) {
        $json[] = (object)[
            'lloc' => $logicalLinesOfCodeByClass[max(round($percentile * ($nbStats - 1) - 1), 0)],
            'percentile' => round($percentile * 100),
        ];
    }
}

?>


<div class="row">
    <div class="column">
        <div class="bloc">
            <h4>Percentile distribution of logical lines of code by class</h4>
            <div id="lloc-repartition" style="height: 200px"></div>
            <div class="help" style="text-align: center">Percentile</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="column">
        <div class="bloc">
            <h4>Explore</h4>
            <table class="js-sort-table" id="table-length">
                <thead>
                <tr>
                    <th>Class</th>
                    <th class="js-sort-number">LLOC</th>
                    <th class="js-sort-number">CLOC</th>
                    <th class="js-sort-number">Volume</th>
                    <th class="js-sort-number">Intelligent content</th>
                    <th class="js-sort-number">Comment Weight</th>
                </tr>
                </thead>
                <?php
                foreach ($this->sharedMetrics->classes as $class) { ?>
                    <tr>
                        <td><span class="path"><?php echo $class['name']; ?></span></td>
                        <?php foreach (['lloc', 'cloc', 'volume', 'intelligentContent', 'commentWeight'] as $attribute) {?>
                            <td>
                                <span class="badge" <?php echo $viewHelper->gradientStyleFor($this->sharedMetrics->classes, $attribute, $class[$attribute]);?>>
                                <?php echo isset($class[$attribute]) ? $class[$attribute] : ''; ?>
                                </span>
                            </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>


<?php require __DIR__ . '/_footer.php'; ?>


<script>

    // table
    sortTable(document.getElementById('table-length'), 1, -1);


    var margin = {top: 20, right: 20, bottom: 30, left: 40},
        width = document.getElementById('lloc-repartition').offsetWidth - margin.left - margin.right,
        height = document.getElementById('lloc-repartition').offsetHeight - margin.top - margin.bottom;

    var x = d3.scale.ordinal()
        .rangeRoundBands([0, width], .1);

    var y = d3.scale.linear()
        .range([height, 0]);

    var xAxis = d3.svg.axis()
        .scale(x)
        .orient("bottom");

    var yAxis = d3.svg.axis()
            .scale(y)
            .orient("left")
        ;

    var svg = d3.select("#lloc-repartition").append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    data = <?php echo json_encode($json, JSON_PRETTY_PRINT); ?>;

    x.domain(data.map(function (d) {
        return d.percentile;
    }));
    y.domain([0, d3.max(data, function (d) {
        return d.lloc;
    })]);

    svg.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis)
        .append("text")
        .style("text-anchor", "end")

    svg.append("g")
        .attr("class", "y axis")
        .call(yAxis)
        .append("text")
        .attr("transform", "rotate(-90)")
        .attr("y", 6)
        .attr("dy", ".71em")
        .style("text-anchor", "end")
        .text("Logical lines of code");

    svg.selectAll(".bar")
        .data(data)
        .enter().append("rect")
        .attr("class", "bar")
        .attr("x", function (d) {
            return x(d.percentile);
        })
        .attr("width", x.rangeBand())
        .attr("y", function (d) {
            return y(d.lloc);
        })
        .attr("height", function (d) {
            return height - y(d.lloc);
        });

    function type(d) {
        d.lloc = +d.lloc;
        return d;
    }

</script>
