<?php
$currentPath = $_SERVER['PHP_SELF']; // e.g. /foodscope/admin/users/index.php
$role = currentUserRole(); 

function isActive($segment, $currentPath) {
    return strpos($currentPath, $segment) !== false ? 'active' : '';
}
?>
<aside id="sidebar" role="navigation" aria-label="Admin navigation">
  <div class="sb-head">
    <div class="sb-logo">Food<span>Scope</span></div>
    <button class="sb-col-btn" onclick="toggleSB()" aria-label="Toggle sidebar"><i class="fas fa-chevron-left"></i></button>
  </div>

  <nav class="sb-nav">

    <div class="sb-sec">
      <div class="sb-sec-t">Main</div>
      <a class="sb-link <?= isActive('/admin/dashboard.php', $currentPath) ?>"
         href="<?= BASE_URL ?>/admin/dashboard.php">
         <i class="fas fa-th-large"></i><span>Dashboard</span>
      </a>
    </div>

    <div class="sb-sec">
      <div class="sb-sec-t">Content</div>

      <?php if ($role === 'admin'): ?>
      <a class="sb-link <?= isActive('/admin/users/', $currentPath) ?>"
         href="<?= BASE_URL ?>/admin/users/index.php">
         <i class="fas fa-users"></i><span>Users</span>
         <span class="sb-badge">248</span>
      </a>
      <?php endif; ?>

      <a class="sb-link <?= isActive('/admin/countries/', $currentPath) ?>"
         href="<?= BASE_URL ?>/admin/countries/index.php">
         <i class="fas fa-globe-americas"></i><span>Countries</span>
      </a>

      <a class="sb-link <?= isActive('/admin/brands/', $currentPath) ?>"
         href="<?= BASE_URL ?>/admin/brands/index.php">
         <i class="fas fa-store"></i><span>Brands</span>
      </a>

      <a class="sb-link <?= isActive('/admin/categories/', $currentPath) ?>"
         href="<?= BASE_URL ?>/admin/categories/index.php">
         <i class="fas fa-layer-group"></i><span>Categories</span>
      </a>

      <a class="sb-link <?= isActive('/admin/products/', $currentPath) ?>"
         href="<?= BASE_URL ?>/admin/products/index.php">
         <i class="fas fa-hamburger"></i><span>Products</span>
         <span class="sb-badge">36</span>
      </a>
      
      <a class="sb-link <?= isActive('/admin/ingredients/', $currentPath) ?>"
         href="<?= BASE_URL ?>/admin/ingredients/index.php">
         <i class="fas fa-leaf"></i><span>Ingredients</span>
         <span class="sb-badge">12</span>
      </a>

      <a class="sb-link <?= isActive('/admin/offers/', $currentPath) ?>"
         href="<?= BASE_URL ?>/admin/offers/index.php">
         <i class="fas fa-tags"></i><span>Offers</span>
      </a>
    </div>

    <?php if ($role === 'admin'): ?>
    <div class="sb-sec">
      <div class="sb-sec-t">Marketing</div>

      <a class="sb-link <?= isActive('/admin/blogs/', $currentPath) ?>"
         href="<?= BASE_URL ?>/admin/blogs/index.php">
         <i class="fas fa-pen-nib"></i><span>Blog Posts</span>
      </a>

      <a class="sb-link <?= isActive('/admin/testimonials/', $currentPath) ?>"
         href="<?= BASE_URL ?>/admin/testimonials/index.php">
         <i class="fas fa-quote-right"></i><span>Testimonials</span>
      </a>

      <a class="sb-link <?= isActive('/admin/faqs/', $currentPath) ?>"
         href="<?= BASE_URL ?>/admin/faqs/index.php">
         <i class="fas fa-circle-question"></i><span>FAQs</span>
      </a>
    </div>
    <?php endif; ?>

    <div class="sb-sec">
      <div class="sb-sec-t">System</div>
      <a class="sb-link <?= isActive('/admin/settings/', $currentPath) ?>"
         href="<?= BASE_URL ?>/admin/settings/index.php">
         <i class="fas fa-cog"></i><span>Settings</span>
      </a>
    </div>

  </nav>

  <div class="sb-foot">
    <div class="sb-av"><?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?></div>
    <div class="sb-uinfo">
      <div class="sb-uname"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></div>
      <div class="sb-urole"><?= ucfirst($_SESSION['user_role'] ?? 'Guest') ?></div>
    </div>
  </div>
</aside>