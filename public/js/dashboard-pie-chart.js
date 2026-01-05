let pieOptions = {
    chart: {
        type: 'donut',
        toolbar: {
            show: false
        }
    },
    colors: ['#0077b5', '#005885', '#004d6f', '#003d59'],
    pie: {
        customScale: 0.8,
        size: 200,
        donut: {
            size: '65%'
        }
    },
    series: [],
    labels: ['Anniversary greetings', 'Invitation sent', 'Post liked', 'Profile viwed'],
    legend: {
        position: 'bottom'
    }
}

const getPieStats = () => {
    $.ajax({
        url: '/piestats',
        method: 'get',
        success: function(res) {
            pieOptions.series = [
                res.stats['Anniversary greetings'] || 0,
                res.stats['Invitation sent'] || 0,
                res.stats['Post liked'] || 0,
                res.stats['Profile viwed'] || 0
            ];
    
            var chart = new ApexCharts(document.querySelector("#pie-chart"), pieOptions);

            chart.render();
        }
    })
}

getPieStats()