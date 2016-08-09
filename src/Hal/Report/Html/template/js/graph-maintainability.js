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