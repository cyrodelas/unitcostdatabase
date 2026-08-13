document.addEventListener('DOMContentLoaded',function(){
  document.querySelectorAll('.intelligence-table').forEach(function(table){new DataTable(table,{pageLength:25,order:[[0,'asc']],language:{emptyTable:'No intelligence records available.'}});});
  var canvas=document.getElementById('trend-chart');if(!canvas||typeof Chart==='undefined')return;
  var labels=JSON.parse(canvas.dataset.labels||'[]'),values=JSON.parse(canvas.dataset.values||'[]');
  new Chart(canvas,{type:'line',data:{labels:labels,datasets:[{label:'Average unit rate',data:values,borderColor:'rgb(13,110,253)',backgroundColor:'rgba(13,110,253,.15)',tension:.2,fill:true}]},options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true}}}});
});
