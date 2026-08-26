<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Login — MTCE System</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
<!-- Google Fonts (Inter) -->
<link href="<?= base_url('assets/css/google-fonts.css') ?>" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">
<script src="<?= base_url('assets/js/sweetalert2.all.min.js') ?>"></script>
<style>
    *, *::before, *::after { box-sizing: border-box; }
    html { font-size: 14px; }
    
    body {
        font-family: 'Inter', sans-serif;
        background: #f8fafc; /* Bright background fallback */
        color: #334155; /* Dark text for bright theme */
        min-height: 100vh;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }

    /* ---- FULL SCREEN VIDEO BACKGROUND ---- */
    .video-bg-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: -2;
        overflow: hidden;
    }

    .hero-video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: -2;
        object-fit: cover;
        /* Geser fokus video ke kiri (30% dari kiri) agar tidak tertutup form di kanan */
        object-position: 30% center; 
    }

    /* Gradient overlay: Putih solid di kanan, transparan ke kiri (Atau sesuaikan agar teks terbaca) */
    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: -1;
        background: linear-gradient(to right, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.5) 45%, rgba(255, 255, 255, 0) 100%);
    }

    /* ---- SEAMLESS LOGIN AREA ---- */
    .login-layout {
        position: relative;
        z-index: 10;
        width: 100%;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: flex-end; /* Pindah ke kanan */
        padding: 0 10%;
    }

    .login-container {
        width: 100%;
        max-width: 420px;
        padding: 3rem;
        background: rgba(255, 255, 255, 0.7); /* Transparan ala frosted glass yang terang */
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.9); /* Border putih tegas */
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); /* Bayangan lebih lembut */
    }

    .brand-logo {
        font-family: 'Chakra Petch', sans-serif;
        font-size: 3rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        margin-bottom: 0.2rem;
        text-transform: uppercase;
        color: #0369a1; /* Biru terang */
        text-shadow: none;
    }
    
    .brand-tagline {
        font-family: 'Roboto Mono', monospace;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.25em;
        color: #64748b;
        margin-bottom: 3.5rem;
    }

    .login-header {
        margin-bottom: 2.5rem;
    }
    .login-header h1 {
        font-family: 'Chakra Petch', sans-serif;
        font-weight: 700;
        font-size: 2rem;
        color: #0f172a; /* Hitam / Gunmetal */
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .login-header p {
        color: #475569;
        font-size: 0.95rem;
    }

    /* Form Styles */
    .form-label {
        font-family: 'Roboto Mono', monospace;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        font-weight: 600;
        color: #475569; /* Gelap */
        margin-bottom: 0.7rem;
    }
    
    .form-control {
        background: rgba(255, 255, 255, 0.8); /* Putih solid tapi sedikit transparan */
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 0.9rem 1.2rem;
        font-size: 1.05rem;
        color: #0f172a;
        transition: all 0.3s ease;
    }
    .form-control:focus {
        background: #ffffff;
        border-color: #0284c7;
        box-shadow: 0 0 10px rgba(2, 132, 199, 0.1);
        color: #0f172a;
    }
    .form-control::placeholder {
        color: #94a3b8;
    }

    .input-group-text {
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid #cbd5e1;
        border-left: none;
        border-radius: 0 6px 6px 0;
        color: #64748b;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .form-control:focus + .input-group-text {
        background: #ffffff;
        border-color: #0284c7;
        color: #0284c7;
    }
    .input-group-text:hover {
        color: #0f172a;
    }

    .btn-login {
        background: #0ea5e9;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        padding: 1.1rem;
        font-family: 'Chakra Petch', sans-serif;
        font-weight: 600;
        font-size: 1.1rem;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        width: 100%;
        margin-top: 2rem;
        transition: all 0.3s ease;
        box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.5);
    }
    .btn-login:hover {
        background: #38bdf8;
        box-shadow: 0 15px 35px -5px rgba(56, 189, 248, 0.6);
        transform: translateY(-2px);
    }
    .btn-login:active {
        transform: translateY(1px);
    }

    .alert {
        border-radius: 6px;
        border: none;
        font-size: 0.9rem;
        padding: 1rem;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(5px);
    }
    .alert-danger {
        color: #fca5a5;
        border-left: 4px solid #ef4444;
    }
    .alert-success {
        color: #86efac;
        border-left: 4px solid #22c55e;
    }

    /* Footer / Links */
    .login-footer {
        margin-top: 4rem;
        font-family: 'Roboto Mono', monospace;
        font-size: 0.75rem;
        color: #64748b;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .login-layout {
            justify-content: center;
            padding: 0 5%;
        }
        .hero-overlay {
            background: linear-gradient(to top, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.6) 60%, rgba(255, 255, 255, 0) 100%);
        }
        .login-container {
            padding: 2rem;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .brand-logo, .brand-tagline, .login-header {
            text-align: center;
        }
    }
</style>
</head>
<body>

<!-- FULL SCREEN VIDEO BACKGROUND -->
<div class="video-bg-container">
    <video class="hero-video" autoplay loop muted playsinline>
        <source src="<?= base_url('videos/mesin_kiri.mp4') ?>" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
</div>

<!-- SEAMLESS LOGIN AREA -->
<div class="login-layout">
    <div class="login-container">
        <div class="brand-logo">MTCE</div>
        <div class="brand-tagline">SYSTEM :: MAINTENANCE</div>

            <div class="login-header">
                <h1>SYSTEM ACCESS</h1>
                <p>Enter your credentials to initiate secure connection.</p>
            </div>

            <!-- Flash messages handled by SweetAlert below -->

            <form action="<?= site_url('login') ?>" method="post">
                <div class="mb-4">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="ID.USER" required autofocus autocomplete="username">
                </div>

                <div class="mb-4">
                    <label class="form-label d-flex justify-content-between">
                        <span>Password</span>
                    </label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required autocomplete="current-password" style="border-right: none;">
                        <span class="input-group-text" id="togglePassword" title="Show/Hide Password">
                            <i class="bi bi-eye"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-login">
                    LOGIN <i class="bi bi-box-arrow-in-right ms-2"></i>
                </button>
            </form>

            <div class="login-footer">
                &copy; <?= date('Y') ?> MTCE // V1.0 // ALL SYSTEMS NORMAL
            </div>
        </div>

<script src="<?= base_url('assets/js/sweetalert2.all.min.js') ?>"></script>
<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const icon = togglePassword.querySelector('i');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });

    <?php if (session()->getFlashdata('success')): ?>
        let audioSuccess = new Audio('<?= base_url('audio/success.ogg') ?>');
        audioSuccess.play().catch(e => console.log("Audio autoplay prevented"));
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '<?= addslashes(session()->getFlashdata('success')) ?>',
            timer: 3000,
            showConfirmButton: false
        });
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('error')): ?>
        let audioError = new Audio('<?= base_url('audio/error.ogg') ?>');
        audioError.play().catch(e => console.log("Audio autoplay prevented"));
        Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: '<?= addslashes(session()->getFlashdata('error')) ?>',
            timer: 3000,
            showConfirmButton: false
        });
    <?php endif; ?>
</script>
</body>
</html>
