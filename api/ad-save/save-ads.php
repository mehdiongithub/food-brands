<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin']); // only admins can change ad settings
header('Content-Type: application/json');

try {
    $pdo->beginTransaction();

    // ---- 1) Global settings ----
    $adsenseClient  = trim($_POST['adsense_client'] ?? '');
    $adsenseEnabled = isset($_POST['adsense_enabled']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE settings SET adsense_client = :client, adsense_enabled = :enabled");
    $stmt->execute([
        ':client'  => $adsenseClient !== '' ? $adsenseClient : null,
        ':enabled' => $adsenseEnabled,
    ]);

    // ---- 2) Each ad placement row ----
    $units = $_POST['units'] ?? [];
    if (is_array($units)) {
        $updateStmt = $pdo->prepare("
            UPDATE ad_units
            SET ad_slot = :ad_slot,
                ad_format = :ad_format,
                full_width_responsive = :full_width,
                status = :status
            WHERE id = :id
        ");

        foreach ($units as $unit) {
            $id = (int) ($unit['id'] ?? 0);
            if ($id <= 0) continue;

            $adSlot   = trim($unit['ad_slot'] ?? '');
            $format   = $unit['ad_format'] ?? 'auto';
            $allowedFormats = ['auto', 'fluid', 'rectangle', 'horizontal', 'vertical'];
            if (!in_array($format, $allowedFormats, true)) $format = 'auto';

            $fullWidth = isset($unit['full_width_responsive']) ? 1 : 0;
            $status    = isset($unit['status']) ? 1 : 0;

            $updateStmt->execute([
                ':ad_slot'    => $adSlot !== '' ? $adSlot : null,
                ':ad_format'  => $format,
                ':full_width' => $fullWidth,
                ':status'     => $status,
                ':id'         => $id,
            ]);
        }
    }

    $pdo->commit();

    // The public site caches `settings` in $_SESSION['site_settings'] (see
    // includes/functions.php -> getSettings()). Clear it here so the
    // Publisher ID / on-off switch take effect immediately for this admin's
    // own session; other visitors' sessions pick it up on their next one
    // (same behavior as every other setting already saved through this app).
    unset($_SESSION['site_settings']);

    echo json_encode(["success" => true, "message" => "Ad settings saved successfully."]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to save ad settings."]);
}