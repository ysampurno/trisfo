$(document).ready(function() {
	
	// Chart Penjualan Perbulan
	let randomBackground = [];		
	for (i = 0; i < 12; i++){
		randomBackground.push(dynamicColors());
	}
		
	let barChartData = {
		labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
		datasets: [{
			data: penjualan_perbulan,
			label: 'Grafik Penjualan ',
			backgroundColor: randomBackground, 
			borderWidth: 1,
		}]
	};
	
	configBarChart = {
		type: 'bar',
		data: barChartData,
		options: {
			responsive: false,
			maintainAspectRatio: false,
			plugins: {
				legend: {
					display: false,
					position: 'top',
					fullWidth: false,
					labels: {
						padding: 10,
						boxWidth: 30
					}
				},
				title: {
					display: true,
					text: 'Grafik Penjualan ' + tahun_current,
					font : {
						size: 16,
						weight: 'normal',
						family: 'Helvetica, Arial, sans-serif'
					},
					padding: {
						bottom: 3
					}
				},
				subtitle: {
					display: true,
					text: 'PT. Intertech Corporation',
					color: '#a3a6ae',
					font: {
						size: 12,
						family: 'Helvetica, Arial, sans-serif',
						weight: 'normal'
					},
					padding: {
						bottom: 15
					}
				}
			},
			tooltips: {
				callbacks: {
					label: function(tooltipItems, data) {
						// return data.labels[tooltipItems.index] + ": " + data.datasets[0].data[tooltipItems.index].toLocaleString();
						// return "Total : " + data.datasets[0].data[tooltipItems.index].toLocaleString();
						return "Total : " + data.datasets[0].data[tooltipItems.index].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
					}
				}
			},
			scales: {
				y: {
					beginAtZero: true,
					ticks: {
						
						callback: function(value, index, values) {
							// return value.toLocaleString();
							return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
						}
					}
				}
			}
		}
	}


	// Pie Chart
	let item_terjual_bg = [];
	item_terjual.map( () => {
		item_terjual_bg.push(dynamicColors());
	})
	
	var configPieChart = {
		type: 'pie',
		data: {
			datasets: [{
				data: item_terjual,
				backgroundColor: item_terjual_bg,
			}],
			labels: item_terjual_label
		},
		options: {
			responsive: false,
			maintainAspectRatio: false,
			plugins: {
			  legend: {
				display: true,
				position: 'right',
				fullWidth: false,
				labels: {
					padding: 10,
					boxWidth: 30
				},
				align: 'right'
			  },
			  title: {
					display: true,
					text: 'Barang Terjual ' + tahun_current,
					font : {
						size: 16,
						weight: 'normal',
						family: 'Helvetica, Arial, sans-serif'
					},
					padding: {
						bottom: 3
					}
				},
				subtitle: {
					display: true,
					text: 'PT. Intertech Corporation',
					color: '#a3a6ae',
					font: {
						size: 12,
						family: 'Helvetica, Arial, sans-serif',
						weight: 'normal'
					},
					padding: {
						bottom: 0
					}
				}
			}
		}
	};
	
	
	/* Penjualan perbulan */
	var ctx = document.getElementById('bar-container').getContext('2d');
	window.chartPenjualan = new Chart(ctx, configBarChart);
	
	/* Item Terjual */
	var ctx = document.getElementById('pie-container').getContext('2d');
	window.chartItemTerjual = new Chart(ctx, configPieChart);
});