<?php
require __DIR__ . '/_header.php'; ?>

    <div class="row">
        <div class="column">
            <div class="bloc">
                <h4>Packages</h4>

                <div id="distances" class="scattered-plot"></div>

                <table class="js-sort-table" id="table-length">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th class="js-sort-number">Classes</th>
                        <th class="js-sort-number">Abstraction</th>
                        <th class="js-sort-number">Instability</th>
                        <th class="js-sort-number">Distance</th>
                        <th class="js-sort-number">Outgoing class dep.</th>
                        <th class="js-sort-number">Outgoing package dep.</th>
                        <th class="js-sort-number">Incoming class dep.</th>
                        <th class="js-sort-number">Incoming package dep.</th>
                    </tr>
                    </thead>
                    <?php
                    foreach ($this->sharedMetrics->packages as $package) { ?>
                        <tr>
                            <td><span class="path"><?= $package['name'] === '\\' ? 'global' : substr($package['name'], 0, -1); ?></span></td>
                            <td><?= $package['classes'] ? count($package['classes']) : 0; ?></td>
                            <td><?= isset($package['abstraction']) ? round($package['abstraction'], 3) : ''; ?></td>
                            <td><?= isset($package['instability']) ? round($package['instability'], 3) : ''; ?></td>
                            <td><?= isset($package['normalized_distance']) ? round($package['normalized_distance'], 3) : ''; ?></td>
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
<?php
$spots = [];
foreach ($this->sharedMetrics->packages as $each) {
    if (isset($each['abstraction'], $each['instability'])) {
        $spots[] = [
            'name' => $each['name'] === '\\' ? 'global' : substr($each['name'], 0, -1),
            'abstraction' => $each['abstraction'],
            'instability' => $each['instability'],
            'distance' => $each['distance'],
            'normalizedDistance' => abs($each['normalized_distance']),
            'classCount' => isset($each['classes']) ? count($each['classes']) : 0
        ];
    }
}
?>
<script>
    /**
     * Thx to http://bl.ocks.org/weiglemc/6185069
     */
    var margin = {top: 20, right: 20, bottom: 20, left: 20},
        width = 500 - margin.left - margin.right,
        height = 500 - margin.top - margin.bottom;

    var spots = <?php echo json_encode($spots, JSON_PRETTY_PRINT); ?>;

    /*
     * value accessor - returns the value to encode for a given data object.
     * scale - maps value to a visual display encoding, such as a pixel position.
     * map function - maps from data value to display value
     * axis - sets up axis
     */

    // setup x
    var xValue = function(d) { return d.abstraction;}, // data -> value
        xScale = d3.scale.linear().range([0, width]), // value -> display
        xMap = function(d) { return xScale(xValue(d));}, // data -> display
        xAxis = d3.svg.axis().scale(xScale).orient("bottom");

    // setup y
    var yValue = function(d) { return d.instability;}, // data -> value
        yScale = d3.scale.linear().range([height, 0]), // value -> display
        yMap = function(d) { return yScale(yValue(d));}, // data -> display
        yAxis = d3.svg.axis().scale(yScale).orient("left");

    // setup fill color
    var cValue = function(d) { return d.normalizedDistance;},
        color = d3.scale.linear().domain([0,1])
            .interpolate(d3.interpolateHcl)
            .range([d3.rgb("#00FF00"), d3.rgb('#FF0000')])

    // add the graph canvas to #distances
    var svg = d3.select("#distances").append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    // add the tooltip area to the webpage
    var tooltip = d3.select("#distances").append("div")
        .attr("class", "tooltip")
        .style("opacity", 0);

    // x-axis
    svg
        .append("g")
            .attr("class", "x axis")
            .attr("transform", "translate(0," + height + ")")
            .call(xAxis)
        .append("text")
            .attr("class", "label")
            .attr("x", width)
            .attr("y", -6)
            .style("text-anchor", "end")
            .text("Abstraction");

    // y-axis
    svg
        .append("g")
            .attr("class", "y axis")
            .call(yAxis)
        .append("text")
            .attr("class", "label")
            .attr("transform", "rotate(-90)")
            .attr("y", 6)
            .attr("dy", ".71em")
            .style("text-anchor", "end")
            .text("Instability");

    // optimal distance line
    svg.append("path")
        .attr("class", "line")
        .attr("d", (d3.svg.line().x(function (d) { return xScale(d); }).y(function (d) { return yScale(1-d); }))([0, 1]));

    // draw dots
    svg.selectAll(".dot")
        .data(spots)
        .enter().append("circle")
        .attr("class", "dot")
        .attr("r", function(d) { return 3.0 + 0.5 * d.classCount; })
        .attr("cx", xMap)
        .attr("cy", yMap)
        .style("fill", function(d) { return color(cValue(d));})
        .on("mouseover", function(d) {
            tooltip.transition()
                .duration(200)
                .style("opacity", .9);
            tooltip.html(d.name + "<br> (" + (Math.round(1000 * xValue(d)) / 1000)
                + ", " + (Math.round(1000 * yValue(d)) / 1000) + ")")
                .style("left", (d3.event.pageX + 5) + "px")
                .style("top", (d3.event.pageY - 28) + "px");
        })
        .on("mouseout", function(d) {
            tooltip.transition()
                .duration(500)
                .style("opacity", 0);
        });
</script>
