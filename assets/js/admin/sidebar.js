    /* ===== PROFILE DROPDOWN ===== */
function togglePD(){closeND();$('#pdDrop').toggleClass('show')}
function closePD(){$('#pdDrop').removeClass('show')}
/* ===== SIDEBAR ===== */
function toggleSB(){$('#sidebar').toggleClass('collapsed');$('#topbar').toggleClass('shifted');$('#main').toggleClass('shifted');var c=$('#sidebar').hasClass('collapsed');$('.sb-col-btn i').attr('class','fas '+(c?'fa-chevron-right':'fa-chevron-left'))}
function openMS(){$('#sidebar').addClass('mo');$('#sbBd').addClass('sh')}
function closeMS(){$('#sidebar').removeClass('mo');$('#sbBd').removeClass('sh')}


/* ===== NOTIFICATION DROPDOWN ===== */
function toggleND(){closePD();renderND();$('#ndDrop').toggleClass('show')}
function closeND(){$('#ndDrop').removeClass('show')}
function renderND(){
  var unread=DB.notifications.filter(function(n){return!n.read}).length;
  $('#notifCount').text(unread||'').toggle(unread>0);
  var h='<div class="nd-head"><span>Notifications</span>'+(unread?'<button class="nd-mark" onclick="markAllRead()">Mark all read</button>':'')+'</div><div class="nd-list">';
  if(DB.notifications.length===0){h+='<div class="nd-empty"><i class="far fa-bell"></i><br>No notifications</div>'}
  else{DB.notifications.forEach(function(n){h+='<div class="nd-item'+(n.read?'':' unread')+'" onclick="markRead('+n.id+')"><div class="nd-icon sc-icon '+n.color+'"><i class="fas '+n.icon+'"></i></div><div><div class="nd-text">'+n.text+'</div><div class="nd-time">'+n.time+'</div></div></div>'})}
  h+='</div><div class="nd-foot"><a href="#" onclick="event.preventDefault();closeND();toast(\'Opening notification center\',\'inf\')">View All Notifications</a></div>';
  $('#ndDrop').html(h);
}
function markRead(id){var n=DB.notifications.find(function(x){return x.id===id});if(n)n.read=true;renderND()}
function markAllRead(){DB.notifications.forEach(function(n){n.read=true});renderND();toast('All notifications marked as read','inf')}

/* ===== THEME ===== */
function toggleTheme(){
  var cur=document.documentElement.getAttribute('data-theme');
  var next=cur==='dark'?'light':'dark';
  document.documentElement.setAttribute('data-theme',next);
  localStorage.setItem('admin-theme',next);
  $('#themeBtn').html('<i class="fas '+(next==='dark'?'fa-sun':'fa-moon')+'"></i>');
  toast((next==='dark'?'Dark':'Light')+' mode enabled','inf');
  if(S.page==='dashboard')setTimeout(initCharts,100);
}
(function(){var s=localStorage.getItem('admin-theme');if(s==='dark'){document.documentElement.setAttribute('data-theme','dark');$(function(){$('#themeBtn').html('<i class="fas fa-sun"></i>')})}})();

/* ===== CLOSE DROPDOWNS ON OUTSIDE CLICK ===== */
 $(document).on('click',function(e){if(!$(e.target).closest('#profileBtn, #pdDrop').length)closePD();if(!$(e.target).closest('#notifBtn, #ndDrop').length)closeND()});

/* ===== NAVIGATION ===== */
function showP(p){
  S.page=p;S.search='';S.filter='';S.lp=1;S.sel=new Set();
  $('.sb-link').removeClass('active');$('.sb-link[data-p="'+p+'"]').addClass('active');
  var t={dashboard:'Dashboard',users:'Users',countries:'Countries',brands:'Brands',categories:'Categories',products:'Products',offers:'Offers',blogs:'Blog Posts',testimonials:'Testimonials',faqs:'FAQs',settings:'Settings'};
  $('#bc').html('<i class="fas fa-home" style="font-size:.75rem;color:var(--muted);"></i><span style="color:var(--muted);">/</span><span class="bc-a">'+(t[p]||p)+'</span>');
  closeMS();renderP();
}
function renderP(){var fn={dashboard:rDash,users:rUsers,countries:rCountries,brands:rBrands,categories:rCats,products:rProducts,offers:rOffers,blogs:rBlogs,testimonials:rTest,faqs:rFaqs,settings:rSettings};$('#pgC').html((fn[S.page]||rDash)());if(S.page==='dashboard')setTimeout(initCharts,50)}
function renderL(){renderP()}
