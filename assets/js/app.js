(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var sidebar = document.querySelector('.sidebar-wrapper');
		var overlayScrollbars = window.OverlayScrollbarsGlobal;

		if (sidebar && overlayScrollbars && overlayScrollbars.OverlayScrollbars && window.innerWidth > 992) {
			overlayScrollbars.OverlayScrollbars(sidebar, {
				scrollbars: {
					theme: 'os-theme-light',
					autoHide: 'leave',
					clickScroll: true
				}
			});
		}

		var themeToggle = document.getElementById('theme-toggle');
		if (themeToggle) {
			themeToggle.addEventListener('click', function () {
				var root = document.documentElement;
				var nextTheme = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
				root.setAttribute('data-bs-theme', nextTheme);
				localStorage.setItem('nexus-ucd-theme', nextTheme);
				window.dispatchEvent(new CustomEvent('nexus:themechange', { detail: { theme: nextTheme } }));
			});
		}
	});
})();
