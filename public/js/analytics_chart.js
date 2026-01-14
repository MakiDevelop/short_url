$(function () {
    var pie1Color = [
        '#0000E3',
        '#FF0000',
        '#00BB00',
        '#F9F900',
        '#FF5809',
        '#8F4586',
        '#6C6C6C'
    ],
    pie2Color = [
        '#6C6C6C',
        '#8F4586',
        '#FF5809',
        '#F9F900',
        '#00BB00',
        '#FF0000',
        '#0000E3'
    ];
    // chart test
    // var ctx = $('#referralChart0');
    // pieData = {
    //     datasets: [{
    //         data: [10, 20, 30],
    //         backgroundColor: [
    //             'rgba(255, 99, 132, 0.2)',
    //             'rgba(54, 162, 235, 0.2)',
    //             'rgba(255, 206, 86, 0.2)',
    //         ]
    //     }],
    
    //     // These labels appear in the legend and in the tooltips when hovering different arcs
    //     labels: [
    //         'Red',
    //         'Yellow',
    //         'Blue'
    //     ]
    // };
    // var myPieChart = new Chart(ctx, {
    //     type: 'pie',
    //     data: pieData,
    //     // options: options
    // });

    // var ctx2 = $('#osChart0');
    // barData = {
    //     datasets: [{
    //         data: [{x:'2016-12-21', y:1},{x:'2016-12-22', y:2},{x:'2016-12-23', y:8},{x:'2016-12-24', y:11},{x:'2016-12-25', y:20}, {x:'2016-12-26', y:10}],
    //         backgroundColor: [
    //             'rgba(255, 99, 132, 0.2)',
    //             'rgba(54, 162, 235, 0.2)',
    //             'rgba(255, 206, 86, 0.2)',
    //             'rgba(75, 192, 192, 0.2)',
    //             'rgba(153, 102, 255, 0.2)',
    //             'rgba(255, 159, 64, 0.2)'
    //         ],
    //         borderColor: [
    //             'rgba(255, 99, 132, 1)',
    //             'rgba(54, 162, 235, 1)',
    //             'rgba(255, 206, 86, 1)',
    //             'rgba(75, 192, 192, 1)',
    //             'rgba(153, 102, 255, 1)',
    //             'rgba(255, 159, 64, 1)'
    //         ]
    //         // barPercentage: 0.5,
    //         // barThickness: 6,
    //         // maxBarThickness: 3
    //         // minBarLength: 2,
    //         // data: [10, 20, 30, 40, 50, 60, 70]
    //     }]
    // };
    // options = {
    //     scales: {
    //         xAxes: [{
    //             type: 'time',
    //             time:{
    //                 unit: 'day',
    //                 displayFormats: {
    //                     'day': 'YYYY-MM-DD'
    //                 }
    //             }
    //         }],
    //         yAxes: [{
    //             ticks: {
    //                 beginAtZero: true
    //             }
    //         }]
    //     }
    // };
    // var myBarChart = new Chart(ctx2, {
    //     type: 'bar',
    //     data: barData,
    //     options: options
    // });

    $('[id^=collapseAnalytics]').on('show.bs.collapse', function (e) {
        var url = '/index/url_analytics',
            method = 'GET',
            num = $(this).data('index'),
            code = $(this).data('code'),
            data = 'code=' + code;
            callbackSuccess = function (response) {
                if ($('#error_alert').is(':visible')) {
                    $('#error_alert').attr('hidden', 'hidden');
                }
                
                if ($('#delete_btn').length > 0) {
					$('#delete_btn').prop('disabled', false);
                }
                if (response.success) {
                    if (response.data.referral.label) {
                        var ctx = 'referralChart' + num;
                        pieData = {
                            datasets: [{
                                data: response.data.referral.data,
                                backgroundColor: pie1Color
                            }],
                            // These labels appear in the legend and in the tooltips when hovering different arcs
                            labels: response.data.referral.label
                        };
                        var myPieChart = new Chart(ctx, {
                            type: 'pie',
                            data: pieData,
                            // options: options
                        });
                    }
                    if (response.data.os.label) {
                        var ctx = 'osChart' + num;
                        pieData = {
                            datasets: [{
                                data: response.data.os.data,
                                backgroundColor: pie2Color
                            }],
                            // These labels appear in the legend and in the tooltips when hovering different arcs
                            labels: response.data.os.label
                        };
                        var myPieChart = new Chart(ctx, {
                            type: 'pie',
                            data: pieData,
                            // options: options
                        });
                    }
                }
            };
        App.ajax(url, method, data, callbackSuccess);
    })
});