<?php
require_once 'config.php';

$isLoggedIn = isLoggedIn();
$isPending   = isset($_SESSION['temp_user_id']) && !$isLoggedIn;

$displayName = '';
if ($isLoggedIn) {
    $displayName = $_SESSION['fullname'] ?? '';
} elseif ($isPending) {
    $displayName = $_SESSION['temp_fullname'] ?? 'کاربر';
}

$csrf_token = getCsrfToken();

$error_message   = '';
$success_message = '';
$register_errors = [];

if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['register_errors']) && is_array($_SESSION['register_errors'])) {
    $register_errors = $_SESSION['register_errors'];
    unset($_SESSION['register_errors']);
}

$old_input = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

$status_message = '';
$status_type = '';

if (isset($_GET['verified']) && (string)$_GET['verified'] === '1') {
    $status_message = 'ایمیل شما با موفقیت تأیید شد. خوش آمدید!';
    $status_type = 'success';
} elseif (isset($_GET['resend']) && (string)$_GET['resend'] === '1') {
    $status_message = 'کد تأیید مجدد به ایمیل شما ارسال شد.';
    $status_type = 'info';
} elseif (isset($_GET['logout']) && (string)$_GET['logout'] === 'success') {
    $status_message = 'شما با موفقیت خارج شدید.';
    $status_type = 'info';
}

$error_param = isset($_GET['error']) ? preg_replace('/[^a-z_]/i', '', (string)$_GET['error']) : '';
$open_login = in_array($error_param, ['invalid', 'login', 'login_error'], true);
$open_signup = in_array($error_param, ['exists', 'register', 'signup_error'], true) || !empty($register_errors);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pezhman Academy | where learning comes alive</title>
    <meta name="theme-color" content="#0a0e17" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700;900&family=Poppins:wght@600;700;800;900&family=Dancing+Script:wght@700&display=swap" rel="stylesheet" />
    <style>
        :root{
            --bg:#0a0e17;
            --bg-2:#0f141f;
            --panel:rgba(15,20,31,.88);
            --card:rgba(22,28,42,.94);
            --card-2:rgba(18,23,36,.96);
            --line:rgba(212,175,55,.15);
            --line-strong:rgba(212,175,55,.25);
            --text:#f0f4f8;
            --muted:#a0aec0;
            --green:#d4af37;
            --green-2:#b8960c;
            --green-3:#f0d060;
            --gold:#d4af37;
            --cyan:#00e5ff;
            --danger:#ff5c63;
            --radius-xl:30px;
            --radius-lg:22px;
            --radius-md:16px;
            --shadow:0 24px 60px rgba(0,0,0,.55);
            --shadow-soft:0 12px 28px rgba(0,0,0,.35);
            --transition:.28s cubic-bezier(.2,.9,.2,1);
        }

        *{box-sizing:border-box}
        html{scroll-behavior:smooth}
        body{
            margin:0;
            font-family:'Vazirmatn',system-ui,sans-serif;
            background:
                radial-gradient(circle at 20% 0%, rgba(212,175,55,.08), transparent 28%),
                radial-gradient(circle at 80% 10%, rgba(0,229,255,.06), transparent 26%),
                linear-gradient(180deg, var(--bg), #040810 60%, #02040a);
            color:var(--text);
            overflow-x:hidden;
            line-height:1.6;
        }
        a{color:inherit;text-decoration:none}
        button,input{font:inherit}
        img{max-width:100%;display:block}
        ::selection{background:rgba(212,175,55,.25)}

        .bg-layer{
            position:fixed;
            inset:0;
            z-index:-2;
            pointer-events:none;
            overflow:hidden;
        }
        .grid{
            position:absolute;
            inset:-2px;
            opacity:.15;
            background-image:
                linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
            background-size:72px 72px;
            mask-image: radial-gradient(ellipse at center, black 20%, transparent 78%);
        }
        .orb{
            position:absolute;
            border-radius:50%;
            filter:blur(100px);
            opacity:.2;
            animation: floatOrb 18s ease-in-out infinite;
        }
        .orb.one{width:450px;height:450px;background:#d4af37;top:-120px;right:-120px}
        .orb.two{width:380px;height:380px;background:#00e5ff;bottom:-120px;left:-120px;animation-delay:-7s}
        .orb.three{width:260px;height:260px;background:#f0d060;top:45%;left:52%;animation-delay:-11s;opacity:.1}
        @keyframes floatOrb{
            0%,100%{transform:translate(0,0) scale(1)}
            50%{transform:translate(40px,22px) scale(1.12)}
        }

        .bg-layer::after {
            content:'';
            position:absolute;
            inset:0;
            background:radial-gradient(circle at 20% 20%, rgba(255,255,255,.05) 1px, transparent 1px),
                       radial-gradient(circle at 80% 60%, rgba(255,255,255,.05) 1px, transparent 1px),
                       radial-gradient(circle at 30% 80%, rgba(255,255,255,.05) 1px, transparent 1px),
                       radial-gradient(circle at 70% 40%, rgba(255,255,255,.05) 1px, transparent 1px);
            background-size: 200px 200px, 300px 300px, 250px 250px, 220px 220px;
            animation: particleDrift 20s linear infinite;
        }
        @keyframes particleDrift {
            0%{transform:translate(0,0)}
            100%{transform:translate(20px, 20px)}
        }

        .top-header{
            position:sticky;
            top:0;
            z-index:200;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:18px;
            padding:16px 5%;
            background:rgba(6,10,15,.85);
            border-bottom:1px solid rgba(255,255,255,.04);
            backdrop-filter:blur(16px);
        }
        .brand{
            display:flex;
            align-items:center;
            gap:14px;
            min-width:220px;
        }
        .brand-logo{
            width:80px;
            height:80px;
            border-radius:18px;
            overflow:hidden;
            box-shadow:0 10px 28px rgba(212,175,55,.2);
            flex:0 0 auto;
            transition:transform var(--transition);
        }
        .brand-logo:hover{transform:scale(1.08)}
        .brand-copy{
            display:flex;
            flex-direction:column;
            line-height:1.2;
        }
        .brand-name{
            font-family:'Dancing Script',cursive;
            font-size:22px;
            line-height:1.1;
            color:#f0d060;
        }
        .brand-tagline{
            font-size:12px;
            color:var(--muted);
            margin-top:2px;
            letter-spacing:.02em;
        }

        .nav-center{
            display:flex;
            align-items:center;
            justify-content:center;
            gap:34px;
            flex:1;
        }
        .nav-center a,
        .nav-center .dropdown > button{
            color:rgba(240,246,248,.86);
            background:none;
            border:none;
            padding:8px 0;
            cursor:pointer;
            position:relative;
            font-weight:600;
            font-size:15px;
            transition:color var(--transition);
        }
        .nav-center a::after,
        .nav-center .dropdown > button::after{
            content:"";
            position:absolute;
            left:0;
            bottom:0;
            width:100%;
            height:2px;
            background:linear-gradient(90deg, var(--gold), transparent);
            transform:scaleX(0);
            transform-origin:left;
            transition:transform var(--transition);
        }
        .nav-center a:hover,
        .nav-center .dropdown > button:hover{color:var(--gold)}
        .nav-center a:hover::after,
        .nav-center .dropdown > button:hover::after{transform:scaleX(1)}

        .nav-right{
            display:flex;
            align-items:center;
            gap:10px;
            min-width:320px;
            justify-content:flex-end;
            flex-wrap:wrap;
        }
        .chip, .btn, .lang-btn{
            border-radius:999px;
            border:1px solid rgba(255,255,255,.07);
            transition:transform var(--transition), border-color var(--transition), background var(--transition), box-shadow var(--transition), color var(--transition);
            white-space:nowrap;
        }
        .lang-wrap{position:relative}
        .lang-btn{
            display:inline-flex;
            align-items:center;
            gap:8px;
            background:rgba(15,20,31,.78);
            color:#f0f4f8;
            padding:11px 16px;
            min-width:132px;
            justify-content:center;
        }
        .lang-btn:hover{border-color:rgba(212,175,55,.3); box-shadow:0 0 0 3px rgba(212,175,55,.08)}
        .lang-list{
            position:absolute;
            top:calc(100% + 10px);
            inset-inline-end:0;
            background:rgba(15,20,31,.98);
            border:1px solid rgba(212,175,55,.18);
            border-radius:18px;
            box-shadow:var(--shadow);
            padding:8px;
            min-width:180px;
            display:none;
            z-index:260;
        }
        .lang-list.open{display:block; animation:pop .18s ease}
        @keyframes pop{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}
        .lang-list button{
            width:100%;
            text-align:start;
            border:none;
            background:none;
            color:var(--text);
            padding:11px 12px;
            border-radius:12px;
            cursor:pointer;
            transition:background var(--transition), color var(--transition);
        }
        .lang-list button:hover{background:rgba(212,175,55,.12); color:var(--gold)}

        .btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            padding:11px 20px;
            font-weight:700;
            border:1px solid rgba(255,255,255,.08);
        }
        .btn-ghost{
            background:rgba(15,20,31,.7);
            color:var(--text);
        }
        .btn-ghost:hover{border-color:rgba(212,175,55,.3); background:rgba(212,175,55,.08)}
        .btn-primary{
            background:linear-gradient(135deg, var(--gold), #b8960c);
            color:#0a0e17;
            box-shadow:0 12px 24px rgba(212,175,55,.3);
        }
        .btn-primary:hover{transform:translateY(-2px); box-shadow:0 18px 32px rgba(212,175,55,.45)}
        .btn-danger{
            background:linear-gradient(135deg, #ff646a, #c9171f);
            color:#fff;
        }

        .dropdown{position:relative}
        .dropdown-panel{
            position:absolute;
            top:calc(100% + 12px);
            left:50%;
            transform:translateX(-50%);
            min-width:640px;
            background:rgba(15,20,31,.98);
            border:1px solid rgba(212,175,55,.18);
            border-radius:24px;
            box-shadow:var(--shadow);
            padding:24px;
            display:none;
            z-index:270;
        }
        .dropdown-panel.open{display:block; animation:pop .18s ease}

        /* استایل جدید برای منوی دسته‌بندی‌ها */
        .mega-grid{
            display:grid;
            grid-template-columns:repeat(3, 1fr);
            gap:24px;
        }
        .mega-col h4{
            color:var(--gold);
            font-size:14px;
            letter-spacing:.15em;
            text-transform:uppercase;
            margin:0 0 12px;
            padding-bottom:8px;
            border-bottom:1px solid rgba(212,175,55,.2);
        }
        .mega-col a{
            display:block;
            padding:8px 12px;
            border-radius:10px;
            color:var(--text);
            font-size:15px;
            margin-bottom:4px;
            transition:background var(--transition), color var(--transition);
        }
        .mega-col a:hover{
            background:rgba(212,175,55,.12);
            color:var(--gold);
        }
        .mega-col .single-link{
            font-weight:700;
            margin-bottom:12px;
            color:var(--text);
            text-decoration:none;
        }
        .mega-col .single-link:hover{color:var(--gold)}

        .hero{
            max-width:1320px;
            margin:0 auto;
            padding:82px 5% 68px;
            display:grid;
            grid-template-columns:1fr 1fr;
            align-items:center;
            gap:48px;
            min-height:calc(100vh - 170px);
        }
        .hero-copy{
            max-width:650px;
        }
        .hero h1{
            margin:0;
            font-family:'Poppins', 'Vazirmatn', sans-serif;
            font-weight:900;
            font-size:clamp(42px, 5.5vw, 72px);
            line-height:1.05;
            letter-spacing:-.04em;
            text-shadow:0 4px 24px rgba(212,175,55,.1);
        }
        .hero h1 span,
        .hero h1 .accent{
            word-break:break-word;
            hyphens:auto;
        }
        .hero h1 .accent{
            background:linear-gradient(90deg, #f0d060 0%, #d4af37 50%, #b8960c 100%);
            -webkit-background-clip:text;
            background-clip:text;
            color:transparent;
        }
        .hero p{
            margin:26px 0 0;
            color:var(--muted);
            font-size:clamp(16px, 1.55vw, 19px);
            max-width:620px;
        }
        .hero-actions{
            display:flex;
            gap:14px;
            flex-wrap:wrap;
            margin-top:32px;
        }
        .hero-stats{
            display:grid;
            grid-template-columns:repeat(4,minmax(0,1fr));
            gap:18px;
            margin-top:46px;
            max-width:760px;
        }
        .stat{
            padding:2px 0;
        }
        .stat b{
            display:block;
            font-family:'Poppins', sans-serif;
            font-weight:800;
            color:var(--gold);
            font-size:clamp(30px, 3.2vw, 42px);
            line-height:1;
        }
        .stat span{
            display:block;
            color:var(--muted);
            font-size:14px;
            margin-top:8px;
        }

        .hero-visual{
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .hero-card-wrap{
            position:relative;
            width:min(650px, 94vw);
            aspect-ratio:1;
            display:grid;
            place-items:center;
        }
        .concentric{
            position:absolute;
            border-radius:50%;
            border:1px solid rgba(212,175,55,.15);
        }
        .concentric.one{width:82%;height:82%;border-style:dashed;animation:spin 24s linear infinite}
        .concentric.two{width:98%;height:98%;opacity:.5;animation:spin 36s linear infinite reverse}
        .concentric.three{width:66%;height:66%;border-color:rgba(0,229,255,.1)}
        @keyframes spin{to{transform:rotate(360deg)}}

        .hero-card{
            width:75%;
            aspect-ratio:1;
            border-radius:28px;
            background:linear-gradient(180deg, rgba(15,20,31,.6) 0%, rgba(22,28,42,.85) 40%, rgba(10,14,23,.95));
            border:1px solid rgba(212,175,55,.2);
            box-shadow:var(--shadow), 0 0 40px rgba(212,175,55,.05);
            display:flex;
            align-items:center;
            justify-content:center;
            position:relative;
            overflow:hidden;
            transform-style:preserve-3d;
            transition:transform .12s ease;
            backdrop-filter:blur(8px);
        }
        .hero-card::before{
            content:"";
            position:absolute;
            inset:14px;
            border-radius:22px;
            border:1px solid rgba(255,255,255,.05);
        }
        .hero-card .logo-box{
            width:140px;
            height:140px;
            border-radius:22px;
            background:linear-gradient(135deg, var(--gold), #b8960c);
            overflow:hidden;
            box-shadow:0 24px 48px rgba(212,175,55,.3);
            transform:translateZ(60px);
        }
        .hero-card .logo-box img {
            width:100%;
            height:100%;
            object-fit:cover;
        }
        .hero-card .name{
            position:absolute;
            bottom:28px;
            left:50%;
            transform:translateX(-50%);
            font-family:'Dancing Script', cursive;
            font-size:28px;
            color:#f0d060;
            text-shadow:0 2px 12px rgba(212,175,55,.3);
            white-space:nowrap;
        }
        .hero-card .tagline{
            position:absolute;
            bottom:10px;
            left:50%;
            transform:translateX(-50%);
            text-transform:uppercase;
            letter-spacing:.38em;
            font-size:10px;
            color:rgba(240,245,255,.7);
            white-space:nowrap;
        }

        .section{
            max-width:1320px;
            margin:0 auto;
            padding:46px 5% 0;
        }
        .section-head{
            display:flex;
            align-items:flex-end;
            justify-content:space-between;
            gap:16px;
            margin-bottom:24px;
        }
        .eyebrow{
            color:var(--gold);
            letter-spacing:.34em;
            text-transform:uppercase;
            font-size:12px;
            font-weight:700;
            margin-bottom:10px;
        }
        .section h2{
            margin:0;
            font-family:'Poppins', sans-serif;
            font-size:clamp(34px, 4vw, 56px);
            line-height:1.02;
            letter-spacing:-.04em;
        }
        .section h2 .accent{
            color:var(--gold);
        }
        .section p.lead{
            margin:14px 0 0;
            color:var(--muted);
            max-width:720px;
            font-size:16px;
        }
        .section .mini-link{
            display:inline-flex;
            align-items:center;
            gap:8px;
            color:#f0f4f8;
            background:rgba(15,20,31,.7);
            border:1px solid rgba(255,255,255,.06);
            padding:12px 16px;
            border-radius:999px;
            white-space:nowrap;
            flex-shrink:0;
            transition:border-color var(--transition);
        }
        .section .mini-link:hover{border-color:rgba(212,175,55,.35)}

        .tracks-grid{
            display:grid;
            grid-template-columns:repeat(3,minmax(0,1fr));
            gap:18px;
            margin-top:28px;
        }
        .track-card{
            position:relative;
            background:linear-gradient(180deg, rgba(22,28,42,.96), rgba(15,20,31,.94));
            border:1px solid rgba(255,255,255,.05);
            border-radius:26px;
            padding:26px;
            min-height:250px;
            overflow:hidden;
            transition:transform var(--transition), border-color var(--transition), box-shadow var(--transition), background var(--transition);
            display:flex;
            flex-direction:column;
            justify-content:space-between;
        }
        .track-card:hover{
            transform:translateY(-8px);
            border-color:rgba(212,175,55,.3);
            box-shadow:0 22px 44px rgba(0,0,0,.45);
            background:linear-gradient(180deg, rgba(26,32,48,.96), rgba(15,20,31,.94));
        }
        .track-top{
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:12px;
        }
        .track-ic{
            width:52px;
            height:52px;
            border-radius:14px;
            background:rgba(212,175,55,.1);
            border:1px solid rgba(212,175,55,.18);
            display:grid;
            place-items:center;
            color:var(--gold);
            font-size:24px;
            flex:0 0 auto;
        }
        .track-badge{
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:7px 10px;
            border-radius:999px;
            font-size:11px;
            letter-spacing:.18em;
            text-transform:uppercase;
            font-weight:800;
            color:rgba(245,248,246,.82);
            background:rgba(255,255,255,.03);
            border:1px solid rgba(255,255,255,.04);
            white-space:nowrap;
        }
        .track-title{
            margin:18px 0 10px;
            font-family:'Poppins',sans-serif;
            font-size:24px;
            line-height:1.06;
            letter-spacing:-.03em;
        }
        .track-desc{
            margin:0;
            color:var(--muted);
            font-size:15px;
            max-width:320px;
        }
        .track-foot{
            margin-top:24px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            padding-top:18px;
            border-top:1px solid rgba(255,255,255,.05);
            color:#dce6dd;
            font-weight:600;
        }
        .track-foot span{color:var(--gold)}

        .why-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:18px;
            margin-top:28px;
        }
        .why-copy{
            background:linear-gradient(180deg, rgba(22,28,42,.96), rgba(15,20,31,.94));
            border:1px solid rgba(255,255,255,.05);
            border-radius:28px;
            padding:34px;
        }
        .why-copy p{
            color:var(--muted);
            margin:18px 0 0;
            font-size:16px;
            max-width:520px;
        }
        .avatar-row{
            display:flex;
            align-items:center;
            gap:10px;
            margin-top:28px;
        }
        .avatar-row .av{
            width:38px;
            height:38px;
            border-radius:50%;
            border:2px solid rgba(8,12,9,.96);
            overflow:hidden;
            margin-inline-start:-10px;
            box-shadow:0 8px 16px rgba(0,0,0,.28);
            background:#132018;
        }
        .avatar-row .av:first-child{margin-inline-start:0}
        .avatar-row strong{display:block}
        .avatar-row small{display:block;color:var(--muted)}

        .benefits{
            display:grid;
            grid-template-columns:repeat(2,minmax(0,1fr));
            gap:18px;
        }
        .benefit{
            background:linear-gradient(180deg, rgba(22,28,42,.96), rgba(15,20,31,.94));
            border:1px solid rgba(255,255,255,.05);
            border-radius:22px;
            padding:24px;
            min-height:176px;
            transition:transform var(--transition), border-color var(--transition);
        }
        .benefit:hover{transform:translateY(-6px); border-color:rgba(212,175,55,.25)}
        .benefit .ic{
            width:44px;
            height:44px;
            border-radius:14px;
            display:grid;
            place-items:center;
            background:rgba(212,175,55,.1);
            border:1px solid rgba(212,175,55,.18);
            color:var(--gold);
            font-size:20px;
            margin-bottom:16px;
        }
        .benefit h3{
            margin:0;
            font-size:22px;
            line-height:1.1;
            font-family:'Poppins',sans-serif;
            letter-spacing:-.02em;
        }
        .benefit p{
            margin:10px 0 0;
            color:var(--muted);
            font-size:15px;
        }

        .test-wrap{
            margin-top:28px;
            background:linear-gradient(180deg, rgba(22,28,42,.96), rgba(10,15,23,.96));
            border:1px solid rgba(212,175,55,.18);
            border-radius:30px;
            padding:30px;
            overflow:hidden;
            position:relative;
        }
        .test-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:28px;
            align-items:center;
        }
        .test-copy .kicker{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:7px 14px;
            border-radius:999px;
            background:rgba(212,175,55,.1);
            border:1px solid rgba(212,175,55,.22);
            color:#f0d060;
            font-weight:700;
            font-size:13px;
            margin-bottom:16px;
        }
        .test-copy p{
            color:var(--muted);
            margin:18px 0 0;
            max-width:560px;
        }
        .steps{
            display:flex;
            flex-direction:column;
            gap:14px;
        }
        .step{
            display:flex;
            gap:16px;
            align-items:flex-start;
            padding:20px 22px;
            border-radius:20px;
            background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.05);
        }
        .step-num{
            flex:0 0 auto;
            width:44px;
            height:44px;
            border-radius:14px;
            display:grid;
            place-items:center;
            background:rgba(212,175,55,.15);
            color:var(--gold);
            font-weight:900;
            font-family:'Poppins',sans-serif;
        }
        .step h4{
            margin:0;
            font-size:18px;
            line-height:1.1;
        }
        .step p{
            margin:6px 0 0;
            color:var(--muted);
            font-size:14px;
        }

        footer{
            padding:64px 5% 90px;
            margin-top:72px;
            border-top:1px solid rgba(255,255,255,.05);
            text-align:center;
            background:rgba(0,0,0,.08);
        }
        footer img{width:66px;height:66px;margin:0 auto 14px;border-radius:16px}
        footer p, footer small{color:var(--muted)}
        footer strong{color:var(--text)}

        .modal-overlay{
            position:fixed;
            inset:0;
            z-index:1000;
            background:rgba(0,0,0,.75);
            backdrop-filter:blur(10px);
            display:flex;
            align-items:center;
            justify-content:center;
            opacity:0;
            pointer-events:none;
            transition:opacity .24s ease;
            padding:20px;
        }
        .modal-overlay.active{
            opacity:1;
            pointer-events:auto;
        }
        .modal{
            width:min(470px, 100%);
            background:linear-gradient(180deg, rgba(15,20,31,.98), rgba(10,15,23,.98));
            border:1px solid rgba(212,175,55,.2);
            border-radius:30px;
            box-shadow:var(--shadow);
            padding:28px;
            position:relative;
            transform:translateY(8px);
            animation:modalIn .22s ease forwards;
        }
        @keyframes modalIn{
            to{transform:none}
        }
        .close-btn{
            position:absolute;
            top:14px;
            inset-inline-end:14px;
            width:42px;
            height:42px;
            border:none;
            border-radius:50%;
            background:rgba(255,255,255,.04);
            color:var(--text);
            font-size:22px;
            cursor:pointer;
        }
        .close-btn:hover{background:rgba(212,175,55,.12); color:var(--gold)}
        .modal h2{
            margin:0 0 20px;
            font-family:'Poppins',sans-serif;
            font-size:30px;
            line-height:1.05;
            letter-spacing:-.03em;
            text-align:center;
            color:var(--text);
        }
        .modal .sub{
            text-align:center;
            color:var(--muted);
            margin:-10px 0 22px;
            font-size:14px;
        }
        .form-group{margin-bottom:16px}
        .form-group label{
            display:block;
            margin-bottom:8px;
            color:#e2e8f0;
            font-size:14px;
            font-weight:600;
        }
        .form-group input{
            width:100%;
            border:none;
            outline:none;
            padding:14px 16px;
            border-radius:16px;
            background:rgba(255,255,255,.04);
            color:var(--text);
            border:1px solid rgba(255,255,255,.05);
            transition:border-color var(--transition), box-shadow var(--transition);
        }
        .form-group input:focus{
            border-color:rgba(212,175,55,.35);
            box-shadow:0 0 0 3px rgba(212,175,55,.1);
        }
        .checkbox{
            display:flex;
            gap:10px;
            align-items:flex-start;
            margin:14px 0;
            color:var(--muted);
            font-size:14px;
        }
        .checkbox input{
            margin-top:4px;
            width:18px;
            height:18px;
            accent-color:var(--gold);
            flex:0 0 auto;
        }
        .auth-btn{
            width:100%;
            border:none;
            padding:14px 16px;
            border-radius:16px;
            background:linear-gradient(135deg, var(--gold), #b8960c);
            color:#0a0e17;
            font-weight:800;
            font-size:16px;
            cursor:pointer;
            box-shadow:0 12px 24px rgba(212,175,55,.25);
            transition:transform var(--transition), box-shadow var(--transition);
        }
        .auth-btn:hover{transform:translateY(-2px); box-shadow:0 16px 30px rgba(212,175,55,.35)}
        .modal-footer{
            text-align:center;
            margin-top:18px;
            color:var(--muted);
            font-size:14px;
        }
        .modal-footer a{
            color:var(--gold);
            font-weight:700;
        }
        .server-error,.server-success{
            border-radius:14px;
            padding:12px 14px;
            margin-bottom:16px;
            font-size:14px;
            line-height:1.5;
        }
        .server-error{
            color:#ff9ca0;
            background:rgba(255,92,99,.15);
            border:1px solid rgba(255,92,99,.2);
        }
        .server-success{
            color:#f0d060;
            background:rgba(212,175,55,.12);
            border:1px solid rgba(212,175,55,.22);
        }
        .error-msg{
            display:none;
            color:#ff9ca0;
            margin-top:6px;
            font-size:13px;
        }

        .gate-modal .modal{text-align:center}
        .gate-msg{
            color:var(--muted);
            margin:10px 0 24px;
            font-size:16px;
        }
        .gate-actions{
            display:flex;
            gap:12px;
            justify-content:center;
            flex-wrap:wrap;
        }

        .rv{
            opacity:0;
            transform:translateY(24px);
            transition:opacity .75s ease, transform .75s cubic-bezier(.2,.8,.2,1);
        }
        .rv.show,
        .rv.initial-visible{
            opacity:1;
            transform:none;
        }

        [dir="rtl"] body{font-family:'Vazirmatn',system-ui,sans-serif}
        [dir="rtl"] .nav-center,
        [dir="rtl"] .hero,
        [dir="rtl"] .section-head,
        [dir="rtl"] .track-top,
        [dir="rtl"] .track-foot,
        [dir="rtl"] .why-grid,
        [dir="rtl"] .test-grid{
            direction:rtl;
        }

        @media (max-width: 1120px){
            .nav-center{gap:22px}
            .dropdown-panel{min-width:560px; left:50%; transform:translateX(-50%)}
            .hero{grid-template-columns:1fr; text-align:center; padding-top:64px; min-height:auto}
            .hero-copy{max-width:100%}
            .hero p{margin-left:auto;margin-right:auto}
            .hero-actions{justify-content:center}
            .hero-stats{margin-left:auto;margin-right:auto}
            .hero-visual{order:-1}
            .hero-card-wrap{width:min(500px, 92vw)}
            .section-head{flex-direction:column; align-items:flex-start}
            .nav-right{min-width:auto}
        }
        @media (max-width: 900px){
            .top-header{
                flex-wrap:wrap;
                justify-content:center;
                gap:14px;
                padding:14px 4%;
            }
            .brand{min-width:auto}
            .nav-center{order:3; width:100%; justify-content:center; flex-wrap:wrap}
            .nav-right{order:2; width:100%; justify-content:center}
            .status-bar{top:0; position:relative}
            .hero-stats{grid-template-columns:repeat(2,minmax(0,1fr))}
            .tracks-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
            .why-grid,
            .test-grid{grid-template-columns:1fr}
            .benefits{grid-template-columns:1fr 1fr}
        }
        @media (max-width: 620px){
            .brand-logo{width:64px;height:64px}
            .brand-name{font-size:18px}
            .brand-tagline{font-size:10px}
            .nav-center a,
            .nav-center .dropdown > button{font-size:14px}
            .hero{padding:54px 4% 54px}
            .hero h1{font-size:clamp(40px, 13vw, 58px)}
            .hero p{font-size:15px}
            .hero-actions{flex-direction:column}
            .hero-actions .btn{width:100%}
            .hero-stats{grid-template-columns:1fr 1fr; gap:14px}
            .section{padding-left:4%; padding-right:4%}
            .tracks-grid,
            .benefits{grid-template-columns:1fr}
            .dropdown-panel{min-width:unset; width:92vw; left:50%; transform:translateX(-50%)}
            .mega-grid{grid-template-columns:1fr 1fr}
            .track-card,
            .why-copy,
            .test-wrap{padding:22px}
            .modal{padding:22px}
        }
    </style>
</head>
<body>

<div class="bg-layer" aria-hidden="true">
    <span class="orb one"></span>
    <span class="orb two"></span>
    <span class="orb three"></span>
    <div class="grid"></div>
</div>

<div class="modal-overlay <?php echo $open_login ? 'active' : ''; ?>" id="loginModalOverlay" aria-hidden="<?php echo $open_login ? 'false' : 'true'; ?>">
    <div class="modal">
        <button class="close-btn" id="closeLoginBtn" aria-label="Close">×</button>
        <h2 data-i18n="loginTitle">Welcome Back</h2>
        <p class="sub" data-i18n="loginSub">Log in to access your dashboard and placement test.</p>

        <?php if (!empty($error_message)): ?>
            <div class="server-error"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form id="loginForm" action="login.php" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-group">
                <label for="loginEmail" data-i18n="email">Email *</label>
                <input type="email" name="email" id="loginEmail" required placeholder="john@example.com" data-i18n-placeholder="emailPlaceholder">
                <div class="error-msg" id="loginEmailError" data-i18n="emailError">Please enter a valid email.</div>
            </div>

            <div class="form-group">
                <label for="loginPassword" data-i18n="password">Password *</label>
                <input type="password" name="password" id="loginPassword" required placeholder="••••••••" data-i18n-placeholder="passwordPlaceholder">
                <div class="error-msg" id="loginPassError" data-i18n="passwordError">Password cannot be empty.</div>
            </div>

            <div class="checkbox">
                <input type="checkbox" id="loginAgreeTerms">
                <label for="loginAgreeTerms">
                    <span data-i18n="agreePrefix">I have read and accept the</span>
                    <a href="rules.html" style="color:var(--gold);font-weight:700;text-decoration:underline" data-i18n="siteTerms">site terms</a>
                </label>
            </div>
            <div class="error-msg" id="loginTermsError" data-i18n="termsError" style="margin-top:-10px;margin-bottom:10px;">You must agree to the terms.</div>

            <div class="checkbox">
                <input type="checkbox" id="loginRememberMe">
                <label for="loginRememberMe" data-i18n="rememberMe">Remember me</label>
            </div>

            <button type="submit" class="auth-btn" id="loginSubmitBtn" data-i18n="loginBtn">Log in</button>
        </form>

        <div class="modal-footer">
            <span data-i18n="noAccount">Don't have an account?</span>
            <a href="#" id="switchToSignup" data-i18n="signupLink">Sign up</a>
        </div>
    </div>
</div>

<div class="modal-overlay <?php echo $open_signup ? 'active' : ''; ?>" id="signupModalOverlay" aria-hidden="<?php echo $open_signup ? 'false' : 'true'; ?>">
    <div class="modal">
        <button class="close-btn" id="closeSignupBtn" aria-label="Close">×</button>
        <h2 data-i18n="signupTitle">Create Account</h2>
        <p class="sub" data-i18n="signupSub">Join in a minute and start learning with the right track.</p>

        <?php if (!empty($error_message) && $open_signup): ?>
            <div class="server-error"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (!empty($register_errors)): ?>
            <div class="server-error">
                <ul style="margin:0;padding-inline-start:18px;">
                    <?php foreach ($register_errors as $err): ?>
                        <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="signupForm" action="register.php" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-group">
                <label for="signupFullName" data-i18n="fullName">Full Name *</label>
                <input type="text" name="fullname" id="signupFullName" required placeholder="John Doe" data-i18n-placeholder="fullNamePlaceholder"
                       value="<?php echo htmlspecialchars($old_input['fullname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="error-msg" id="signupNameError" data-i18n="nameError">Please enter your name.</div>
            </div>

            <div class="form-group">
                <label for="signupEmail" data-i18n="email">Email *</label>
                <input type="email" name="email" id="signupEmail" required placeholder="john@example.com" data-i18n-placeholder="emailPlaceholder"
                       value="<?php echo htmlspecialchars($old_input['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="error-msg" id="signupEmailError" data-i18n="emailError">Please enter a valid email.</div>
            </div>

            <div class="form-group">
                <label for="signupUsername" data-i18n="username">Username *</label>
                <input type="text" name="username" id="signupUsername" required placeholder="johndoe" data-i18n-placeholder="usernamePlaceholder"
                       value="<?php echo htmlspecialchars($old_input['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="error-msg" id="signupUsernameError" data-i18n="usernameError">Username is required.</div>
            </div>

            <div class="form-group">
                <label for="signupPassword" data-i18n="password">Password *</label>
                <input type="password" name="password" id="signupPassword" required placeholder="At least 8 characters" data-i18n-placeholder="passwordCreatePlaceholder">
                <div class="error-msg" id="signupPassError" data-i18n="passwordRule">Min 8 characters with uppercase, lowercase, number and special character.</div>
            </div>

            <div class="form-group">
                <label for="signupConfirmPassword" data-i18n="confirmPassword">Confirm Password *</label>
                <input type="password" name="confirm_password" id="signupConfirmPassword" required placeholder="Confirm your password" data-i18n-placeholder="confirmPasswordPlaceholder">
                <div class="error-msg" id="signupConfirmError" data-i18n="passwordMatchError">Passwords do not match.</div>
            </div>

            <div class="form-group">
                <label for="signupPhone" data-i18n="phone">Phone (optional)</label>
                <input type="tel" name="phone" id="signupPhone" placeholder="+1 234 567 890" data-i18n-placeholder="phonePlaceholder"
                       value="<?php echo htmlspecialchars($old_input['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="checkbox">
                <input type="checkbox" id="signupAgreeTerms" name="agree_terms" required>
                <label for="signupAgreeTerms">
                    <span data-i18n="agreePrefix">I have read and accept the</span>
                    <a href="rules.html" style="color:var(--gold);font-weight:700;text-decoration:underline" data-i18n="siteTerms">site terms</a>
                </label>
            </div>
            <div class="error-msg" id="signupTermsError" data-i18n="termsError" style="margin-top:-10px;margin-bottom:10px;">You must agree to the terms.</div>

            <div class="checkbox">
                <input type="checkbox" id="signupRememberMe" name="remember_me">
                <label for="signupRememberMe" data-i18n="rememberMe">Remember me</label>
            </div>

            <button type="submit" class="auth-btn" id="signupSubmitBtn" data-i18n="signupBtn">Sign up</button>
        </form>

        <div class="modal-footer">
            <span data-i18n="alreadyAccount">Already have an account?</span>
            <a href="#" id="switchToLogin" data-i18n="loginLink">Log in</a>
        </div>
    </div>
</div>

<div class="modal-overlay gate-modal" id="loginRequiredOverlay" aria-hidden="true">
    <div class="modal">
        <button class="close-btn" id="closeLoginRequiredBtn" aria-label="Close">×</button>
        <h2 data-i18n="gateTitleGeneric">Login Required</h2>
        <p class="gate-msg" data-i18n="gateMsgGeneric">Please log in to access this test.</p>
        <div class="gate-actions">
            <a href="#" class="btn btn-primary" id="gateLoginBtn" data-i18n="login">Login</a>
            <a href="#" class="btn btn-ghost" id="gateSignupBtn" data-i18n="signup">Sign up</a>
        </div>
    </div>
</div>

<header class="top-header">
    <!-- اصلاح ساختار برند: حذف استایل‌های درون‌خطی و استفاده از brand-copy -->
    <a class="brand" href="#home" aria-label="Pezhman Academy">
        <div class="brand-logo">
            <img src="images/logo.png" alt="Pezhman Academy logo">
        </div>
        <div class="brand-copy">
            <div class="brand-name">pezhman Academy</div>
            <div class="brand-tagline">where learning comes alive</div>
        </div>
    </a>

    <nav class="nav-center" aria-label="Main navigation">
        <div class="dropdown" id="mainDropdown">
            <button type="button" data-i18n="categories" id="dropdownToggle">Categories ▾</button>
            <div class="dropdown-panel" id="dropdownPanel">
                <div class="mega-grid">
                    <!-- Courses -->
                    <div class="mega-col">
                        <h4 data-i18n="cat_courses">Courses</h4>
                        <a href="general.html" data-i18n="cat_zero1">Zero to Hero (Level 1)</a>
                        <a href="general.html#level2" data-i18n="cat_zero2">Zero to Hero (Level 2)</a>
                        <a href="general.html#level3" data-i18n="cat_zero3">Zero to Hero (Level 3)</a>
                        <a href="medical.html" data-i18n="cat_medical_english">Medical English</a>
                    </div>
                    <!-- Tests -->
                    <div class="mega-col">
                        <h4 data-i18n="cat_tests">Tests</h4>
                        <a href="english-test.php" class="test-link" data-i18n="cat_placement">Placement Test</a>
                        <a href="grammar.html" class="test-link" data-i18n="cat_grammar">Grammar</a>
                        <a href="vocabulary.html" class="test-link" data-i18n="cat_vocab">Vocabulary</a>
                        <a href="rn-nursing.html" class="test-link" data-i18n="cat_rn_nursing">RN Nursing</a>
                    </div>
                    <!-- Other services -->
                    <div class="mega-col">
                        <h4 data-i18n="cat_games">Games</h4>
                        <a href="veil-speaking-game.php" data-i18n="cat_veil">Veil</a>
                        <a href="games.html" data-i18n="cat_more_games">More games</a>
                        <div style="margin-top:20px;">
                            <a href="online.html" class="single-link" data-i18n="cat_classes">Online Classes</a>
                        </div>
                        <div style="margin-top:10px;">
                            <a href="booklets.html" class="single-link" data-i18n="cat_booklets">Booklets & Samples</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a href="articles.php" data-i18n="articles">Articles</a>
        <a href="faq.html" data-i18n="faq">FAQ</a>
        <a href="about.html" data-i18n="about">About Us</a>
    </nav>

    <div class="nav-right">
        <div class="lang-wrap">
            <button class="lang-btn" id="langBtn" type="button" aria-haspopup="true" aria-expanded="false">
                🌐 <span id="langLabel">English</span> <span>▾</span>
            </button>
            <div class="lang-list" id="langList" role="menu" aria-label="Language selector">
                <button type="button" data-l="en">English</button>
                <button type="button" data-l="fa">فارسی</button>
                <button type="button" data-l="ar">العربية</button>
                <button type="button" data-l="hi">हिन्दी</button>
                <button type="button" data-l="fr">Français</button>
                <button type="button" data-l="es">Español</button>
                <button type="button" data-l="de">Deutsch</button>
                <button type="button" data-l="ko">한국어</button>
                <button type="button" data-l="zh">中文</button>
            </div>
        </div>

        <?php if ($isLoggedIn): ?>
            <span class="chip" style="padding:10px 14px;color:var(--gold);background:rgba(212,175,55,.1);border-color:rgba(212,175,55,.18)">👋 <?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></span>
            <a href="dashboard.php" class="btn btn-ghost">Dashboard</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        <?php elseif ($isPending): ?>
            <span class="chip" style="padding:10px 14px;color:#ffd978;background:rgba(247,201,72,.08);border-color:rgba(247,201,72,.15)">⏳ در انتظار تأیید ایمیل</span>
            <a href="verify-email.php" class="btn btn-ghost" style="border-color:rgba(247,201,72,.25);color:#ffd978">Verify Email</a>
            <a href="logout.php" class="btn btn-danger">Cancel</a>
        <?php else: ?>
            <a href="#" class="btn btn-ghost" id="loginLink" data-i18n="login">Login</a>
            <a href="#" class="btn btn-primary" id="signupLink" data-i18n="signup">Sign up</a>
        <?php endif; ?>
    </div>
</header>

<?php if (!empty($status_message)): ?>
    <div class="status-bar <?php echo htmlspecialchars($status_type, ENT_QUOTES, 'UTF-8'); ?>">
        <?php echo htmlspecialchars($status_message, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<section class="hero" id="home">
    <div class="hero-copy rv initial-visible">
        <h1>
            <span data-i18n="heroLine1">Learn English</span><br>
            <span class="accent" data-i18n="heroLine2">professionally</span><br>
            <span data-i18n="heroLine3">and practically.</span>
        </h1>
        <p data-i18n="heroSub">
            Get ready for conversation, exams, or immigration — with courses designed around your level, your goal, and your real-life schedule.
        </p>
        <div class="hero-actions">
            <a href="english-test.php" class="btn btn-primary test-link" id="placementBtn" data-i18n="ctaPrimary">Start free placement test</a>
            <a href="guide.html" class="btn btn-ghost" data-i18n="ctaSecondary">Site Guide</a>
        </div>

        <div class="hero-stats">
            <div class="stat"><b>4.9★</b><span data-i18n="stat4">Student rating</span></div>
        </div>
    </div>

    <div class="hero-visual rv initial-visible">
        <div class="hero-card-wrap" id="heroTilt">
            <span class="concentric one"></span>
            <span class="concentric two"></span>
            <span class="concentric three"></span>

            <div class="hero-card">
                <div class="logo-box">
                    <img src="images/logo.png" alt="Pezhman Academy Logo">
                </div>
                <div class="name">Pezhman Academy</div>
                <div class="tagline">where learning comes alive</div>
            </div>
        </div>
    </div>
</section>

<section class="section" id="tracks">
    <div class="section-head">
        <div>
            <div class="eyebrow" data-i18n="tracksEyebrow">Learning Tracks</div>
            <h2><span data-i18n="tracksTitle1">Pick the track that</span> <span class="accent" data-i18n="tracksTitle2">matches your goal</span></h2>
            <p class="lead" data-i18n="tracksLead">Every course is mapped to CEFR levels and built around real conversation, so progress feels measurable from week one.</p>
        </div>
        <a class="mini-link" href="compare.html" data-i18n="comparePrograms">Compare all programs →</a>
    </div>

    <div class="tracks-grid">
        <a class="track-card rv" href="general.html">
            <div>
                <div class="track-top">
                    <div class="track-ic">🗣️</div>
                    <div class="track-badge" data-i18n="badgeMostPopular">Most popular</div>
                </div>
                <div class="track-title" data-i18n="track1Title">General English</div>
                <p class="track-desc" data-i18n="track1Desc">Zero to Hero (3 levels) & Kid Lingo for fluent everyday conversation.</p>
            </div>
            <div class="track-foot"><span data-i18n="trackExplore">Explore track</span><span>↗</span></div>
        </a>

        <a class="track-card rv" href="medical.html">
            <div>
                <div class="track-top">
                    <div class="track-ic" style="background:rgba(0,229,255,.1);border-color:rgba(0,229,255,.18);color:var(--cyan)">🩺</div>
                    <div class="track-badge" style="color:#80f0ff" data-i18n="badgeSpecialized">Specialized</div>
                </div>
                <div class="track-title" data-i18n="track2Title">Medical English</div>
                <p class="track-desc" data-i18n="track2Desc">Specialized vocabulary and case studies for healthcare professionals.</p>
            </div>
            <div class="track-foot"><span data-i18n="trackExplore">Explore track</span><span>↗</span></div>
        </a>

        <a class="track-card rv" href="exam.html">
            <div>
                <div class="track-top">
                    <div class="track-ic" style="background:rgba(247,201,72,.1);border-color:rgba(247,201,72,.18);color:#ffd978">🎯</div>
                    <div class="track-badge" style="color:#ffd978" data-i18n="badgeGoal">Goal-oriented</div>
                </div>
                <div class="track-title" data-i18n="track3Title">Exam Prep</div>
                <p class="track-desc" data-i18n="track3Desc">IELTS, TOEFL, OET, MHLE, Konkur and RN preparation, with real mock tests.</p>
            </div>
            <div class="track-foot"><span data-i18n="trackExplore">Explore track</span><span>↗</span></div>
        </a>

        <a class="track-card rv" href="online.html">
            <div>
                <div class="track-top">
                    <div class="track-ic">💻</div>
                    <div class="track-badge" style="color:#5ee28b" data-i18n="badgeFlexible">Live & flexible</div>
                </div>
                <div class="track-title" data-i18n="track4Title">Online Classes</div>
                <p class="track-desc" data-i18n="track4Desc">Live sessions for General English, Free Discussion, OET and Writing.</p>
            </div>
            <div class="track-foot"><span data-i18n="trackExplore">Explore track</span><span>↗</span></div>
        </a>

        <a class="track-card rv" href="games.html">
            <div>
                <div class="track-top">
                    <div class="track-ic" style="background:rgba(247,201,72,.1);border-color:rgba(247,201,72,.18);color:#ffd978">🎮</div>
                    <div class="track-badge" style="color:#ffd978" data-i18n="badgeFun">Fun first</div>
                </div>
                <div class="track-title" data-i18n="track5Title">Learning Games</div>
                <p class="track-desc" data-i18n="track5Desc">Master grammar and vocabulary through fun games and weekly challenges.</p>
            </div>
            <div class="track-foot"><span data-i18n="trackExplore">Explore track</span><span>↗</span></div>
        </a>

        <a class="track-card rv test-link" href="english-test.php">
            <div>
                <div class="track-top">
                    <div class="track-ic" style="background:rgba(0,229,255,.1);border-color:rgba(0,229,255,.18);color:var(--cyan)">📝</div>
                    <div class="track-badge" style="color:#80f0ff" data-i18n="badgeStartHere">Start here</div>
                </div>
                <div class="track-title" data-i18n="track6Title">Placement Test</div>
                <p class="track-desc" data-i18n="track6Desc">Three free tests for kids, teens and adults to find your exact level.</p>
            </div>
            <div class="track-foot"><span data-i18n="trackExplore">Explore track</span><span>↗</span></div>
        </a>
    </div>
</section>

<section class="section" id="why">
    <div class="section-head">
        <div>
            <div class="eyebrow" data-i18n="whyEyebrow">Why Pezhman Academy</div>
            <h2><span data-i18n="whyTitle1">A program that actually</span> <span class="accent" data-i18n="whyTitle2">makes you fluent.</span></h2>
            <p class="lead" data-i18n="whyLead">Taught by Pezhman Shafiei and a team of certified instructors with 12+ years of experience preparing students for global exams and immigration.</p>
        </div>
    </div>

    <div class="why-grid">
        <div class="why-copy rv">
            <div class="avatar-row">
                <div class="av"><img src="https://i.pravatar.cc/80?img=12" alt=""></div>
                <div class="av"><img src="https://i.pravatar.cc/80?img=32" alt=""></div>
                <div class="av"><img src="https://i.pravatar.cc/80?img=45" alt=""></div>
                <div class="av"><img src="https://i.pravatar.cc/80?img=21" alt=""></div>
                <div style="margin-inline-start:10px">
                    <strong>+250 active students</strong>
                    <small data-i18n="whyStudents">across 14 countries</small>
                </div>
            </div>
            <p data-i18n="whyCopy">
                Every lesson is designed to be practical, measurable and conversation-driven. You study with a clear path, get useful feedback, and move forward without wasting time.
            </p>
        </div>

        <div class="benefits">
            <div class="benefit rv">
                <div class="ic">🎓</div>
                <h3 data-i18n="benefit1Title">Level-based curriculum</h3>
                <p data-i18n="benefit1Desc">Every course is designed around CEFR levels so you progress with clarity.</p>
            </div>

            <div class="benefit rv">
                <div class="ic">🗣️</div>
                <h3 data-i18n="benefit2Title">Real conversation focus</h3>
                <p data-i18n="benefit2Desc">Speaking labs and discussion clubs that build fluent, natural communication.</p>
            </div>

            <div class="benefit rv">
                <div class="ic">🏅</div>
                <h3 data-i18n="benefit3Title">Internationally accepted exams</h3>
                <p data-i18n="benefit3Desc">Targeted prep for IELTS, TOEFL, OET, MHLE — scored by certified examiners.</p>
            </div>

            <div class="benefit rv">
                <div class="ic">⏰</div>
                <h3 data-i18n="benefit4Title">Flexible schedules</h3>
                <p data-i18n="benefit4Desc">Pick online, in-person, or hybrid. Morning, evening and weekend slots available.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="placement">
    <div class="test-wrap rv">
        <div class="test-grid">
            <div class="test-copy">
                <div class="kicker">⚡ <span data-i18n="testKicker">Free · 20 minutes</span></div>
                <h2><span data-i18n="testTitle1">Don't guess your level.</span><br><span class="accent" data-i18n="testTitle2">Test it in 3 steps.</span></h2>
                <p data-i18n="testLead">Our placement test is built and reviewed by certified examiners, so the result you get is the level you'll actually study at — no time wasted.</p>
                <div class="hero-actions" style="margin-top:26px">
                    <a href="english-test.php" class="btn btn-primary test-link" id="placementBtn2" data-i18n="testBtn">Start free test</a>
                    <a href="how-it-works.html" class="btn btn-ghost" data-i18n="howItWorks">How it works</a>
                </div>
            </div>

            <div class="steps">
                <div class="step">
                    <div class="step-num">01</div>
                    <div>
                        <h4 data-i18n="step1Title">Take the test</h4>
                        <p data-i18n="step1Desc">20 quick questions covering grammar, vocabulary and comprehension.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">02</div>
                    <div>
                        <h4 data-i18n="step2Title">Get your level</h4>
                        <p data-i18n="step2Desc">We map your result to CEFR (A1 → C2) and recommend the right track.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">03</div>
                    <div>
                        <h4 data-i18n="step3Title">Start learning</h4>
                        <p data-i18n="step3Desc">Join your matched course online or in-person, with a 7-day trial.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<footer>
    <img src="images/logo.png" alt="Pezhman Academy">
    <p><strong>Pezhman Academy</strong> — <span data-i18n="footerLine">where learning comes alive</span></p>
    <small data-i18n="footerSub">Taught by Pezhman Shafiei</small>
</footer>

<script>
(function(){
    'use strict';

    const isLoggedInPHP = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

    const T = {
        en: {
            dir:'ltr', label:'English',
            login:'Login', signup:'Sign up', categories:'Categories ▾', articles:'Articles', faq:'FAQ', about:'About Us',
            badge:'Language learning, done professionally',
            heroLine1:'Learn English', heroLine2:'professionally', heroLine3:'and practically.',
            heroSub:'Get ready for conversation, exams, or immigration — with courses designed around your level, your goal, and your real-life schedule.',
            ctaPrimary:'Start free placement test', ctaSecondary:'Site Guide',
            stat1:'Courses', stat2:'Online & In-person', stat3:'Languages', stat4:'Student rating',
            badgeTop:'Avg. top score', badgeBottom:'since 2017',
            tracksEyebrow:'Learning Tracks', tracksTitle1:'Pick the track that', tracksTitle2:'matches your goal',
            tracksLead:'Every course is mapped to CEFR levels and built around real conversation, so progress feels measurable from week one.',
            comparePrograms:'Compare all programs →', badgeMostPopular:'Most popular', badgeSpecialized:'Specialized', badgeGoal:'Goal-oriented', badgeFlexible:'Live & flexible', badgeFun:'Fun first', badgeStartHere:'Start here',
            track1Title:'General English', track1Desc:'Zero to Hero (3 levels) & Kid Lingo for fluent everyday conversation.',
            track2Title:'Medical English', track2Desc:'Specialized vocabulary and case studies for healthcare professionals.',
            track3Title:'Exam Prep', track3Desc:'IELTS, TOEFL, OET, MHLE, Konkur and RN preparation, with real mock tests.',
            track4Title:'Online Classes', track4Desc:'Live sessions for General English, Free Discussion, OET and Writing.',
            track5Title:'Learning Games', track5Desc:'Master grammar and vocabulary through fun games and weekly challenges.',
            track6Title:'Placement Test', track6Desc:'Three free tests for kids, teens and adults to find your exact level.',
            trackExplore:'Explore track',
            whyEyebrow:'Why Pezhman Academy', whyTitle1:'A program that actually', whyTitle2:'makes you fluent.',
            whyLead:'Taught by Pezhman Shafiei and a team of certified instructors with 12+ years of experience preparing students for global exams and immigration.',
            whyStudents:'across 14 countries', whyCopy:'Every lesson is designed to be practical, measurable and conversation-driven. You study with a clear path, get useful feedback, and move forward without wasting time.',
            benefit1Title:'Level-based curriculum', benefit1Desc:'Every course is designed around CEFR levels so you progress with clarity.',
            benefit2Title:'Real conversation focus', benefit2Desc:'Speaking labs and discussion clubs that build fluent, natural communication.',
            benefit3Title:'Internationally accepted exams', benefit3Desc:'Targeted prep for IELTS, TOEFL, OET, MHLE — scored by certified examiners.',
            benefit4Title:'Flexible schedules', benefit4Desc:'Pick online, in-person, or hybrid. Morning, evening and weekend slots available.',
            testKicker:'Free · 20 minutes', testTitle1:"Don't guess your level.", testTitle2:'Test it in 3 steps.',
            testLead:"Our placement test is built and reviewed by certified examiners, so the result you get is the level you'll actually study at — no time wasted.",
            testBtn:'Start free test', howItWorks:'How it works',
            step1Title:'Take the test', step1Desc:'20 quick questions covering grammar, vocabulary and comprehension.',
            step2Title:'Get your level', step2Desc:'We map your result to CEFR (A1 → C2) and recommend the right track.',
            step3Title:'Start learning', step3Desc:'Join your matched course online or in-person, with a 7-day trial.',
            footerLine:'where learning comes alive', footerSub:'Taught by Pezhman Shafiei',
            loginTitle:'Welcome Back', loginSub:'Log in to access your dashboard and placement test.',
            email:'Email *', password:'Password *', emailPlaceholder:'john@example.com', passwordPlaceholder:'••••••••',
            emailError:'Please enter a valid email.', passwordError:'Password cannot be empty.',
            agreePrefix:'I have read and accept the', siteTerms:'site terms', rememberMe:'Remember me',
            loginBtn:'Log in', noAccount:"Don't have an account?", signupLink:'Sign up',
            signupTitle:'Create Account', signupSub:'Join in a minute and start learning with the right track.',
            fullName:'Full Name *', fullNamePlaceholder:'John Doe', nameError:'Please enter your name.',
            username:'Username *', usernamePlaceholder:'johndoe', usernameError:'Username is required.',
            confirmPassword:'Confirm Password *', confirmPasswordPlaceholder:'Confirm your password',
            passwordCreatePlaceholder:'At least 8 characters', passwordRule:'Min 8 characters with uppercase, lowercase, number and special character.',
            passwordMatchError:'Passwords do not match.', phone:'Phone (optional)', phonePlaceholder:'+1 234 567 890',
            signupBtn:'Sign up', alreadyAccount:'Already have an account?', loginLink:'Log in',
            termsError:'You must agree to the site terms.',
            gateTitleGeneric:'Login Required', gateMsgGeneric:'Please log in to access this test.',
            cat_courses:'Courses', cat_zero1:'Zero to Hero (Level 1)', cat_zero2:'Zero to Hero (Level 2)', cat_zero3:'Zero to Hero (Level 3)', cat_medical_english:'Medical English',
            cat_tests:'Tests', cat_placement:'Placement Test', cat_grammar:'Grammar', cat_vocab:'Vocabulary', cat_rn_nursing:'RN Nursing',
            cat_games:'Games', cat_veil:'Veil', cat_more_games:'More games', cat_classes:'Online Classes', cat_booklets:'Booklets & Samples'
        },
        fa: {
            dir:'rtl', label:'فارسی',
            login:'ورود', signup:'ثبت نام', categories:'دسته‌بندی‌ها ▾', articles:'مقالات', faq:'سوالات رایج', about:'درباره ما',
            badge:'آموزش زبان، حرفه‌ای انجام می‌شود',
            heroLine1:'انگلیسی را', heroLine2:'حرفه‌ای', heroLine3:'و کاربردی یاد بگیر.',
            heroSub:'برای مکالمه، آزمون یا مهاجرت آماده شو؛ با دوره‌هایی که بر اساس سطح، هدف و برنامه‌ی واقعی تو طراحی شده‌اند.',
            ctaPrimary:'شروع تعیین سطح رایگان', ctaSecondary:'راهنمای سایت',
            stat1:'دوره', stat2:'آنلاین و حضوری', stat3:'زبان', stat4:'امتیاز دانشجو',
            badgeTop:'میانگین نمره برتر', badgeBottom:'از 2017',
            tracksEyebrow:'مسیرهای یادگیری', tracksTitle1:'مسیری را انتخاب کن که', tracksTitle2:'با هدفت هماهنگ است',
            tracksLead:'تمام دوره‌ها بر اساس CEFR طراحی شده‌اند و بر مکالمه‌ی واقعی تکیه دارند تا از هفته‌ی اول پیشرفت قابل اندازه‌گیری باشد.',
            comparePrograms:'مقایسه همه برنامه‌ها →', badgeMostPopular:'محبوب‌ترین', badgeSpecialized:'تخصصی', badgeGoal:'هدف‌محور', badgeFlexible:'زنده و منعطف', badgeFun:'اول سرگرمی', badgeStartHere:'از اینجا شروع کن',
            track1Title:'انگلیسی عمومی', track1Desc:'صفر تا قهرمان (۳ سطح) و کودک لینگو برای مکالمه روزمره.',
            track2Title:'انگلیسی پزشکی', track2Desc:'واژگان تخصصی و کیس‌استادی برای کادر درمان.',
            track3Title:'آمادگی آزمون', track3Desc:'IELTS، TOEFL، OET، MHLE، کنکور و RN با آزمون‌های شبیه‌ساز.',
            track4Title:'کلاس‌های آنلاین', track4Desc:'جلسات زنده برای انگلیسی عمومی، بحث آزاد، OET و رایتینگ.',
            track5Title:'بازی‌های آموزشی', track5Desc:'گرامر و لغت را با بازی‌های جذاب و چالش‌های هفتگی یاد بگیر.',
            track6Title:'آزمون تعیین سطح', track6Desc:'سه آزمون رایگان برای کودکان، نوجوانان و بزرگسالان.',
            trackExplore:'مشاهده مسیر',
            whyEyebrow:'چرا پژمان آکادمی', whyTitle1:'برنامه‌ای که واقعاً', whyTitle2:'تو را روان می‌کند.',
            whyLead:'تدریس توسط پژمان شفیعی و تیمی از اساتید دارای گواهی با ۱۲+ سال تجربه در آماده‌سازی برای آزمون‌ها و مهاجرت.',
            whyStudents:'در ۱۴ کشور', whyCopy:'هر درس کاربردی، قابل‌سنجش و مکالمه‌محور طراحی شده است تا بدون اتلاف وقت با مسیر روشن و بازخورد مفید جلو بروی.',
            benefit1Title:'برنامه بر اساس سطح', benefit1Desc:'تمام دوره‌ها بر اساس CEFR طراحی شده‌اند تا روند پیشرفت شفاف باشد.',
            benefit2Title:'تمرکز بر مکالمه واقعی', benefit2Desc:'کلاس‌های گفت‌وگو و speaking lab برای ارتباط روان و طبیعی.',
            benefit3Title:'آزمون‌های بین‌المللی', benefit3Desc:'آمادگی هدفمند برای IELTS، TOEFL، OET و MHLE با ارزیابی حرفه‌ای.',
            benefit4Title:'برنامه منعطف', benefit4Desc:'آنلاین، حضوری یا ترکیبی. زمان‌های صبح، عصر و آخرهفته موجود است.',
            testKicker:'رایگان · ۲۰ دقیقه', testTitle1:'سطحت را حدس نزن.', testTitle2:'در ۳ مرحله تست کن.',
            testLead:'آزمون تعیین سطح ما توسط داوران حرفه‌ای طراحی و بازبینی شده تا نتیجه‌ای بگیری که واقعاً با همان سطح درس بخوانی.',
            testBtn:'شروع تست رایگان', howItWorks:'نحوه کار',
            step1Title:'آزمون را بده', step1Desc:'۲۰ سؤال کوتاه درباره گرامر، واژگان و درک مطلب.',
            step2Title:'سطحت را بگیر', step2Desc:'نتیجه به CEFR (A1 تا C2) نگاشت می‌شود و مسیر مناسب پیشنهاد می‌گردد.',
            step3Title:'یادگیری را شروع کن', step3Desc:'به دوره‌ی متناسب خود آنلاین یا حضوری بپیوند، با ۷ روز آزمون.',
            footerLine:'جایی که یادگیری جان می‌گیرد', footerSub:'تدریس توسط پژمان شفیعی',
            loginTitle:'خوش آمدید', loginSub:'برای ورود به داشبورد و آزمون تعیین سطح وارد شوید.',
            email:'ایمیل *', password:'رمز عبور *', emailPlaceholder:'john@example.com', passwordPlaceholder:'••••••••',
            emailError:'لطفاً یک ایمیل معتبر وارد کنید.', passwordError:'رمز عبور نباید خالی باشد.',
            agreePrefix:'من خوانده‌ام و می‌پذیرم', siteTerms:'قوانین سایت', rememberMe:'مرا به خاطر بسپار',
            loginBtn:'ورود', noAccount:'حساب ندارید؟', signupLink:'ثبت نام',
            signupTitle:'ایجاد حساب', signupSub:'در یک دقیقه عضو شوید و با مسیر درست شروع کنید.',
            fullName:'نام کامل *', fullNamePlaceholder:'John Doe', nameError:'لطفاً نام خود را وارد کنید.',
            username:'نام کاربری *', usernamePlaceholder:'johndoe', usernameError:'نام کاربری الزامی است.',
            confirmPassword:'تأیید رمز عبور *', confirmPasswordPlaceholder:'رمز عبور را تکرار کنید',
            passwordCreatePlaceholder:'حداقل ۸ کاراکتر', passwordRule:'حداقل ۸ کاراکتر شامل حروف بزرگ، کوچک، عدد و کاراکتر ویژه.',
            passwordMatchError:'رمزها با هم مطابقت ندارند.', phone:'تلفن (اختیاری)', phonePlaceholder:'+1 234 567 890',
            signupBtn:'ثبت نام', alreadyAccount:'حساب کاربری دارید؟', loginLink:'ورود',
            termsError:'شما باید قوانین سایت را بپذیرید.',
            gateTitleGeneric:'نیاز به ورود', gateMsgGeneric:'لطفاً برای دسترسی به این آزمون وارد شوید.',
            cat_courses:'دوره‌ها', cat_zero1:'صفر تا قهرمان (سطح ۱)', cat_zero2:'صفر تا قهرمان (سطح ۲)', cat_zero3:'صفر تا قهرمان (سطح ۳)', cat_medical_english:'انگلیسی پزشکی',
            cat_tests:'آزمون‌ها', cat_placement:'آزمون تعیین سطح', cat_grammar:'گرامر', cat_vocab:'واژگان', cat_rn_nursing:'پرستاری RN',
            cat_games:'بازی‌ها', cat_veil:'Veil', cat_more_games:'بازی‌های بیشتر', cat_classes:'کلاس‌های آنلاین', cat_booklets:'جزوات و نمونه سوال'
        },
        ar: {
            dir:'rtl', label:'العربية',
            login:'تسجيل الدخول', signup:'إنشاء حساب', categories:'التصنيفات ▾', articles:'المقالات', faq:'الأسئلة الشائعة', about:'من نحن',
            badge:'تعلّم اللغة بطريقة احترافية',
            heroLine1:'تعلم الإنجليزية', heroLine2:'بشكل احترافي', heroLine3:'وعملي.',
            heroSub:'استعد للمحادثة أو الاختبارات أو الهجرة — مع دورات مبنية على مستواك وهدفك وجدولك الحقيقي.',
            ctaPrimary:'ابدأ اختبار المستوى المجاني', ctaSecondary:'دليل الموقع',
            stat1:'دورة', stat2:'أونلاين وحضوري', stat3:'لغات', stat4:'تقييم الطلاب',
            badgeTop:'متوسط أعلى نتيجة', badgeBottom:'منذ 2017',
            tracksEyebrow:'مسارات التعلّم', tracksTitle1:'اختر المسار الذي', tracksTitle2:'يناسب هدفك',
            tracksLead:'تم ربط كل دورة بمستويات CEFR وبُنيت حول المحادثة الواقعية لتشعر بالتقدم من الأسبوع الأول.',
            comparePrograms:'قارن جميع البرامج →', badgeMostPopular:'الأكثر شيوعًا', badgeSpecialized:'متخصص', badgeGoal:'موجه للهدف', badgeFlexible:'مباشر ومرن', badgeFun:'الأكثر متعة', badgeStartHere:'ابدأ هنا',
            track1Title:'الإنجليزية العامة', track1Desc:'Zero to Hero (3 مستويات) و Kid Lingo للمحادثة اليومية.',
            track2Title:'الإنجليزية الطبية', track2Desc:'مصطلحات متخصصة ودراسات حالة للعاملين في المجال الصحي.',
            track3Title:'التحضير للاختبارات', track3Desc:'IELTS و TOEFL و OET و MHLE و Konkur و RN مع اختبارات تجريبية.',
            track4Title:'الدروس عبر الإنترنت', track4Desc:'جلسات مباشرة للإنجليزية العامة والنقاش الحر و OET والكتابة.',
            track5Title:'ألعاب التعلّم', track5Desc:'أتقن القواعد والمفردات عبر ألعاب ممتعة وتحديات أسبوعية.',
            track6Title:'اختبار تحديد المستوى', track6Desc:'ثلاثة اختبارات مجانية للأطفال والمراهقين والبالغين.',
            trackExplore:'استكشاف المسار',
            whyEyebrow:'لماذا Pezhman Academy', whyTitle1:'برنامج فعلاً', whyTitle2:'يجعلك تتحدث بطلاقة.',
            whyLead:'يُدرّس بواسطة پژمان شفيعي وفريق من المدرسين المعتمدين بخبرة تزيد عن 12 سنة في إعداد الطلاب للاختبارات والهجرة.',
            whyStudents:'في 14 دولة', whyCopy:'كل درس مصمم ليكون عمليًا وقابلًا للقياس ويعتمد على المحادثة، لتتقدم بخطة واضحة وملاحظات مفيدة دون إضاعة الوقت.',
            benefit1Title:'منهج حسب المستوى', benefit1Desc:'كل دورة مبنية على مستويات CEFR لضمان وضوح التقدم.',
            benefit2Title:'تركيز على المحادثة', benefit2Desc:'مختبرات التحدث ونوادي النقاش لبناء تواصل طبيعي وطليق.',
            benefit3Title:'اختبارات معترف بها دوليًا', benefit3Desc:'تحضير مخصص لـ IELTS و TOEFL و OET و MHLE مع تقييم احترافي.',
            benefit4Title:'جداول مرنة', benefit4Desc:'اختر أونلاين أو حضوريًا أو هجينًا. متاح صباحًا ومساءً وعطلة الأسبوع.',
            testKicker:'مجاني · 20 دقيقة', testTitle1:'لا تخمّن مستواك.', testTitle2:'اختبره في 3 خطوات.',
            testLead:'اختبار تحديد المستوى لدينا مصمم ومراجع من قبل ممتحنين معتمدين، بحيث تحصل على المستوى الذي ستدرس به فعلاً.',
            testBtn:'ابدأ الاختبار المجاني', howItWorks:'كيف يعمل',
            step1Title:'أجرِ الاختبار', step1Desc:'20 سؤالًا سريعًا تغطي القواعد والمفردات والفهم.',
            step2Title:'احصل على مستواك', step2Desc:'نطابق النتيجة مع CEFR (من A1 إلى C2) ونقترح المسار المناسب.',
            step3Title:'ابدأ التعلّم', step3Desc:'انضم إلى المسار المناسب لك أونلاين أو حضوريًا مع تجربة 7 أيام.',
            footerLine:'حيث يصبح التعلّم حيًا', footerSub:'يُدرّس بواسطة پژمان شفيعي',
            loginTitle:'مرحبًا بعودتك', loginSub:'سجّل الدخول للوصول إلى لوحة التحكم واختبار المستوى.',
            email:'البريد الإلكتروني *', password:'كلمة المرور *', emailPlaceholder:'john@example.com', passwordPlaceholder:'••••••••',
            emailError:'يرجى إدخال بريد إلكتروني صحيح.', passwordError:'لا يمكن أن تكون كلمة المرور فارغة.',
            agreePrefix:'لقد قرأت وأوافق على', siteTerms:'شروط الموقع', rememberMe:'تذكرني',
            loginBtn:'تسجيل الدخول', noAccount:'ليس لديك حساب؟', signupLink:'إنشاء حساب',
            signupTitle:'إنشاء حساب', signupSub:'انضم خلال دقيقة وابدأ من المسار الصحيح.',
            fullName:'الاسم الكامل *', fullNamePlaceholder:'John Doe', nameError:'يرجى إدخال اسمك.',
            username:'اسم المستخدم *', usernamePlaceholder:'johndoe', usernameError:'اسم المستخدم مطلوب.',
            confirmPassword:'تأكيد كلمة المرور *', confirmPasswordPlaceholder:'أعد إدخال كلمة المرور',
            passwordCreatePlaceholder:'8 أحرف على الأقل', passwordRule:'8 أحرف على الأقل مع حرف كبير وصغير ورقم ورمز خاص.',
            passwordMatchError:'كلمتا المرور غير متطابقتين.', phone:'الهاتف (اختياري)', phonePlaceholder:'+1 234 567 890',
            signupBtn:'إنشاء حساب', alreadyAccount:'لديك حساب بالفعل؟', loginLink:'تسجيل الدخول',
            termsError:'يجب الموافقة على شروط الموقع.',
            gateTitleGeneric:'تسجيل الدخول مطلوب', gateMsgGeneric:'يرجى تسجيل الدخول للوصول إلى هذا الاختبار.',
            cat_courses:'الدورات', cat_zero1:'Zero to Hero (المستوى 1)', cat_zero2:'Zero to Hero (المستوى 2)', cat_zero3:'Zero to Hero (المستوى 3)', cat_medical_english:'الإنجليزية الطبية',
            cat_tests:'الاختبارات', cat_placement:'اختبار تحديد المستوى', cat_grammar:'القواعد', cat_vocab:'المفردات', cat_rn_nursing:'تمريض RN',
            cat_games:'الألعاب', cat_veil:'Veil', cat_more_games:'المزيد من الألعاب', cat_classes:'الدروس عبر الإنترنت', cat_booklets:'الكتيبات والنماذج'
        },
        hi: { dir:'ltr', label:'हिन्दी',
            login:'Login', signup:'Sign up', categories:'Categories ▾', articles:'Articles', faq:'FAQ', about:'About Us',
            badge:'Language learning, done professionally', heroLine1:'Learn English', heroLine2:'professionally', heroLine3:'and practically.',
            heroSub:'Get ready for conversation, exams, or immigration — with courses designed around your level, your goal, and your real-life schedule.',
            ctaPrimary:'Start free placement test', ctaSecondary:'Site Guide', stat1:'Courses', stat2:'Online & In-person', stat3:'Languages', stat4:'Student rating',
            badgeTop:'Avg. top score', badgeBottom:'since 2017', tracksEyebrow:'Learning Tracks', tracksTitle1:'Pick the track that', tracksTitle2:'matches your goal',
            tracksLead:'Every course is mapped to CEFR levels and built around real conversation, so progress feels measurable from week one.',
            comparePrograms:'Compare all programs →', badgeMostPopular:'Most popular', badgeSpecialized:'Specialized', badgeGoal:'Goal-oriented', badgeFlexible:'Live & flexible', badgeFun:'Fun first', badgeStartHere:'Start here',
            track1Title:'General English', track1Desc:'Zero to Hero (3 levels) & Kid Lingo for fluent everyday conversation.',
            track2Title:'Medical English', track2Desc:'Specialized vocabulary and case studies for healthcare professionals.',
            track3Title:'Exam Prep', track3Desc:'IELTS, TOEFL, OET, MHLE, Konkur and RN preparation, with real mock tests.',
            track4Title:'Online Classes', track4Desc:'Live sessions for General English, Free Discussion, OET and Writing.',
            track5Title:'Learning Games', track5Desc:'Master grammar and vocabulary through fun games and weekly challenges.',
            track6Title:'Placement Test', track6Desc:'Three free tests for kids, teens and adults to find your exact level.', trackExplore:'Explore track',
            whyEyebrow:'Why Pezhman Academy', whyTitle1:'A program that actually', whyTitle2:'makes you fluent.', whyLead:'Taught by Pezhman Shafiei and a team of certified instructors with 12+ years of experience preparing students for global exams and immigration.',
            whyStudents:'across 14 countries', whyCopy:'Every lesson is designed to be practical, measurable and conversation-driven. You study with a clear path, get useful feedback, and move forward without wasting time.',
            benefit1Title:'Level-based curriculum', benefit1Desc:'Every course is designed around CEFR levels so you progress with clarity.',
            benefit2Title:'Real conversation focus', benefit2Desc:'Speaking labs and discussion clubs that build fluent, natural communication.',
            benefit3Title:'Internationally accepted exams', benefit3Desc:'Targeted prep for IELTS, TOEFL, OET, MHLE — scored by certified examiners.',
            benefit4Title:'Flexible schedules', benefit4Desc:'Pick online, in-person, or hybrid. Morning, evening and weekend slots available.',
            testKicker:'Free · 20 minutes', testTitle1:"Don't guess your level.", testTitle2:'Test it in 3 steps.', testLead:"Our placement test is built and reviewed by certified examiners, so the result you get is the level you'll actually study at — no time wasted.", testBtn:'Start free test', howItWorks:'How it works',
            step1Title:'Take the test', step1Desc:'20 quick questions covering grammar, vocabulary and comprehension.', step2Title:'Get your level', step2Desc:'We map your result to CEFR (A1 → C2) and recommend the right track.', step3Title:'Start learning', step3Desc:'Join your matched course online or in-person, with a 7-day trial.', footerLine:'where learning comes alive', footerSub:'Taught by Pezhman Shafiei',
            loginTitle:'Welcome Back', loginSub:'Log in to access your dashboard and placement test.',
            email:'Email *', password:'Password *', emailPlaceholder:'john@example.com', passwordPlaceholder:'••••••••', emailError:'Please enter a valid email.', passwordError:'Password cannot be empty.',
            agreePrefix:'I have read and accept the', siteTerms:'site terms', rememberMe:'Remember me',
            loginBtn:'Log in', noAccount:"Don't have an account?", signupLink:'Sign up',
            signupTitle:'Create Account', signupSub:'Join in a minute and start learning with the right track.', fullName:'Full Name *', fullNamePlaceholder:'John Doe', nameError:'Please enter your name.',
            username:'Username *', usernamePlaceholder:'johndoe', usernameError:'Username is required.', confirmPassword:'Confirm Password *', confirmPasswordPlaceholder:'Confirm your password',
            passwordCreatePlaceholder:'At least 8 characters', passwordRule:'Min 8 characters with uppercase, lowercase, number and special character.', passwordMatchError:'Passwords do not match.', phone:'Phone (optional)', phonePlaceholder:'+1 234 567 890',
            signupBtn:'Sign up', alreadyAccount:'Already have an account?', loginLink:'Log in',
            termsError:'You must agree to the site terms.', gateTitleGeneric:'Login Required', gateMsgGeneric:'Please log in to access this test.',
            cat_courses:'Courses', cat_zero1:'Zero to Hero (Level 1)', cat_zero2:'Zero to Hero (Level 2)', cat_zero3:'Zero to Hero (Level 3)', cat_medical_english:'Medical English', cat_tests:'Tests',
            cat_placement:'Placement Test', cat_grammar:'Grammar', cat_vocab:'Vocabulary', cat_rn_nursing:'RN Nursing', cat_games:'Games', cat_veil:'Veil', cat_more_games:'More games', cat_classes:'Online Classes', cat_booklets:'Booklets & Samples'
        },
        fr: { dir:'ltr', label:'Français', login:'Connexion', signup:'S’inscrire', categories:'Catégories ▾', articles:'Articles', faq:'FAQ', about:'À propos',
            badge:'Apprendre une langue, de façon professionnelle', heroLine1:'Apprenez l’anglais', heroLine2:'professionnellement', heroLine3:'et concrètement.',
            heroSub:'Préparez-vous à la conversation, aux examens ou à l’immigration — avec des cours conçus selon votre niveau, votre objectif et votre emploi du temps.',
            ctaPrimary:'Commencer le test de niveau', ctaSecondary:'Guide du site', stat1:'Cours', stat2:'En ligne & Présentiel', stat3:'Langues', stat4:'Note des étudiants',
            badgeTop:'Meilleur score moyen', badgeBottom:'depuis 2017', tracksEyebrow:'Parcours d’apprentissage', tracksTitle1:'Choisissez le parcours', tracksTitle2:'qui correspond à votre objectif',
            tracksLead:'Chaque cours est aligné sur les niveaux CECR et centré sur la conversation réelle pour des progrès mesurables dès la première semaine.',
            comparePrograms:'Comparer tous les programmes →', badgeMostPopular:'Le plus populaire', badgeSpecialized:'Spécialisé', badgeGoal:'Orienté objectif', badgeFlexible:'En direct & flexible', badgeFun:'Le plaisir d’abord', badgeStartHere:'Commencer ici',
            track1Title:'Anglais général', track1Desc:'Zero to Hero (3 niveaux) & Kid Lingo pour une conversation quotidienne fluide.',
            track2Title:'Anglais médical', track2Desc:'Vocabulaire spécialisé et études de cas pour les professionnels de santé.',
            track3Title:'Préparation aux examens', track3Desc:'IELTS, TOEFL, OET, MHLE, Konkur et RN avec de vrais tests blancs.',
            track4Title:'Cours en ligne', track4Desc:'Sessions en direct pour Anglais général, discussion libre, OET et rédaction.',
            track5Title:'Jeux d’apprentissage', track5Desc:'Maîtrisez la grammaire et le vocabulaire avec des jeux amusants et des défis hebdomadaires.',
            track6Title:'Test de niveau', track6Desc:'Trois tests gratuits pour enfants, ados et adultes.', trackExplore:'Explorer le parcours',
            whyEyebrow:'Pourquoi Pezhman Academy', whyTitle1:'Un programme qui vous', whyTitle2:'rend vraiment fluent.', whyLead:'Enseigné par Pezhman Shafiei et une équipe d’instructeurs certifiés avec plus de 12 ans d’expérience.',
            whyStudents:'dans 14 pays', whyCopy:'Chaque leçon est pratique, mesurable et centrée sur la conversation. Vous avancez avec un plan clair et des retours utiles.',
            benefit1Title:'Programme par niveau', benefit1Desc:'Chaque cours est conçu autour des niveaux CECR pour une progression claire.', benefit2Title:'Conversation réelle', benefit2Desc:'Des ateliers d’oral et clubs de discussion pour une communication naturelle.', benefit3Title:'Examens internationaux', benefit3Desc:'Préparation ciblée pour IELTS, TOEFL, OET, MHLE avec correction certifiée.', benefit4Title:'Horaires flexibles', benefit4Desc:'En ligne, en présentiel ou hybride. Créneaux matin, soir et week-end.',
            testKicker:'Gratuit · 20 minutes', testTitle1:'Ne devinez pas votre niveau.', testTitle2:'Testez-le en 3 étapes.', testLead:'Notre test de placement est conçu et revu par des examinateurs certifiés.', testBtn:'Commencer le test gratuit', howItWorks:'Comment ça marche',
            step1Title:'Passez le test', step1Desc:'20 questions rapides sur la grammaire, le vocabulaire et la compréhension.', step2Title:'Obtenez votre niveau', step2Desc:'Nous cartographions votre résultat selon le CECR (A1 → C2).', step3Title:'Commencez à apprendre', step3Desc:'Rejoignez le cours correspondant en ligne ou sur place.', footerLine:'où l’apprentissage prend vie', footerSub:'Enseigné par Pezhman Shafiei',
            loginTitle:'Bon retour', loginSub:'Connectez-vous pour accéder à votre tableau de bord et au test de niveau.',
            email:'E-mail *', password:'Mot de passe *', emailPlaceholder:'john@example.com', passwordPlaceholder:'••••••••', emailError:'Veuillez saisir un e-mail valide.', passwordError:'Le mot de passe ne peut pas être vide.', agreePrefix:'J’ai lu et j’accepte les', siteTerms:'conditions du site', rememberMe:'Se souvenir de moi',
            loginBtn:'Connexion', noAccount:"Vous n’avez pas de compte ?", signupLink:'S’inscrire',
            signupTitle:'Créer un compte', signupSub:'Inscrivez-vous en une minute et commencez au bon niveau.', fullName:'Nom complet *', fullNamePlaceholder:'John Doe', nameError:'Veuillez saisir votre nom.', username:'Nom d’utilisateur *', usernamePlaceholder:'johndoe', usernameError:'Le nom d’utilisateur est requis.', confirmPassword:'Confirmer le mot de passe *', confirmPasswordPlaceholder:'Confirmez votre mot de passe', passwordCreatePlaceholder:'Au moins 8 caractères', passwordRule:'8 caractères minimum avec majuscule, minuscule, chiffre et symbole.', passwordMatchError:'Les mots de passe ne correspondent pas.', phone:'Téléphone (facultatif)', phonePlaceholder:'+1 234 567 890', signupBtn:'S’inscrire', alreadyAccount:'Vous avez déjà un compte ?', loginLink:'Connexion',
            termsError:'Vous devez accepter les conditions du site.', gateTitleGeneric:'Connexion requise', gateMsgGeneric:'Veuillez vous connecter pour accéder à ce test.',
            cat_courses:'Cours', cat_zero1:'Zero to Hero (Niveau 1)', cat_zero2:'Zero to Hero (Niveau 2)', cat_zero3:'Zero to Hero (Niveau 3)', cat_medical_english:'Anglais médical', cat_tests:'Tests', cat_placement:'Test de niveau', cat_grammar:'Grammaire', cat_vocab:'Vocabulaire', cat_rn_nursing:'Infirmier RN', cat_games:'Jeux', cat_veil:'Veil', cat_more_games:'Plus de jeux', cat_classes:'Cours en ligne', cat_booklets:'Brochures & exemples'
        },
        es: { dir:'ltr', label:'Español', login:'Iniciar sesión', signup:'Crear cuenta', categories:'Categorías ▾', articles:'Artículos', faq:'FAQ', about:'Sobre nosotros',
            badge:'Aprender idiomas, de forma profesional', heroLine1:'Aprende inglés', heroLine2:'profesionalmente', heroLine3:'y de forma práctica.',
            heroSub:'Prepárate para conversaciones, exámenes o inmigración — con cursos diseñados según tu nivel, tu objetivo y tu horario.',
            ctaPrimary:'Comenzar prueba de nivel', ctaSecondary:'Guía del sitio', stat1:'Cursos', stat2:'Online y presencial', stat3:'Idiomas', stat4:'Valoración',
            badgeTop:'Puntuación media alta', badgeBottom:'desde 2017', tracksEyebrow:'Rutas de aprendizaje', tracksTitle1:'Elige la ruta que', tracksTitle2:'se ajusta a tu objetivo',
            tracksLead:'Cada curso está alineado con los niveles CEFR y basado en conversación real para que el progreso sea medible desde la primera semana.',
            comparePrograms:'Comparar todos los programas →', badgeMostPopular:'Más popular', badgeSpecialized:'Especializado', badgeGoal:'Orientado a objetivos', badgeFlexible:'En vivo y flexible', badgeFun:'Primero lo divertido', badgeStartHere:'Empieza aquí',
            track1Title:'Inglés general', track1Desc:'Zero to Hero (3 niveles) y Kid Lingo para conversación cotidiana fluida.',
            track2Title:'Inglés médico', track2Desc:'Vocabulario especializado y casos para profesionales de la salud.',
            track3Title:'Preparación de exámenes', track3Desc:'IELTS, TOEFL, OET, MHLE, Konkur y RN con simulacros reales.',
            track4Title:'Clases online', track4Desc:'Sesiones en vivo para Inglés general, discusión libre, OET y escritura.',
            track5Title:'Juegos de aprendizaje', track5Desc:'Domina gramática y vocabulario con juegos y desafíos semanales.',
            track6Title:'Prueba de nivel', track6Desc:'Tres pruebas gratis para niños, adolescentes y adultos.', trackExplore:'Explorar ruta',
            whyEyebrow:'Por qué Pezhman Academy', whyTitle1:'Un programa que de verdad', whyTitle2:'te hace fluido.', whyLead:'Impartido por Pezhman Shafiei y un equipo de instructores certificados con más de 12 años de experiencia.',
            whyStudents:'en 14 países', whyCopy:'Cada clase se diseña para ser práctica, medible y basada en conversación, avanzando con un plan claro y feedback útil.',
            benefit1Title:'Currículo por niveles', benefit1Desc:'Cada curso está diseñado según los niveles CEFR para una progresión clara.', benefit2Title:'Enfoque conversacional', benefit2Desc:'Laboratorios de speaking y clubs de discusión para comunicación natural.', benefit3Title:'Exámenes internacionales', benefit3Desc:'Preparación específica para IELTS, TOEFL, OET y MHLE con evaluación certificada.', benefit4Title:'Horarios flexibles', benefit4Desc:'Online, presencial o híbrido. Turnos por la mañana, tarde y fin de semana.',
            testKicker:'Gratis · 20 minutos', testTitle1:'No adivines tu nivel.', testTitle2:'Haz la prueba en 3 pasos.', testLead:'Nuestra prueba de nivel está diseñada y revisada por examinadores certificados.', testBtn:'Comenzar prueba gratis', howItWorks:'Cómo funciona',
            step1Title:'Haz la prueba', step1Desc:'20 preguntas rápidas sobre gramática, vocabulario y comprensión.', step2Title:'Obtén tu nivel', step2Desc:'Mapeamos tu resultado según CEFR (A1 → C2).', step3Title:'Empieza a aprender', step3Desc:'Únete al curso adecuado online o presencial.', footerLine:'donde aprender cobra vida', footerSub:'Impartido por Pezhman Shafiei',
            loginTitle:'Bienvenido de nuevo', loginSub:'Inicia sesión para acceder al panel y la prueba de nivel.',
            email:'Correo electrónico *', password:'Contraseña *', emailPlaceholder:'john@example.com', passwordPlaceholder:'••••••••', emailError:'Introduce un correo válido.', passwordError:'La contraseña no puede estar vacía.', agreePrefix:'He leído y acepto los', siteTerms:'términos del sitio', rememberMe:'Recuérdame',
            loginBtn:'Iniciar sesión', noAccount:'¿No tienes cuenta?', signupLink:'Crear cuenta',
            signupTitle:'Crear cuenta', signupSub:'Únete en un minuto y empieza con la ruta correcta.', fullName:'Nombre completo *', fullNamePlaceholder:'John Doe', nameError:'Introduce tu nombre.', username:'Nombre de usuario *', usernamePlaceholder:'johndoe', usernameError:'El nombre de usuario es obligatorio.', confirmPassword:'Confirmar contraseña *', confirmPasswordPlaceholder:'Confirma tu contraseña', passwordCreatePlaceholder:'Al menos 8 caracteres', passwordRule:'Mínimo 8 caracteres con mayúscula, minúscula, número y símbolo.', passwordMatchError:'Las contraseñas no coinciden.', phone:'Teléfono (opcional)', phonePlaceholder:'+1 234 567 890', signupBtn:'Crear cuenta', alreadyAccount:'¿Ya tienes cuenta?', loginLink:'Iniciar sesión',
            termsError:'Debes aceptar los términos del sitio.', gateTitleGeneric:'Inicio de sesión requerido', gateMsgGeneric:'Inicia sesión para acceder a este test.',
            cat_courses:'Cursos', cat_zero1:'Zero to Hero (Nivel 1)', cat_zero2:'Zero to Hero (Nivel 2)', cat_zero3:'Zero to Hero (Nivel 3)', cat_medical_english:'Inglés médico', cat_tests:'Pruebas', cat_placement:'Prueba de nivel', cat_grammar:'Gramática', cat_vocab:'Vocabulario', cat_rn_nursing:'Enfermería RN', cat_games:'Juegos', cat_veil:'Veil', cat_more_games:'Más juegos', cat_classes:'Clases online', cat_booklets:'Folletos y ejemplos'
        },
        de: { dir:'ltr', label:'Deutsch', login:'Anmelden', signup:'Registrieren', categories:'Kategorien ▾', articles:'Artikel', faq:'FAQ', about:'Über uns',
            badge:'Sprachen lernen, professionell gemacht', heroLine1:'Englisch lernen', heroLine2:'professionell', heroLine3:'und praktisch.',
            heroSub:'Bereite dich auf Gespräche, Prüfungen oder Migration vor — mit Kursen, die auf dein Niveau, dein Ziel und deinen Alltag abgestimmt sind.',
            ctaPrimary:'Kostenlosen Einstufungstest starten', ctaSecondary:'Seitenübersicht', stat1:'Kurse', stat2:'Online & vor Ort', stat3:'Sprachen', stat4:'Bewertung',
            badgeTop:'Durchschnittliche Bestnote', badgeBottom:'seit 2017', tracksEyebrow:'Lernpfade', tracksTitle1:'Wähle den Pfad, der', tracksTitle2:'zu deinem Ziel passt',
            tracksLead:'Jeder Kurs ist an CEFR-Niveaus angelehnt und auf echte Konversation ausgerichtet, damit Fortschritt ab der ersten Woche messbar wird.',
            comparePrograms:'Alle Programme vergleichen →', badgeMostPopular:'Am beliebtesten', badgeSpecialized:'Spezialisiert', badgeGoal:'Zielorientiert', badgeFlexible:'Live & flexibel', badgeFun:'Spaß zuerst', badgeStartHere:'Hier starten',
            track1Title:'Allgemeines Englisch', track1Desc:'Zero to Hero (3 Stufen) & Kid Lingo für fließende Alltagssprache.',
            track2Title:'Medizinisches Englisch', track2Desc:'Fachsprache und Fallstudien für medizinische Fachkräfte.',
            track3Title:'Prüfungsvorbereitung', track3Desc:'IELTS, TOEFL, OET, MHLE, Konkur und RN mit echten Probetests.',
            track4Title:'Online-Kurse', track4Desc:'Live-Sessions für Allgemeines Englisch, freie Diskussion, OET und Writing.',
            track5Title:'Lernspiele', track5Desc:'Grammatik und Wortschatz mit Spielen und wöchentlichen Challenges meistern.',
            track6Title:'Einstufungstest', track6Desc:'Drei kostenlose Tests für Kinder, Jugendliche und Erwachsene.', trackExplore:'Pfad ansehen',
            whyEyebrow:'Warum Pezhman Academy', whyTitle1:'Ein Programm, das dich', whyTitle2:'wirklich fließend macht.', whyLead:'Unterrichtet von Pezhman Shafiei und zertifizierten Lehrkräften mit über 12 Jahren Erfahrung.',
            whyStudents:'in 14 Ländern', whyCopy:'Jede Lektion ist praxisnah, messbar und konversationsbasiert, damit du mit klarem Plan und hilfreichem Feedback vorankommst.',
            benefit1Title:'Niveaubasierter Lehrplan', benefit1Desc:'Jeder Kurs ist an CEFR-Niveaus angelehnt, damit der Fortschritt klar bleibt.', benefit2Title:'Fokus auf Konversation', benefit2Desc:'Sprechlabore und Diskussionsclubs für natürliche Kommunikation.', benefit3Title:'Internationale Prüfungen', benefit3Desc:'Gezielte Vorbereitung auf IELTS, TOEFL, OET und MHLE.', benefit4Title:'Flexible Zeiten', benefit4Desc:'Online, vor Ort oder hybrid. Morgen-, Abend- und Wochenendtermine verfügbar.',
            testKicker:'Kostenlos · 20 Minuten', testTitle1:'Rate dein Niveau nicht.', testTitle2:'Teste es in 3 Schritten.', testLead:'Unser Einstufungstest wurde von zertifizierten Prüfern entwickelt und geprüft.', testBtn:'Kostenlosen Test starten', howItWorks:'So funktioniert es',
            step1Title:'Test machen', step1Desc:'20 kurze Fragen zu Grammatik, Wortschatz und Leseverständnis.', step2Title:'Dein Niveau erhalten', step2Desc:'Wir ordnen dein Ergebnis CEFR (A1 → C2) zu.', step3Title:'Lernen starten', step3Desc:'Beginne den passenden Kurs online oder vor Ort.', footerLine:'wo Lernen lebendig wird', footerSub:'Unterrichtet von Pezhman Shafiei',
            loginTitle:'Willkommen zurück', loginSub:'Melde dich an, um auf dein Dashboard und den Einstufungstest zuzugreifen.',
            email:'E-Mail *', password:'Passwort *', emailPlaceholder:'john@example.com', passwordPlaceholder:'••••••••', emailError:'Bitte eine gültige E-Mail eingeben.', passwordError:'Das Passwort darf nicht leer sein.', agreePrefix:'Ich habe die', siteTerms:'Nutzungsbedingungen', rememberMe:'Angemeldet bleiben',
            loginBtn:'Anmelden', noAccount:'Noch kein Konto?', signupLink:'Registrieren',
            signupTitle:'Konto erstellen', signupSub:'In einer Minute anmelden und mit dem passenden Pfad starten.', fullName:'Vollständiger Name *', fullNamePlaceholder:'John Doe', nameError:'Bitte deinen Namen eingeben.', username:'Benutzername *', usernamePlaceholder:'johndoe', usernameError:'Benutzername ist erforderlich.', confirmPassword:'Passwort bestätigen *', confirmPasswordPlaceholder:'Passwort bestätigen', passwordCreatePlaceholder:'Mindestens 8 Zeichen', passwordRule:'Mindestens 8 Zeichen mit Groß-/Kleinbuchstaben, Zahl und Sonderzeichen.', passwordMatchError:'Passwörter stimmen nicht überein.', phone:'Telefon (optional)', phonePlaceholder:'+1 234 567 890', signupBtn:'Registrieren', alreadyAccount:'Bereits ein Konto?', loginLink:'Anmelden',
            termsError:'Sie müssen den Nutzungsbedingungen zustimmen.', gateTitleGeneric:'Anmeldung erforderlich', gateMsgGeneric:'Bitte melde dich an, um auf diesen Test zuzugreifen.',
            cat_courses:'Kurse', cat_zero1:'Zero to Hero (Stufe 1)', cat_zero2:'Zero to Hero (Stufe 2)', cat_zero3:'Zero to Hero (Stufe 3)', cat_medical_english:'Medizinisches Englisch', cat_tests:'Tests', cat_placement:'Einstufungstest', cat_grammar:'Grammatik', cat_vocab:'Wortschatz', cat_rn_nursing:'RN-Pflege', cat_games:'Spiele', cat_veil:'Veil', cat_more_games:'Mehr Spiele', cat_classes:'Online-Kurse', cat_booklets:'Heftchen & Beispiele'
        },
        ko: { dir:'ltr', label:'한국어', login:'로그인', signup:'회원가입', categories:'카테고리 ▾', articles:'아티클', faq:'FAQ', about:'소개',
            badge:'언어 학습을 전문적으로', heroLine1:'영어를', heroLine2:'전문적으로', heroLine3:'그리고 실용적으로 배우세요.',
            heroSub:'회화, 시험, 이민 준비까지 — 당신의 수준과 목표, 일정에 맞춘 수업으로 시작하세요.', ctaPrimary:'무료 레벨 테스트 시작', ctaSecondary:'사이트 가이드',
            stat1:'강좌', stat2:'온라인 & 오프라인', stat3:'언어', stat4:'학생 평점', badgeTop:'평균 최고 점수', badgeBottom:'2017년부터',
            tracksEyebrow:'학습 트랙', tracksTitle1:'당신의 목표에', tracksTitle2:'맞는 트랙을 선택하세요', tracksLead:'모든 코스는 CEFR 수준에 맞춰져 있으며 실제 회화 중심으로 구성되어 첫 주부터 성과가 보입니다.',
            comparePrograms:'전체 프로그램 비교 →', badgeMostPopular:'가장 인기', badgeSpecialized:'전문 과정', badgeGoal:'목표 중심', badgeFlexible:'실시간 & 유연', badgeFun:'재미 우선', badgeStartHere:'여기서 시작',
            track1Title:'일반 영어', track1Desc:'Zero to Hero (3단계) & Kid Lingo로 일상 회화를 유창하게.',
            track2Title:'의학 영어', track2Desc:'의료 종사자를 위한 전문 어휘와 사례 학습.',
            track3Title:'시험 대비', track3Desc:'IELTS, TOEFL, OET, MHLE, Konkur, RN 실전 모의고사.',
            track4Title:'온라인 클래스', track4Desc:'일반 영어, 자유 토론, OET, Writing 라이브 세션.', track5Title:'학습 게임', track5Desc:'재미있는 게임과 주간 챌린지로 문법과 어휘를 익히세요.', track6Title:'레벨 테스트', track6Desc:'어린이, 청소년, 성인을 위한 3개의 무료 테스트.', trackExplore:'트랙 보기',
            whyEyebrow:'Pezhman Academy를 선택하는 이유', whyTitle1:'정말 유창해지게 하는', whyTitle2:'프로그램입니다.', whyLead:'12년 이상 경험의 공인 강사진과 함께합니다.', whyStudents:'14개국에서', whyCopy:'모든 수업은 실용적이고 측정 가능하며 대화 중심으로 설계되어 명확한 계획과 피드백으로 전진합니다.',
            benefit1Title:'레벨 기반 커리큘럼', benefit1Desc:'CEFR 레벨에 맞춘 명확한 진도로 학습합니다.', benefit2Title:'실전 회화 중심', benefit2Desc:'스피킹 랩과 토론 클럽으로 자연스러운 의사소통을 만듭니다.', benefit3Title:'국제 공인 시험', benefit3Desc:'IELTS, TOEFL, OET, MHLE 대비를 체계적으로 진행합니다.', benefit4Title:'유연한 일정', benefit4Desc:'온라인, 오프라인, 하이브리드 모두 가능. 아침/저녁/주말 운영.',
            testKicker:'무료 · 20분', testTitle1:'레벨을 추측하지 마세요.', testTitle2:'3단계로 확인하세요.', testLead:'공인 시험관이 설계·검토한 레벨 테스트입니다.', testBtn:'무료 테스트 시작', howItWorks:'작동 방식',
            step1Title:'테스트 시작', step1Desc:'문법, 어휘, 독해를 다루는 20개의 빠른 문제.', step2Title:'레벨 확인', step2Desc:'결과를 CEFR(A1 → C2)로 매핑하고 적절한 트랙을 추천합니다.', step3Title:'학습 시작', step3Desc:'맞는 코스에 온라인 또는 오프라인으로 참여하세요.', footerLine:'학습이 살아나는 곳', footerSub:'Pezhman Shafiei가 지도',
            loginTitle:'다시 오신 것을 환영합니다', loginSub:'대시보드와 레벨 테스트에 접속하려면 로그인하세요.',
            email:'이메일 *', password:'비밀번호 *', emailPlaceholder:'john@example.com', passwordPlaceholder:'••••••••', emailError:'유효한 이메일을 입력하세요.', passwordError:'비밀번호는 비워둘 수 없습니다.', agreePrefix:'나는 다음을 읽고 동의합니다', siteTerms:'사이트 약관', rememberMe:'로그인 상태 유지',
            loginBtn:'로그인', noAccount:'계정이 없나요?', signupLink:'회원가입',
            signupTitle:'계정 만들기', signupSub:'1분 만에 가입하고 올바른 트랙으로 시작하세요.', fullName:'이름 전체 *', fullNamePlaceholder:'John Doe', nameError:'이름을 입력하세요.', username:'사용자 이름 *', usernamePlaceholder:'johndoe', usernameError:'사용자 이름이 필요합니다.', confirmPassword:'비밀번호 확인 *', confirmPasswordPlaceholder:'비밀번호를 다시 입력', passwordCreatePlaceholder:'최소 8자', passwordRule:'대문자, 소문자, 숫자, 특수문자를 포함한 8자 이상.', passwordMatchError:'비밀번호가 일치하지 않습니다.', phone:'전화번호 (선택)', phonePlaceholder:'+1 234 567 890', signupBtn:'회원가입', alreadyAccount:'이미 계정이 있나요?', loginLink:'로그인',
            termsError:'사이트 약관에 동의해야 합니다.', gateTitleGeneric:'로그인 필요', gateMsgGeneric:'이 테스트에 접근하려면 로그인하세요.',
            cat_courses:'코스', cat_zero1:'Zero to Hero (레벨 1)', cat_zero2:'Zero to Hero (레벨 2)', cat_zero3:'Zero to Hero (레벨 3)', cat_medical_english:'의학 영어', cat_tests:'테스트', cat_placement:'레벨 테스트', cat_grammar:'문법', cat_vocab:'어휘', cat_rn_nursing:'RN 간호', cat_games:'게임', cat_veil:'Veil', cat_more_games:'더 많은 게임', cat_classes:'온라인 클래스', cat_booklets:'책자 & 샘플'
        },
        zh: { dir:'ltr', label:'中文', login:'登录', signup:'注册', categories:'分类 ▾', articles:'文章', faq:'常见问题', about:'关于我们',
            badge:'专业学习英语', heroLine1:'学习英语', heroLine2:'专业地', heroLine3:'而且实用地。',
            heroSub:'为对话、考试或移民做好准备——课程会根据你的水平、目标和实际时间安排设计。', ctaPrimary:'开始免费分级测试', ctaSecondary:'网站指南',
            stat1:'课程', stat2:'线上和线下', stat3:'语言', stat4:'学生评分', badgeTop:'平均高分', badgeBottom:'自 2017 年起',
            tracksEyebrow:'学习路径', tracksTitle1:'选择与你目标', tracksTitle2:'匹配的路径', tracksLead:'每门课程都对应 CEFR 等级，并以真实交流为核心，让进步从第一周就可衡量。',
            comparePrograms:'比较所有项目 →', badgeMostPopular:'最受欢迎', badgeSpecialized:'专业', badgeGoal:'目标导向', badgeFlexible:'实时与灵活', badgeFun:'先有趣', badgeStartHere:'从这里开始',
            track1Title:'通用英语', track1Desc:'Zero to Hero（3 个等级）与 Kid Lingo，帮助日常口语流利表达。',
            track2Title:'医学英语', track2Desc:'为医护人员提供专门词汇和案例学习。',
            track3Title:'考试备考', track3Desc:'IELTS、TOEFL、OET、MHLE、Konkur 和 RN 的实战模考。',
            track4Title:'在线课程', track4Desc:'通用英语、自由讨论、OET 和写作的直播课程。',
            track5Title:'学习游戏', track5Desc:'通过有趣游戏和每周挑战掌握语法和词汇。',
            track6Title:'分级测试', track6Desc:'为儿童、青少年和成人提供 3 个免费测试。', trackExplore:'查看路径',
            whyEyebrow:'为什么选择 Pezhman Academy', whyTitle1:'真正让你', whyTitle2:'说得流利的课程。', whyLead:'由 Pezhman Shafiei 和拥有 12 年以上经验的认证教师团队授课。',
            whyStudents:'覆盖 14 个国家', whyCopy:'每节课都注重实用性、可衡量性和对话式学习，让你在清晰路径和有效反馈中前进。',
            benefit1Title:'分级课程', benefit1Desc:'每门课都按 CEFR 等级设计，进步清晰可见。', benefit2Title:'真实对话重点', benefit2Desc:'口语实验室和讨论俱乐部帮助建立自然流利沟通。', benefit3Title:'国际考试认可', benefit3Desc:'针对 IELTS、TOEFL、OET、MHLE 的专业备考。', benefit4Title:'灵活安排', benefit4Desc:'可选线上、线下或混合。提供早晚及周末时间。',
            testKicker:'免费 · 20 分钟', testTitle1:'不要猜你的水平。', testTitle2:'3 步完成测试。', testLead:'我们的分级测试由认证考官设计和审核。', testBtn:'开始免费测试', howItWorks:'如何运作',
            step1Title:'开始测试', step1Desc:'20 道快速题，涵盖语法、词汇和阅读理解。', step2Title:'获取等级', step2Desc:'我们将结果映射到 CEFR（A1 → C2）。', step3Title:'开始学习', step3Desc:'加入匹配课程，线上或线下均可。', footerLine:'让学习真正活起来', footerSub:'由 Pezhman Shafiei 授课',
            loginTitle:'欢迎回来', loginSub:'登录后可访问你的控制面板和分级测试。',
            email:'邮箱 *', password:'密码 *', emailPlaceholder:'john@example.com', passwordPlaceholder:'••••••••', emailError:'请输入有效邮箱。', passwordError:'密码不能为空。', agreePrefix:'我已阅读并同意', siteTerms:'网站条款', rememberMe:'记住我',
            loginBtn:'登录', noAccount:'还没有账号？', signupLink:'注册',
            signupTitle:'创建账号', signupSub:'一分钟加入，选择正确的学习路径。', fullName:'姓名 *', fullNamePlaceholder:'John Doe', nameError:'请输入姓名。', username:'用户名 *', usernamePlaceholder:'johndoe', usernameError:'用户名必填。', confirmPassword:'确认密码 *', confirmPasswordPlaceholder:'再次输入密码', passwordCreatePlaceholder:'至少 8 个字符', passwordRule:'至少 8 个字符，包含大小写字母、数字和特殊字符。', passwordMatchError:'两次密码不一致。', phone:'电话（可选）', phonePlaceholder:'+1 234 567 890',
            signupBtn:'注册', alreadyAccount:'已有账号？', loginLink:'登录',
            termsError:'你必须同意网站条款。', gateTitleGeneric:'需要登录', gateMsgGeneric:'请登录以访问此测试。',
            cat_courses:'课程', cat_zero1:'Zero to Hero（第 1 级）', cat_zero2:'Zero to Hero（第 2 级）', cat_zero3:'Zero to Hero（第 3 级）', cat_medical_english:'医学英语', cat_tests:'测试', cat_placement:'分级测试', cat_grammar:'语法', cat_vocab:'词汇', cat_rn_nursing:'RN 护理', cat_games:'游戏', cat_veil:'Veil', cat_more_games:'更多游戏', cat_classes:'在线课程', cat_booklets:'手册与样题'
        }
    };

    let currentLang = 'en';

    function setText(key, lang){
        const value = lang[key];
        if (value === undefined) return;
        document.querySelectorAll('[data-i18n="'+key+'"]').forEach(el => {
            el.textContent = value;
        });
    }

    function setPlaceholder(key, lang){
        const value = lang[key];
        if (value === undefined) return;
        document.querySelectorAll('[data-i18n-placeholder="'+key+'"]').forEach(el => {
            el.setAttribute('placeholder', value);
        });
    }

    function applyLang(code, save = false){
        const lang = T[code] || T.en;
        currentLang = code in T ? code : 'en';
        document.documentElement.lang = currentLang;
        document.documentElement.dir = lang.dir;
        document.documentElement.dataset.lang = currentLang;
        document.getElementById('langLabel').textContent = lang.label;

        const keys = new Set();
        Object.keys(T.en).forEach(k => keys.add(k));
        Object.keys(lang).forEach(k => keys.add(k));

        keys.forEach(key => {
            setText(key, lang);
            setPlaceholder(key, lang);
        });

        if (save) localStorage.setItem('preferredLang', currentLang);
    }

    function openLogin(){ document.getElementById('loginModalOverlay').classList.add('active'); }
    function closeLogin(){ document.getElementById('loginModalOverlay').classList.remove('active'); }
    function openSignup(){ document.getElementById('signupModalOverlay').classList.add('active'); }
    function closeSignup(){ document.getElementById('signupModalOverlay').classList.remove('active'); }
    function openGate(){
        document.getElementById('loginRequiredOverlay').classList.add('active');
    }
    function closeGate(){ document.getElementById('loginRequiredOverlay').classList.remove('active'); }

    const savedLang = localStorage.getItem('preferredLang');
    const browserLang = (navigator.language || 'en').toLowerCase().slice(0,2);
    const initialLang = (savedLang && T[savedLang]) ? savedLang : (T[browserLang] ? browserLang : 'en');
    applyLang(initialLang, false);

    const langBtn = document.getElementById('langBtn');
    const langList = document.getElementById('langList');

    langBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        langList.classList.toggle('open');
        langBtn.setAttribute('aria-expanded', langList.classList.contains('open') ? 'true' : 'false');
    });
    document.addEventListener('click', () => {
        langList.classList.remove('open');
        langBtn.setAttribute('aria-expanded', 'false');
    });
    langList.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-l]');
        if (!btn) return;
        applyLang(btn.dataset.l, true);
        langList.classList.remove('open');
        langBtn.setAttribute('aria-expanded', 'false');
    });

    document.getElementById('loginLink')?.addEventListener('click', (e) => { e.preventDefault(); openLogin(); });
    document.getElementById('signupLink')?.addEventListener('click', (e) => { e.preventDefault(); openSignup(); });
    document.getElementById('switchToSignup')?.addEventListener('click', (e) => { e.preventDefault(); closeLogin(); openSignup(); });
    document.getElementById('switchToLogin')?.addEventListener('click', (e) => { e.preventDefault(); closeSignup(); openLogin(); });
    document.getElementById('closeLoginBtn')?.addEventListener('click', closeLogin);
    document.getElementById('closeSignupBtn')?.addEventListener('click', closeSignup);
    document.getElementById('closeLoginRequiredBtn')?.addEventListener('click', closeGate);
    document.getElementById('gateLoginBtn')?.addEventListener('click', (e) => { e.preventDefault(); closeGate(); openLogin(); });
    document.getElementById('gateSignupBtn')?.addEventListener('click', (e) => { e.preventDefault(); closeGate(); openSignup(); });

    ['loginModalOverlay', 'signupModalOverlay', 'loginRequiredOverlay'].forEach(id => {
        const el = document.getElementById(id);
        el?.addEventListener('click', (e) => {
            if (e.target === el) el.classList.remove('active');
        });
    });

    // Gate handler for all test links
    function handleTestLink(e){
        if (!isLoggedInPHP) {
            e.preventDefault();
            openGate();
        }
    }

    document.querySelectorAll('.test-link').forEach(el => {
        el.addEventListener('click', handleTestLink);
    });

    const tilt = document.getElementById('heroTilt');
    if (tilt) {
        tilt.addEventListener('mousemove', function(e){
            const rect = this.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;
            this.style.transform = 'rotateY(' + (x * 12) + 'deg) rotateX(' + (-y * 12) + 'deg)';
        });
        tilt.addEventListener('mouseleave', function(){
            this.style.transform = 'rotateY(0deg) rotateX(0deg)';
        });
    }

    const rv = document.querySelectorAll('.rv:not(.initial-visible)');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('show');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    rv.forEach(el => observer.observe(el));

    // ====== مدیریت باز و بسته شدن منوی دسته‌بندی‌ها (فقط کلیک روی دکمه) ======
    const dropdownToggle = document.getElementById('dropdownToggle');
    const dropdownPanel = document.getElementById('dropdownPanel');
    const dropdown = document.getElementById('mainDropdown');

    dropdownToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownPanel.classList.toggle('open');
    });

    // کلیک بیرون از دراپ‌داون پنل را می‌بندد
    document.addEventListener('click', function(event) {
        if (!dropdown.contains(event.target)) {
            dropdownPanel.classList.remove('open');
        }
    });

    // جلوگیری از بسته شدن هنگام کلیک داخل پنل
    dropdownPanel.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    document.getElementById('loginForm')?.addEventListener('submit', (e) => {
        const email = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value.trim();
        const agreeTerms = document.getElementById('loginAgreeTerms');
        let valid = true;

        if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
            document.getElementById('loginEmailError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('loginEmailError').style.display = 'none';
        }

        if (!password) {
            document.getElementById('loginPassError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('loginPassError').style.display = 'none';
        }

        if (agreeTerms && !agreeTerms.checked) {
            document.getElementById('loginTermsError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('loginTermsError').style.display = 'none';
        }

        if (!valid) e.preventDefault();
    });

    document.getElementById('signupForm')?.addEventListener('submit', (e) => {
        let valid = true;

        const fullname = document.getElementById('signupFullName').value.trim();
        const email = document.getElementById('signupEmail').value.trim();
        const username = document.getElementById('signupUsername').value.trim();
        const password = document.getElementById('signupPassword').value;
        const confirm = document.getElementById('signupConfirmPassword').value;
        const agreeTerms = document.getElementById('signupAgreeTerms');

        document.getElementById('signupNameError').style.display = fullname ? 'none' : 'block';
        document.getElementById('signupEmailError').style.display = /^\S+@\S+\.\S+$/.test(email) ? 'none' : 'block';
        document.getElementById('signupUsernameError').style.display = username ? 'none' : 'block';

        const passwordOk = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(password);
        document.getElementById('signupPassError').style.display = passwordOk ? 'none' : 'block';

        const matchOk = password === confirm && confirm.length > 0;
        document.getElementById('signupConfirmError').style.display = matchOk ? 'none' : 'block';

        if (agreeTerms && !agreeTerms.checked) {
            document.getElementById('signupTermsError').style.display = 'block';
            valid = false;
        } else {
            document.getElementById('signupTermsError').style.display = 'none';
        }

        valid = fullname && /^\S+@\S+\.\S+$/.test(email) && username && passwordOk && matchOk && (agreeTerms?.checked ?? false);

        if (!valid) e.preventDefault();
    });

    const urlParams = new URLSearchParams(window.location.search);
    const errorParam = urlParams.get('error');
    if (errorParam === 'invalid' || errorParam === 'login' || errorParam === 'login_error') openLogin();
    if (errorParam === 'exists' || errorParam === 'register' || errorParam === 'signup_error') openSignup();
    <?php if (!empty($register_errors)): ?> openSignup(); <?php endif; ?>

    console.log('Index page loaded');
})();
</script>
</body>
</html>
