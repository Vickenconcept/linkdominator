let lineOptions = {
    chart: {
        type: 'line'
    },
    stroke: {
        curve: 'smooth',
    },
    markers: {
        size: 0,
    },
    series: [
        {
            name: 'Connection followed', 
            data: []
        },
        {
            name: 'Connections endorsed', 
            data: []
        },
        {
            name: 'Invitation accepted', 
            data: []
        },
        {
            name: 'Message sent', 
            data: []
        },
        {
            name: 'New job greetings sent', 
            data: []
        },
    ],
    xaxis: {
        categories: []
    }
}

const getLineStats = () => {
    $.ajax({
        url: '/linestats',
        method: 'get',
        success: function(data) {
            $.each(data.stats, function(i, item){
                lineOptions.xaxis.categories.push(item.datee)
                lineOptions.series[0].data.push(parseInt(item['Connection followed']))
                lineOptions.series[1].data.push(parseInt(item['Connections endorsed']))
                lineOptions.series[2].data.push(parseInt(item['Invitation accepted']))
                lineOptions.series[3].data.push(parseInt(item['Message sent']))
                lineOptions.series[4].data.push(parseInt(item['New job greetings sent']))
            })
            var chart = new ApexCharts(document.querySelector("#line-chart"), lineOptions);
            chart.render();
        }
    })
}
getLineStats()