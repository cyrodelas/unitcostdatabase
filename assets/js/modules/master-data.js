(function () {
	'use strict';
	if (typeof window.DataTable === 'undefined') return;
	var tables = {};
	document.querySelectorAll('.master-data-table').forEach(function (element) {
		var instance = new window.DataTable(element, { pageLength: 25, order: [[0, 'asc']], language: { emptyTable: 'No records found.' } });
		if (element.id) tables[element.id] = instance;
	});
	document.querySelectorAll('[data-master-filter]').forEach(function (element) {
		var table = tables[element.getAttribute('data-table-target')];
		var column = parseInt(element.getAttribute('data-column'), 10);
		if (!table || isNaN(column)) return;
		element.addEventListener('change', function () {
			table.column(column).search(element.value, { exact: true }).draw();
		});
	});
})();
