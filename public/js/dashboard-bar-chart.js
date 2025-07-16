let barOptions = {
    chart: {
      	type: 'bar'
    },
	dataLabels: {
		enabled: false
	},
    series: [
		{
			name: 'Connections removed',
			data: []
		},
		{
			name: 'Comments liked',
			data: []
		}, 
		{
			name: 'Profiles scraped',
			data: []
		},
		{
			name: 'Birthday greetings',
			data: []
		}, 
		{
			name: 'Invitation withdrawn',
			data: []
		}
    ],
	xaxis: {
        categories: []
    }
}

const getBarStats = () => {
    $.ajax({
		url: '/barstats',
		method: 'get',
		success: function(data) {
			$.each(data.stats, function(i, item){
				barOptions.xaxis.categories.push(item.datee)
                barOptions.series[0].data.push(parseInt(item['Connections removed']))
                barOptions.series[1].data.push(parseInt(item['Comments liked']))
                barOptions.series[2].data.push(parseInt(item['Profiles scraped']))
                barOptions.series[3].data.push(parseInt(item['Birthday greetings']))
                barOptions.series[4].data.push(parseInt(item['Invitation withdrawn']))
			})
			var chart = new ApexCharts(document.querySelector("#bar-chart"), barOptions);
			chart.render();
		}
    })
}
getBarStats()