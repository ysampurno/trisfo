$(document).ready( function() {
	var barChart = echarts.init(document.getElementById('bar-container'));
	var option = {
		grid: {
			containLabel: true
		},
		title: {
			text: 'Data Penjualan ' + tahun_current,
			subtext: 'PT. Intertech Corporation',
			left: 'center',
			padding: 0,
			textStyle: {
				fontWeight: 'normal'
			}
		},
		toolbox: {
			feature: {
				dataZoom: {
					yAxisIndex: 'none'
				},
				restore: {},
				saveAsImage: {}
			}
		},
		tooltip: {
			formatter: function(a) {  return a.name + '<hr style="margin:5px 0;padding:0;border: 0; height: 1px; background: #CCCCCC"/>' + a.marker + a.seriesName + ' <strong>Rp. ' + a.value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '</strong>' }
		},
		legend: {
			bottom: 30,
			data:['Penjualan', 'Pembelian']
		},
		 xAxis: {
			type: 'category',
			data: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
		},
		yAxis: {
			type: 'value',
			name: 'Dalam Rupiah (Rp.)',
			nameRotate: 90,
			nameLocation: 'center',
			nameGap: 90,
			axisLabel : {
				formatter: function (value, index) {
					return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
				}
			}
		},
		series: [{
				name: 'Penjualan',
				data: penjualan_perbulan,
				type: 'bar'
			},
			{
				name: 'Pembelian',
				data: pembelian_perbulan,
				type: 'bar'
			}
		
		]
	};

	barChart.setOption(option);
	
	/* PIE Chart */
	var pieChart = echarts.init(document.getElementById('pie-container'));
	var option = {
		title: {
			text: 'Barang Terjual ' + tahun_current,
			subtext: 'PT. Intertech Corporation',
			left: 'center',
			top: 0,
			textStyle: {
				fontWeight: 'normal'
			}
		},
		toolbox: {
			feature: {
				saveAsImage: {}
			}
		},
		tooltip: {
			trigger: 'item'
		},
		legend: {
			orient: 'horizontal',
			top: 'bottom',
			left: 'center'
		},
		series: [
			{
				name: 'Barang Terjual',
				type: 'pie',
				selectedMode: 'single',
				radius: '50%',
				center: ['50%', '45%'],
				label : {
					formatter: function (data) {
						return data.value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ' (' + data.percent.toFixed(1) + '%)'
					},
					overflow: 'break',
					position: 'outside'
				},
				data: item_terjual,
				emphasis: {
					itemStyle: {
						shadowBlur: 10,
						shadowOffsetX: 0,
						shadowColor: 'rgba(0, 0, 0, 0.5)'
					}
				}
			}
		]
	};
	
	pieChart.setOption(option);
})