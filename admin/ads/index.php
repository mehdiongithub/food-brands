<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();

// Only admins can manage ads (employees can view-only, same pattern as categories)
$isAdmin = (currentUserRole() === 'admin');

// Global AdSense settings (client id / enabled switch) live on the settings row
$settingsRow = $pdo->query("SELECT adsense_client, adsense_enabled FROM settings LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];
$adsenseClient  = $settingsRow['adsense_client'] ?? '';
$adsenseEnabled = !empty($settingsRow['adsense_enabled']);

// All placements currently defined
$adUnits = $pdo->query("SELECT * FROM ad_units ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodScope — Admin Ads</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin/style.css">
    <style>
        .ad-form-card { background:var(--surface,#fff); border:1px solid var(--border,#e5e5e5); border-radius:12px; padding:22px; margin-bottom:20px; }
        .ad-form-card h3 { font-size:1rem; font-weight:700; margin-bottom:14px; }
        .ad-row { display:grid; grid-template-columns: 1.6fr 1fr 0.8fr 0.9fr 0.6fr; gap:12px; align-items:center; padding:12px 0; border-bottom:1px solid var(--border,#eee); }
        .ad-row:last-child { border-bottom:none; }
        .ad-row-head { font-size:0.72rem; text-transform:uppercase; letter-spacing:.04em; color:var(--muted,#888); font-weight:700; }
        .ad-row label.small-label { display:block; font-size:0.7rem; color:var(--muted,#888); margin-bottom:3px; }
        .ad-row input[type=text] { width:100%; padding:8px 10px; border:1px solid var(--border,#ddd); border-radius:8px; font-size:0.85rem; }
        .ad-row select { width:100%; padding:8px 10px; border:1px solid var(--border,#ddd); border-radius:8px; font-size:0.85rem; }
        .ad-slug-badge { font-family:monospace; font-size:0.78rem; background:var(--bg,#f4f4f4); padding:2px 7px; border-radius:5px; }
        .switch { position:relative; display:inline-block; width:44px; height:24px; }
        .switch input { opacity:0; width:0; height:0; }
        .slider-toggle { position:absolute; cursor:pointer; inset:0; background:#ccc; transition:.2s; border-radius:24px; }
        .slider-toggle:before { position:absolute; content:""; height:18px; width:18px; left:3px; bottom:3px; background:#fff; transition:.2s; border-radius:50%; }
        input:checked + .slider-toggle { background:var(--accent,#E85D04); }
        input:checked + .slider-toggle:before { transform:translateX(20px); }
        .save-bar { position:sticky; bottom:0; background:var(--surface,#fff); padding:14px 0; border-top:1px solid var(--border,#eee); margin-top:10px; text-align:right; }
    </style>
</head>

<body>

    <div class="sb-bd" id="sbBd" onclick="closeMS()"></div>

    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/header.php'; ?>

    <div id="main">
        <div class="pg-content" id="pgC">

            <div class="pg-head" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
                <div>
                    <h1 class="pg-title">Google AdSense</h1>
                    <p class="pg-desc">Turn ads on/off and manage each ad placement. Nothing shows on the site until a placement below has a valid Ad Slot ID and is enabled.</p>
                </div>
            </div>

            <?php if (!$isAdmin): ?>
                <div class="alert alert-warning">You have view-only access. Only an admin can save changes here.</div>
            <?php endif; ?>

            <form id="adsForm" <?php if (!$isAdmin) echo 'onsubmit="return false;"'; ?>>

                <!-- Global switch -->
                <div class="ad-form-card">
                    <h3><i class="fas fa-toggle-on"></i> Global AdSense Settings</h3>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-7">
                            <label class="small-label">AdSense Publisher ID</label>
                            <input type="text" class="form-control" name="adsense_client" id="adsenseClient"
                                   placeholder="ca-pub-1234567890123456"
                                   value="<?php echo htmlspecialchars($adsenseClient); ?>"
                                   <?php if (!$isAdmin) echo 'disabled'; ?>>
                            <small class="text-muted">Found in your AdSense account under Account &rarr; Account information.</small>
                        </div>
                        <div class="col-md-5">
                            <label class="small-label">Show ads on the site</label>
                            <label class="switch">
                                <input type="checkbox" name="adsense_enabled" id="adsenseEnabled"
                                       <?php echo $adsenseEnabled ? 'checked' : ''; ?>
                                       <?php if (!$isAdmin) echo 'disabled'; ?>>
                                <span class="slider-toggle"></span>
                            </label>
                            <span style="font-size:0.82rem;color:var(--muted,#888);margin-left:8px;">
                                <?php echo $adsenseEnabled ? 'Ads are ON' : 'Ads are OFF'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Placements -->
                <div class="ad-form-card">
                    <h3><i class="fas fa-table-cells"></i> Ad Placements</h3>

                    <div class="ad-row ad-row-head">
                        <div>Placement</div>
                        <div>Ad Slot ID</div>
                        <div>Format</div>
                        <div>Full-width responsive</div>
                        <div>Enabled</div>
                    </div>

                    <?php if (empty($adUnits)): ?>
                        <p class="text-muted" style="padding:16px 0;">No ad placements found. Run <code>ads-migration.sql</code> to seed the default placements used by the site templates.</p>
                    <?php endif; ?>

                    <?php foreach ($adUnits as $i => $u): ?>
                    <div class="ad-row" data-id="<?php echo (int) $u['id']; ?>">
                        <div>
                            <div style="font-weight:600;"><?php echo htmlspecialchars($u['name']); ?></div>
                            <span class="ad-slug-badge"><?php echo htmlspecialchars($u['slug']); ?></span>
                        </div>
                        <div>
                            <input type="text" name="units[<?php echo $i; ?>][ad_slot]"
                                   value="<?php echo htmlspecialchars($u['ad_slot'] ?? ''); ?>"
                                   placeholder="e.g. 1234567890" <?php if (!$isAdmin) echo 'disabled'; ?>>
                            <input type="hidden" name="units[<?php echo $i; ?>][id]" value="<?php echo (int) $u['id']; ?>">
                        </div>
                        <div>
                            <select name="units[<?php echo $i; ?>][ad_format]" <?php if (!$isAdmin) echo 'disabled'; ?>>
                                <?php foreach (['auto', 'fluid', 'rectangle', 'horizontal', 'vertical'] as $fmt): ?>
                                <option value="<?php echo $fmt; ?>" <?php echo ($u['ad_format'] === $fmt) ? 'selected' : ''; ?>><?php echo ucfirst($fmt); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="switch">
                                <input type="checkbox" name="units[<?php echo $i; ?>][full_width_responsive]"
                                       <?php echo $u['full_width_responsive'] ? 'checked' : ''; ?>
                                       <?php if (!$isAdmin) echo 'disabled'; ?>>
                                <span class="slider-toggle"></span>
                            </label>
                        </div>
                        <div>
                            <label class="switch">
                                <input type="checkbox" name="units[<?php echo $i; ?>][status]"
                                       <?php echo $u['status'] ? 'checked' : ''; ?>
                                       <?php if (!$isAdmin) echo 'disabled'; ?>>
                                <span class="slider-toggle"></span>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($isAdmin): ?>
                <div class="save-bar">
                    <button type="submit" class="ba" id="saveAdsBtn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
                <?php endif; ?>
            </form>

        </div>
    </div>

    <div class="tw2" id="tw2"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin/sidebar.js"></script>

    <script>
        function toast(m, t) {
            t = t || 'suc';
            var ic = { suc: 'fa-check-circle', err: 'fa-times-circle', wrn: 'fa-exclamation-triangle', inf: 'fa-info-circle' };
            var $t = $('<div class="ti2 ' + t + '"><i class="fas ' + ic[t] + '"></i><span>' + m + '</span></div>');
            $('#tw2').append($t);
            setTimeout(function () { $t.fadeOut(250, function () { $(this).remove(); }); }, 2800);
        }

        $('#adsenseEnabled').on('change', function () {
            $(this).closest('.col-md-5').find('span').last().text(this.checked ? 'Ads are ON' : 'Ads are OFF');
        });

        $('#adsForm').on('submit', function (e) {
            e.preventDefault();
            var $btn = $('#saveAdsBtn');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: '../../api/ad-save/save-ads.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json'
            })
            .done(function (res) {
                if (res.success) {
                    toast(res.message || 'Ad settings saved successfully', 'suc');
                } else {
                    toast(res.message || 'Failed to save ad settings', 'err');
                }
            })
            .fail(function () {
                toast('Something went wrong. Please try again.', 'err');
            })
            .always(function () {
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Changes');
            });
        });
    </script>
</body>

</html>