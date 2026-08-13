<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title><?= esc($title ?? 'MTCE') ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
<!-- Google Fonts (Inter) -->
<link href="<?= base_url('assets/css/google-fonts.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/css/tom-select.bootstrap5.min.css') ?>" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">
<style>
    /* ============================================================
       GLOBAL DESIGN SYSTEM - MTCE (Modern Minimalist)
       Font: Inter | Palette: Slate + Indigo accent
    ============================================================ */

    :root {
        --sidebar-w: 240px;
        --sidebar-bg: #111827; /* Carbon Black */
        --sidebar-item: #94a3b8;
        --sidebar-active: #ffffff;
        --sidebar-active-bg: rgba(2, 132, 199, 0.2); /* Electric Blue tint */
        --sidebar-border: rgba(255,255,255,0.05);
        --accent: #0284c7; /* Electric Blue */
        --accent-hover: #0369a1;
        --accent-light: #e0f2fe;
        --body-bg: #f8fafc; /* Clean Light Steel */
        --white: #ffffff;
        --border: #e2e8f0;
        --border-strong: #cbd5e1;
        --text-primary: #0f172a; /* Gunmetal */
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        --success: #16a34a; 
        --success-bg: #dcfce7;
        --warning: #ea580c; /* Safety Orange */
        --warning-bg: #ffedd5;
        --danger: #dc2626; 
        --danger-bg: #fee2e2;
        --radius-sm: 2px;
        --radius: 4px; /* Sharp edges for industrial precision */
        --radius-lg: 6px;
        --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.05);
        --shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.1), 0 2px 4px -1px rgba(15, 23, 42, 0.06);
    }

    *, *::before, *::after { box-sizing: border-box; }

    html { font-size: 14px; }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: var(--body-bg);
        color: var(--text-primary);
        overflow-x: hidden;
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
    }

    /* Fix TomSelect shrinking in table cells */
    th .ts-wrapper {
        min-width: 110px;
        width: 100% !important;
    }

    /* ---- LAYOUT ---- */
    .app-container { display: flex; min-height: 100vh; width: 100%; overflow-x: hidden; }

    /* ---- SIDEBAR ---- */
    .sidebar {
        width: var(--sidebar-w);
        background: var(--sidebar-bg);
        position: fixed;
        top: 0; left: 0; bottom: 0;
        z-index: 200;
        display: flex;
        flex-direction: column;
        border-right: 1px solid var(--sidebar-border);
        transition: left 0.25s ease;
    }
    .sidebar-brand {
        height: 64px;
        display: flex;
        align-items: center;
        padding: 0 1.25rem;
        font-weight: 700;
        font-size: 1.05rem;
        letter-spacing: -0.01em;
        color: #ffffff;
        text-decoration: none;
        border-bottom: 1px solid var(--sidebar-border);
        gap: 0.6rem;
    }
    .sidebar-brand .brand-icon {
        width: 32px; height: 32px;
        background: var(--accent);
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }
    .sidebar-menu { flex: 1; padding: 1rem 0; overflow-y: auto; }
    .menu-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: var(--text-muted);
        padding: 1.25rem 1.25rem 0.4rem;
        display: block;
    }
    .menu-item {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.62rem 1.25rem;
        color: var(--sidebar-item);
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 0;
        border-left: 3px solid transparent;
        transition: color 0.15s, background 0.15s;
        margin: 1px 0;
    }
    .menu-item i { font-size: 1rem; flex-shrink: 0; }
    .menu-item:hover {
        color: var(--sidebar-active);
        background: var(--sidebar-active-bg);
    }
    .menu-item.active {
        color: var(--sidebar-active);
        background: var(--sidebar-active-bg);
        border-left-color: var(--accent);
    }
    .sidebar-footer {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--sidebar-border);
        display: flex; align-items: center; gap: 0.75rem;
    }
    .sidebar-footer .user-avatar {
        width: 34px; height: 34px;
        border-radius: 50%;
        background: var(--accent);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.8rem; font-weight: 700; color: #fff; flex-shrink: 0;
    }
    .sidebar-footer .user-name {
        font-size: 0.825rem; font-weight: 600; color: #e5e7eb;
        line-height: 1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .sidebar-footer .user-role {
        font-size: 0.68rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.08em;
        color: var(--text-muted);
    }

    /* ---- MAIN WRAPPER ---- */
    .main-wrapper {
        margin-left: var(--sidebar-w);
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    /* ---- TOPBAR ---- */
    .topbar {
        height: 64px;
        background: var(--white);
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 1.75rem;
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: var(--shadow-sm);
    }
    .topbar-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
        letter-spacing: -0.01em;
    }

    /* ---- CONTENT ---- */
    .content-area { padding: 1.75rem; flex: 1; min-width: 0; }

    /* ---- PAGE HEADER utility ---- */
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .page-header h1, .page-header h5 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
        letter-spacing: -0.01em;
    }

    /* ---- CARDS ---- */
    .card, .card-stat {
        background: var(--white) !important;
        border: 1px solid var(--border) !important;
        border-radius: var(--radius) !important;
        box-shadow: var(--shadow-sm) !important;
        transition: box-shadow 0.2s;
    }
    .card-stat { padding: 1.25rem !important; }
    .card-hover:hover {
        box-shadow: var(--shadow) !important;
        border-color: var(--border-strong) !important;
    }
    .card-stat .label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--text-secondary);
    }
    .card-stat .value {
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.1;
        letter-spacing: -0.02em;
    }

    /* ---- DASHBOARD PREMIUM STYLES ---- */
    .dashboard-header {
        background: linear-gradient(135deg, #0f172a 0%, #0284c7 100%);
        border-radius: var(--radius-lg);
        padding: 2rem;
        color: white;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 25px -5px rgba(13, 148, 136, 0.3);
    }
    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        border-radius: 50%;
    }
    .card-stat-premium {
        position: relative;
        overflow: hidden;
        border-radius: var(--radius-lg) !important;
        border: none !important;
        background: var(--white);
        box-shadow: 0 4px 15px -3px rgba(0,0,0,0.05) !important;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1;
    }
    .card-stat-premium:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px -5px rgba(0,0,0,0.1) !important;
    }
    .card-stat-premium::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(120deg, rgba(255,255,255,0.4) 0%, transparent 50%);
        z-index: -1;
        pointer-events: none;
    }
    .card-stat-premium .watermark-icon {
        position: absolute;
        bottom: -15px;
        right: -10px;
        font-size: 5rem;
        opacity: 0.07;
        z-index: -1;
        transform: rotate(-10deg);
        transition: transform 0.3s ease;
    }
    .card-stat-premium:hover .watermark-icon {
        transform: rotate(0deg) scale(1.1);
    }
    .grad-indigo { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white !important; border-left: 4px solid #0284c7; }
    .grad-emerald { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white !important; border-left: 4px solid #16a34a; }
    .grad-amber { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white !important; border-left: 4px solid #ea580c; }
    .grad-rose { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white !important; border-left: 4px solid #dc2626; }
    .grad-cyan { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white !important; border-left: 4px solid #06b6d4; }
    
    .card-stat-premium.grad-indigo .watermark-icon,
    .card-stat-premium.grad-emerald .watermark-icon,
    .card-stat-premium.grad-amber .watermark-icon,
    .card-stat-premium.grad-rose .watermark-icon,
    .card-stat-premium.grad-cyan .watermark-icon {
        opacity: 0.15;
    }

    /* ---- BUTTONS ---- */
    .btn {
        border-radius: var(--radius-sm);
        font-weight: 600;
        font-size: 0.8rem;
        letter-spacing: 0.01em;
        padding: 0.5rem 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        transition: all 0.15s;
        border: 1.5px solid transparent;
        line-height: 1.4;
    }
    .btn-sm { padding: 0.38rem 0.8rem; font-size: 0.775rem; }
    .btn-lg { padding: 0.65rem 1.5rem; font-size: 0.875rem; }

    .btn-primary {
        background: var(--accent);
        border-color: var(--accent);
        color: #fff;
    }
    .btn-primary:hover, .btn-primary:focus {
        background: var(--accent-hover);
        border-color: var(--accent-hover);
        color: #fff;
        box-shadow: 0 0 0 3px rgba(79,70,229,0.2);
    }
    .btn-success {
        background: var(--success);
        border-color: var(--success);
        color: #fff;
    }
    .btn-success:hover {
        background: #047857;
        border-color: #047857;
        color: #fff;
        box-shadow: 0 0 0 3px rgba(5,150,105,0.2);
    }
    .btn-danger {
        background: var(--danger);
        border-color: var(--danger);
        color: #fff;
    }
    .btn-danger:hover { background: #b91c1c; border-color: #b91c1c; color: #fff; }

    .btn-outline-secondary {
        background: transparent;
        border-color: var(--border-strong);
        color: var(--text-secondary);
    }
    .btn-outline-secondary:hover {
        background: #f9fafb;
        border-color: #9ca3af;
        color: var(--text-primary);
    }
    .btn-outline-primary {
        background: transparent;
        border-color: var(--accent);
        color: var(--accent);
    }
    .btn-outline-primary:hover {
        background: var(--accent);
        color: #fff;
        box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
    }
    .btn-outline-danger {
        background: transparent;
        border-color: var(--danger);
        color: var(--danger);
    }
    .btn-outline-danger:hover { background: var(--danger); color: #fff; }

    /* ---- BADGES ---- */
    .badge {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.3em 0.7em;
        border-radius: 100px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .badge.bg-success { background: var(--success-bg) !important; color: var(--success) !important; }
    .badge.bg-warning { background: var(--warning-bg) !important; color: var(--warning) !important; }
    .badge.bg-danger  { background: var(--danger-bg)  !important; color: var(--danger)  !important; }
    .badge.bg-secondary { background: #f3f4f6 !important; color: #6b7280 !important; }
    .badge.bg-primary { background: var(--accent-light) !important; color: var(--accent) !important; }

    /* ---- TABLES ---- */
    .table-responsive {
        border-radius: var(--radius);
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        border: 1px solid var(--border-strong) !important;
        box-shadow: var(--shadow-sm);
        background: var(--white);
    }
    .table {
        margin-bottom: 0;
        width: 100%;
        border-collapse: collapse !important;
        font-family: 'Inter', sans-serif !important;
    }
    .table th, .table thead th, .table-light th, .table tr.table-light th, .kontrol-table th {
        font-family: 'Roboto Mono', monospace;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #e2e8f0 !important;
        background: #0f172a !important;
        padding: 0.9rem 1rem !important;
        border: 1px solid #334155 !important;
        border-bottom: 2px solid #0284c7 !important;
        white-space: nowrap;
        vertical-align: middle !important;
    }
    .table th a, .table thead th a, .table-light th a, .kontrol-table th a {
        color: #ffffff !important;
        font-size: 0.82rem !important;
        font-weight: 700 !important;
        text-decoration: none !important;
    }
    .table th a:hover, .table thead th a:hover {
        color: #0284c7 !important;
    }
    .table td, .kontrol-table td {
        padding: 0.85rem 1rem !important;
        border: 1px solid #e7e5e4 !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        color: var(--text-primary) !important;
        vertical-align: middle !important;
        background-color: #ffffff;
    }
    .table tbody tr:nth-child(even) td { background-color: #faf9f6 !important; }
    .table tbody tr:hover td, .kontrol-table tbody tr:hover td { background-color: #f0fdf4 !important; transition: background-color 0.15s ease; }

    /* Checklist specific table */
    .checklist-table { border: none !important; width: 100%; font-family: 'Inter', sans-serif !important; }
    .checklist-table th {
        background: #0f172a !important;
        font-family: 'Roboto Mono', monospace;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        color: #e2e8f0 !important;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 0.9rem 1rem !important;
        border: 1px solid #334155 !important;
        border-bottom: 2px solid #0284c7 !important;
    }
    .checklist-table td {
        padding: 0.85rem 1rem !important;
        border: 1px solid #e7e5e4 !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        vertical-align: middle !important;
        color: var(--text-primary) !important;
        background-color: #ffffff;
    }
    .checklist-table tbody tr:nth-child(even) td { background-color: #faf9f6 !important; }
    .checklist-table td.bagian-cell {
        font-weight: 700 !important;
        color: var(--text-primary) !important;
        background: #f5f5f4 !important;
        border-right: 2px solid var(--border-strong) !important;
        font-size: 0.875rem !important;
    }
    .checklist-table tr.section-header td {
        background: #ffffff !important;
        color: #000000 !important;
        font-family: 'Roboto Mono', monospace;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        text-align: center !important;
        padding: 0.7rem 1rem !important;
        border: 1px solid var(--border-light) !important;
    }

    /* ---- FORMS ---- */
    .form-control, .form-select {
        border-radius: var(--radius-sm);
        border: 1.5px solid var(--border);
        padding: 0.55rem 0.85rem;
        font-size: 0.875rem;
        color: var(--text-primary);
        background: var(--white);
        transition: border-color 0.15s, box-shadow 0.15s;
        line-height: 1.5;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
        color: var(--text-primary);
        outline: none;
    }
    .form-control:read-only {
        background: #f9fafb;
        color: var(--text-secondary);
        border-color: var(--border);
    }
    .form-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.35rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* ---- RADIO BUTTONS (Checklist V/Δ/X) ---- */
    .form-check-inline { margin-right: 0.4rem; }
    .form-check-input[type="radio"] { display: none !important; }
    .form-check-label {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 36px; height: 36px;
        border-radius: 50% !important;
        border: 2px solid var(--border);
        font-size: 0.85rem;
        font-weight: 700 !important;
        cursor: pointer;
        transition: all 0.15s ease;
        user-select: none;
        background: var(--white);
        color: var(--text-muted);
    }
    /* V Label */
    .form-check-input[value="V"] + .form-check-label {
        color: var(--success) !important;
        border-color: #6ee7b7;
        background: #f0fdf4;
    }
    .form-check-input[value="V"]:checked + .form-check-label {
        color: #fff !important;
        border-color: var(--success);
        background: var(--success);
        box-shadow: 0 0 0 3px rgba(5,150,105,0.2);
    }
    /* Δ Label */
    .form-check-input[value="Δ"] + .form-check-label {
        color: var(--warning) !important;
        border-color: #fcd34d;
        background: #fffbeb;
    }
    .form-check-input[value="Δ"]:checked + .form-check-label {
        color: #fff !important;
        border-color: var(--warning);
        background: var(--warning);
        box-shadow: 0 0 0 3px rgba(217,119,6,0.2);
    }
    /* X Label */
    .form-check-input[value="X"] + .form-check-label {
        color: var(--danger) !important;
        border-color: #fca5a5;
        background: #fff5f5;
    }
    .form-check-input[value="X"]:checked + .form-check-label {
        color: #fff !important;
        border-color: var(--danger);
        background: var(--danger);
        box-shadow: 0 0 0 3px rgba(220,38,38,0.2);
    }

    /* ---- ALERTS ---- */
    .alert {
        border-radius: var(--radius);
        border: none;
        padding: 0.85rem 1.1rem;
        font-size: 0.85rem;
        font-weight: 500;
    }
    .alert-success { background: var(--success-bg); color: var(--success); }
    .alert-danger { background: var(--danger-bg); color: var(--danger); }
    .alert-warning { background: var(--warning-bg); color: var(--warning); }

    /* ---- SCROLLBAR ---- */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

    /* ---- SIDEBAR OVERLAY ---- */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        z-index: 190;
        backdrop-filter: blur(4px);
    }

    /* ---- TOM SELECT OVERRIDES ---- */
    .ts-dropdown {
        z-index: 1060 !important; /* Mencegah tertutup oleh Bootstrap Modal (z-index: 1050) */
    }

    /* ---- RESPONSIVE ---- */
    @media (max-width: 1199.98px) {
        .sidebar { left: calc(-1 * var(--sidebar-w)); }
        .sidebar.show { left: 0; }
        .sidebar-overlay.show { display: block; }
        .main-wrapper { margin-left: 0; }
    }
      /* ---- KOP TABLE (FORM HEADER) ---- */
      .kop-table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: 0.8rem; background-color: #fff; }
      .kop-table th, .kop-table td { border: 1px solid #1e293b; padding: 6px 10px; vertical-align: middle; }
      .kop-table-title { background-color: #92b0d6 !important; text-align: center; font-weight: bold; font-size: 1.1rem; letter-spacing: 1px; color: #000 !important; }
      .kop-logo { text-align: center; width: 12%; font-weight: bold; }
      .kop-label { font-weight: 700; background-color: #f1f5f9; color: #334155; }
      .kop-val { font-weight: 500; color: #0f172a; }
  </style>
</head>
<body>
<?php 
$role = session()->get('role'); 
$uri = service('uri');
$seg1 = $uri->getTotalSegments() >= 1 ? $uri->getSegment(1) : '';
$seg2 = $uri->getTotalSegments() >= 2 ? $uri->getSegment(2) : '';
$seg3 = $uri->getTotalSegments() >= 3 ? $uri->getSegment(3) : '';
?>
<div class="app-container">
  
  <!-- Sidebar Overlay for Mobile -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- Sidebar -->
  <aside class="sidebar">
    <a href="<?= site_url('dashboard') ?>" class="sidebar-brand">
      <div class="brand-icon"><i class="bi bi-shield-fill-check text-white"></i></div>
      MTCE SYSTEM
    </a>
    
    <div class="sidebar-menu">
      <a href="<?= site_url('dashboard') ?>" class="menu-item <?= $seg1 === 'dashboard' ? 'active' : '' ?>">
        <i class="bi bi-grid-1x2-fill"></i>Dashboard
      </a>
      
      <?php if (in_array($role, ['magang', 'member', 'admin'], true)): ?>
        <a href="<?= site_url('checklist') ?>" class="menu-item <?= ($seg1 === 'checklist' || $seg1 === 'scan') ? 'active' : '' ?>">
          <i class="bi bi-clipboard-check-fill"></i>Pengecekan
        </a>
      <?php endif; ?>

      <?php $isFromApproval = (isset($_GET['from']) && $_GET['from'] === 'approval'); ?>
      <?php if (in_array($role, ['admin', 'member', 'leader', 'sheadprd', 'sheadmtc'], true)): ?>
        <a href="<?= site_url('approval') ?>" class="menu-item <?= ($seg1 === 'approval' || $isFromApproval) ? 'active' : '' ?>">
          <i class="bi bi-bell-fill"></i>Approval
          <?php
            // Hitung jumlah dokumen pending untuk badge
            try {
              $__db = \Config\Database::connect();
              $__cnt = 0;
              if ($role === 'leader') {
                $__line = session()->get('line');
                $__q = $__db->table('transaksi_check tc')
                  ->join('master_mesin mm', 'mm.id_mesin = tc.id_mesin', 'left')
                  ->where('tc.jenis_check', 'Overhaul')->where('tc.status', 'Pending');
                if ($__line) $__q->where('mm.line', $__line);
                $__cnt = $__q->countAllResults();
              } elseif ($role === 'sheadprd') {
                $__cnt = $__db->table('transaksi_check')->whereIn('jenis_check', ['Overhaul', 'Preventive'])->where('status', 'Approved L1')->countAllResults();
                $__cnt += $__db->table('approval_bulanan')->where('status', 'Approved L1')->countAllResults();
              } elseif ($role === 'sheadmtc') {
                $__cnt = $__db->table('transaksi_check')->whereIn('jenis_check', ['Overhaul', 'Preventive'])->where('status', 'Approved L2')->countAllResults();
                $__cnt += $__db->table('approval_bulanan')->where('status', 'Approved L2')->countAllResults();
              } elseif ($role === 'member') {
                $__cnt = $__db->table('transaksi_check')->where('jenis_check', 'Preventive')->where('status', 'Pending')->countAllResults();
                $__approvalSvc = new \App\Services\ApprovalService();
                $__approvalModel = new \App\Models\ApprovalBulananModel();
                $__belumSelesaiRows = $__approvalSvc->getBelumSelesaiRows('member', date('Y-m'), $__approvalModel);
                foreach ($__belumSelesaiRows as $__r) {
                  if (isset($__r['persen']) && $__r['persen'] == 100) {
                      $__cnt++;
                  }
                }
              } elseif ($role === 'admin') {
                $__cnt = $__db->table('transaksi_check')->whereNotIn('status', ['Approved'])->countAllResults();
                $__approvalSvc = new \App\Services\ApprovalService();
                $__approvalModel = new \App\Models\ApprovalBulananModel();
                $__belumSelesaiRows = $__approvalSvc->getBelumSelesaiRows('admin', date('Y-m'), $__approvalModel);
                foreach ($__belumSelesaiRows as $__r) {
                  if (isset($__r['persen']) && $__r['persen'] == 100) {
                      $__cnt++;
                  }
                }
              }
              if ($__cnt > 0): ?>
                <span class="badge bg-danger ms-auto" style="font-size:0.65rem;"><?= $__cnt ?></span>
              <?php endif;
            } catch (\Throwable $e) { /* silent */ }
          ?>
        </a>
      <?php endif; ?>

      <!-- HISTORY MENU (COLLAPSE) -->
      <?php 
        $isLaporanOpen = ($seg1 === 'riwayat' || $seg1 === 'kontrol' || $seg1 === 'abnormal' || $seg1 === 'laporan');
      ?>
      <a href="#laporanMenu" data-bs-toggle="collapse" class="menu-item <?= $isLaporanOpen ? 'active' : '' ?>">
        <i class="bi bi-folder-fill"></i>History
        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
      </a>
      <div class="collapse <?= $isLaporanOpen ? 'show' : '' ?>" id="laporanMenu">
        <div style="padding-left: 1.5rem; margin-top: 0.5rem; margin-bottom: 0.5rem;">
          
          <?php 
            $isPreventiveMenu = false;
            $isOverhaulMenu = false;
            if ($seg1 === 'riwayat') {
                if ($seg2 === 'lokasi' || $seg2 === 'index') {
                    if (!isset($_GET['jenis_check']) || strcasecmp($_GET['jenis_check'], 'Preventive') === 0) {
                        $isPreventiveMenu = true;
                    } elseif (isset($_GET['jenis_check']) && strcasecmp($_GET['jenis_check'], 'Overhaul') === 0) {
                        $isOverhaulMenu = true;
                    }
                } elseif (is_numeric($seg2)) {
                    if (isset($header['jenis_check'])) {
                        if (strtolower(str_replace(' ', '-', $header['jenis_check'])) === 'overhaul') {
                            $isOverhaulMenu = true;
                        } else {
                            $isPreventiveMenu = true;
                        }
                    }
                }
            }
          ?>
          <?php if ($role !== 'leader'): ?>
          <div style="font-size: 0.65rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.2rem; letter-spacing: 0.05em;">Preventive</div>

          <?php if (!in_array($role, ['sheadprd', 'sheadmtc'])): ?>
          <a href="<?= site_url('riwayat/lokasi/semua?jenis_check=Preventive') ?>" class="menu-item <?= $isPreventiveMenu ? 'active' : '' ?>" style="padding: 0.4rem 0.75rem; margin-bottom: 2px;">
            <i class="bi bi-file-earmark-text"></i>Checklist Report
          </a>
          <?php endif; ?>
          <?php if (in_array($role, ['sheadprd', 'sheadmtc', 'admin', 'member'])): ?>
          <a href="<?= site_url('kontrol') ?>" class="menu-item <?= $seg1 === 'kontrol' ? 'active' : '' ?>" style="padding: 0.4rem 0.75rem; margin-bottom: 2px;">
            <i class="bi bi-calendar2-check"></i>Checklist Control
          </a>
          <?php endif; ?>
          <?php if (!in_array($role, ['sheadprd', 'sheadmtc', 'leader'])): ?>
          <a href="<?= site_url('abnormal') ?>" class="menu-item <?= ($seg1 === 'abnormal' && $seg2 !== 'overhaul') ? 'active' : '' ?>" style="padding: 0.4rem 0.75rem; margin-bottom: 10px;">
            <i class="bi bi-exclamation-triangle"></i>Laporan Abnormal
          </a>
          <?php endif; ?>
          <?php endif; ?>

          <div style="font-size: 0.65rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.2rem; letter-spacing: 0.05em;">Overhaul</div>
          <a href="<?= site_url('riwayat/lokasi/semua?jenis_check=Overhaul') ?>" class="menu-item <?= $isOverhaulMenu ? 'active' : '' ?>" style="padding: 0.4rem 0.75rem; margin-bottom: 2px;">
            <i class="bi bi-tools"></i>Inspection Report
          </a>
          <?php if (!in_array($role, ['sheadprd', 'sheadmtc', 'leader'])): ?>
          <a href="<?= site_url('abnormal/overhaul') ?>" class="menu-item <?= ($seg1 === 'abnormal' && $seg2 === 'overhaul') ? 'active' : '' ?>" style="padding: 0.4rem 0.75rem; margin-bottom: 10px;">
            <i class="bi bi-exclamation-triangle"></i>Laporan Abnormal
          </a>
          <?php endif; ?>

          <?php if (in_array($role, ['member', 'admin'], true)): ?>
            <a href="<?= site_url('laporan/durasi') ?>" class="menu-item <?= $seg1 === 'laporan' ? 'active' : '' ?>" style="padding: 0.4rem 0.75rem; margin-bottom: 2px;">
              <i class="bi bi-bar-chart-line"></i>Laporan Durasi
            </a>
          <?php endif; ?>
        </div>
      </div>


      
      <a href="<?= site_url('admin/jadwal') ?>" class="menu-item <?= $seg2 === 'jadwal' ? 'active' : '' ?>">
        <i class="bi bi-calendar-event"></i>Jadwal Preventive
      </a>

      <?php 
        $hasMasterData = in_array($role, ['admin', 'member'], true);
        $isMasterDataOpen = ($seg2 === 'mesin' || $seg2 === 'user' || $seg2 === 'pic' || $seg2 === 'parameter');
      ?>
      <?php if ($hasMasterData): ?>
        <a href="#masterDataMenu" data-bs-toggle="collapse" class="menu-item <?= $isMasterDataOpen ? 'active' : '' ?> mt-2">
          <i class="bi bi-database-fill-gear"></i>Master Data
          <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
        </a>
        <div class="collapse <?= $isMasterDataOpen ? 'show' : '' ?>" id="masterDataMenu">
          <div style="padding-left: 1.5rem; margin-top: 0.5rem; margin-bottom: 0.5rem;">
            
            <a href="<?= site_url('admin/mesin') ?>" class="menu-item <?= $seg2 === 'mesin' ? 'active' : '' ?>" style="padding: 0.4rem 0.75rem; margin-bottom: 2px;">
              <i class="bi bi-gear-wide-connected"></i>Master Mesin
            </a>
            
            <?php if ($role === 'admin'): ?>
              <a href="<?= site_url('admin/user') ?>" class="menu-item <?= $seg2 === 'user' ? 'active' : '' ?>" style="padding: 0.4rem 0.75rem; margin-bottom: 2px;">
                <i class="bi bi-people"></i>Master User
              </a>
            <?php endif; ?>

            <?php if (in_array($role, ['admin', 'sheadmtc'], true)): ?>
              <a href="<?= site_url('admin/parameter') ?>" class="menu-item <?= $seg2 === 'parameter' ? 'active' : '' ?>" style="padding: 0.4rem 0.75rem; margin-bottom: 2px;">
                <i class="bi bi-sliders"></i>Master Parameter
              </a>
            <?php endif; ?>

          </div>
        </div>
      <?php endif; ?>
    </div>
    
    <div class="sidebar-footer">
      <div class="user-avatar"><?= strtoupper(substr(session()->get('nama') ?? 'U', 0, 1)) ?></div>
      <div class="overflow-hidden">
        <div class="user-name"><?= esc(session()->get('nama')) ?></div>
        <div class="user-role"><?= esc($role) ?></div>
      </div>
    </div>
  </aside>

  <!-- Main Content Wrapper -->
  <div class="main-wrapper">
    <!-- Header Topbar -->
    <header class="topbar">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-outline-secondary d-xl-none" id="sidebarToggle" style="padding:0.35rem 0.6rem;">
          <i class="bi bi-list"></i>
        </button>
        <h1 class="topbar-title"><?= esc($title ?? 'Dashboard') ?></h1>
      </div>
      <div class="d-flex align-items-center gap-2">
        <a href="<?= site_url('logout') ?>" id="btn-logout" class="btn btn-sm btn-outline-secondary" style="color:var(--danger); border-color:var(--danger);">
          <i class="bi bi-power"></i> Keluar
        </a>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="content-area">
      <!-- Flash Alerts -->
      <?php $flashErrors = session()->getFlashdata('errors'); ?>
      <?php if (!empty($flashErrors) || !empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <ul class="mb-0 ps-3">
            <?php 
              $allErrors = !empty($flashErrors) ? $flashErrors : (!empty($errors) ? $errors : []);
              if (is_array($allErrors)) {
                  foreach ($allErrors as $e) echo "<li>" . esc($e) . "</li>";
              } else {
                  echo "<li>" . esc($allErrors) . "</li>";
              }
            ?>
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>


