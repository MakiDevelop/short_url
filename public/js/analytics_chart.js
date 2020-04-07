$(function () {
    // chart test
    var ctx = $('#myChart');
    pieData = {
        datasets: [{
            data: [10, 20, 30],
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
            ]
        }],
    
        // These labels appear in the legend and in the tooltips when hovering different arcs
        labels: [
            'Red',
            'Yellow',
            'Blue'
        ]
    };
    var myPieChart = new Chart(ctx, {
        type: 'pie',
        data: pieData,
        // options: options
    });

    var ctx2 = $('#myChart2');
    barData = {
        datasets: [{
            data: [{x:'2016-12-21', y:1},{x:'2016-12-22', y:2},{x:'2016-12-23', y:8},{x:'2016-12-24', y:11},{x:'2016-12-25', y:20}, {x:'2016-12-26', y:10}],
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ]
            // barPercentage: 0.5,
            // barThickness: 6,
            // maxBarThickness: 3
            // minBarLength: 2,
            // data: [10, 20, 30, 40, 50, 60, 70]
        }]
    };
    options = {
        scales: {
            xAxes: [{
                type: 'time',
                time:{
                    unit: 'day',
                    displayFormats: {
                        'day': 'YYYY-MM-DD'
                    }
                }
            }],
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        }
    };
    var myBarChart = new Chart(ctx2, {
        type: 'bar',
        data: barData,
        options: options
    });
});