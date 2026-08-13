(function () {
	'use strict';
	var table = document.getElementById('reference-table');
	if (!table || typeof window.DataTable === 'undefined') return;
	new window.DataTable(table, {
		pageLength: 25,
		order: [[0, 'asc']],
		layout: { topStart: 'pageLength', topEnd: 'search', bottomStart: 'info', bottomEnd: 'paging' },
		language: { search: 'Search:', emptyTable: 'No reference records found.' }
	});
})();
