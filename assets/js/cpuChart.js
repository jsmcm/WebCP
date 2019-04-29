var cpuChart = function () {

    var runCharts = function () {

        var plot = $.plot($("#cpuPlaceHolder"), [{
            data: cpuUsage,
            label: "CPU Usage"
        }, {
	    data: cpuMax,
	    label: "Available CPU"
	}], {

            series: {
                lines: {
                    show: true,
                    lineWidth: 2,
                    fill: true,
                    fillColor: {
                        colors: [{
                            opacity: 0.05
                        }, {
                            opacity: 0.01
                        }]
                    }
                },

                points: {
                    show: true
                },

                shadowSize: 2
            },

            grid: {
                hoverable: true,
                clickable: true,
                tickColor: "#eee",
                borderWidth: 0
            },

            colors: ["#d12610", "#52e136"],

            xaxis: {
                ticks: 11,
		tickDecimals: 0 //ramLabels
            },

            yaxis: {
                ticks: 11,
                tickDecimals: 0
            }

        });

        function showTooltip(x, y, contents) {

            $('<div id="tooltip">' + contents + '</div>').css({

                position: 'absolute',
                display: 'none',
                top: y + 5,
                left: x + 15,
                border: '1px solid #333',
                padding: '4px',
                color: '#fff',
                'border-radius': '3px',
                'background-color': '#333',
                opacity: 0.80

            }).appendTo("body").fadeIn(200);

        }

        var previousPoint = null;

        $("#cpuPlaceHolder").bind("plothover", function (event, pos, item) {

            $("#x").text(pos.x.toFixed(2));
            $("#y").text(pos.y.toFixed(2));

            if (item) {
                if (previousPoint != item.dataIndex) {
                    previousPoint = item.dataIndex;
                    $("#tooltip").remove();

                    var x = item.datapoint[0].toFixed(2),
                        y = item.datapoint[1].toFixed(2);
			

                    showTooltip(item.pageX, item.pageY, item.series.label + ": " + y + "% - " + cpuLabels[parseInt(x) - 1][1]);
                }
            } else {
                $("#tooltip").remove();
                previousPoint = null;
            }
        });
    };

    return {
        //main function to initiate template pages

        init: function () {
            runCharts();
        }
    };

}();
