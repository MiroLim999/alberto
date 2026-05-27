<?php
session_start();
include "db_connect.php";

// ── Role guard: driver only ──────────────────────
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'driver') {
    header("Location: login.php");
    exit;
}

$driver_id = intval($_SESSION['user_id']);
$driver_name = htmlspecialchars($_SESSION['username'] ?? 'Driver');

// ── FETCH: Orders ready for delivery ──
$availableQuery = "
    SELECT o.order_id, o.branch_id, o.address, o.order_type,
           o.payment_method, o.status, o.created_at, o.driver_id,
           COALESCE(u.username,      oc.customer_name) AS customer_name,
           COALESCE(u.mobile_number, oc.mobile_number) AS mobile_number,
           COALESCE(u.email,         oc.email)         AS email,
           (SELECT SUM(oi.quantity * pv.price)
            FROM order_items oi
            JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
            WHERE oi.order_id = o.order_id)            AS total_amount,
           b.branch_name, b.location AS branch_location
    FROM orders o
    LEFT JOIN users          u  ON o.user_id  = u.user_id
    LEFT JOIN order_contacts oc ON o.order_id = oc.order_id
    LEFT JOIN branches       b  ON o.branch_id = b.branch_id
    WHERE o.order_type = 'DELIVERY' AND o.status = 'completed' AND o.driver_id IS NULL
    ORDER BY o.created_at ASC
";
$availableResult = $conn->query($availableQuery);
$availableRows = [];
while ($row = $availableResult->fetch_assoc()) $availableRows[] = $row;

// ── FETCH: Active deliveries ──
$activeStmt = $conn->prepare("
    SELECT o.order_id, o.branch_id, o.address, o.order_type,
           o.payment_method, o.status, o.created_at, o.driver_id,
           COALESCE(u.username,      oc.customer_name) AS customer_name,
           COALESCE(u.mobile_number, oc.mobile_number) AS mobile_number,
           COALESCE(u.email,         oc.email)         AS email,
           (SELECT SUM(oi.quantity * pv.price)
            FROM order_items oi
            JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
            WHERE oi.order_id = o.order_id)            AS total_amount,
           b.branch_name, b.location AS branch_location
    FROM orders o
    LEFT JOIN users          u  ON o.user_id  = u.user_id
    LEFT JOIN order_contacts oc ON o.order_id = oc.order_id
    LEFT JOIN branches       b  ON o.branch_id = b.branch_id
    WHERE o.order_type = 'DELIVERY' AND o.status = 'out_for_delivery' AND o.driver_id = ?
    ORDER BY o.created_at ASC
");
$activeStmt->bind_param("i", $driver_id);
$activeStmt->execute();
$activeResult = $activeStmt->get_result();
$activeRows = [];
while ($row = $activeResult->fetch_assoc()) $activeRows[] = $row;

// ── FETCH: Recently delivered ──
$doneStmt = $conn->prepare("
    SELECT o.order_id, o.branch_id, o.address, o.order_type,
           o.payment_method, o.status, o.created_at, o.updated_at, o.driver_id,
           COALESCE(u.username,      oc.customer_name) AS customer_name,
           COALESCE(u.mobile_number, oc.mobile_number) AS mobile_number,
           (SELECT SUM(oi.quantity * pv.price)
            FROM order_items oi
            JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
            WHERE oi.order_id = o.order_id)            AS total_amount,
           b.branch_name, b.location AS branch_location
    FROM orders o
    LEFT JOIN users          u  ON o.user_id  = u.user_id
    LEFT JOIN order_contacts oc ON o.order_id = oc.order_id
    LEFT JOIN branches       b  ON o.branch_id = b.branch_id
    WHERE o.order_type = 'DELIVERY' AND o.status = 'delivered' AND o.driver_id = ?
    ORDER BY o.updated_at DESC
    LIMIT 20
");
$doneStmt->bind_param("i", $driver_id);
$doneStmt->execute();
$doneResult = $doneStmt->get_result();
$doneRows = [];
while ($row = $doneResult->fetch_assoc()) $doneRows[] = $row;

// ── Today's stats ──
$todayStmt = $conn->prepare("
    SELECT COUNT(*) AS today_count,
           COALESCE(SUM((SELECT SUM(oi.quantity * pv.price)
                         FROM order_items oi
                         JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
                         WHERE oi.order_id = o.order_id)), 0) AS today_total
    FROM orders o
    WHERE o.driver_id = ?
      AND o.status = 'delivered'
      AND DATE(o.updated_at) = CURDATE()
");
$todayStmt->bind_param("i", $driver_id);
$todayStmt->execute();
$todayStats = $todayStmt->get_result()->fetch_assoc();

// Helper function to fetch items for an order
function getOrderItems($conn, $oid) {
    $oid = intval($oid);
    return $conn->query("
        SELECT oi.quantity, p.pizza_name, pv.size, pv.cheese, pv.price,
               (oi.quantity * pv.price) AS total
        FROM order_items oi
        JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
        JOIN pizzas p ON pv.pizza_id = p.pizza_id
        WHERE oi.order_id = $oid
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Alberto's Pizza | Driver Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>

    /* ══ DRIVER DASHBOARD — MODERN UI ══ */
    :root {
      --d-primary: #2563EB;
      --d-primary-dark: #1D4ED8;
      --d-success: #10B981;
      --d-success-dark: #059669;
      --d-warning: #F59E0B;
      --d-danger: #EF4444;
      --d-gray-50: #F9FAFB;
      --d-gray-100: #F3F4F6;
      --d-gray-200: #E5E7EB;
      --d-gray-300: #D1D5DB;
      --d-gray-400: #9CA3AF;
      --d-gray-500: #6B7280;
      --d-gray-600: #4B5563;
      --d-gray-700: #374151;
      --d-gray-800: #1F2937;
      --d-gray-900: #111827;
      --d-shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05);
      --d-shadow: 0 1px 3px 0 rgba(0,0,0,0.1), 0 1px 2px 0 rgba(0,0,0,0.06);
      --d-shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
      --d-shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
      --d-shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
    }

    body.driver-page {
      background: #F4F6FA;
      min-height: 100vh;
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      color: var(--d-gray-900);
    }

    body.driver-page * { box-sizing: border-box; }

    /* ── HERO HEADER ── */
    .d-hero {
      background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 50%, #3B82F6 100%);
      color: #fff;
      padding: 32px 40px 80px;
      position: relative;
      overflow: hidden;
    }
    .d-hero::before {
      content: '';
      position: absolute;
      top: -50%; right: -10%;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
      border-radius: 50%;
    }
    .d-hero::after {
      content: '🛵';
      position: absolute;
      bottom: -20px; right: 40px;
      font-size: 200px;
      opacity: 0.08;
      transform: rotate(-15deg);
    }

    .d-hero-inner {
      position: relative;
      z-index: 1;
      max-width: 1400px;
      margin: 0 auto;
    }

    .d-hero-greeting {
      font-size: 13px;
      font-weight: 500;
      opacity: 0.85;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }

    .d-hero h1 {
      font-size: 28px;
      font-weight: 800;
      letter-spacing: -0.5px;
      margin: 0 0 6px;
    }

    .d-hero-sub {
      font-size: 14px;
      opacity: 0.8;
      margin: 0;
    }

    /* ── STATS CARDS ── */
    .d-stats {
      max-width: 1400px;
      margin: -52px auto 0;
      padding: 0 40px;
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      position: relative;
      z-index: 2;
    }

    .d-stat-card {
      background: #fff;
      border-radius: 14px;
      padding: 18px 20px;
      box-shadow: var(--d-shadow-md);
      display: flex;
      align-items: center;
      gap: 14px;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .d-stat-card:hover {
      transform: translateY(-3px);
      box-shadow: var(--d-shadow-xl);
    }

    .d-stat-icon {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      flex-shrink: 0;
    }
    .d-stat-icon.blue   { background: #DBEAFE; color: #1D4ED8; }
    .d-stat-icon.amber  { background: #FEF3C7; color: #B45309; }
    .d-stat-icon.green  { background: #D1FAE5; color: #047857; }
    .d-stat-icon.purple { background: #EDE9FE; color: #6D28D9; }

    .d-stat-info { flex: 1; min-width: 0; }
    .d-stat-label {
      font-size: 11px;
      font-weight: 600;
      color: var(--d-gray-500);
      text-transform: uppercase;
      letter-spacing: 0.6px;
      margin-bottom: 2px;
    }
    .d-stat-value {
      font-size: 22px;
      font-weight: 800;
      color: var(--d-gray-900);
      line-height: 1.1;
      letter-spacing: -0.5px;
    }
    .d-stat-meta {
      font-size: 11px;
      color: var(--d-gray-400);
      margin-top: 2px;
    }

    /* ── TABS ── */
    .d-tabs-wrap {
      max-width: 1400px;
      margin: 32px auto 0;
      padding: 0 40px;
    }

    .d-tabs {
      display: flex;
      gap: 4px;
      background: #fff;
      padding: 6px;
      border-radius: 12px;
      box-shadow: var(--d-shadow);
      width: fit-content;
    }

    .d-tab {
      padding: 10px 20px;
      border: none;
      background: transparent;
      font-family: inherit;
      font-size: 13px;
      font-weight: 600;
      color: var(--d-gray-500);
      cursor: pointer;
      border-radius: 8px;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
      letter-spacing: 0.2px;
    }
    .d-tab:hover { color: var(--d-gray-900); background: var(--d-gray-50); }
    .d-tab.active {
      background: var(--d-gray-900);
      color: #fff;
      box-shadow: var(--d-shadow);
    }

    .d-tab-count {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 20px;
      height: 20px;
      padding: 0 6px;
      border-radius: 10px;
      background: var(--d-gray-200);
      color: var(--d-gray-700);
      font-size: 11px;
      font-weight: 700;
    }
    .d-tab.active .d-tab-count {
      background: rgba(255,255,255,0.2);
      color: #fff;
    }

    /* ── PANELS ── */
    .d-panel {
      max-width: 1400px;
      margin: 24px auto 0;
      padding: 0 40px 60px;
    }
    .d-panel[hidden] { display: none; }

    .d-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 18px;
    }

    /* ── ORDER CARD ── */
    .d-card {
      background: #fff;
      border-radius: 16px;
      padding: 0;
      box-shadow: var(--d-shadow);
      overflow: hidden;
      transition: transform 0.2s, box-shadow 0.2s;
      border: 1px solid var(--d-gray-200);
      position: relative;
    }
    .d-card:hover {
      transform: translateY(-2px);
      box-shadow: var(--d-shadow-lg);
    }

    .d-card-stripe {
      height: 4px;
      width: 100%;
    }
    .d-card.available .d-card-stripe { background: linear-gradient(90deg, #3B82F6, #60A5FA); }
    .d-card.active    .d-card-stripe { background: linear-gradient(90deg, #F59E0B, #FBBF24); }
    .d-card.done      .d-card-stripe { background: linear-gradient(90deg, #10B981, #34D399); }

    .d-card-body { padding: 18px 20px 16px; }

    .d-card-head {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 14px;
    }

    .d-order-num {
      font-family: 'Inter', monospace;
      font-size: 13px;
      font-weight: 800;
      color: var(--d-gray-900);
      letter-spacing: -0.5px;
    }

    .d-status {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }
    .d-status::before {
      content: '';
      width: 6px; height: 6px;
      border-radius: 50%;
      background: currentColor;
    }
    .d-status.blue   { background: #DBEAFE; color: #1D4ED8; }
    .d-status.amber  { background: #FEF3C7; color: #B45309; }
    .d-status.amber::before { animation: pulse-dot 1.5s infinite; }
    .d-status.green  { background: #D1FAE5; color: #047857; }

    @keyframes pulse-dot {
      0%, 100% { opacity: 1; transform: scale(1); }
      50%       { opacity: 0.4; transform: scale(1.4); }
    }

    /* ── CARD ROWS ── */
    .d-row {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      padding: 7px 0;
      font-size: 13px;
    }
    .d-row + .d-row { border-top: 1px dashed var(--d-gray-200); }
    .d-row-icon {
      width: 28px; height: 28px;
      flex-shrink: 0;
      border-radius: 8px;
      background: var(--d-gray-100);
      display: flex; align-items: center; justify-content: center;
      font-size: 13px;
    }
    .d-row-content { flex: 1; min-width: 0; }
    .d-row-label {
      font-size: 10px;
      font-weight: 600;
      color: var(--d-gray-400);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 1px;
    }
    .d-row-value {
      font-size: 13px;
      font-weight: 600;
      color: var(--d-gray-800);
      line-height: 1.4;
      word-break: break-word;
    }

    /* ── TOTAL ROW ── */
    .d-total {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin: 14px -20px 0;
      padding: 14px 20px;
      background: linear-gradient(135deg, var(--d-gray-50), #fff);
      border-top: 1px solid var(--d-gray-200);
    }
    .d-total-label {
      font-size: 11px;
      font-weight: 600;
      color: var(--d-gray-500);
      text-transform: uppercase;
      letter-spacing: 0.6px;
    }
    .d-total-value {
      font-size: 22px;
      font-weight: 800;
      color: var(--d-gray-900);
      letter-spacing: -0.5px;
    }

    .d-time {
      font-size: 11px;
      color: var(--d-gray-400);
      font-weight: 500;
      margin-top: 4px;
    }

    /* ── ITEMS DISCLOSURE ── */
    .d-items-toggle {
      width: 100%;
      margin-top: 12px;
      padding: 9px 12px;
      background: var(--d-gray-50);
      border: 1px solid var(--d-gray-200);
      border-radius: 9px;
      font-family: inherit;
      font-size: 12px;
      font-weight: 600;
      color: var(--d-gray-600);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: all 0.15s;
    }
    .d-items-toggle:hover {
      background: var(--d-gray-100);
      color: var(--d-gray-900);
    }
    .d-items-chevron {
      transition: transform 0.2s;
      font-size: 11px;
    }
    .d-items-toggle.open .d-items-chevron { transform: rotate(180deg); }

    .d-items-list {
      display: none;
      margin-top: 10px;
      background: var(--d-gray-50);
      border-radius: 10px;
      padding: 10px 12px;
      max-height: 220px;
      overflow-y: auto;
    }
    .d-items-list.open { display: block; }

    .d-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 6px 0;
      font-size: 12px;
      gap: 10px;
    }
    .d-item + .d-item { border-top: 1px solid var(--d-gray-200); }
    .d-item-name {
      font-weight: 600;
      color: var(--d-gray-800);
      flex: 1;
      min-width: 0;
    }
    .d-item-meta {
      font-size: 10px;
      color: var(--d-gray-500);
      font-weight: 500;
    }
    .d-item-qty {
      background: var(--d-gray-900);
      color: #fff;
      padding: 2px 8px;
      border-radius: 10px;
      font-size: 10px;
      font-weight: 700;
      flex-shrink: 0;
    }
    .d-item-price {
      font-weight: 700;
      color: var(--d-gray-900);
      font-size: 12px;
      flex-shrink: 0;
    }

    /* ── ACTION BUTTONS ── */
    .d-action {
      width: 100%;
      margin-top: 14px;
      padding: 12px 16px;
      border: none;
      border-radius: 10px;
      font-family: inherit;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.3px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.15s;
      box-shadow: var(--d-shadow-sm);
    }
    .d-action.accept {
      background: var(--d-primary);
      color: #fff;
    }
    .d-action.accept:hover {
      background: var(--d-primary-dark);
      transform: translateY(-1px);
      box-shadow: 0 8px 20px rgba(37,99,235,0.35);
    }
    .d-action.deliver {
      background: var(--d-success);
      color: #fff;
    }
    .d-action.deliver:hover {
      background: var(--d-success-dark);
      transform: translateY(-1px);
      box-shadow: 0 8px 20px rgba(16,185,129,0.35);
    }
    .d-action:active { transform: translateY(0); }

    /* ── EMPTY STATE ── */
    .d-empty {
      grid-column: 1 / -1;
      text-align: center;
      padding: 60px 20px;
      background: #fff;
      border-radius: 16px;
      border: 2px dashed var(--d-gray-200);
    }
    .d-empty-icon {
      font-size: 48px;
      margin-bottom: 14px;
      opacity: 0.4;
    }
    .d-empty-title {
      font-size: 15px;
      font-weight: 700;
      color: var(--d-gray-700);
      margin-bottom: 4px;
    }
    .d-empty-msg {
      font-size: 13px;
      color: var(--d-gray-500);
    }

    /* ── CONFIRM MODAL ── */
    #driverConfirmModal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(15,23,42,0.55);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(8px);
    }
    #driverConfirmModal.open { display: flex; animation: fadeIn 0.2s ease; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    .d-confirm-box {
      background: #fff;
      border-radius: 20px;
      padding: 32px;
      max-width: 420px;
      width: 90%;
      text-align: center;
      box-shadow: var(--d-shadow-xl);
      animation: popIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes popIn {
      from { transform: scale(0.85); opacity: 0; }
      to   { transform: scale(1);    opacity: 1; }
    }

    .d-confirm-icon {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      margin: 0 auto 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 32px;
    }
    .d-confirm-icon.blue  { background: #DBEAFE; }
    .d-confirm-icon.green { background: #D1FAE5; }

    .d-confirm-title {
      font-size: 18px;
      font-weight: 800;
      color: var(--d-gray-900);
      margin-bottom: 8px;
      letter-spacing: -0.3px;
    }
    .d-confirm-msg {
      font-size: 14px;
      color: var(--d-gray-600);
      line-height: 1.5;
      margin-bottom: 24px;
    }

    .d-confirm-actions {
      display: flex;
      gap: 10px;
    }

    .d-confirm-btn {
      flex: 1;
      padding: 12px 18px;
      border: none;
      border-radius: 10px;
      font-family: inherit;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.15s;
    }
    .d-confirm-btn:hover { transform: translateY(-1px); }
    .d-btn-cancel {
      background: var(--d-gray-100);
      color: var(--d-gray-700);
    }
    .d-btn-cancel:hover { background: var(--d-gray-200); }
    .d-btn-blue  { background: var(--d-primary); color: #fff; }
    .d-btn-blue:hover  { background: var(--d-primary-dark); box-shadow: 0 6px 16px rgba(37,99,235,0.4); }
    .d-btn-green { background: var(--d-success); color: #fff; }
    .d-btn-green:hover { background: var(--d-success-dark); box-shadow: 0 6px 16px rgba(16,185,129,0.4); }

    /* ── TOASTS ── */
    #toast-container {
      position: fixed;
      top: 24px;
      right: 24px;
      z-index: 99999;
      display: flex;
      flex-direction: column;
      gap: 10px;
      pointer-events: none;
    }
    .toast {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      min-width: 320px;
      max-width: 400px;
      padding: 14px 16px;
      border-radius: 12px;
      background: #fff;
      box-shadow: var(--d-shadow-xl);
      font-size: 13px;
      pointer-events: all;
      animation: toastIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
      position: relative;
      overflow: hidden;
      border-left: 4px solid var(--d-gray-300);
    }
    @keyframes toastIn {
      from { transform: translateX(120%) scale(0.9); opacity: 0; }
      to   { transform: translateX(0) scale(1);     opacity: 1; }
    }
    @keyframes toastOut {
      from { transform: translateX(0);    opacity: 1; }
      to   { transform: translateX(120%); opacity: 0; }
    }
    .toast.removing { animation: toastOut 0.3s ease forwards; }
    .toast-icon {
      width: 32px; height: 32px;
      flex-shrink: 0;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
    }
    .toast-body { flex: 1; min-width: 0; }
    .toast-title {
      font-weight: 700;
      font-size: 13px;
      margin-bottom: 2px;
      color: var(--d-gray-900);
    }
    .toast-msg {
      font-size: 12px;
      color: var(--d-gray-600);
      line-height: 1.4;
    }
    .toast-close {
      background: none; border: none;
      cursor: pointer;
      font-size: 18px;
      opacity: 0.4;
      padding: 0; line-height: 1;
      flex-shrink: 0;
      color: var(--d-gray-500);
    }
    .toast-close:hover { opacity: 1; }
    .toast-progress {
      position: absolute;
      bottom: 0; left: 0;
      height: 2px;
      animation: toastProgress linear forwards;
    }
    @keyframes toastProgress { from { width: 100%; } to { width: 0%; } }

    .toast-error   { border-left-color: var(--d-danger); }
    .toast-error .toast-icon { background: #FEE2E2; color: var(--d-danger); }
    .toast-error .toast-progress { background: var(--d-danger); }
    .toast-warning { border-left-color: var(--d-warning); }
    .toast-warning .toast-icon { background: #FEF3C7; color: var(--d-warning); }
    .toast-warning .toast-progress { background: var(--d-warning); }
    .toast-success { border-left-color: var(--d-success); }
    .toast-success .toast-icon { background: #D1FAE5; color: var(--d-success); }
    .toast-success .toast-progress { background: var(--d-success); }
    .toast-info    { border-left-color: var(--d-primary); }
    .toast-info .toast-icon { background: #DBEAFE; color: var(--d-primary); }
    .toast-info .toast-progress { background: var(--d-primary); }

    /* Responsive */
    @media (max-width: 900px) {
      .d-stats { grid-template-columns: repeat(2, 1fr); }
      .d-hero { padding: 28px 20px 80px; }
      .d-stats, .d-tabs-wrap, .d-panel { padding-left: 20px; padding-right: 20px; }
    }
    @media (max-width: 540px) {
      .d-stats { grid-template-columns: 1fr; }
      .d-tabs { width: 100%; }
      .d-tab { flex: 1; justify-content: center; padding: 10px 12px; font-size: 12px; }
    }

  </style>
</head>

<body class="driver-page">

<!-- NAVBAR -->
<header class="navbar">
  <img src="logo/Alberto's Pizza.png" class="logo-img" alt="Alberto's Pizza Logo">
  <div class="nav-links">
    <a href="driver.php">DASHBOARD</a>
    <a href="logout.php">LOG OUT</a>
  </div>
</header>

<!-- ══ HERO ══ -->
<section class="d-hero">
  <div class="d-hero-inner">
    <div class="d-hero-greeting">
      <?php
        $hour = (int)date('H');
        $greet = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        echo $greet . ',';
      ?>
    </div>
    <h1>👋 <?= $driver_name ?></h1>
    <p class="d-hero-sub">Here's what's on your delivery queue today.</p>
  </div>
</section>

<!-- ══ STATS ══ -->
<section class="d-stats">
  <div class="d-stat-card">
    <div class="d-stat-icon blue">📦</div>
    <div class="d-stat-info">
      <div class="d-stat-label">Available</div>
      <div class="d-stat-value"><?= count($availableRows) ?></div>
      <div class="d-stat-meta">Ready for pickup</div>
    </div>
  </div>
  <div class="d-stat-card">
    <div class="d-stat-icon amber">🛵</div>
    <div class="d-stat-info">
      <div class="d-stat-label">Active</div>
      <div class="d-stat-value"><?= count($activeRows) ?></div>
      <div class="d-stat-meta">Out for delivery</div>
    </div>
  </div>
  <div class="d-stat-card">
    <div class="d-stat-icon green">✅</div>
    <div class="d-stat-info">
      <div class="d-stat-label">Today</div>
      <div class="d-stat-value"><?= (int)$todayStats['today_count'] ?></div>
      <div class="d-stat-meta">Deliveries completed</div>
    </div>
  </div>
  <div class="d-stat-card">
    <div class="d-stat-icon purple">💰</div>
    <div class="d-stat-info">
      <div class="d-stat-label">Today's Volume</div>
      <div class="d-stat-value">₱<?= number_format((float)$todayStats['today_total'], 0) ?></div>
      <div class="d-stat-meta">Total delivered</div>
    </div>
  </div>
</section>

<!-- ══ TABS ══ -->
<div class="d-tabs-wrap">
  <div class="d-tabs" role="tablist">
    <button class="d-tab active" data-tab="available">
      📦 Available
      <span class="d-tab-count"><?= count($availableRows) ?></span>
    </button>
    <button class="d-tab" data-tab="active">
      🛵 My Deliveries
      <span class="d-tab-count"><?= count($activeRows) ?></span>
    </button>
    <button class="d-tab" data-tab="done">
      ✅ History
      <span class="d-tab-count"><?= count($doneRows) ?></span>
    </button>
  </div>
</div>

<!-- ══ PANEL: AVAILABLE ══ -->
<section class="d-panel" id="panel-available">
  <div class="d-grid">
    <?php if (empty($availableRows)): ?>
      <div class="d-empty">
        <div class="d-empty-icon">📭</div>
        <div class="d-empty-title">No deliveries available</div>
        <div class="d-empty-msg">Check back in a moment — new orders come in regularly.</div>
      </div>
    <?php else: foreach ($availableRows as $order):
      $oid = intval($order['order_id']);
      $items = getOrderItems($conn, $oid);
    ?>
      <article class="d-card available">
        <div class="d-card-stripe"></div>
        <div class="d-card-body">
          <div class="d-card-head">
            <div>
              <div class="d-order-num">Order #<?= $order['order_id'] ?></div>
              <div class="d-time"><?= date('M d • g:i A', strtotime($order['created_at'])) ?></div>
            </div>
            <span class="d-status blue">Ready</span>
          </div>

          <div class="d-row">
            <div class="d-row-icon">👤</div>
            <div class="d-row-content">
              <div class="d-row-label">Customer</div>
              <div class="d-row-value"><?= htmlspecialchars($order['customer_name']) ?></div>
            </div>
          </div>
          <div class="d-row">
            <div class="d-row-icon">📱</div>
            <div class="d-row-content">
              <div class="d-row-label">Mobile</div>
              <div class="d-row-value"><?= htmlspecialchars($order['mobile_number']) ?></div>
            </div>
          </div>
          <div class="d-row">
            <div class="d-row-icon">🏪</div>
            <div class="d-row-content">
              <div class="d-row-label">Branch</div>
              <div class="d-row-value"><?= htmlspecialchars($order['branch_name'] . ' • ' . $order['branch_location']) ?></div>
            </div>
          </div>
          <div class="d-row">
            <div class="d-row-icon">📍</div>
            <div class="d-row-content">
              <div class="d-row-label">Delivery Address</div>
              <div class="d-row-value"><?= htmlspecialchars($order['address'] ?: '—') ?></div>
            </div>
          </div>
          <div class="d-row">
            <div class="d-row-icon">💳</div>
            <div class="d-row-content">
              <div class="d-row-label">Payment</div>
              <div class="d-row-value"><?= htmlspecialchars($order['payment_method']) ?></div>
            </div>
          </div>

          <button class="d-items-toggle" onclick="toggleItems(this)">
            <span>📋 View Items</span>
            <span class="d-items-chevron">▼</span>
          </button>
          <div class="d-items-list">
            <?php while ($item = $items->fetch_assoc()): ?>
              <div class="d-item">
                <div>
                  <div class="d-item-name"><?= htmlspecialchars($item['pizza_name']) ?></div>
                  <div class="d-item-meta"><?= $item['size'] ?>" • <?= htmlspecialchars($item['cheese']) ?></div>
                </div>
                <span class="d-item-qty">×<?= $item['quantity'] ?></span>
                <span class="d-item-price">₱<?= number_format($item['total'], 2) ?></span>
              </div>
            <?php endwhile; ?>
          </div>

          <div class="d-total">
            <span class="d-total-label">Order Total</span>
            <span class="d-total-value">₱<?= number_format($order['total_amount'], 2) ?></span>
          </div>

          <button class="d-action accept" onclick="confirmAccept(<?= $order['order_id'] ?>, '<?= htmlspecialchars(addslashes($order['customer_name'])) ?>')">
            🚀 Accept Delivery
          </button>
        </div>
      </article>
    <?php endforeach; endif; ?>
  </div>
</section>

<!-- ══ PANEL: ACTIVE ══ -->
<section class="d-panel" id="panel-active" hidden>
  <div class="d-grid">
    <?php if (empty($activeRows)): ?>
      <div class="d-empty">
        <div class="d-empty-icon">🛵</div>
        <div class="d-empty-title">No active deliveries</div>
        <div class="d-empty-msg">Accept an available order to start delivering.</div>
      </div>
    <?php else: foreach ($activeRows as $order):
      $oid = intval($order['order_id']);
      $items = getOrderItems($conn, $oid);
    ?>
      <article class="d-card active">
        <div class="d-card-stripe"></div>
        <div class="d-card-body">
          <div class="d-card-head">
            <div>
              <div class="d-order-num">Order #<?= $order['order_id'] ?></div>
              <div class="d-time"><?= date('M d • g:i A', strtotime($order['created_at'])) ?></div>
            </div>
            <span class="d-status amber">In Transit</span>
          </div>

          <div class="d-row">
            <div class="d-row-icon">👤</div>
            <div class="d-row-content">
              <div class="d-row-label">Customer</div>
              <div class="d-row-value"><?= htmlspecialchars($order['customer_name']) ?></div>
            </div>
          </div>
          <div class="d-row">
            <div class="d-row-icon">📱</div>
            <div class="d-row-content">
              <div class="d-row-label">Mobile</div>
              <div class="d-row-value"><?= htmlspecialchars($order['mobile_number']) ?></div>
            </div>
          </div>
          <div class="d-row">
            <div class="d-row-icon">🏪</div>
            <div class="d-row-content">
              <div class="d-row-label">Branch</div>
              <div class="d-row-value"><?= htmlspecialchars($order['branch_name'] . ' • ' . $order['branch_location']) ?></div>
            </div>
          </div>
          <div class="d-row">
            <div class="d-row-icon">📍</div>
            <div class="d-row-content">
              <div class="d-row-label">Delivery Address</div>
              <div class="d-row-value"><?= htmlspecialchars($order['address'] ?: '—') ?></div>
            </div>
          </div>
          <div class="d-row">
            <div class="d-row-icon">💳</div>
            <div class="d-row-content">
              <div class="d-row-label">Payment</div>
              <div class="d-row-value"><?= htmlspecialchars($order['payment_method']) ?></div>
            </div>
          </div>

          <button class="d-items-toggle" onclick="toggleItems(this)">
            <span>📋 View Items</span>
            <span class="d-items-chevron">▼</span>
          </button>
          <div class="d-items-list">
            <?php while ($item = $items->fetch_assoc()): ?>
              <div class="d-item">
                <div>
                  <div class="d-item-name"><?= htmlspecialchars($item['pizza_name']) ?></div>
                  <div class="d-item-meta"><?= $item['size'] ?>" • <?= htmlspecialchars($item['cheese']) ?></div>
                </div>
                <span class="d-item-qty">×<?= $item['quantity'] ?></span>
                <span class="d-item-price">₱<?= number_format($item['total'], 2) ?></span>
              </div>
            <?php endwhile; ?>
          </div>

          <div class="d-total">
            <span class="d-total-label">Order Total</span>
            <span class="d-total-value">₱<?= number_format($order['total_amount'], 2) ?></span>
          </div>

          <button class="d-action deliver" onclick="confirmDeliver(<?= $order['order_id'] ?>, '<?= htmlspecialchars(addslashes($order['customer_name'])) ?>')">
            ✅ Mark as Delivered
          </button>
        </div>
      </article>
    <?php endforeach; endif; ?>
  </div>
</section>

<!-- ══ PANEL: HISTORY ══ -->
<section class="d-panel" id="panel-done" hidden>
  <div class="d-grid">
    <?php if (empty($doneRows)): ?>
      <div class="d-empty">
        <div class="d-empty-icon">📜</div>
        <div class="d-empty-title">No delivery history yet</div>
        <div class="d-empty-msg">Your completed deliveries will appear here.</div>
      </div>
    <?php else: foreach ($doneRows as $order):
      $oid = intval($order['order_id']);
      $items = getOrderItems($conn, $oid);
    ?>
      <article class="d-card done">
        <div class="d-card-stripe"></div>
        <div class="d-card-body">
          <div class="d-card-head">
            <div>
              <div class="d-order-num">Order #<?= $order['order_id'] ?></div>
              <div class="d-time">
                Delivered <?= $order['updated_at'] ? date('M d • g:i A', strtotime($order['updated_at'])) : date('M d • g:i A', strtotime($order['created_at'])) ?>
              </div>
            </div>
            <span class="d-status green">Delivered</span>
          </div>

          <div class="d-row">
            <div class="d-row-icon">👤</div>
            <div class="d-row-content">
              <div class="d-row-label">Customer</div>
              <div class="d-row-value"><?= htmlspecialchars($order['customer_name']) ?></div>
            </div>
          </div>
          <div class="d-row">
            <div class="d-row-icon">📍</div>
            <div class="d-row-content">
              <div class="d-row-label">Delivered To</div>
              <div class="d-row-value"><?= htmlspecialchars($order['address'] ?: '—') ?></div>
            </div>
          </div>

          <button class="d-items-toggle" onclick="toggleItems(this)">
            <span>📋 View Items</span>
            <span class="d-items-chevron">▼</span>
          </button>
          <div class="d-items-list">
            <?php while ($item = $items->fetch_assoc()): ?>
              <div class="d-item">
                <div>
                  <div class="d-item-name"><?= htmlspecialchars($item['pizza_name']) ?></div>
                  <div class="d-item-meta"><?= $item['size'] ?>" • <?= htmlspecialchars($item['cheese']) ?></div>
                </div>
                <span class="d-item-qty">×<?= $item['quantity'] ?></span>
              </div>
            <?php endwhile; ?>
          </div>

          <div class="d-total">
            <span class="d-total-label">Order Total</span>
            <span class="d-total-value">₱<?= number_format($order['total_amount'], 2) ?></span>
          </div>
        </div>
      </article>
    <?php endforeach; endif; ?>
  </div>
</section>

<!-- ══ CONFIRM MODAL ══ -->
<div id="driverConfirmModal">
  <div class="d-confirm-box">
    <div class="d-confirm-icon" id="confirmIconWrap">
      <span id="confirmIcon">🚀</span>
    </div>
    <div class="d-confirm-title" id="confirmTitle">Confirm Action</div>
    <div class="d-confirm-msg" id="confirmMessage">Are you sure?</div>
    <div class="d-confirm-actions">
      <button class="d-confirm-btn d-btn-cancel" onclick="closeConfirm()">Cancel</button>
      <button class="d-confirm-btn d-btn-blue" id="confirmYesBtn">Confirm</button>
    </div>
  </div>
</div>

<!-- TOAST CONTAINER -->
<div id="toast-container"></div>

<script>
// ══ TOAST SYSTEM ══
function showToast(type, title, msg, duration = 4000) {
  const icons = { error: '✕', warning: '!', success: '✓', info: 'i' };
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `
    <div class="toast-icon">${icons[type] || 'i'}</div>
    <div class="toast-body">
      <div class="toast-title">${title}</div>
      ${msg ? `<div class="toast-msg">${msg}</div>` : ''}
    </div>
    <button class="toast-close" onclick="dismissToast(this.parentElement)">&times;</button>
    <div class="toast-progress" style="animation-duration:${duration}ms"></div>
  `;
  container.appendChild(toast);
  toast._timer = setTimeout(() => dismissToast(toast), duration);
}

function dismissToast(toast) {
  if (!toast || toast._removing) return;
  toast._removing = true;
  clearTimeout(toast._timer);
  toast.classList.add('removing');
  toast.addEventListener('animationend', () => toast.remove(), { once: true });
}

// ══ TABS ══
document.querySelectorAll('.d-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.d-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    const target = tab.dataset.tab;
    document.querySelectorAll('.d-panel').forEach(p => p.hidden = true);
    document.getElementById('panel-' + target).hidden = false;
  });
});

// ══ TOGGLE ITEMS ══
function toggleItems(btn) {
  const list = btn.nextElementSibling;
  const open = list.classList.toggle('open');
  btn.classList.toggle('open', open);
  btn.querySelector('span:first-child').textContent = open ? '📋 Hide Items' : '📋 View Items';
}

// ══ CONFIRM MODAL ══
function openConfirm(icon, iconClass, title, message, btnClass, btnLabel, action) {
  document.getElementById('confirmIcon').textContent      = icon;
  document.getElementById('confirmIconWrap').className    = 'd-confirm-icon ' + iconClass;
  document.getElementById('confirmTitle').textContent     = title;
  document.getElementById('confirmMessage').textContent   = message;
  const yesBtn = document.getElementById('confirmYesBtn');
  yesBtn.className = 'd-confirm-btn ' + btnClass;
  yesBtn.textContent = btnLabel;
  yesBtn.onclick = action;
  document.getElementById('driverConfirmModal').classList.add('open');
}
function closeConfirm() {
  document.getElementById('driverConfirmModal').classList.remove('open');
}
document.getElementById('driverConfirmModal').addEventListener('click', e => {
  if (e.target === e.currentTarget) closeConfirm();
});

// ══ ACCEPT DELIVERY ══
function confirmAccept(orderId, customerName) {
  openConfirm(
    '🚀', 'blue',
    'Accept this delivery?',
    `Order #${orderId} for ${customerName} will move into your active deliveries.`,
    'd-btn-blue',
    'Yes, Accept',
    () => doAccept(orderId)
  );
}

function doAccept(orderId) {
  closeConfirm();
  fetch('accept_delivery.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `order_id=${orderId}`
  })
  .then(res => res.text())
  .then(data => {
    const r = data.trim();
    if (r === 'success') {
      showToast('success', 'Delivery Accepted', `Order #${orderId} is now in your queue.`);
      setTimeout(() => location.reload(), 1500);
    } else if (r === 'already_taken') {
      showToast('warning', 'Already Taken', 'Another driver beat you to it. Refreshing list...');
      setTimeout(() => location.reload(), 1800);
    } else {
      showToast('error', 'Accept Failed', `Server response: ${r}`);
    }
  })
  .catch(() => showToast('error', 'Network Error', 'Could not reach the server.'));
}

// ══ COMPLETE DELIVERY ══
function confirmDeliver(orderId, customerName) {
  openConfirm(
    '✅', 'green',
    'Mark as delivered?',
    `Confirm that order #${orderId} for ${customerName} was delivered successfully.`,
    'd-btn-green',
    'Yes, Delivered',
    () => doDeliver(orderId)
  );
}

function doDeliver(orderId) {
  closeConfirm();
  fetch('complete_delivery.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `order_id=${orderId}`
  })
  .then(res => res.text())
  .then(data => {
    const r = data.trim();
    if (r === 'success') {
      showToast('success', 'Delivered!', `Order #${orderId} marked as delivered.`);
      setTimeout(() => location.reload(), 1500);
    } else {
      showToast('error', 'Update Failed', `Server response: ${r}`);
    }
  })
  .catch(() => showToast('error', 'Network Error', 'Could not reach the server.'));
}
</script>

</body>
</html>
