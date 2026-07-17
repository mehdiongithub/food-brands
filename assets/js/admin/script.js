/* ===== DATA ===== */
var DB={
  users:[
    {id:1,name:'Alex Kumar',email:'alex@menucrest.com',role:'Super Admin',status:'active',joined:'2023-01-15',avatar:'https://picsum.photos/seed/u1/80/80.jpg'},
    {id:2,name:'Sarah Mitchell',email:'sarah@menucrest.com',role:'Editor',status:'active',joined:'2023-03-22',avatar:'https://picsum.photos/seed/u2/80/80.jpg'},
    {id:3,name:'James Rodriguez',email:'james@menucrest.com',role:'Moderator',status:'active',joined:'2023-05-10',avatar:'https://picsum.photos/seed/u3/80/80.jpg'},
    {id:4,name:'Emily Chen',email:'emily@menucrest.com',role:'Editor',status:'draft',joined:'2023-07-01',avatar:'https://picsum.photos/seed/u4/80/80.jpg'},
    {id:5,name:'Michael Thompson',email:'michael@menucrest.com',role:'Viewer',status:'active',joined:'2023-08-14',avatar:'https://picsum.photos/seed/u5/80/80.jpg'},
    {id:6,name:'Aisha Patel',email:'aisha@menucrest.com',role:'Editor',status:'active',joined:'2023-09-20',avatar:'https://picsum.photos/seed/u6/80/80.jpg'},
    {id:7,name:'David Kim',email:'david@menucrest.com',role:'Moderator',status:'draft',joined:'2023-11-05',avatar:'https://picsum.photos/seed/u7/80/80.jpg'},
    {id:8,name:'Lisa Wang',email:'lisa@menucrest.com',role:'Viewer',status:'active',joined:'2024-01-12',avatar:'https://picsum.photos/seed/u8/80/80.jpg'}
  ],
  countries:[
    {id:'us',name:'United States',flag:'\u{1F1FA}\u{1F1F8}',currency:'USD',symbol:'$',brands:8,products:36,status:'active'},
    {id:'uk',name:'United Kingdom',flag:'\u{1F1EC}\u{1F1E7}',currency:'GBP',symbol:'\u00A3',brands:8,products:36,status:'active'},
    {id:'ae',name:'UAE',flag:'\u{1F1E6}\u{1F1EA}',currency:'AED',symbol:'AED',brands:8,products:34,status:'active'},
    {id:'sa',name:'Saudi Arabia',flag:'\u{1F1F8}\u{1F1E6}',currency:'SAR',symbol:'SAR',brands:7,products:30,status:'active'},
    {id:'in',name:'India',flag:'\u{1F1EE}\u{1F1F3}',currency:'INR',symbol:'\u20B9',brands:7,products:30,status:'active'},
    {id:'de',name:'Germany',flag:'\u{1F1E9}\u{1F1EA}',currency:'EUR',symbol:'\u20AC',brands:8,products:36,status:'draft'}
  ],
  brands:[
    {id:'kfc',name:'KFC',logo:'https://picsum.photos/seed/kfclogo/100/100.jpg',cover:'https://picsum.photos/seed/kfccover/400/200.jpg',countries:6,categories:4,products:6,status:'active'},
    {id:'mcd',name:"McDonald's",logo:'https://picsum.photos/seed/mcdlogo/100/100.jpg',cover:'https://picsum.photos/seed/mcdcover/400/200.jpg',countries:6,categories:5,products:6,status:'active'},
    {id:'bk',name:'Burger King',logo:'https://picsum.photos/seed/bklogo/100/100.jpg',cover:'https://picsum.photos/seed/bkcover/400/200.jpg',countries:6,categories:4,products:5,status:'active'},
    {id:'subway',name:'Subway',logo:'https://picsum.photos/seed/subwaylogo/100/100.jpg',cover:'https://picsum.photos/seed/subwaycover/400/200.jpg',countries:6,categories:3,products:4,status:'active'},
    {id:'ph',name:'Pizza Hut',logo:'https://picsum.photos/seed/phlogo/100/100.jpg',cover:'https://picsum.photos/seed/phcover/400/200.jpg',countries:6,categories:3,products:4,status:'active'},
    {id:'dom',name:"Domino's",logo:'https://picsum.photos/seed/domlogo/100/100.jpg',cover:'https://picsum.photos/seed/domcover/400/200.jpg',countries:6,categories:3,products:4,status:'active'},
    {id:'sbx',name:'Starbucks',logo:'https://picsum.photos/seed/sbxlogo/100/100.jpg',cover:'https://picsum.photos/seed/sbxcover/400/200.jpg',countries:6,categories:3,products:4,status:'active'},
    {id:'fg',name:'Five Guys',logo:'https://picsum.photos/seed/fglogo/100/100.jpg',cover:'https://picsum.photos/seed/fgcover/400/200.jpg',countries:3,categories:3,products:3,status:'draft'}
  ],
  categories:[
    {id:'burgers',name:'Burgers',image:'https://picsum.photos/seed/catburgers/200/200.jpg',products:8,status:'active'},
    {id:'chicken',name:'Chicken',image:'https://picsum.photos/seed/catchicken/200/200.jpg',products:6,status:'active'},
    {id:'pizza',name:'Pizza',image:'https://picsum.photos/seed/catpizza/200/200.jpg',products:7,status:'active'},
    {id:'sandwiches',name:'Sandwiches',image:'https://picsum.photos/seed/catsandwiches/200/200.jpg',products:5,status:'active'},
    {id:'sides',name:'Sides',image:'https://picsum.photos/seed/catsides/200/200.jpg',products:8,status:'active'},
    {id:'drinks',name:'Drinks',image:'https://picsum.photos/seed/catdrinks/200/200.jpg',products:4,status:'active'},
    {id:'desserts',name:'Desserts',image:'https://picsum.photos/seed/catdesserts/200/200.jpg',products:4,status:'active'},
    {id:'breakfast',name:'Breakfast',image:'https://picsum.photos/seed/catbreakfast/200/200.jpg',products:1,status:'draft'},
    {id:'salads',name:'Salads',image:'https://picsum.photos/seed/catsalads/200/200.jpg',products:0,status:'active'},
    {id:'combos',name:'Combo Meals',image:'https://picsum.photos/seed/catcombos/200/200.jpg',products:1,status:'active'}
  ],
  products:[
    {id:1,name:'Original Recipe Chicken',brand:'kfc',category:'chicken',image:'https://picsum.photos/seed/kfcor/200/200.jpg',price:5.99,calories:390,status:'active'},
    {id:2,name:'Zinger Burger',brand:'kfc',category:'burgers',image:'https://picsum.photos/seed/kfczinger/200/200.jpg',price:6.49,calories:475,status:'active'},
    {id:3,name:'Coleslaw',brand:'kfc',category:'sides',image:'https://picsum.photos/seed/kfccs/200/200.jpg',price:2.29,calories:170,status:'active'},
    {id:4,name:'French Fries',brand:'kfc',category:'sides',image:'https://picsum.photos/seed/kfcfries/200/200.jpg',price:2.49,calories:290,status:'active'},
    {id:5,name:'Family Bucket',brand:'kfc',category:'combos',image:'https://picsum.photos/seed/kfcbucket/200/200.jpg',price:24.99,calories:3120,status:'active'},
    {id:6,name:'Big Mac',brand:'mcd',category:'burgers',image:'https://picsum.photos/seed/mcdbigmac/200/200.jpg',price:5.99,calories:550,status:'active'},
    {id:7,name:'Quarter Pounder',brand:'mcd',category:'burgers',image:'https://picsum.photos/seed/mcdqp/200/200.jpg',price:6.49,calories:520,status:'active'},
    {id:8,name:'Chicken McNuggets',brand:'mcd',category:'chicken',image:'https://picsum.photos/seed/mcdnuggets/200/200.jpg',price:4.99,calories:440,status:'active'},
    {id:9,name:'McFlurry Oreo',brand:'mcd',category:'desserts',image:'https://picsum.photos/seed/mcdmcflurry/200/200.jpg',price:3.99,calories:510,status:'active'},
    {id:10,name:'Whopper',brand:'bk',category:'burgers',image:'https://picsum.photos/seed/bkwhopper/200/200.jpg',price:6.79,calories:657,status:'active'},
    {id:11,name:'Triple Stacker',brand:'bk',category:'burgers',image:'https://picsum.photos/seed/bkstacker/200/200.jpg',price:10.99,calories:1250,status:'active'},
    {id:12,name:'Italian B.M.T.',brand:'subway',category:'sandwiches',image:'https://picsum.photos/seed/subbmt/200/200.jpg',price:7.49,calories:410,status:'active'},
    {id:13,name:'Meatball Marinara',brand:'subway',category:'sandwiches',image:'https://picsum.photos/seed/submbm/200/200.jpg',price:6.99,calories:580,status:'active'},
    {id:14,name:'Margherita Pizza',brand:'ph',category:'pizza',image:'https://picsum.photos/seed/phmarg/200/200.jpg',price:12.99,calories:680,status:'active'},
    {id:15,name:'Pepperoni Lovers',brand:'ph',category:'pizza',image:'https://picsum.photos/seed/phpep/200/200.jpg',price:15.99,calories:820,status:'active'},
    {id:16,name:'Caramel Macchiato',brand:'sbx',category:'drinks',image:'https://picsum.photos/seed/sbxcm/200/200.jpg',price:5.75,calories:250,status:'active'},
    {id:17,name:'Caramel Frappuccino',brand:'sbx',category:'drinks',image:'https://picsum.photos/seed/sbxfrap/200/200.jpg',price:6.25,calories:380,status:'draft'}
  ],
  offers:[
    {id:1,title:'Buy 1 Get 1 Free on All Burgers',brand:'kfc',discount:50,code:'BOGO50',status:'active',expires:'2025-03-31'},
    {id:2,title:'20% Off Family Meals',brand:'mcd',discount:20,code:'FAMILY20',status:'active',expires:'2025-02-28'},
    {id:3,title:'Free Delivery on Orders Above $25',brand:'bk',discount:0,code:'FREEDEL',status:'active',expires:'2025-04-15'},
    {id:4,title:'30% Off Large Pizzas',brand:'ph',discount:30,code:'PIZZA30',status:'active',expires:'2025-03-15'},
    {id:5,title:'Sub of the Day - $5.99',brand:'subway',discount:40,code:'SUB599',status:'expired',expires:'2025-01-10'},
    {id:6,title:'Buy 2 Medium Pizzas Get 1 Free',brand:'dom',discount:33,code:'2FOR1',status:'active',expires:'2025-03-20'},
    {id:7,title:'Happy Hour - Half Price Drinks',brand:'sbx',discount:50,code:'HAPPY50',status:'draft',expires:'2025-06-30'}
  ],
  blogs:[
    {id:1,title:'The Secret Behind KFC\'s 11 Herbs and Spices',category:'Brand Stories',image:'https://picsum.photos/seed/blog1/200/200.jpg',date:'2025-01-15',status:'active'},
    {id:2,title:'How McDonald\'s Menus Differ Around the World',category:'Global Food',image:'https://picsum.photos/seed/blog2/200/200.jpg',date:'2025-01-12',status:'active'},
    {id:3,title:'Top 10 Highest-Calorie Fast Food Items',category:'Health',image:'https://picsum.photos/seed/blog3/200/200.jpg',date:'2025-01-08',status:'draft'},
    {id:4,title:'Pizza Hut vs Domino\'s: The Ultimate Comparison',category:'Comparisons',image:'https://picsum.photos/seed/blog4/200/200.jpg',date:'2025-01-05',status:'active'}
  ],
  testimonials:[
    {id:1,name:'Sarah Mitchell',role:'Food Blogger',avatar:'https://picsum.photos/seed/avatar1/80/80.jpg',rating:5,text:'MenuCrest has completely changed how I compare prices.',status:'active'},
    {id:2,name:'James Rodriguez',role:'Travel Enthusiast',avatar:'https://picsum.photos/seed/avatar2/80/80.jpg',rating:5,text:'The country switching feature is brilliant.',status:'active'},
    {id:3,name:'Emily Chen',role:'Digital Nomad',avatar:'https://picsum.photos/seed/avatar3/80/80.jpg',rating:4,text:'The detail on each product is impressive.',status:'active'},
    {id:4,name:'Michael Thompson',role:'Analyst',avatar:'https://picsum.photos/seed/avatar4/80/80.jpg',rating:5,text:'Invaluable tool for market research.',status:'draft'},
    {id:5,name:'Aisha Patel',role:'Student',avatar:'https://picsum.photos/seed/avatar5/80/80.jpg',rating:4,text:'The offers section saves me money every week!',status:'active'}
  ],
  faqs:[
    {id:1,question:'How does MenuCrest get its pricing data?',answer:'Our team collects data from brand websites and verified submissions.',status:'active'},
    {id:2,question:'Can I compare prices across different countries?',answer:'Yes, use the country selector to switch and compare.',status:'active'},
    {id:3,question:'How often are prices updated?',answer:'Weekly. Prices may vary by location.',status:'active'},
    {id:4,question:'Why are some products not available in my country?',answer:'Brands tailor menus to local tastes and regulations.',status:'active'},
    {id:5,question:'Is MenuCrest free to use?',answer:'Yes, completely free for all users.',status:'draft'},
    {id:6,question:'How can I report incorrect pricing?',answer:'Use our Contact page with product details.',status:'active'}
  ],
  notifications:[
    {id:1,icon:'fa-user-plus',color:'g',text:'<strong>Lisa Wang</strong> registered as a new user',time:'5 min ago',read:false},
    {id:2,icon:'fa-tag',color:'o',text:'Offer <strong>BOGO50</strong> expires in 3 days',time:'1 hour ago',read:false},
    {id:3,icon:'fa-hamburger',color:'b',text:'<strong>3 new products</strong> added to Burger King',time:'3 hours ago',read:false},
    {id:4,icon:'fa-pen-nib',color:'b',text:'Blog post published by <strong>Sarah Mitchell</strong>',time:'5 hours ago',read:true},
    {id:5,icon:'fa-chart-line',color:'g',text:'Weekly analytics report is ready to view',time:'1 day ago',read:true},
    {id:6,icon:'fa-shield-halved',color:'o',text:'Security scan completed — no issues found',time:'2 days ago',read:true}
  ],
  nid:7,uid:9,cid:7,bid:9,catid:11,pid:18,oid:8,blogid:5,tid:6,fid:7
};

/* ===== STATE ===== */
var S={page:'dashboard',search:'',filter:'',lp:1,pp:8,sel:new Set()};

/* ===== UTILS ===== */
function toast(m,t){t=t||'suc';var ic={suc:'fa-check-circle',err:'fa-times-circle',wrn:'fa-exclamation-triangle',inf:'fa-info-circle'};var $t=$('<div class="ti2 '+t+'"><i class="fas '+ic[t]+'"></i><span>'+m+'</span></div>');$('#tw2').append($t);setTimeout(function(){$t.fadeOut(250,function(){$(this).remove()})},2800)}
function openMo(h){$('#moBox').html(h);$('#moOv').addClass('show');$('body').css('overflow','hidden')}
function closeMo(){$('#moOv').removeClass('show');$('body').css('overflow','')}
 $('#moOv').on('click',function(e){if(e.target===this)closeMo()});
 $(document).on('keydown',function(e){if(e.key==='Escape'){closeMo();closePD();closeND()}});
function confirmDel(m,fn){openMo('<div class="mo-b" style="padding:28px"><div class="cf-body"><div class="cf-icon"><i class="fas fa-trash-alt"></i></div><div class="cf-t">Are you sure?</div><div class="cf-d">'+m+'</div><div class="cf-acts"><button class="bo" onclick="closeMo()">Cancel</button><button class="bdn" id="cfBtn">Delete</button></div></div></div>');$('#cfBtn').on('click',function(){closeMo();fn()})}
function bN(id){var b=DB.brands.find(function(x){return x.id===id});return b?b.name:id}
function bL(id){var b=DB.brands.find(function(x){return x.id===id});return b?b.logo:''}
function cN(id){var c=DB.categories.find(function(x){return x.id===id});return c?c.name:id}
function sts(s){return'<span class="sb-badge2 '+s+'">'+s.charAt(0).toUpperCase()+s.slice(1)+'</span>'}
function pgItems(it){var st=(S.lp-1)*S.pp;return it.slice(st,st+S.pp)}
function pgH(tot){var p=Math.ceil(tot/S.pp);if(p<=1)return'<div class="pg-i">Showing '+tot+' items</div>';var h='<div class="pg-i">Showing '+((S.lp-1)*S.pp+1)+'-'+Math.min(S.lp*S.pp,tot)+' of '+tot+'</div>';h+='<button class="pg-b" onclick="S.lp='+Math.max(1,S.lp-1)+';renderL()" '+(S.lp<=1?'disabled':'')+'><i class="fas fa-chevron-left"></i></button>';for(var i=1;i<=p;i++)h+='<button class="pg-b '+(i===S.lp?'act':'')+'" onclick="S.lp='+i+';renderL()">'+i+'</button>';h+='<button class="pg-b" onclick="S.lp='+Math.min(p,S.lp+1)+';renderL()" '+(S.lp>=p?'disabled':'')+'><i class="fas fa-chevron-right"></i></button>';return'<div class="pg">'+h+'</div>'}
function fItems(it,sf){var q=S.search.toLowerCase(),f=S.filter;if(!q&&!f)return it;return it.filter(function(i){var ms=!q||sf.some(function(s){return(i[s]+'').toLowerCase().indexOf(q)>-1});var mf=!f||i.status===f;return ms&&mf})}
function stars(n){var s='';for(var i=0;i<5;i++)s+='<i class="fas fa-star" style="color:'+(i<n?'var(--warn)':'var(--border)')+';font-size:.68rem;"></i>';return s}

/* ===== DASHBOARD ===== */
function rDash(){
  var h=new Date().getHours(),g=h<12?'Good morning':h<17?'Good afternoon':'Good evening';
  var uName=(document.querySelector('.tb-pn')?document.querySelector('.tb-pn').textContent.trim().split(' ')[0]:'User');
  var ao=DB.offers.filter(function(o){return o.status==='active'}).length;
  var nr=DB.users.filter(function(u){return u.joined>='2024-01-01'}).length;
  return'<div class="ps act"><div class="pg-head"><h1 class="pg-title">'+g+', '+uName+'</h1><p class="pg-desc">You have <strong>'+ao+' active offers</strong> and <strong>'+nr+' new users</strong> this month to review.</p></div>'+
  '<div class="row g-3 mb-4">'+sC('fa-hamburger','o','36','Total Products','<span class="sc-chg up"><i class="fas fa-arrow-up"></i> 12%</span>')+sC('fa-store','g','8','Active Brands','<span class="sc-chg up"><i class="fas fa-arrow-up"></i> 2 new</span>')+sC('fa-users','b','248','Registered Users','<span class="sc-chg up"><i class="fas fa-arrow-up"></i> 8.3%</span>')+sC('fa-tags','r',ao,'Active Offers','<span class="sc-chg dn"><i class="fas fa-arrow-down"></i> 2 expired</span>')+'</div>'+
  '<div class="row g-3 mb-4"><div class="col-lg-8"><div class="cd"><div class="cd-h"><span class="cd-t">Revenue Overview</span><div class="d-flex gap-2"><button class="bo btn-sm" onclick="initCharts()"><i class="fas fa-sync-alt"></i></button></div></div><div class="cd-b"><div class="cc"><canvas id="revChart"></canvas></div></div></div></div><div class="col-lg-4"><div class="cd"><div class="cd-h"><span class="cd-t">By Category</span></div><div class="cd-b"><div class="cc sm"><canvas id="catChart"></canvas></div></div></div></div></div>'+
  '<div class="row g-3 mb-4"><div class="col-lg-5"><div class="cd"><div class="cd-h"><span class="cd-t">Quick Actions</span></div><div class="cd-b"><div class="d-grid gap-2">'+qA('fa-plus','var(--accent)','rgba(232,93,4,.1)','Add New Product','Create a product listing')+qA('fa-tags','var(--success)','rgba(5,150,105,.1)','Create Offer','Set up a new deal')+qA('fa-pen-nib','var(--info)','rgba(8,145,178,.1)','Write Blog Post','Publish an article')+qA('fa-user-plus','var(--warn)','rgba(217,119,6,.1)','Invite User','Send an invitation')+'</div></div></div></div><div class="col-lg-7"><div class="cd"><div class="cd-h"><span class="cd-t">Recent Activity</span></div><div class="cd-b">'+acI('g','<strong>Sarah Mitchell</strong> published "How McDonald\'s Menus Differ"','2h ago')+acI('o','<strong>System</strong> updated 12 product prices in UAE','5h ago')+acI('b','<strong>James Rodriguez</strong> added 3 products to Burger King','Yesterday')+acI('r','Offer <strong>"Sub of the Day"</strong> has expired','Yesterday')+acI('g','<strong>Aisha Patel</strong> approved a new testimonial','2d ago')+'</div></div></div></div>'+
  '<div class="row g-3 mb-4"><div class="col-lg-6"><div class="cd"><div class="cd-h"><span class="cd-t">Top Products</span><span style="font-size:.72rem;color:var(--muted);">By views this week</span></div><div class="cd-b p0"><div class="tw"><table class="at"><thead><tr><th>Product</th><th>Brand</th><th>Views</th><th>Trend</th></tr></thead><tbody>'+tP('Big Mac','mcd','12,847','up')+tP('Zinger Burger','kfc','10,234','up')+tP('Whopper','bk','9,812','dn')+tP('Caramel Macchiato','sbx','8,456','up')+tP('Margherita Pizza','ph','7,891','up')+'</tbody></table></div></div></div></div><div class="col-lg-6"><div class="cd"><div class="cd-h"><span class="cd-t">Recent Signups</span></div><div class="cd-b p0"><div class="tw"><table class="at"><thead><tr><th>User</th><th>Role</th><th>Date</th><th>Status</th></tr></thead><tbody>'+rS('Lisa Wang','Viewer','2024-01-12','active','https://picsum.photos/seed/u8/80/80.jpg')+rS('David Kim','Moderator','2023-11-05','draft','https://picsum.photos/seed/u7/80/80.jpg')+rS('Aisha Patel','Editor','2023-09-20','active','https://picsum.photos/seed/u6/80/80.jpg')+rS('Michael Thompson','Viewer','2023-08-14','active','https://picsum.photos/seed/u5/80/80.jpg')+'</tbody></table></div></div></div></div></div>'+
  '<div class="row g-3"><div class="col-lg-6"><div class="cd"><div class="cd-h"><span class="cd-t">Country Distribution</span></div><div class="cd-b"><div class="cc sm"><canvas id="ctryChart"></canvas></div></div></div></div><div class="col-lg-6"><div class="cd"><div class="cd-h"><span class="cd-t">Performance</span></div><div class="cd-b"><div class="d-grid gap-3" style="grid-template-columns:1fr 1fr;">'+pM('Conversion Rate','3.24%','up','fa-percent')+pM('Avg. Order Value','$12.48','up','fa-receipt')+pM('Bounce Rate','34.2%','dn','fa-arrow-right-from-bracket')+pM('Session Duration','4m 32s','up','fa-clock')+'</div></div></div></div></div></div>';
}
function sC(i,c,v,l,ch){return'<div class="col-6 col-lg-3"><div class="sc '+c+'"><div class="sc-icon '+c+'"><i class="fas '+i+'"></i></div><div class="sc-val">'+v+'</div><div class="sc-lbl">'+l+'</div>'+ch+'</div></div>'}
function qA(i,c,bg,t,s){return'<a class="qa" href="#" onclick="event.preventDefault();showP(\''+t.split(' ')[0].toLowerCase()+'\')"><div class="qa-i" style="background:'+bg+';color:'+c+';"><i class="fas '+i+'"></i></div><div><div class="qa-t">'+t+'</div><div class="qa-s">'+s+'</div></div></a>'}
function acI(c,t,tm){return'<div class="aci"><div class="acd '+c+'"></div><div><div class="act">'+t+'</div><div class="atm">'+tm+'</div></div></div>'}
function tP(n,b,v,t){return'<tr><td><div class="tu"><div class="tn">'+n+'</div></div></td><td>'+bN(b)+'</td><td style="font-weight:600;">'+v+'</td><td><span class="sc-chg '+t+'" style="font-size:.68rem;"><i class="fas fa-arrow-'+t+'"></i> '+(t==='up'?'12%':'3%')+'</span></td></tr>'}
function rS(n,r,d,s,a){return'<tr><td><div class="tu"><img class="ta" src="'+a+'" alt="'+n+'" loading="lazy"><div><div class="tn">'+n+'</div><div class="ts">'+r+'</div></div></div></td><td style="font-size:.82rem;">'+r+'</td><td style="font-size:.82rem;color:var(--muted);">'+d+'</td><td>'+sts(s)+'</td></tr>'}
function pM(l,v,t,i){return'<div style="padding:12px;border:1px solid var(--border-l);border-radius:var(--r);display:flex;align-items:center;gap:10px;"><div style="width:34px;height:34px;border-radius:50%;background:var(--bg);display:flex;align-items:center;justify-content:center;color:var(--text2);font-size:.8rem;flex-shrink:0;"><i class="fas '+i+'"></i></div><div style="flex:1;"><div style="font-size:.73rem;color:var(--muted);">'+l+'</div><div style="font-size:.95rem;font-weight:700;">'+v+'</div></div><span class="sc-chg '+t+'" style="font-size:.65rem;"><i class="fas fa-arrow-'+t+'"></i> '+(t==='up'?'4.2%':'1.8%')+'</span></div>'}

var CH={};
function initCharts(){
  Object.keys(CH).forEach(function(k){if(CH[k]){CH[k].destroy();delete CH[k]}});
  var gc='rgba(0,0,0,.04)',fc='#9CA3AF';
  var isDark=document.documentElement.getAttribute('data-theme')==='dark';
  if(isDark){gc='rgba(255,255,255,.04)';fc='#6B6B82'}
  var base={responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}};
  CH.rev=new Chart(document.getElementById('revChart'),Object.assign({},base,{type:'line',data:{labels:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],datasets:[{label:'Revenue',data:[4200,5100,4800,6200,7100,6800,8200,9100,8700,10200,11500,12800],borderColor:'#E85D04',backgroundColor:'rgba(232,93,4,.08)',fill:true,tension:.4,borderWidth:2.5,pointRadius:0,pointHoverRadius:5,pointHoverBackgroundColor:'#E85D04'},{label:'Visitors',data:[3200,3800,3500,4600,5200,4900,6100,6800,6500,7800,8400,9200],borderColor:'#0891B2',backgroundColor:'rgba(8,145,178,.05)',fill:true,tension:.4,borderWidth:2,borderDash:[5,5],pointRadius:0,pointHoverRadius:5,pointHoverBackgroundColor:'#0891B2'}]},options:{scales:{x:{grid:{display:false},ticks:{color:fc,font:{size:10}}},y:{grid:{color:gc},ticks:{color:fc,font:{size:10},callback:function(v){return'$'+v/1000+'k'}}}},plugins:{legend:{display:true,position:'top',align:'end',labels:{usePointStyle:true,pointStyle:'circle',padding:14,font:{size:10},color:fc}},tooltip:{mode:'index',intersect:false,backgroundColor:isDark?'#181824':'#0C0C14',titleFont:{size:11},bodyFont:{size:10},padding:8,cornerRadius:6}}}}));
  CH.cat=new Chart(document.getElementById('catChart'),Object.assign({},base,{type:'doughnut',data:{labels:['Burgers','Chicken','Pizza','Sides','Drinks','Others'],datasets:[{data:[28,18,22,16,10,6],backgroundColor:['#E85D04','#059669','#0891B2','#D97706','#7C3AED','#9CA3AF'],borderWidth:0,hoverOffset:5}]},options:{cutout:'70%',plugins:{legend:{display:true,position:'bottom',labels:{usePointStyle:true,pointStyle:'circle',padding:8,font:{size:9},color:fc}},tooltip:{backgroundColor:isDark?'#181824':'#0C0C14',cornerRadius:6,padding:6,bodyFont:{size:10}}}}}));
  CH.ctry=new Chart(document.getElementById('ctryChart'),Object.assign({},base,{type:'bar',data:{labels:['USA','UK','UAE','India','Germany','S. Arabia'],datasets:[{data:[36,36,34,30,36,30],backgroundColor:'rgba(232,93,4,.75)',borderRadius:5,barThickness:24}]},options:{indexAxis:'y',scales:{x:{grid:{color:gc},ticks:{color:fc,font:{size:10}}},y:{grid:{display:false},ticks:{color:fc,font:{size:10}}}},plugins:{tooltip:{backgroundColor:isDark?'#181824':'#0C0C14',cornerRadius:6,padding:6,bodyFont:{size:10},callbacks:{label:function(c){return c.raw+' products'}}}}}}));
}

/* ===== GENERIC LIST ===== */
function listHTML(title,desc,addLbl,addP,fOpts,tbl,total){
  var fo='<select class="fs" onchange="S.filter=this.value;S.lp=1;renderL()"><option value="">All Status</option>';
  fOpts.forEach(function(o){fo+='<option value="'+o+'" '+(S.filter===o?'selected':'')+'>'+o.charAt(0).toUpperCase()+o.slice(1)+'</option>'});
  fo+='</select>';
  return'<div class="ps act"><div class="pg-head"><h1 class="pg-title">'+title+'</h1><p class="pg-desc">'+desc+'</p></div>'+
  '<div class="lt"><div class="lt-l"><div class="ls"><i class="fas fa-search"></i><input type="text" placeholder="Search..." value="'+S.search+'" oninput="S.search=this.value;S.lp=1;renderL()" aria-label="Search"></div>'+fo+'</div><div class="lt-r"><button class="bo" onclick="toast(\'Exported to CSV\',\'inf\')"><i class="fas fa-download"></i> Export</button><button class="ba" onclick="openF(\''+addP+'\')"><i class="fas fa-plus"></i> '+addLbl+'</button></div></div>'+
  '<div class="bulk-bar" id="bulkBar"><span id="bulkCount">0 selected</span><button class="bb-btn" onclick="bulkDel()"><i class="fas fa-trash"></i> Delete</button><button class="bb-btn" onclick="bulkStatus(\'active\')"><i class="fas fa-check"></i> Activate</button><button class="bb-btn" onclick="bulkStatus(\'draft\')"><i class="fas fa-pause"></i> Deactivate</button><button class="bb-close" onclick="S.sel.clear();renderL()"><i class="fas fa-times"></i></button></div>'+
  '<div class="cd"><div class="cd-b p0"><div class="tw">'+tbl+'</div></div></div>'+pgH(total)+'</div>';
}
function chkAll(type){var items=fItems(DB[type],getSF(type));var allC=items.every(function(i){return S.sel.has(i.id)});items.forEach(function(i){if(allC)S.sel.delete(i.id);else S.sel.add(i.id)});renderL()}
function toggleSel(id){if(S.sel.has(id))S.sel.delete(id);else S.sel.add(id);renderL()}
function updateBulk(){var b=$('#bulkBar');if(S.sel.size>0){b.addClass('show');$('#bulkCount').text(S.sel.size+' selected')}else b.removeClass('show')}
function bulkDel(){confirmDel(S.sel.size+' selected item(s) will be permanently deleted.',function(){S.sel.forEach(function(id){DB[S.page]=DB[S.page].filter(function(x){return x.id!==id})});S.sel.clear();toast('Items deleted','err');renderL()})}
function bulkStatus(s){S.sel.forEach(function(id){var it=DB[S.page].find(function(x){return x.id===id});if(it)it.status=s});S.sel.clear();toast('Status updated to '+(s==='active'?'Active':'Draft'),'inf');renderL()}

/* ===== TABLE HELPERS ===== */
function sCell(item,type){var ck=item.status==='active'?'checked':'';return'<td><label class="fsw" style="margin:0"><input type="checkbox" class="chk" '+ck+' onchange="toggleSt(\''+type+'\','+item.id+',this.checked)"><span></span></label></td>'}
function emptyR(c){return'<tr><td colspan="'+c+'"><div class="es"><i class="fas fa-inbox"></i><h4>No items found</h4><p>Try adjusting your search or filters.</p></div></td></tr>'}
function getSF(t){return{users:['name','email','role'],countries:['name','currency'],brands:['name'],categories:['name'],products:['name','brand','category'],offers:['title','code','brand'],blogs:['title','category'],testimonials:['name','role','text'],faqs:['question','answer']}[t]||['name']}

/* ===== USERS ===== */
function rUsers(){var it=fItems(DB.users,getSF('users')),p=pgItems(it),r='';var allC=p.every(function(i){return S.sel.has(i.id)});r+='<tr><th><input type="checkbox" class="chk" '+(allC?'checked':'')+' onchange="chkAll(\'users\')"></th><th>User</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr>';p.forEach(function(u){r+='<tr><td><input type="checkbox" class="chk" '+(S.sel.has(u.id)?'checked':'')+' onchange="toggleSel('+u.id+')"></td><td><div class="tu"><img class="ta" src="'+u.avatar+'" alt="'+u.name+'" loading="lazy"><div><div class="tn">'+u.name+'</div><div class="ts">'+u.email+'</div></div></div></td><td style="font-size:.82rem;">'+u.role+'</td>'+sCell(u,'users')+'<td style="font-size:.82rem;color:var(--muted);">'+u.joined+'</td><td><button class="ab" onclick="openF(\'users\','+u.id+')" title="Edit"><i class="fas fa-pen"></i></button><button class="ab dng" onclick="delItem(\'users\','+u.id+')" title="Delete"><i class="fas fa-trash"></i></button></td></tr>'});if(!p.length)r+=emptyR(6);updateBulk();return listHTML('Users','Manage user accounts and permissions','Add User','users',['active','draft'],'<table class="at">'+r+'</table>',it.length)}

/* ===== COUNTRIES ===== */
function rCountries(){var it=fItems(DB.countries,getSF('countries')),p=pgItems(it),r='';var allC=p.every(function(i){return S.sel.has(i.id)});r+='<tr><th><input type="checkbox" class="chk" '+(allC?'checked':'')+' onchange="chkAll(\'countries\')"></th><th>Flag</th><th>Country</th><th>Currency</th><th style="text-align:center;">Brands</th><th style="text-align:center;">Products</th><th>Status</th><th>Actions</th></tr>';p.forEach(function(c){r+='<tr><td><input type="checkbox" class="chk" '+(S.sel.has(c.id)?'checked':'')+' onchange="toggleSel('+c.id+')"></td><td style="font-size:1.3rem;">'+c.flag+'</td><td style="font-weight:600;">'+c.name+'</td><td>'+c.currency+' ('+c.symbol+')</td><td style="text-align:center;">'+c.brands+'</td><td style="text-align:center;">'+c.products+'</td>'+sCell(c,'countries')+'<td><button class="ab" onclick="openF(\'countries\','+c.id+')" title="Edit"><i class="fas fa-pen"></i></button><button class="ab dng" onclick="delItem(\'countries\','+c.id+')" title="Delete"><i class="fas fa-trash"></i></button></td></tr>'});if(!p.length)r+=emptyR(8);updateBulk();return listHTML('Countries','Manage supported countries and pricing','Add Country','countries',['active','draft'],'<table class="at">'+r+'</table>',it.length)}

/* ===== BRANDS ===== */
function rBrands(){var it=fItems(DB.brands,getSF('brands')),p=pgItems(it),r='';var allC=p.every(function(i){return S.sel.has(i.id)});r+='<tr><th><input type="checkbox" class="chk" '+(allC?'checked':'')+' onchange="chkAll(\'brands\')"></th><th>Brand</th><th style="text-align:center;">Countries</th><th style="text-align:center;">Categories</th><th style="text-align:center;">Products</th><th>Status</th><th>Actions</th></tr>';p.forEach(function(b){r+='<tr><td><input type="checkbox" class="chk" '+(S.sel.has(b.id)?'checked':'')+' onchange="toggleSel('+b.id+')"></td><td><div class="tb-brand"><img src="'+b.logo+'" alt="'+b.name+'" loading="lazy"><span style="font-weight:600;">'+b.name+'</span></div></td><td style="text-align:center;">'+b.countries+'</td><td style="text-align:center;">'+b.categories+'</td><td style="text-align:center;">'+b.products+'</td>'+sCell(b,'brands')+'<td><button class="ab" onclick="openF(\'brands\','+b.id+')" title="Edit"><i class="fas fa-pen"></i></button><button class="ab dng" onclick="delItem(\'brands\','+b.id+')" title="Delete"><i class="fas fa-trash"></i></button></td></tr>'});if(!p.length)r+=emptyR(7);updateBulk();return listHTML('Brands','Manage food brand partnerships','Add Brand','brands',['active','draft'],'<table class="at">'+r+'</table>',it.length)}

/* ===== CATEGORIES ===== */
function rCats(){var it=fItems(DB.categories,getSF('categories')),p=pgItems(it),r='';var allC=p.every(function(i){return S.sel.has(i.id)});r+='<tr><th><input type="checkbox" class="chk" '+(allC?'checked':'')+' onchange="chkAll(\'categories\')"></th><th>Category</th><th style="text-align:center;">Products</th><th>Status</th><th>Actions</th></tr>';p.forEach(function(c){r+='<tr><td><input type="checkbox" class="chk" '+(S.sel.has(c.id)?'checked':'')+' onchange="toggleSel('+c.id+')"></td><td><div class="tu"><img class="ti" src="'+c.image+'" alt="'+c.name+'" loading="lazy"><div class="tn">'+c.name+'</div></div></td><td style="text-align:center;font-weight:600;">'+c.products+'</td>'+sCell(c,'categories')+'<td><button class="ab" onclick="openF(\'categories\','+c.id+')" title="Edit"><i class="fas fa-pen"></i></button><button class="ab dng" onclick="delItem(\'categories\','+c.id+')" title="Delete"><i class="fas fa-trash"></i></button></td></tr>'});if(!p.length)r+=emptyR(5);updateBulk();return listHTML('Categories','Organize menu categories','Add Category','categories',['active','draft'],'<table class="at">'+r+'</table>',it.length)}

/* ===== PRODUCTS ===== */
function rProducts(){var it=fItems(DB.products,getSF('products')),p=pgItems(it),r='';var allC=p.every(function(i){return S.sel.has(i.id)});r+='<tr><th><input type="checkbox" class="chk" '+(allC?'checked':'')+' onchange="chkAll(\'products\')"></th><th>Product</th><th>Brand</th><th>Category</th><th>Price</th><th style="text-align:center;">Cal</th><th>Status</th><th>Actions</th></tr>';p.forEach(function(p){r+='<tr><td><input type="checkbox" class="chk" '+(S.sel.has(p.id)?'checked':'')+' onchange="toggleSel('+p.id+')"></td><td><div class="tu"><img class="ti" src="'+p.image+'" alt="'+p.name+'" loading="lazy"><div class="tn">'+p.name+'</div></div></td><td><div class="tb-brand"><img src="'+bL(p.brand)+'" alt="" loading="lazy"><span>'+bN(p.brand)+'</span></div></td><td>'+cN(p.category)+'</td><td style="font-weight:600;">$'+p.price.toFixed(2)+'</td><td style="text-align:center;">'+p.calories+'</td>'+sCell(p,'products')+'<td><button class="ab" onclick="openF(\'products\','+p.id+')" title="Edit"><i class="fas fa-pen"></i></button><button class="ab dng" onclick="delItem(\'products\','+p.id+')" title="Delete"><i class="fas fa-trash"></i></button></td></tr>'});if(!p.length)r+=emptyR(8);updateBulk();return listHTML('Products','Manage your product catalog','Add Product','products',['active','draft'],'<table class="at">'+r+'</table>',it.length)}

/* ===== OFFERS ===== */
function rOffers(){var it=fItems(DB.offers,getSF('offers')),p=pgItems(it),r='';var allC=p.every(function(i){return S.sel.has(i.id)});r+='<tr><th><input type="checkbox" class="chk" '+(allC?'checked':'')+' onchange="chkAll(\'offers\')"></th><th>Offer</th><th>Brand</th><th style="text-align:center;">Off</th><th>Code</th><th>Expires</th><th>Status</th><th>Actions</th></tr>';p.forEach(function(o){r+='<tr><td><input type="checkbox" class="chk" '+(S.sel.has(o.id)?'checked':'')+' onchange="toggleSel('+o.id+')"></td><td style="font-weight:600;max-width:220px;">'+o.title+'</td><td><div class="tb-brand"><img src="'+bL(o.brand)+'" alt="" loading="lazy"><span>'+bN(o.brand)+'</span></div></td><td style="text-align:center;font-weight:700;color:'+(o.discount?'var(--danger)':'var(--success)')+';">'+(o.discount?o.discount+'%':'FREE')+'</td><td><code style="background:var(--bg);padding:2px 7px;border-radius:4px;font-size:.78rem;font-weight:600;color:var(--accent);">'+o.code+'</code></td><td style="font-size:.8rem;color:var(--muted);">'+o.expires+'</td>'+sCell(o,'offers')+'<td><button class="ab" onclick="openF(\'offers\','+o.id+')" title="Edit"><i class="fas fa-pen"></i></button><button class="ab dng" onclick="delItem(\'offers\','+o.id+')" title="Delete"><i class="fas fa-trash"></i></button></td></tr>'});if(!p.length)r+=emptyR(8);updateBulk();return listHTML('Offers','Manage discount codes and deals','Add Offer','offers',['active','draft','expired'],'<table class="at">'+r+'</table>',it.length)}

/* ===== BLOGS ===== */
function rBlogs(){var it=fItems(DB.blogs,getSF('blogs')),p=pgItems(it),r='';var allC=p.every(function(i){return S.sel.has(i.id)});r+='<tr><th><input type="checkbox" class="chk" '+(allC?'checked':'')+' onchange="chkAll(\'blogs\')"></th><th>Post</th><th>Category</th><th>Date</th><th>Status</th><th>Actions</th></tr>';p.forEach(function(b){r+='<tr><td><input type="checkbox" class="chk" '+(S.sel.has(b.id)?'checked':'')+' onchange="toggleSel('+b.id+')"></td><td><div class="tu"><img class="ti" src="'+b.image+'" alt="" loading="lazy"><div class="tn">'+b.title+'</div></div></td><td><span style="font-size:.8rem;padding:2px 8px;background:var(--bg);border-radius:99px;">'+b.category+'</span></td><td style="font-size:.8rem;color:var(--muted);">'+b.date+'</td>'+sCell(b,'blogs')+'<td><button class="ab" onclick="openF(\'blogs\','+b.id+')" title="Edit"><i class="fas fa-pen"></i></button><button class="ab dng" onclick="delItem(\'blogs\','+b.id+')" title="Delete"><i class="fas fa-trash"></i></button></td></tr>'});if(!p.length)r+=emptyR(6);updateBulk();return listHTML('Blog Posts','Manage content marketing','New Post','blogs',['active','draft'],'<table class="at">'+r+'</table>',it.length)}

/* ===== TESTIMONIALS ===== */
function rTest(){var it=fItems(DB.testimonials,getSF('testimonials')),p=pgItems(it),r='';var allC=p.every(function(i){return S.sel.has(i.id)});r+='<tr><th><input type="checkbox" class="chk" '+(allC?'checked':'')+' onchange="chkAll(\'testimonials\')"></th><th>Person</th><th>Rating</th><th>Review</th><th>Status</th><th>Actions</th></tr>';p.forEach(function(t){r+='<tr><td><input type="checkbox" class="chk" '+(S.sel.has(t.id)?'checked':'')+' onchange="toggleSel('+t.id+')"></td><td><div class="tu"><img class="ta" src="'+t.avatar+'" alt="'+t.name+'" loading="lazy"><div><div class="tn">'+t.name+'</div><div class="ts">'+t.role+'</div></div></div></td><td>'+stars(t.rating)+'</td><td style="max-width:200px;font-size:.8rem;color:var(--text2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+t.text+'</td>'+sCell(t,'testimonials')+'<td><button class="ab" onclick="openF(\'testimonials\','+t.id+')" title="Edit"><i class="fas fa-pen"></i></button><button class="ab dng" onclick="delItem(\'testimonials\','+t.id+')" title="Delete"><i class="fas fa-trash"></i></button></td></tr>'});if(!p.length)r+=emptyR(6);updateBulk();return listHTML('Testimonials','Manage customer reviews','Add Testimonial','testimonials',['active','draft'],'<table class="at">'+r+'</table>',it.length)}

/* ===== FAQS ===== */
function rFaqs(){var it=fItems(DB.faqs,getSF('faqs')),p=pgItems(it),r='';var allC=p.every(function(i){return S.sel.has(i.id)});r+='<tr><th><input type="checkbox" class="chk" '+(allC?'checked':'')+' onchange="chkAll(\'faqs\')"></th><th>Question</th><th>Answer</th><th>Status</th><th>Actions</th></tr>';p.forEach(function(f){r+='<tr><td><input type="checkbox" class="chk" '+(S.sel.has(f.id)?'checked':'')+' onchange="toggleSel('+f.id+')"></td><td style="font-weight:600;max-width:300px;">'+f.question+'</td><td style="max-width:280px;font-size:.8rem;color:var(--text2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+f.answer+'</td>'+sCell(f,'faqs')+'<td><button class="ab" onclick="openF(\'faqs\','+f.id+')" title="Edit"><i class="fas fa-pen"></i></button><button class="ab dng" onclick="delItem(\'faqs\','+f.id+')" title="Delete"><i class="fas fa-trash"></i></button></td></tr>'});if(!p.length)r+=emptyR(5);updateBulk();return listHTML('FAQs','Manage frequently asked questions','Add FAQ','faqs',['active','draft'],'<table class="at">'+r+'</table>',it.length)}

/* ===== SETTINGS ===== */
function rSettings(){
  return'<div class="ps act"><div class="pg-head"><h1 class="pg-title">Settings</h1><p class="pg-desc">Configure platform preferences and system options.</p></div>'+
  '<div class="row g-4"><div class="col-lg-8">'+
    sSec('General',sR('Site Name','MenuCrest','Displayed across the platform')+sR('Site URL','https://menucrest.com','Primary domain')+sR('Admin Email','admin@menucrest.com','Notification address')+sR('Default Country','United States','Pre-selected for visitors'))+
    sSec('Appearance',sR('Primary Color','#E85D04','Main accent color')+sR('Items Per Page','12','List view items')+sR('Date Format','MM/DD/YYYY','Display format for dates'))+
    sSec('Notifications',sT('Email Notifications','Receive email alerts for new registrations')+sT('Order Alerts','Get notified on new orders')+sT('Weekly Report','Weekly summary of platform activity')+sT('Price Change Alerts','Notifications when prices update'))+
    sSec('API & Integrations',sR('API Key','fs_live_xxxxxxxxxxxxxxxx','External integrations')+sT('Enable REST API','Allow third-party access')+sT('Webhooks','Real-time event notifications'))+
    '<button class="ba" style="margin-top:6px;" onclick="toast(\'Settings saved successfully!\')"><i class="fas fa-save"></i> Save All Changes</button>'+
  '</div><div class="col-lg-4">'+
    '<div class="cd mb-3"><div class="cd-h"><span class="cd-t">Platform Info</span></div><div class="cd-b">'+sI('Version','2.4.1')+sI('Last Updated','Jan 15, 2025')+sI('PHP Version','8.2.15')+sI('Database','MySQL 8.0')+'</div></div>'+
    '<div class="cd mb-3"><div class="cd-h"><span class="cd-t">Storage</span></div><div class="cd-b">'+sBar('Images','2.4 GB','10 GB',24)+sBar('Database','180 MB','1 GB',18)+sBar('Logs','45 MB','500 MB',9)+'</div></div>'+
    '<div class="cd"><div class="cd-h"><span class="cd-t">Quick Links</span></div><div class="cd-b d-grid gap-2">'+qA('fa-file-alt','var(--info)','rgba(8,145,178,.1)','View Logs','System activity logs')+qA('fa-database','var(--warn)','rgba(217,119,6,.1)','Backup','Create database backup')+qA('fa-broom','var(--danger)','rgba(220,38,38,.1)','Clear Cache','Remove cached files')+'</div></div>'+
  '</div></div></div>';
}
function sSec(t,c){return'<div class="cd mb-3"><div class="cd-b"><div class="ss-sec"><div class="ss-t">'+t+'</div>'+c+'</div></div></div>'}
function sR(l,v,d){return'<div class="ss-r"><div class="ss-i"><h4>'+l+'</h4><p>'+d+'</p></div><input class="fi" style="width:200px;flex-shrink:0;" value="'+v+'"></div>'}
function sT(l,d){return'<div class="ss-r"><div class="ss-i"><h4>'+l+'</h4><p>'+d+'</p></div><label class="fsw" style="margin:0"><input type="checkbox" checked><span></span></label></div>'}
function sI(l,v){return'<div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border-l);font-size:.82rem;"><span style="color:var(--text2);">'+l+'</span><span style="font-weight:600;">'+v+'</span></div>'}
function sBar(l,u,t,p){return'<div style="margin-bottom:10px;"><div style="display:flex;justify-content:space-between;font-size:.75rem;margin-bottom:3px;"><span>'+l+'</span><span style="font-weight:600;">'+u+' / '+t+'</span></div><div style="height:5px;background:var(--bg);border-radius:3px;overflow:hidden;"><div style="width:'+p+'%;height:100%;background:var(--accent);border-radius:3px;transition:width .5s ease;"></div></div></div>'}

/* ===== FORMS ===== */
function openF(type,id){
  var item=id?DB[type].find(function(x){return x.id==id}):null;
  var isE=!!item;
  var t=isE?'Edit ':'Add ';
  if(type==='faqs')t+=(isE?'FAQ':'FAQ'); else t+=type.slice(0,-1);
  t=t.charAt(0).toUpperCase()+t.slice(1);
  var fh={users:fUser,countries:fCountry,brands:fBrand,categories:fCat,products:fProduct,offers:fOffer,blogs:fBlog,testimonials:fTest,faqs:fFaq};
  openMo('<div class="mo-h"><span class="mo-t">'+t+'</span><button class="mo-c" onclick="closeMo()"><i class="fas fa-times"></i></button></div><div class="mo-b"><form id="iForm" onsubmit="event.preventDefault();saveItem(\''+type+'\','+(id||'null')+')">'+(fh[type]?fh[type](item):'')+'<div class="mo-f" style="padding:0;margin-top:18px;border:none;"><button type="button" class="bo" onclick="closeMo()">Cancel</button><button type="submit" class="ba"><i class="fas fa-save"></i> '+(isE?'Update':'Create')+'</button></div></form></div>');
}
function fUser(u){return'<div class="fr"><div class="fg"><label class="fl">Full Name</label><input class="fi" name="name" value="'+(u?u.name:'')+'" required></div><div class="fg"><label class="fl">Email</label><input class="fi" name="email" type="email" value="'+(u?u.email:'')+'" required></div></div><div class="fr"><div class="fg"><label class="fl">Role</label><select class="fss" name="role"><option'+(u&&u.role==='Super Admin'?' selected':'')+'>Super Admin</option><option'+(u&&u.role==='Editor'?' selected':'')+'>Editor</option><option'+(u&&u.role==='Moderator'?' selected':'')+'>Moderator</option><option'+(u&&u.role==='Viewer'?' selected':'')+'>Viewer</option></select></div><div class="fg"><label class="fl">Status</label><select class="fss" name="status"><option value="active"'+(u&&u.status==='active'?' selected':'')+'>Active</option><option value="draft"'+(u&&u.status==='draft'?' selected':'')+'>Draft</option></select></div></div>'}
function fCountry(c){return'<div class="fr"><div class="fg"><label class="fl">Country Name</label><input class="fi" name="name" value="'+(c?c.name:'')+'" required></div><div class="fg"><label class="fl">Currency Code</label><input class="fi" name="currency" value="'+(c?c.currency:'')+'" placeholder="USD" required></div></div><div class="fr"><div class="fg"><label class="fl">Currency Symbol</label><input class="fi" name="symbol" value="'+(c?c.symbol:'')+'" placeholder="$" required></div><div class="fg"><label class="fl">Status</label><select class="fss" name="status"><option value="active"'+(c&&c.status==='active'?' selected':'')+'>Active</option><option value="draft"'+(c&&c.status==='draft'?' selected':'')+'>Draft</option></select></div></div>'}
function fBrand(b){return'<div class="fg"><label class="fl">Brand Name</label><input class="fi" name="name" value="'+(b?b.name:'')+'" required></div><div class="fr"><div class="fg"><label class="fl">Logo URL</label><input class="fi" name="logo" value="'+(b?b.logo:'')+'"></div><div class="fg"><label class="fl">Cover URL</label><input class="fi" name="cover" value="'+(b?b.cover:'')+'"></div></div><div class="fg"><label class="fl">Status</label><select class="fss" name="status"><option value="active"'+(b&&b.status==='active'?' selected':'')+'>Active</option><option value="draft"'+(b&&b.status==='draft'?' selected':'')+'>Draft</option></select></div>'}
function fCat(c){return'<div class="fr"><div class="fg"><label class="fl">Category Name</label><input class="fi" name="name" value="'+(c?c.name:'')+'" required></div><div class="fg"><label class="fl">Image URL</label><input class="fi" name="image" value="'+(c?c.image:'')+'"></div></div><div class="fg"><label class="fl">Status</label><select class="fss" name="status"><option value="active"'+(c&&c.status==='active'?' selected':'')+'>Active</option><option value="draft"'+(c&&c.status==='draft'?' selected':'')+'>Draft</option></select></div>'}
function fProduct(p){var bo=DB.brands.map(function(b){return'<option value="'+b.id+'"'+(p&&p.brand===b.id?' selected':'')+'>'+b.name+'</option>'}).join('');var co=DB.categories.map(function(c){return'<option value="'+c.id+'"'+(p&&p.category===c.id?' selected':'')+'>'+c.name+'</option>'}).join('');return'<div class="fg"><label class="fl">Product Name</label><input class="fi" name="name" value="'+(p?p.name:'')+'" required></div><div class="fr"><div class="fg"><label class="fl">Brand</label><select class="fss" name="brand" required>'+bo+'</select></div><div class="fg"><label class="fl">Category</label><select class="fss" name="category" required>'+co+'</select></div></div><div class="fr"><div class="fg"><label class="fl">Price (USD)</label><input class="fi" name="price" type="number" step="0.01" value="'+(p?p.price:'')+'" required></div><div class="fg"><label class="fl">Calories</label><input class="fi" name="calories" type="number" value="'+(p?p.calories:'')+'" required></div></div><div class="fg"><label class="fl">Image URL</label><input class="fi" name="image" value="'+(p?p.image:'')+'"></div><div class="fg"><label class="fl">Status</label><select class="fss" name="status"><option value="active"'+(p&&p.status==='active'?' selected':'')+'>Active</option><option value="draft"'+(p&&p.status==='draft'?' selected':'')+'>Draft</option></select></div>'}
function fOffer(o){var bo=DB.brands.map(function(b){return'<option value="'+b.id+'"'+(o&&o.brand===b.id?' selected':'')+'>'+b.name+'</option>'}).join('');return'<div class="fg"><label class="fl">Offer Title</label><input class="fi" name="title" value="'+(o?o.title:'')+'" required></div><div class="fr"><div class="fg"><label class="fl">Brand</label><select class="fss" name="brand" required>'+bo+'</select></div><div class="fg"><label class="fl">Discount %</label><input class="fi" name="discount" type="number" min="0" max="100" value="'+(o?o.discount:'')+'"></div></div><div class="fr"><div class="fg"><label class="fl">Promo Code</label><input class="fi" name="code" value="'+(o?o.code:'')+'" required style="text-transform:uppercase"></div><div class="fg"><label class="fl">Expires</label><input class="fi" name="expires" type="date" value="'+(o?o.expires:'')+'"></div></div><div class="fg"><label class="fl">Status</label><select class="fss" name="status"><option value="active"'+(o&&o.status==='active'?' selected':'')+'>Active</option><option value="draft"'+(o&&o.status==='draft'?' selected':'')+'>Draft</option><option value="expired"'+(o&&o.status==='expired'?' selected':'')+'>Expired</option></select></div>'}
function fBlog(b){return'<div class="fg"><label class="fl">Post Title</label><input class="fi" name="title" value="'+(b?b.title:'')+'" required></div><div class="fr"><div class="fg"><label class="fl">Category</label><input class="fi" name="category" value="'+(b?b.category:'')+'"></div><div class="fg"><label class="fl">Date</label><input class="fi" name="date" type="date" value="'+(b?b.date:'')+'"></div></div><div class="fg"><label class="fl">Image URL</label><input class="fi" name="image" value="'+(b?b.image:'')+'"></div><div class="fg"><label class="fl">Status</label><select class="fss" name="status"><option value="active"'+(b&&b.status==='active'?' selected':'')+'>Active</option><option value="draft"'+(b&&b.status==='draft'?' selected':'')+'>Draft</option></select></div>'}
function fTest(t){return'<div class="fr"><div class="fg"><label class="fl">Name</label><input class="fi" name="name" value="'+(t?t.name:'')+'" required></div><div class="fg"><label class="fl">Role</label><input class="fi" name="role" value="'+(t?t.role:'')+'"></div></div><div class="fr"><div class="fg"><label class="fl">Avatar URL</label><input class="fi" name="avatar" value="'+(t?t.avatar:'')+'"></div><div class="fg"><label class="fl">Rating (1-5)</label><input class="fi" name="rating" type="number" min="1" max="5" value="'+(t?t.rating:'5')+'"></div></div><div class="fg"><label class="fl">Review Text</label><textarea class="ft" name="text" required>'+(t?t.text:'')+'</textarea></div><div class="fg"><label class="fl">Status</label><select class="fss" name="status"><option value="active"'+(t&&t.status==='active'?' selected':'')+'>Active</option><option value="draft"'+(t&&t.status==='draft'?' selected':'')+'>Draft</option></select></div>'}
function fFaq(f){return'<div class="fg"><label class="fl">Question</label><input class="fi" name="question" value="'+(f?f.question:'')+'" required></div><div class="fg"><label class="fl">Answer</label><textarea class="ft" name="answer" required>'+(f?f.answer:'')+'</textarea></div><div class="fg"><label class="fl">Status</label><select class="fss" name="status"><option value="active"'+(f&&f.status==='active'?' selected':'')+'>Active</option><option value="draft"'+(f&&f.status==='draft'?' selected':'')+'>Draft</option></select></div>'}

/* ===== SAVE ===== */
function saveItem(type, id) {
  var d = {};
  $('#iForm').find('input[name],select[name],textarea[name]').each(function () {
    var n = $(this).attr('name'), v = $(this).val();
    if (['price', 'calories', 'discount', 'rating'].indexOf(n) > -1) v = parseFloat(v) || 0;
    d[n] = v;
  });

  var idKeyMap = {
    users: 'uid',
    countries: 'cid',
    brands: 'bid',
    categories: 'catid',
    products: 'pid',
    offers: 'oid',
    blogs: 'blogid',
    testimonials: 'tid',
    faqs: 'fid' // <-- adjust key name to match your actual "type" value for this one
  };

  if (id) {
    var it = DB[type].find(function (x) { return x.id == id; });
    if (it) Object.assign(it, d);
    toast('Updated successfully');
  } else {
    var idKey = idKeyMap[type];
    d.id = DB[idKey];
    DB[idKey]++;
    DB[type].push(d);
    toast('Created successfully');
  }

  closeMo();
  renderL();
}

/* ===== DELETE ===== */
function delItem(type,id){var it=DB[type].find(function(x){return x.id==id});var n=it?(it.name||it.title||it.question||'item'):'';confirmDel('Delete "'+n+'"? This cannot be undone.',function(){DB[type]=DB[type].filter(function(x){return x.id!=id});S.sel.delete(id);toast('Deleted successfully','err');renderL()})}

/* ===== TOGGLE STATUS ===== */
function toggleSt(type,id,ck){var it=DB[type].find(function(x){return x.id==id});if(it){it.status=ck?'active':'draft';toast('Status: '+(ck?'Active':'Draft'),'inf')}}

/* ===== INIT ===== */
 $(document).ready(function(){renderP();renderND()});
