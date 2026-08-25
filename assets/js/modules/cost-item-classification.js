(function () {
  'use strict';
  var project = document.getElementById('project_type_id');
  var market = document.getElementById('market_segment_id');
  if (!project || !market) return;

  function filterMarkets() {
    var projectId = project.value;
    var selectedIsValid = false;
    Array.prototype.forEach.call(market.options, function (option) {
      if (!option.value) return;
      var matches = option.getAttribute('data-project-type-id') === projectId;
      option.hidden = !matches;
      option.disabled = !matches;
      if (matches && option.selected) selectedIsValid = true;
    });
    if (!selectedIsValid) market.value = '';
    market.disabled = !projectId;
  }

  project.addEventListener('change', filterMarkets);
  filterMarkets();
}());
