document.addEventListener('DOMContentLoaded',function(){
  document.querySelectorAll('.benchmark-table').forEach(function(table){new DataTable(table,{pageLength:25,order:[[4,'desc']],layout:{topStart:'search',topEnd:'pageLength'}});});
  var canvas=document.getElementById('benchmark-chart');if(!canvas||typeof Chart==='undefined')return;
  var labels=JSON.parse(canvas.dataset.labels||'[]'),values=JSON.parse(canvas.dataset.values||'[]');
  new Chart(canvas,{type:'bar',data:{labels:labels,datasets:[{label:'Average Unit Rate',data:values,backgroundColor:'rgba(13,110,253,.65)',borderColor:'rgb(13,110,253)',borderWidth:1}]},options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{beginAtZero:true}}}});
});
