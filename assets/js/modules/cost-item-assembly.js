(function () {
	'use strict';
	var form = document.getElementById('cost-item-assembly-form');
	if (!form) return;
	var steps = Array.prototype.slice.call(form.querySelectorAll('[data-assembly-step]'));
	var targets = Array.prototype.slice.call(form.querySelectorAll('[data-step-target]'));
	var current = steps.length - 1;
	function show(index) {
		current = Math.max(0, Math.min(index, steps.length - 1));
		steps.forEach(function (step, i) { step.hidden = i !== current; });
		targets.forEach(function (button, i) { button.classList.toggle('btn-primary', i === current); button.classList.toggle('btn-outline-secondary', i !== current); });
		var previous = form.querySelector('[data-step-previous]'); var next = form.querySelector('[data-step-next]');
		if (previous) previous.hidden = current === 0; if (next) next.hidden = current === steps.length - 1;
	}
	targets.forEach(function (button) { button.addEventListener('click', function () { show(parseInt(button.getAttribute('data-step-target'), 10)); }); });
	form.querySelector('[data-step-previous]').addEventListener('click', function () { show(current - 1); });
	form.querySelector('[data-step-next]').addEventListener('click', function () { show(current + 1); });
	form.addEventListener('click', function (event) {
		var add = event.target.closest('[data-add-row]'); if (add) { var type = add.getAttribute('data-add-row'); var template = document.getElementById(type + '-row-template'); var list = form.querySelector('[data-row-list="' + type + '"]'); if (template && list) list.appendChild(template.content.cloneNode(true)); return; }
		var remove = event.target.closest('[data-remove-row]'); if (remove) { var row = remove.closest('[data-resource-row]'); if (row) row.remove(); }
	});
	var mode = document.getElementById('labor_mode');
	function laborMode() { form.querySelectorAll('[data-labor-panel]').forEach(function (panel) { panel.hidden = panel.getAttribute('data-labor-panel') !== mode.value; }); }
	mode.addEventListener('change', laborMode); laborMode();
	show(steps[steps.length - 1].querySelector('.alert-success') ? steps.length - 1 : 0);
}());
