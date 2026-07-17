<?php
require_once __DIR__ . "/../config/bootstrap.php";
requireLogin();

?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MenuCrest — Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="../assets/css/admin/style.css">
</head>
<body>

<div class="sb-bd" id="sbBd" onclick="closeMS()"></div>

<!-- Sidebar -->
<?php include 'includes/sidebar.php'; ?>

<!-- Header -->
<?php include 'includes/header.php'; ?>

<!-- Main Content -->
<div id="main">
<div class="pg-content" id="pgC">
<div class="ps act">

  <div class="pg-head">
    <h1 class="pg-title" id="dashGreeting">Good morning, Alex</h1>
    <p class="pg-desc" id="dashSummary">Loading your overview…</p>
  </div>

  <!-- ===== TOP STAT CARDS ===== -->
  <div class="row g-3 mb-4" id="statCards">
    <div class="col-6 col-lg-3">
      <div class="sc o">
        <div class="sc-icon o"><i class="fas fa-hamburger"></i></div>
        <div class="sc-val" id="statTotalProducts">--</div>
        <div class="sc-lbl">Total Products</div>
        <span class="sc-chg up" id="statTotalProductsChg"><i class="fas fa-arrow-up"></i> --</span>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="sc g">
        <div class="sc-icon g"><i class="fas fa-store"></i></div>
        <div class="sc-val" id="statActiveBrands">--</div>
        <div class="sc-lbl">Active Brands</div>
        <span class="sc-chg up" id="statActiveBrandsChg"><i class="fas fa-arrow-up"></i> --</span>
      </div>
    </div>
    <?php if (currentUserRole() === 'admin') { ?>
    <div class="col-6 col-lg-3">
      <div class="sc b">
        <div class="sc-icon b"><i class="fas fa-users"></i></div>
        <div class="sc-val" id="statRegisteredUsers">--</div>
        <div class="sc-lbl">Registered Users</div>
        <span class="sc-chg up" id="statRegisteredUsersChg"><i class="fas fa-arrow-up"></i> --</span>
      </div>
    </div>
    <?php } ?>

    <div class="col-6 col-lg-3">
      <div class="sc r">
        <div class="sc-icon r"><i class="fas fa-tags"></i></div>
        <div class="sc-val" id="statActiveOffers">--</div>
        <div class="sc-lbl">Active Offers</div>
        <span class="sc-chg dn" id="statActiveOffersChg"><i class="fas fa-arrow-down"></i> --</span>
      </div>
    </div>
  </div>

  </div>

  <!-- ===== QUICK ACTIONS + RECENT ACTIVITY ===== -->
  <div class="row g-3 mb-4">
     <?php if (currentUserRole() === 'admin') { ?>
    <div class="col-lg-6">
      <div class="cd">
        <div class="cd-h"><span class="cd-t">Quick Actions</span></div>
        <div class="cd-b">
          <div class="d-grid gap-2" id="quickActions">
            <!-- filled by loadQuickActions() -->
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="cd">
        <div class="cd-h"><span class="cd-t">Recent Activity</span></div>
        <div class="cd-b" id="recentActivity">
          <!-- filled by loadRecentActivity() -->
        </div>
      </div>
    </div>
</div>

<!-- ===== TOP PRODUCTS + RECENT SIGNUPS ===== -->
<div class="row g-3 mb-4">
<?php } ?>
    <div class="col-lg-6">
      <div class="cd">
        <div class="cd-h">
          <span class="cd-t">Top Products</span>
          <span style="font-size:.72rem;color:var(--muted);">By views this week</span>
        </div>
        <div class="cd-b p0">
          <div class="tw">
            <table class="at">
              <thead><tr><th>Product</th><th>Brand</th><th>Views</th><th>Trend</th></tr></thead>
              <tbody id="topProducts">
                <!-- filled by loadTopProducts() -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <?php if (currentUserRole() === 'admin') { ?>
    <div class="col-lg-6">
      <div class="cd">
        <div class="cd-h"><span class="cd-t">Recent Signups</span></div>
        <div class="cd-b p0">
          <div class="tw">
            <table class="at">
              <thead><tr><th>User</th><th>Role</th><th>Date</th><th>Status</th></tr></thead>
              <tbody id="recentSignups">
                <!-- filled by loadRecentSignups() -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <?php } ?>
  </div>

</div>
</div>
</div>

<div class="mo" id="moOv"><div class="mo-box" id="moBox" role="dialog"></div></div>
<div class="tw2" id="tw2"></div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="../assets/js/admin/sidebar.js"></script>

<script>
/* ============================================================
   DASHBOARD DATA LOADING
   Each section calls its own API endpoint. Replace the URLs
   below with your real backend endpoints. Every function is
   independent, so any one of them failing won't break the rest.
   ============================================================ */

var API = {
  stats:          "../api/dashboard/stats.php",
  quickActions:   "../api/dashboard/quick-actions.php",
  recentActivity: "../api/dashboard/recent-activity.php",
  topProducts:    "../api/dashboard/top-products.php",
  recentSignups:  "../api/dashboard/recent-signups.php"
};

var CH = {}; // chart instances so we can destroy/rebuild on refresh

function sbBadge(status){
  status = (status || "").toLowerCase();
  var label = status.charAt(0).toUpperCase() + status.slice(1);
  return '<span class="sb-badge2 ' + status + '">' + label + '</span>';
}

/* ---------- Greeting ---------- */
(function setGreeting(){
  var h = new Date().getHours();
  var g = h < 12 ? "Good morning" : h < 17 ? "Good afternoon" : "Good evening";
  document.getElementById("dashGreeting").textContent = g + ", Alex";
})();

/* ---------- 1. Top stat cards ----------
Expected JSON:
{
  "total_products": 36, "total_products_change": "+12%", "total_products_trend": "up",
  "active_brands": 8, "active_brands_change": "+2 new", "active_brands_trend": "up",
  "registered_users": 248, "registered_users_change": "+8.3%", "registered_users_trend": "up",
  "active_offers": 5, "active_offers_change": "-2 expired", "active_offers_trend": "down",
  "summary": "You have 5 active offers and 12 new users this month to review."
}
*/
function loadStats(){
  fetch(API.stats)
    .then(function(r){ return r.json(); })
    .then(function(d){
      document.getElementById("statTotalProducts").textContent    = d.total_products;
      document.getElementById("statActiveBrands").textContent     = d.active_brands;
      <?php if (currentUserRole() === 'admin') { ?>
      document.getElementById("statRegisteredUsers").textContent  = d.registered_users;
        <?php } ?>
      document.getElementById("statActiveOffers").textContent     = d.active_offers;

      setChg("statTotalProductsChg",   d.total_products_change,   d.total_products_trend);
      setChg("statActiveBrandsChg",    d.active_brands_change,    d.active_brands_trend);
      setChg("statRegisteredUsersChg", d.registered_users_change, d.registered_users_trend);
      setChg("statActiveOffersChg",    d.active_offers_change,    d.active_offers_trend);

      if (d.summary) document.getElementById("dashSummary").textContent = d.summary;
    })
    .catch(function(err){ console.error("stats load failed:", err); });
}
function setChg(id, text, trend){
  var el = document.getElementById(id);
  if (!el) return;
  trend = (trend === "down") ? "dn" : "up";
  el.className = "sc-chg " + trend;
  el.innerHTML = '<i class="fas fa-arrow-' + (trend === "dn" ? "down" : "up") + '"></i> ' + (text || "");
}

/* ---------- 4. Quick Actions ----------
Expected JSON (array):
[
  { "icon": "fa-plus", "color": "var(--accent)", "bg": "rgba(232,93,4,.1)", "title": "Add New Product", "desc": "Create a product listing", "link": "products.php" },
  { "icon": "fa-tags", "color": "var(--success)", "bg": "rgba(5,150,105,.1)", "title": "Create Offer", "desc": "Set up a new deal", "link": "offers.php" }
]
*/
<?php if (currentUserRole() === 'admin') { ?>
function loadQuickActions(){
  fetch(API.quickActions)
    .then(function(r){ return r.json(); })
    .then(function(list){
      var html = list.map(function(a){
        return '<a class="qa" href="' + (a.link || '#') + '">' +
                 '<div class="qa-i" style="background:' + a.bg + ';color:' + a.color + ';"><i class="fas ' + a.icon + '"></i></div>' +
                 '<div><div class="qa-t">' + a.title + '</div><div class="qa-s">' + a.desc + '</div></div>' +
               '</a>';
      }).join('');
      document.getElementById("quickActions").innerHTML = html || '<div class="es"><p>No quick actions.</p></div>';
    })
    .catch(function(err){ console.error("quick actions load failed:", err); });
}
<?php } ?>
/* ---------- 5. Recent Activity ----------
Expected JSON (array):
[ { "color": "g", "text": "<strong>Sarah Mitchell</strong> published a post", "time": "2h ago" } ]
color: g = green, o = orange, b = blue, r = red
*/
<?php if (currentUserRole() === 'admin') { ?>
function loadRecentActivity(){
  fetch(API.recentActivity)
    .then(function(r){ return r.json(); })
    .then(function(list){
      var html = list.map(function(a){
        return '<div class="aci">' +
                 '<div class="acd ' + a.color + '"></div>' +
                 '<div><div class="act">' + a.text + '</div><div class="atm">' + a.time + '</div></div>' +
               '</div>';
      }).join('');
      document.getElementById("recentActivity").innerHTML = html || '<div class="es"><p>No recent activity.</p></div>';
    })
    .catch(function(err){ console.error("recent activity load failed:", err); });
}

<?php } ?>

/* ---------- 6. Top Products ----------
Expected JSON (array):
[ { "name": "Big Mac", "brand": "McDonald's", "views": "12,847", "trend": "up" } ]
trend: "up" or "down"
*/
function loadTopProducts(){
  fetch(API.topProducts)
    .then(function(r){ return r.json(); })
    .then(function(list){
      var html = list.map(function(p){
        var trendClass = p.trend === "down" ? "dn" : "up";
        var arrow = p.trend === "down" ? "down" : "up";
        return '<tr>' +
                 '<td><div class="tu"><div class="tn">' + p.name + '</div></div></td>' +
                 '<td>' + p.brand + '</td>' +
                 '<td style="font-weight:600;">' + p.views + '</td>' +
                 '<td><span class="sc-chg ' + trendClass + '" style="font-size:.68rem;"><i class="fas fa-arrow-' + arrow + '"></i> ' + (p.change || '') + '</span></td>' +
               '</tr>';
      }).join('');
      document.getElementById("topProducts").innerHTML = html || '<tr><td colspan="4"><div class="es"><i class="fas fa-inbox"></i><h4>No data</h4></div></td></tr>';
    })
    .catch(function(err){ console.error("top products load failed:", err); });
}

/* ---------- 7. Recent Signups ----------
Expected JSON (array):
[ { "name": "Lisa Wang", "avatar": "https://...", "role": "Viewer", "date": "2024-01-12", "status": "active" } ]
*/
function avatarMarkup(u){
  if (u.avatar) {
    return '<img class="ta" src="' + u.avatar + '" alt="' + u.name + '" loading="lazy">';
  }
  // No photo on file — show an initials circle instead, same "ta" size/shape.
  var bg = u.avatar_color || '#9CA3AF';
  return '<div class="ta" style="background:' + bg + ';color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.72rem;">' +
           (u.initials || '?') +
         '</div>';
}

<?php if (currentUserRole() === 'admin') { ?>
function loadRecentSignups(){
  fetch(API.recentSignups)
    .then(function(r){ return r.json(); })
    .then(function(d){
      var list = d.signups || [];
      var html = list.map(function(u){
        return '<tr>' +
                 '<td><div class="tu">' + avatarMarkup(u) +
                 '<div><div class="tn">' + u.name + '</div><div class="ts">' + u.role + '</div></div></div></td>' +
                 '<td style="font-size:.82rem;">' + u.role + '</td>' +
                 '<td style="font-size:.82rem;color:var(--muted);">' + u.date + '</td>' +
                 '<td>' + sbBadge(u.status) + '</td>' +
               '</tr>';
      }).join('');
      document.getElementById("recentSignups").innerHTML = html || '<tr><td colspan="4"><div class="es"><i class="fas fa-inbox"></i><h4>No data</h4></div></td></tr>';
    })
    .catch(function(err){ console.error("recent signups load failed:", err); });
}

<?php } ?>

/* ---------- Boot ---------- */
document.addEventListener("DOMContentLoaded", function(){
  loadStats();
  loadTopProducts();
  <?php if (currentUserRole() === 'admin') { ?>
  loadQuickActions();
  loadRecentActivity();
  loadRecentSignups();
  <?php } ?>
});
</script>
</body>
</html>