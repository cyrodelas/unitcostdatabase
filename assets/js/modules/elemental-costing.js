(function () {
  'use strict';
  var level3 = document.getElementById('uniformat_level3_id');
  var level4 = document.getElementById('uniformat_level4_id');
  if (!level3 || !level4) return;
  function filterLevel4() {
    var selectedValid = false;
    Array.prototype.forEach.call(level4.options, function (option) {
      if (!option.value) return;
      var valid = option.getAttribute('data-level3-id') === level3.value;
      option.hidden = !valid; option.disabled = !valid;
      if (valid && option.selected) selectedValid = true;
    });
    if (!selectedValid) level4.value = '';
    level4.disabled = !level3.value;
  }
  level3.addEventListener('change', filterLevel4);
  filterLevel4();
}());
