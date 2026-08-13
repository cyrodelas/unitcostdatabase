(function () {
	'use strict';

	var dataElement = document.getElementById('dashboard-data');
	if (!dataElement || typeof window.Chart === 'undefined') {
		return;
	}

	var dashboardData;
	try {
		dashboardData = JSON.parse(dataElement.dataset.chart || '{}');
	} catch (error) {
		return;
	}

	var charts = [];
	var palette = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#0dcaf0'];

	function themeColors() {
		var styles = getComputedStyle(document.documentElement);
		return {
			text: styles.getPropertyValue('--bs-body-color').trim() || '#495057',
			grid: styles.getPropertyValue('--bs-border-color').trim() || '#dee2e6'
		};
	}

	var colors = themeColors();
	window.Chart.defaults.color = colors.text;

	var revisionCanvas = document.getElementById('revision-status-chart');
	if (revisionCanvas && dashboardData.revision_status) {
		charts.push(new window.Chart(revisionCanvas, {
			type: 'doughnut',
			data: {
				labels: dashboardData.revision_status.labels,
				datasets: [{ data: dashboardData.revision_status.values, backgroundColor: palette, borderWidth: 0 }]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: { legend: { position: 'bottom' } }
			}
		}));
	}

	var coverageCanvas = document.getElementById('resource-coverage-chart');
	if (coverageCanvas && dashboardData.resource_coverage) {
		charts.push(new window.Chart(coverageCanvas, {
			type: 'bar',
			data: {
				labels: dashboardData.resource_coverage.labels,
				datasets: [{
					data: dashboardData.resource_coverage.values,
					backgroundColor: ['#0d6efd', '#198754', '#fd7e14'],
					borderRadius: 4
				}]
			},
			options: {
				indexAxis: 'y',
				responsive: true,
				maintainAspectRatio: false,
				plugins: { legend: { display: false } },
				scales: {
					x: {
						beginAtZero: true,
						suggestedMax: dashboardData.resource_coverage.total_current_revisions,
						ticks: { precision: 0, color: colors.text },
						grid: { color: colors.grid }
					},
					y: { ticks: { color: colors.text }, grid: { display: false } }
				}
			}
		}));
	}

	window.addEventListener('nexus:themechange', function () {
		colors = themeColors();
		window.Chart.defaults.color = colors.text;
		charts.forEach(function (chart) {
			if (chart.options.scales && chart.options.scales.x) {
				chart.options.scales.x.ticks.color = colors.text;
				chart.options.scales.x.grid.color = colors.grid;
			}
			if (chart.options.scales && chart.options.scales.y) {
				chart.options.scales.y.ticks.color = colors.text;
			}
			chart.update();
		});
	});
})();
