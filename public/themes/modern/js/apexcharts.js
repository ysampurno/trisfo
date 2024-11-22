$(document).ready( function() {

	var options = {
		title: {
			text: 'Data Penjualan ' + tahun_current,
			floating: false,
			offsetY: 0,
			align: 'center',
			style: {
				color: '#444',
				fontWeight:  'normal',
				fontSize:  '16px'
			}
		},
		subtitle: {
			text: 'PT. Intertech Corporation',
			align: 'center',
			margin: 10,
			offsetX: 0,
			offsetY: 20,
			floating: false,
			style: {
				fontSize:  '12px',
				fontWeight:  'normal',
				color:  '#9699a2'
			},
		},
		series: [{
				name: 'Penjualan',
				data: penjualan_perbulan
			}, {
				name: 'Pembelian',
				data: pembelian_perbulan
			}, {
				name: 'Gross Profit',
				data: profit_perbulan
			}
		],
		chart: {
			type: 'bar',
			height: 350
		},
		theme: {
			mode: 'light', 
			palette: 'palette1'
		},
		plotOptions: {
			bar: {
				horizontal: false,
				columnWidth: '55%',
				endingShape: 'rounded',
				offsetY: 20,
			},
		},
		dataLabels: {
			enabled: false
		},
		stroke: {
			show: true,
			width: 2,
			colors: ['transparent']
		},
		xaxis: {
			categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
		},
		yaxis: {
			title: {
				text: 'Dalam Rupiah (Rp.)',
				style: {
					fontWeight: 400
				}
			},
			labels: {
				formatter: function (value) {
					return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
				}
			}
		},
		fill: {
			opacity: 1
		},
		tooltip: {
			y: {
				formatter: function (val) {
					return "Rp. " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
				}
			}
		}
	};

	var chart = new ApexCharts(document.querySelector("#chart-container"), options);
	chart.render();
	
	/* Pie Chart */
	var options = {
		title: {
			text: 'Data Penjualan ' + tahun_current,
			floating: false,
			offsetY: 0,
			align: 'center',
			margin: 0,
			style: {
				color: '#444',
				fontWeight:  'normal',
				fontSize:  '16px'
			}
		},
		subtitle: {
			text: 'PT. Intertech Corporation',
			align: 'center',
			margin: 10,
			offsetX: 0,
			offsetY: 20,
			floating: false,
			style: {
				fontSize:  '12px',
				fontWeight:  'normal',
				color:  '#9699a2'
			},
		},
		colors : [
			'#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A', '#F86624'
		],
		/*
		colors:[
			<?php
			foreach ($item_terjual as $val) {
				$func[] = 'dynamicColors()';
			}
					
			echo join(',', $func);
			?>
		],*/
		series: item_terjual,
			chart: {
			width: 490,
			type: 'pie',
		},
		plotOptions: {
			pie: {
				expandOnClick: true,
				 offsetY: 20,
			}
		},
		theme: {
			mode: 'light', 
			palette: 'palette1'
		},
		dataLabels: {
			style: {
				fontSize: '12px',
				fontWeight: 'normal'
			},
			dropShadow: {
				enabled: false,
			}
		},
		labels: item_terjual_label,
		legend: {
			position: 'right',
			 offsetY: 50,
			 offsetX: 0,
		},
		responsive: [{
			breakpoint: 640,
			options: {
				chart: {
					width: '100%'
				},
				legend: {
					position: 'bottom',
					offsetY: 0,
				}
			}
		}]
	};

	var chart = new ApexCharts(document.querySelector("#pie-container"), options);
	chart.render();
})