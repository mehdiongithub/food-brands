<?php
// Pull the logged-in user's info from session (set at login.php)
$currentName  = $_SESSION['user_name'] ?? 'Guest User';
$currentRole  = $_SESSION['user_role'] ?? 'guest';
$currentImage = $_SESSION['user_image'] ?? '';

// Get email + fresh image from DB (session only stores name/role/image at login time,
// but image might change later via profile edit — safer to pull fresh each page load)
$currentEmail = '';
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT email, image FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u) {
        $currentEmail = $u['email'];
        $currentImage = $u['image'];
    }
}

// Avatar: real image if set, otherwise initials
if (!empty($currentImage)) {
    $avatarUrl = BASE_URL . '/' . $currentImage;
    $avatarHtml = '<img class="tb-pav" src="' . htmlspecialchars($avatarUrl) . '" alt="Profile">';
    $avatarHtmlSmall = '<img src="' . htmlspecialchars($avatarUrl) . '" alt="">';
} else {
    $initials = getInitials($currentName); // uses the helper from functions.php
    $avatarHtml = '<div class="tb-pav" style="display:flex;align-items:center;justify-content:center;background:' . initialsColor($currentName) . ';color:#fff;font-weight:700;font-size:.8rem;">' . $initials . '</div>';
    $avatarHtmlSmall = '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:' . initialsColor($currentName) . ';color:#fff;font-weight:700;border-radius:50%;">' . $initials . '</div>';
}

$roleLabel = ucfirst($currentRole);
?>
<header id="topbar">
  <button class="tb-menu" onclick="openMS()" aria-label="Menu"><i class="fas fa-bars"></i></button>
  <div class="tb-bc" id="bc"><i class="fas fa-home" style="font-size:.75rem;color:var(--muted);"></i><span style="color:var(--muted);">/</span><span class="bc-a">Dashboard</span></div>
  <div class="tb-acts">
    <div class="tb-search"><i class="fas fa-search"></i><input type="text" placeholder="Search..." aria-label="Search" onkeydown="if(event.key==='Enter'){S.search=this.value;S.lp=1;renderL();}"></div>
    <button class="tb-ib" onclick="toggleTheme()" aria-label="Toggle theme" id="themeBtn"><i class="fas fa-moon"></i></button>
    <div style="position:relative">
      <div class="tb-profile" onclick="togglePD(event)" id="profileBtn">
        <?= $avatarHtml ?>
        <div><div class="tb-pn"><?= htmlspecialchars($currentName) ?></div><div class="tb-pr"><?= htmlspecialchars($roleLabel) ?></div></div>
        <i class="fas fa-chevron-down tb-pch"></i>
      </div>
      <div class="pd-drop" id="pdDrop">
          <div class="pd-head">
              <?= $avatarHtmlSmall ?>
              <div><div class="pd-hn"><?= htmlspecialchars($currentName) ?></div><div class="pd-he"><?= htmlspecialchars($currentEmail) ?></div></div>
          </div>
          <div class="pd-div"></div>
          <a class="pd-item" href="<?= BASE_URL ?>/admin/settings/index.php?tab=profile"><i class="fas fa-user"></i> My Profile</a>
          <a class="pd-item" href="<?= BASE_URL ?>/admin/settings/index.php?tab=security"><i class="fas fa-key"></i> Change Password</a>
          <a class="pd-item" href="<?= BASE_URL ?>/admin/settings/index.php?tab=account"><i class="fas fa-id-badge"></i> Account Info</a>
          <div class="pd-div"></div>
          <a class="pd-item lo" href="<?= BASE_URL ?>/admin/logout.php"><i class="fas fa-right-from-bracket"></i> Sign Out</a>
      </div>
    </div>
  </div>
</header>