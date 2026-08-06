<?php
// ============================================
// FRONTEND INDEX PAGE - Rasmak Garage (FINAL v6 - WITH VIDEO SLIDER)
// ============================================

// Start session to check login status
session_start();

// ============================================
// VISITOR & SUBSCRIBER FUNCTIONS - DIRECT EMBED
// ============================================

/**
 * Count visitor (only once per day)
 */
function countVisitor() {
    $log_file = 'visitors.log';
    $today = date('Y-m-d');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $data = [];
    
    if (file_exists($log_file)) {
        $data = json_decode(file_get_contents($log_file), true) ?: [];
    }
    
    if (!isset($data[$today])) {
        $data[$today] = ['unique' => [], 'total' => 0];
    }
    
    if (!in_array($ip, $data[$today]['unique'])) {
        $data[$today]['unique'][] = $ip;
    }
    $data[$today]['total'] = count($data[$today]['unique']);
    
    file_put_contents($log_file, json_encode($data));
}

/**
 * Get visitor stats
 */
function getVisitorStats() {
    $log_file = 'visitors.log';
    $today = date('Y-m-d');
    $stats = ['unique' => 0, 'daily' => 0];
    
    if (file_exists($log_file)) {
        $data = json_decode(file_get_contents($log_file), true) ?: [];
        $total_unique = [];
        
        foreach ($data as $date => $info) {
            if (isset($info['unique']) && is_array($info['unique'])) {
                $total_unique = array_merge($total_unique, $info['unique']);
            }
        }
        
        $stats['unique'] = count(array_unique($total_unique));
        
        if (isset($data[$today]['total'])) {
            $stats['daily'] = $data[$today]['total'];
        }
    }
    
    return $stats;
}

// Call visitor functions
countVisitor();
$stats = getVisitorStats();

// Get user data if logged in
$user_fullname = $_SESSION['fullname'] ?? $_SESSION['full_name'] ?? 'Guest';
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = ($user_role === 'admin');

// Set page title and active page
$page_title = "Home";
$active_page = "home";

// ============================================
// WHATSAPP & CONTACT CONFIGURATION
// ============================================
$whatsapp_number = '255622826068';
$phone_number = '+255638540909';
$email_address = 'rasmakgarage@gmail.com';

// Ujumbe wa WhatsApp kwa booking
$default_whatsapp_message = "Hello Rasmak Garage!%0A%0A" .
                            "I would like to book a service.%0A" .
                            "Please provide the following details:%0A%0A" .
                            "Name: %0A" .
                            "Service needed: %0A" .
                            "Preferred date: %0A" .
                            "Vehicle model: %0A%0A" .
                            "Thank you!";

// ============================================
// GET MECHANICS FROM DATABASE
// ============================================
require_once 'connection.php';

$mechanics = [];
try {
    $mech_query = $conn->query("
        SELECT id, fullname, username, phone, email, 
               status, profile_image, bio, experience_years, 
               specialty, created_at,
               (SELECT COUNT(*) FROM jobs WHERE assigned_to = users.id AND LOWER(status) != 'completed') as active_jobs,
               (SELECT COALESCE(SUM(labor_cost), 0) FROM jobs WHERE assigned_to = users.id) as total_earnings
        FROM users 
        WHERE role = 'mechanic' AND is_active = 1 
        ORDER BY fullname
    ");
    
    if ($mech_query && $mech_query->num_rows > 0) {
        while ($row = $mech_query->fetch_assoc()) {
            $mechanics[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Mechanics Query Error: " . $e->getMessage());
}

// If no mechanics in database, use default data
if (empty($mechanics)) {
    $mechanics = [
        [
            'id' => 1,
            'fullname' => 'John Khamis',
            'username' => 'johnk',
            'phone' => '+255 712 345 678',
            'email' => 'john@rasmakgarage.com',
            'status' => 'available',
            'profile_image' => '',
            'bio' => 'Expert mechanic with over 10 years of experience specializing in engine diagnostics and repair. Certified Toyota and Nissan specialist.',
            'experience_years' => 10,
            'specialty' => 'Engine & Diagnostics',
            'active_jobs' => 2,
            'total_earnings' => 1250000
        ],
        [
            'id' => 2,
            'fullname' => 'Ali Haji',
            'username' => 'alih',
            'phone' => '+255 765 432 109',
            'email' => 'ali@rasmakgarage.com',
            'status' => 'busy',
            'profile_image' => '',
            'bio' => 'Specialized in brake systems, suspension, and wheel alignment. Known for quick and accurate diagnostics.',
            'experience_years' => 7,
            'specialty' => 'Brakes & Suspension',
            'active_jobs' => 3,
            'total_earnings' => 980000
        ],
        [
            'id' => 3,
            'fullname' => 'Salim Mohammed',
            'username' => 'salimm',
            'phone' => '+255 623 456 789',
            'email' => 'salim@rasmakgarage.com',
            'status' => 'available',
            'profile_image' => '',
            'bio' => 'Electrical systems expert. Handles all automotive electrical issues from wiring to computerized systems.',
            'experience_years' => 8,
            'specialty' => 'Electrical Systems',
            'active_jobs' => 1,
            'total_earnings' => 850000
        ],
        [
            'id' => 4,
            'fullname' => 'Fatma Ali',
            'username' => 'fatmaa',
            'phone' => '+255 654 321 987',
            'email' => 'fatma@rasmakgarage.com',
            'status' => 'available',
            'profile_image' => '',
            'bio' => 'Transmission and gearbox specialist. Expert in both manual and automatic transmission repairs.',
            'experience_years' => 6,
            'specialty' => 'Transmission',
            'active_jobs' => 2,
            'total_earnings' => 720000
        ],
        [
            'id' => 5,
            'fullname' => 'Hamza Said',
            'username' => 'hamzas',
            'phone' => '+255 698 765 432',
            'email' => 'hamza@rasmakgarage.com',
            'status' => 'busy',
            'profile_image' => '',
            'bio' => 'AC and cooling systems expert. Provides professional air conditioning repair and maintenance services.',
            'experience_years' => 5,
            'specialty' => 'AC & Cooling',
            'active_jobs' => 3,
            'total_earnings' => 650000
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="description" content="Rasmak Garage - Professional auto services in Zanzibar. Quality car repair, maintenance, diagnostics and oil services." />
    <meta name="keywords" content="garage, car repair, auto service, Zanzibar, mechanics, oil change, brake repair" />
    <meta name="author" content="Rasmak Garage" />
    <meta name="robots" content="index, follow" />
    
    <!-- Open Graph -->
    <meta property="og:title" content="Rasmak Garage | Professional Auto Services" />
    <meta property="og:description" content="Professional auto services in Zanzibar. Quality car repair, maintenance, diagnostics and oil services." />
    <meta property="og:image" content="./images/og-image.jpg" />
    <meta property="og:url" content="https://rasmakgarage.com" />
    <meta property="og:type" content="website" />
    <meta name="twitter:card" content="summary_large_image" />
    
    <title>Rasmak Garage | Professional Auto Services</title>
    
    <!-- Favicon -->
    <link rel="icon" href="./images/favicon.png" type="image/png" />
    <link rel="apple-touch-icon" href="./images/favicon.png" />
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-9JNMMT5XKV"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-9JNMMT5XKV');
    </script>
    
    <!-- SEO Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "AutoRepair",
      "name": "Rasmak Garage",
      "image": "https://rasmakgarage.com/images/GARAGE.jpg",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Zanzibar",
        "addressCountry": "TZ"
      },
      "telephone": "+255638540909",
      "openingHours": "Mo-Sa 08:00-20:00"
    }
    </script>
    
    <style>
        /* ================= RESET & GLOBAL ================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        html {
            scroll-behavior: smooth;
        }
        body {
            background: #070b1a;
            color: white;
            overflow-x: hidden;
            font-family: 'Poppins', sans-serif;
        }

        /* ================= PRELOADER ================= */
        #preloader {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: #070b1a;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 99999;
        }
        .spinner {
            width: 60px; height: 60px;
            border: 5px solid rgba(255,179,71,0.2);
            border-top: 5px solid #FFB347;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ================= HEADER ================= */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 85px;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            background: rgba(10, 10, 20, 0.65);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            transition: 0.3s;
        }
        .header.scrolled {
            background: rgba(7, 11, 26, 0.95);
            height: 75px;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
        }
        .logo img {
            width: 85px;
            height: 90px;
            border-radius: 100%;
            object-fit: cover;
            box-shadow: 0 0 25px rgba(255, 179, 71, 0.3);
            transition: 0.3s;
        }
        .logo img:hover {
            transform: scale(1.05);
        }
        .logo h1 {
            color: #FFB347;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .navbar {
            display: flex;
            align-items: center;
            gap: 35px;
        }
        .navbar a {
            color: #ddd;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: 0.4s;
            position: relative;
        }
        .navbar a::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -7px;
            width: 0%;
            height: 2px;
            background: #FFB347;
            transition: 0.4s;
        }
        .navbar a:hover,
        .navbar a.active {
            color: #FFB347;
        }
        .navbar a:hover::after,
        .navbar a.active::after {
            width: 100%;
        }

        /* ================= AUTH BUTTONS ================= */
        .auth-buttons {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .login-btn {
            padding: 10px 28px;
            border: none;
            border-radius: 35px;
            cursor: pointer;
            background: linear-gradient(135deg, #FFB347, #ff7b00);
            color: white;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 12px 30px rgba(255, 123, 0, 0.35);
            transition: 0.4s;
            text-decoration: none;
            display: inline-block;
        }
        .login-btn:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 16px 40px rgba(255, 123, 0, 0.5);
        }
        .register-btn {
            padding: 10px 28px;
            border: 2px solid #FFB347;
            border-radius: 35px;
            cursor: pointer;
            background: transparent;
            color: #FFB347;
            font-weight: 600;
            font-size: 14px;
            transition: 0.4s;
            text-decoration: none;
            display: inline-block;
        }
        .register-btn:hover {
            background: #FFB347;
            color: #070b1a;
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 12px 30px rgba(255, 179, 71, 0.25);
        }
        .logout-btn {
            padding: 10px 28px;
            border: 2px solid #ff4444;
            border-radius: 35px;
            cursor: pointer;
            background: transparent;
            color: #ff4444;
            font-weight: 600;
            font-size: 14px;
            transition: 0.4s;
            text-decoration: none;
            display: inline-block;
        }
        .logout-btn:hover {
            background: #ff4444;
            color: white;
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 12px 30px rgba(255, 68, 68, 0.3);
        }
        .user-name {
            color: #FFB347;
            font-weight: 500;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .user-name .user-icon {
            background: #FFB347;
            color: #070b1a;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
        }
        .user-name .user-icon:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(255, 179, 71, 0.4);
        }
        .menu-btn {
            display: none;
            font-size: 32px;
            cursor: pointer;
            color: #FFB347;
            transition: 0.3s;
            background: transparent;
            border: none;
            padding: 5px 10px;
        }
        .menu-btn:hover {
            color: #ff7b00;
        }

        /* ================= LIGHT EFFECTS ================= */
        .light {
            position: fixed;
            border-radius: 50%;
            filter: blur(70px);
            z-index: -1;
            pointer-events: none;
        }
        .light1 {
            width: 350px;
            height: 350px;
            background: #ff7b00;
            top: -100px;
            left: -100px;
            opacity: 0.15;
        }
        .light2 {
            width: 300px;
            height: 300px;
            background: #00bfff;
            bottom: -120px;
            right: -100px;
            opacity: 0.12;
        }

        /* ================= SLIDER ================= */
        .container {
            width: 100%;
            height: 100vh;
            overflow: hidden;
            position: relative;
            margin-top: 0;
        }
        .slide {
            width: 100%;
            height: 100%;
            position: relative;
        }
        .item {
            width: 220px;
            height: 320px;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            border-radius: 25px;
            overflow: hidden;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            transition: all 0.7s cubic-bezier(0.22, 0.61, 0.36, 1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
            will-change: transform;
        }
        .item::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.1));
            z-index: 1;
        }
        .slide .item:nth-child(1),
        .slide .item:nth-child(2) {
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transform: none;
            border-radius: 0;
        }
        .slide .item:nth-child(3) {
            left: 60%;
        }
        .slide .item:nth-child(4) {
            left: calc(60% + 240px);
        }
        .slide .item:nth-child(5) {
            left: calc(60% + 480px);
        }
        .slide .item:nth-child(6) {
            left: calc(60% + 720px);
        }
        .slide .item:nth-child(n+7) {
            opacity: 0;
        }

        .content {
            position: absolute;
            left: 90px;
            bottom: 120px;
            width: 550px;
            z-index: 20;
            display: none;
            animation: fadeIn 1s ease;
        }
        .slide .item:nth-child(2) .content {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .content .name {
            font-size: 64px;
            font-weight: 800;
            line-height: 1.1;
            color: #FFB347;
            text-shadow: 0 6px 25px rgba(0, 0, 0, 0.45);
        }
        .content .des {
            margin-top: 20px;
            color: #f0f0f0;
            line-height: 1.9;
            font-size: 16px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
        }
        .content button {
            margin-top: 30px;
            padding: 14px 34px;
            border: none;
            border-radius: 35px;
            cursor: pointer;
            background: linear-gradient(135deg, #FFB347, #ff7b00);
            color: white;
            font-weight: 600;
            font-size: 15px;
            transition: 0.4s;
            box-shadow: 0 15px 35px rgba(255, 123, 0, 0.35);
        }
        .content button:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 45px rgba(255, 123, 0, 0.5);
        }

        .navigation {
            position: absolute;
            bottom: 50px;
            right: 60px;
            display: flex;
            gap: 15px;
            z-index: 100;
        }
        .nav-btn {
            width: 60px;
            height: 60px;
            border: none;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 28px;
            cursor: pointer;
            backdrop-filter: blur(10px);
            transition: 0.4s;
        }
        .nav-btn:hover {
            background: #FFB347;
            color: #070b1a;
            transform: scale(1.1);
        }

        .indicators {
            position: absolute;
            bottom: 70px;
            left: 70px;
            display: flex;
            gap: 12px;
            z-index: 100;
        }
        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: white;
            opacity: 0.3;
            transition: 0.4s;
            cursor: pointer;
        }
        .dot.active {
            width: 35px;
            border-radius: 30px;
            background: #FFB347;
            opacity: 1;
        }

        /* ============================================
           TEAM VIDEO SLIDER SECTION
           ============================================ */
        .team-slider-section {
            padding: 80px 0;
            background: rgba(255, 255, 255, 0.02);
            text-align: center;
            position: relative;
        }
        .team-slider-section .section-title {
            color: #FFB347;
            font-size: 36px;
            margin-bottom: 10px;
        }
        .team-slider-section .section-subtitle {
            color: #aaa;
            font-size: 16px;
            margin-bottom: 50px;
        }

        .team-slider-container {
            width: 100%;
            height: 500px;
            overflow: hidden;
            position: relative;
        }

        .team-slide-wrapper {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .team-slide-item {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.6);
            width: 700px;
            max-width: 85%;
            height: 400px;
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
            transition: all 0.7s cubic-bezier(0.22, 0.61, 0.36, 1);
            opacity: 0;
            pointer-events: none;
            display: flex;
            align-items: center;
            padding: 40px;
            overflow: hidden;
            background: #0d1327; /* Rangi ya nyuma ikiwa video inabainika polepole */
        }

        /* Video Background */
        .team-bg-video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            transform: translate(-50%, -50%);
            object-fit: cover;
            z-index: 0;
            pointer-events: none;
            opacity: 0.2;
            transition: opacity 0.5s ease;
        }

        /* Active slide (kuu) */
        .team-slide-item.active {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
            pointer-events: auto;
            z-index: 10;
        }
        .team-slide-item.active .team-bg-video {
            opacity: 1;
        }

        /* Slides zilizo karibu (kwa ajili ya kuonyesha animation) */
        .team-slide-item.prev-slide {
            transform: translate(-150%, -50%) scale(0.8);
            opacity: 0.6;
            z-index: 5;
        }
        .team-slide-item.next-slide {
            transform: translate(50%, -50%) scale(0.8);
            opacity: 0.6;
            z-index: 5;
        }

        .team-slide-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(7, 11, 26, 0.85), rgba(13, 19, 39, 0.9));
            border-radius: 25px;
            z-index: 1;
        }

        .team-slide-content {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 40px;
            width: 100%;
            color: white;
        }

        .team-member-image {
            flex-shrink: 0;
        }
        .team-member-image img, 
        .team-avatar {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            border: 5px solid #FFB347;
            object-fit: cover;
            box-shadow: 0 0 40px rgba(255, 179, 71, 0.3);
        }
        .team-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            font-weight: 700;
            background: linear-gradient(135deg, #FFB347, #ff7b00);
            color: #070b1a;
        }

        .team-member-info {
            text-align: left;
            flex: 1;
        }
        .team-name {
            font-size: 32px;
            font-weight: 700;
            color: #FFB347;
        }
        .team-specialty {
            font-size: 16px;
            color: #aaa;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .team-specialty i { color: #FFB347; }
        .team-bio {
            font-size: 15px;
            line-height: 1.8;
            color: #ddd;
            margin-bottom: 20px;
        }

        .team-stats-large {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: rgba(255, 255, 255, 0.05);
            padding: 10px 20px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid rgba(255,179,71,0.1);
        }
        .stat-val {
            display: block;
            font-size: 20px;
            font-weight: 700;
            color: #FFB347;
        }
        .stat-val.jobs { color: #74B9FF; }
        .stat-val.earnings { color: #00B894; }
        .stat-lbl {
            font-size: 12px;
            color: #888;
        }

        .team-status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            background: rgba(0, 184, 148, 0.85);
            color: white;
        }
        .team-status-badge.busy {
            background: rgba(255, 107, 107, 0.85);
        }
        .team-status-badge .dot {
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            margin-right: 6px;
            background: white;
            animation: pulse-dot 1.5s infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.5); opacity: 0.7; }
        }

        /* Team Buttons & Dots */
        .team-nav {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 30px;
            transform: translateY(-50%);
            z-index: 20;
            pointer-events: none;
        }
        .team-nav-btn {
            width: 55px; height: 55px;
            border: none;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 24px;
            cursor: pointer;
            backdrop-filter: blur(10px);
            transition: 0.4s;
            pointer-events: auto;
        }
        .team-nav-btn:hover {
            background: #FFB347;
            color: #070b1a;
            transform: scale(1.1);
        }

        .team-indicators {
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 12px;
            z-index: 20;
        }
        .team-dot {
            width: 12px; height: 12px;
            border-radius: 50%;
            background: white;
            opacity: 0.3;
            transition: 0.4s;
            cursor: pointer;
        }
        .team-dot.active {
            width: 35px;
            border-radius: 30px;
            background: #FFB347;
            opacity: 1;
        }

        /* Responsive Team Slider */
        @media (max-width: 900px) {
            .team-slide-item { height: auto; padding: 30px; width: 90%; }
            .team-slide-content { flex-direction: column; text-align: center; gap: 20px; }
            .team-member-info { text-align: center; }
            .team-stats-large { justify-content: center; flex-wrap: wrap; }
            .team-name { font-size: 26px; }
            .team-avatar, .team-member-image img { width: 120px; height: 120px; font-size: 36px; }
            .team-nav { display: none; } /* Hide arrows on mobile, use swipe or dots */
        }
        @media (max-width: 500px) {
            .team-slider-container { height: 650px; }
            .team-stats-large { flex-direction: column; gap: 10px; align-items: center; }
            .stat-box { width: 80%; }
            .team-name { font-size: 22px; }
            .team-indicators { bottom: -30px; }
        }

        /* ============================================
           TOAST NOTIFICATIONS
           ============================================ */
        .toast-container {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .toast {
            padding: 16px 24px;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            font-size: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: slideInRight 0.5s ease;
            min-width: 300px;
            max-width: 400px;
        }
        .toast.success { background: linear-gradient(135deg, #22c55e, #16a34a); }
        .toast.error { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .toast.warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .toast.info { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .toast .toast-close {
            float: right;
            background: transparent;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            margin-left: 15px;
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes slideOutRight {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(100px); }
        }

        /* ================= SCROLL PROGRESS ================= */
        #scrollProgress {
            position: fixed;
            top: 85px;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #FFB347, #ff7b00);
            width: 0%;
            z-index: 9999;
            transition: width 0.1s;
        }

        /* ================= BACK TO TOP ================= */
        #backToTop {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, #FFB347, #ff7b00);
            color: #070b1a;
            border: none;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            display: none;
            z-index: 999;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 25px rgba(255, 179, 71, 0.5);
        }
        #backToTop:hover {
            transform: scale(1.15) rotate(10deg);
            box-shadow: 0 12px 40px rgba(255, 179, 71, 0.7);
        }

        /* ================= FLOATING WHATSAPP ================= */
        .whatsapp-float {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #25D366, #128C7E);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            z-index: 999;
            box-shadow: 0 8px 30px rgba(37, 211, 102, 0.5);
            animation: pulse-wa 2s infinite;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .whatsapp-float i { font-size: 32px; }
        @keyframes pulse-wa {
            0%, 100% { transform: scale(1); box-shadow: 0 8px 30px rgba(37, 211, 102, 0.5); }
            50% { transform: scale(1.08); box-shadow: 0 12px 45px rgba(37, 211, 102, 0.7); }
        }
        .whatsapp-float:hover {
            transform: scale(1.15) rotate(5deg);
            box-shadow: 0 12px 50px rgba(37, 211, 102, 0.8);
        }
        .whatsapp-float .tooltip {
            position: absolute;
            right: 75px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            white-space: nowrap;
            opacity: 0;
            transition: 0.3s;
            pointer-events: none;
        }
        .whatsapp-float:hover .tooltip { opacity: 1; }
        .whatsapp-float .tooltip::after {
            content: "";
            position: absolute;
            right: -8px;
            top: 50%;
            transform: translateY(-50%);
            border-left: 8px solid rgba(0, 0, 0, 0.8);
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
        }

        /* ================= FLOATING CALL ================= */
        .call-float {
            position: fixed;
            bottom: 175px;
            right: 30px;
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, #FFB347, #ff7b00);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #070b1a;
            text-decoration: none;
            z-index: 999;
            box-shadow: 0 5px 20px rgba(255, 179, 71, 0.4);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: pulse-call 2s infinite;
        }
        .call-float i { font-size: 24px; }
        @keyframes pulse-call {
            0%, 100% { transform: scale(1); box-shadow: 0 5px 20px rgba(255, 179, 71, 0.4); }
            50% { transform: scale(1.08); box-shadow: 0 8px 30px rgba(255, 179, 71, 0.6); }
        }
        .call-float:hover {
            transform: scale(1.15) rotate(5deg);
            box-shadow: 0 10px 40px rgba(255, 179, 71, 0.7);
        }

        /* ================= TESTIMONIALS ================= */
        .testimonials-section {
            padding: 80px 8%;
            background: rgba(255, 255, 255, 0.02);
            text-align: center;
        }
        .testimonials-section h2 {
            color: #FFB347;
            font-size: 36px;
            margin-bottom: 15px;
        }
        .testimonials-section .subtitle {
            color: #aaa;
            font-size: 16px;
            margin-bottom: 40px;
        }
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }
        .testimonial-card {
            background: #0d1327;
            padding: 30px 25px;
            border-radius: 20px;
            border: 1px solid rgba(255, 179, 71, 0.15);
            transition: 0.4s;
        }
        .testimonial-card:hover {
            transform: translateY(-10px);
            border-color: #FFB347;
            box-shadow: 0 10px 40px rgba(255, 179, 71, 0.1);
        }
        .testimonial-card .stars {
            color: #FFB347;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .testimonial-card p {
            color: #ddd;
            font-style: italic;
            line-height: 1.8;
            font-size: 15px;
        }
        .testimonial-card .author {
            color: #FFB347;
            font-weight: 600;
            margin-top: 15px;
            font-size: 16px;
        }
        .testimonial-card .role {
            color: #888;
            font-size: 13px;
        }

        /* ================= NEWSLETTER ================= */
        .newsletter-section {
            background: rgba(255, 179, 71, 0.05);
            padding: 50px 8%;
            text-align: center;
            border-top: 1px solid rgba(255, 179, 71, 0.1);
            border-bottom: 1px solid rgba(255, 179, 71, 0.1);
        }
        .newsletter-section h3 {
            color: #FFB347;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .newsletter-section p {
            color: #aaa;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .newsletter-form {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            max-width: 500px;
            margin: 0 auto;
        }
        .newsletter-form input {
            flex: 1;
            padding: 12px 20px;
            border-radius: 30px;
            border: 1px solid rgba(255, 179, 71, 0.2);
            background: rgba(255, 255, 255, 0.05);
            color: white;
            font-size: 14px;
            min-width: 200px;
            outline: none;
        }
        .newsletter-form input:focus {
            border-color: #FFB347;
        }
        .newsletter-form button {
            padding: 12px 30px;
            border: none;
            border-radius: 30px;
            background: linear-gradient(135deg, #FFB347, #ff7b00);
            color: #070b1a;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .newsletter-form button:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(255, 179, 71, 0.4);
        }

        /* ================= FOOTER ================= */
        .footer {
            background: #050814;
            padding: 60px 8% 30px;
        }
        .footer-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 50px;
        }
        .footer-logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            text-decoration: none;
        }
        .footer-logo img {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            border: 2px solid #FFB347;
            transition: 0.3s;
        }
        .footer-logo img:hover {
            transform: scale(1.05);
        }
        .footer-logo h2 {
            color: #FFB347;
            font-weight: 700;
        }
        .footer-text {
            color: #bbb;
            line-height: 1.9;
            font-size: 14px;
        }
        .footer-title {
            color: #fff;
            font-size: 20px;
            margin-bottom: 25px;
            position: relative;
            font-weight: 600;
        }
        .footer-title::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -8px;
            width: 60px;
            height: 3px;
            background: #FFB347;
        }
        .footer-links a {
            display: block;
            color: #bbb;
            text-decoration: none;
            margin-bottom: 14px;
            transition: 0.3s;
            font-weight: 300;
        }
        .footer-links a:hover {
            color: #FFB347;
            transform: translateX(5px);
        }
        .contact-info div {
            margin-bottom: 16px;
            color: #bbb;
            line-height: 1.7;
        }
        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        .social-icons a {
            width: 45px;
            height: 45px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            color: white;
            text-decoration: none;
            font-size: 20px;
            font-weight: 600;
            transition: 0.4s;
        }
        .social-icons a:hover {
            background: #FFB347;
            color: #070b1a;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(255, 179, 71, 0.2);
        }
        .footer-bottom {
            margin-top: 60px;
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            text-align: center;
            color: #888;
            font-size: 14px;
        }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 1100px) {
            .slide .item:nth-child(4) { left: calc(60% + 220px); }
            .slide .item:nth-child(5) { left: calc(60% + 440px); }
            .slide .item:nth-child(6) { left: calc(60% + 660px); }
        }
        @media (max-width: 900px) {
            #scrollProgress { top: 75px; }
            .header { padding: 0 20px; height: 75px; }
            .header.scrolled { height: 65px; }
            .navbar {
                position: absolute;
                top: 75px;
                left: -100%;
                width: 100%;
                background: #0d1327;
                flex-direction: column;
                padding: 35px 0;
                transition: 0.5s;
                border-bottom: 1px solid rgba(255, 179, 71, 0.1);
                gap: 20px;
            }
            .navbar.active { left: 0; }
            .menu-btn { display: block; }
            .auth-buttons { display: flex !important; flex-direction: column; gap: 10px; margin-top: 15px; }
            .content { width: 90%; left: 25px; bottom: 100px; }
            .content .name { font-size: 40px; }
            .content .des { font-size: 14px; }
            .slide .item:nth-child(4),
            .slide .item:nth-child(5),
            .slide .item:nth-child(6) { display: none; }
            .slide .item:nth-child(3) { width: 140px; height: 220px; left: 70%; }
        }
        @media (max-width: 600px) {
            .content .name { font-size: 32px; }
            .content .des { font-size: 13px; line-height: 1.6; }
            .content button { padding: 12px 24px; font-size: 13px; }
            .slide .item:nth-child(3) { display: none; }
            .navigation { right: 20px; bottom: 30px; }
            .nav-btn { width: 50px; height: 50px; font-size: 22px; }
            .indicators { left: 20px; bottom: 40px; }
            .logo img { width: 55px; height: 60px; }
            .logo h1 { font-size: 16px; }
            .toast { min-width: 90%; max-width: 90%; font-size: 13px; padding: 14px 18px; }
            .toast-container { right: 10px; left: 10px; top: 90px; }
            .testimonials-section { padding: 50px 5%; }
            .testimonials-section h2 { font-size: 28px; }
            .team-slider-section { padding: 50px 0; }
            .team-slider-section .section-title { font-size: 28px; }
            .footer { padding: 40px 5% 20px; }
            .whatsapp-float { bottom: 80px; right: 15px; width: 50px; height: 50px; }
            .whatsapp-float .tooltip { display: none; }
            .call-float { bottom: 145px; right: 15px; width: 45px; height: 45px; }
            .call-float i { font-size: 20px; }
            #backToTop { bottom: 210px; right: 15px; width: 45px; height: 45px; font-size: 20px; }
            .newsletter-form input { min-width: 100%; }
        }
        @media (max-width: 400px) {
            .content .name { font-size: 26px; }
            .logo h1 { font-size: 14px; }
            .logo img { width: 45px; height: 50px; }
        }
    </style>
</head>
<body>

    <!-- ================= PRELOADER ================= -->
    <div id="preloader">
        <div class="spinner"></div>
    </div>

    <div class="light light1"></div>
    <div class="light light2"></div>

    <!-- ================= SCROLL PROGRESS BAR ================= -->
    <div id="scrollProgress"></div>

    <!-- ================= TOAST CONTAINER ================= -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- ================= HEADER ================= -->
    <header class="header" id="header">
        <a href="index.php" class="logo">
            <img src="./images/GARAGE.jpg" alt="Rasmak Garage Logo" 
                 loading="lazy"
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%23FFB347%22/%3E%3Ctext x=%2250%22 y=%2268%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2248%22 font-weight=%22bold%22 font-family=%22Arial%22%3ERG%3Ctext%3E%3C/svg%3E'" />
            <h1>RASMAK GARAGE</h1>
        </a>
        <nav class="navbar" id="navbar" aria-label="Main Navigation">
            <a href="index.php" class="active" aria-current="page">Home</a>
            <a href="about.php">About</a>
            <a href="services.php">Services</a>
            <a href="contact.php">Contact</a>
            <?php if ($is_admin): ?>
            <a href="admin/dashboard.php" style="color: #FFB347;" aria-label="Admin Dashboard">📊 Admin</a>
            <?php endif; ?>
            
            <div class="auth-buttons" id="authButtons">
                <?php if ($is_logged_in): ?>
                    <div class="user-name">
                        <span class="user-icon" onclick="window.location.href='<?php echo $is_admin ? 'admin/dashboard.php' : 'dashboard.php'; ?>'" title="Go to Dashboard" role="button" tabindex="0">
                            <?php echo strtoupper(substr($user_fullname, 0, 2)); ?>
                        </span>
                        <?php echo htmlspecialchars($user_fullname); ?>
                    </div>
                    <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')" aria-label="Logout">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="login-btn">Login</a>
                    <a href="registration.php" class="register-btn">Register</a>
                <?php endif; ?>
            </div>
        </nav>
        <button class="menu-btn" id="menuBtn" aria-label="Toggle menu" aria-expanded="false">☰</button>
    </header>

    <!-- ================= MAIN SLIDER (SERVICES) ================= -->
    <div class="container" role="region" aria-label="Services Slider">
        <div class="slide">

            <!-- Slide 1 -->
            <div class="item" style="background-image:url('./images/professional-repair.webp');" loading="lazy">
                <div class="content">
                    <div class="name">Professional Repair</div>
                    <div class="des">Professional repair services with experienced mechanics and modern equipment. Quality guaranteed.</div>
                    <button class="service-btn" data-service="Professional Repair" onclick="bookService('Professional Repair')" aria-label="Book Professional Repair">Book Service</button>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="item" style="background-image:url('./images/oil-service.jpg');" loading="lazy">
                <div class="content">
                    <div class="name">Oil Service</div>
                    <div class="des">Premium oil changing and engine maintenance for long engine life. Trusted by hundreds of customers.</div>
                    <button class="service-btn" data-service="Oil Change" onclick="bookService('Oil Change')" aria-label="Book Oil Change">Get Service</button>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="item" style="background-image:url('./images/brake-repair.jpg');" loading="lazy">
                <div class="content">
                    <div class="name">Brake Repair</div>
                    <div class="des">Reliable brake system maintenance and safety inspections. Your safety is our priority.</div>
                    <button class="service-btn" data-service="Brake Repair" onclick="bookService('Brake Repair')" aria-label="Book Brake Repair">Book Now</button>
                </div>
            </div>

            <!-- Slide 4 -->
            <div class="item" style="background-image:url('./images/diagnostics.jpg');" loading="lazy">
                <div class="content">
                    <div class="name">Diagnostics</div>
                    <div class="des">Advanced computerized diagnostics for all modern vehicles. Fast and accurate results.</div>
                    <button class="service-btn" data-service="Diagnostics" onclick="bookService('Diagnostics')" aria-label="Book Diagnostics">Scan Now</button>
                </div>
            </div>

            <!-- Slide 5 -->
            <div class="item" style="background-image:url('./images/maintenance.jpg');" loading="lazy">
                <div class="content">
                    <div class="name">Maintenance</div>
                    <div class="des">Complete vehicle maintenance and professional diagnostics solutions. Keep your car running smoothly.</div>
                    <button class="service-btn" data-service="Maintenance" onclick="bookService('Maintenance')" aria-label="Book Maintenance">Learn More</button>
                </div>
            </div>

        </div>

        <div class="navigation">
            <button class="nav-btn prev" aria-label="Previous slide">‹</button>
            <button class="nav-btn next" aria-label="Next slide">›</button>
        </div>

        <div class="indicators" role="tablist">
            <div class="dot active" data-index="0" role="tab" aria-selected="true"></div>
            <div class="dot" data-index="1" role="tab" aria-selected="false"></div>
            <div class="dot" data-index="2" role="tab" aria-selected="false"></div>
            <div class="dot" data-index="3" role="tab" aria-selected="false"></div>
            <div class="dot" data-index="4" role="tab" aria-selected="false"></div>
        </div>
    </div>

    <!-- ============================================
    TEAM VIDEO SLIDER SECTION
    ============================================ -->
    <section class="team-slider-section" aria-label="Our Mechanics Team">
        <h2 class="section-title">👨‍🔧 Our Expert Team</h2>
        <p class="section-subtitle">Meet our professional mechanics dedicated to keeping your car in perfect condition</p>
        
        <div class="team-slider-container">
            <div class="team-slide-wrapper">
                <?php foreach ($mechanics as $index => $mechanic): 
                    $status = $mechanic['status'] ?? 'available';
                    $statusClass = $status === 'busy' ? 'busy' : 'available';
                    $initial = strtoupper(substr($mechanic['fullname'], 0, 2));
                    $activeJobs = (int)($mechanic['active_jobs'] ?? 0);
                    $totalEarnings = (float)($mechanic['total_earnings'] ?? 0);
                    $experience = (int)($mechanic['experience_years'] ?? rand(3, 10));
                    $specialty = $mechanic['specialty'] ?? 'Auto Specialist';
                    $bio = $mechanic['bio'] ?? 'Experienced mechanic dedicated to providing quality service and ensuring customer satisfaction.';
                    $profileImage = !empty($mechanic['profile_image']) ? $mechanic['profile_image'] : '';
                ?>
                <div class="team-slide-item">
                    
                    <!-- VIDEO BACKGROUND -->
                    <!-- BADILISHA HAPA: 'src' inapaswa kuwa URL ya video yako. Mfano huu ni video ya majaribio ya mtandaoni -->
                    <video class="team-bg-video" src="https://www.w3schools.com/html/mov_bbb.mp4" muted loop playsinline></video>
                    
                    <!-- Overlay ya giza kwa ajili ya maandishi kuonekana vizuri -->
                    <div class="team-slide-overlay"></div>
                    
                    <div class="team-slide-content">
                        <div class="team-member-image">
                            <?php if (!empty($profileImage)): ?>
                                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="<?php echo htmlspecialchars($mechanic['fullname']); ?>">
                            <?php else: ?>
                                <div class="team-avatar">
                                    <?php echo $initial; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="team-member-info">
                            <div class="team-name"><?php echo htmlspecialchars($mechanic['fullname']); ?></div>
                            <div class="team-specialty"><i class="fas fa-bolt"></i> <?php echo htmlspecialchars($specialty); ?></div>
                            
                            <div class="team-bio"><?php echo htmlspecialchars($bio); ?></div>
                            
                            <div class="team-stats-large">
                                <div class="stat-box">
                                    <span class="stat-val jobs"><?php echo $activeJobs; ?></span>
                                    <span class="stat-lbl">Active Jobs</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-val earnings">TZS <?php echo number_format($totalEarnings / 1000, 0); ?>K</span>
                                    <span class="stat-lbl">Total Earnings</span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-val"><?php echo $experience; ?>+</span>
                                    <span class="stat-lbl">Years Exp.</span>
                                </div>
                            </div>
                            
                            <div class="team-status-badge <?php echo $statusClass; ?>">
                                <span class="dot"></span> <?php echo $status === 'busy' ? 'Currently Busy' : 'Available for Service'; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Navigation Buttons for Team Slider -->
            <div class="team-nav">
                <button class="team-nav-btn team-prev" aria-label="Previous team member">‹</button>
                <button class="team-nav-btn team-next" aria-label="Next team member">›</button>
            </div>

            <!-- Indicators for Team Slider -->
            <div class="team-indicators" role="tablist">
                <?php foreach ($mechanics as $index => $mechanic): ?>
                    <div class="team-dot <?php echo $index === 0 ? 'active' : ''; ?>" data-team-index="<?php echo $index; ?>" role="tab" aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ================= TESTIMONIALS ================= -->
    <section class="testimonials-section" aria-label="Customer Reviews">
        <h2>What Our Customers Say</h2>
        <p class="subtitle">Real reviews from real people who trust Rasmak Garage</p>
        
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p>"Best garage in Zanzibar! My car runs like new after every service. The team is professional and friendly."</p>
                <div class="author">— John Doe</div>
                <div class="role">Toyota Customer</div>
            </div>
            
            <div class="testimonial-card">
                <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p>"Fast, reliable, and affordable. I highly recommend Rasmak Garage for anyone needing quality car maintenance."</p>
                <div class="author">— Sarah M.</div>
                <div class="role">Nissan Customer</div>
            </div>
            
            <div class="testimonial-card">
                <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p>"They diagnosed my car's problem quickly and fixed it at a fair price. I'm a customer for life!"</p>
                <div class="author">— Michael K.</div>
                <div class="role">BMW Customer</div>
            </div>
        </div>
    </section>

    <!-- ================= NEWSLETTER SECTION ================= -->
    <section class="newsletter-section" aria-label="Email Newsletter">
        <h3>Stay Updated</h3>
        <p>Subscribe to our newsletter for exclusive offers, service tips, and garage news.</p>
        <form class="newsletter-form" id="newsletterForm" onsubmit="return handleNewsletter(event)">
            <input type="email" id="newsletterEmail" placeholder="Enter your email address" required />
            <button type="submit"><i class="fas fa-paper-plane"></i> Subscribe</button>
        </form>
    </section>

    <!-- ================= FOOTER ================= -->
    <footer class="footer" role="contentinfo">
        <div class="footer-container">
            <div>
                <a href="index.php" class="footer-logo">
                    <img src="./images/GARAGE.jpg" alt="Rasmak Garage Logo" loading="lazy" />
                    <h2>RASMAK GARAGE</h2>
                </a>
                <div class="footer-text">
                    Professional garage services in Zanzibar with trusted mechanics,
                    vehicle diagnostics, maintenance and repair services for all car types.
                </div>
                <div class="social-icons">
                    <a href="https://wa.me/<?php echo $whatsapp_number; ?>" target="_blank" aria-label="WhatsApp" rel="noopener">W</a>
                    <a href="https://facebook.com" target="_blank" aria-label="Facebook" rel="noopener">F</a>
                    <a href="https://instagram.com" target="_blank" aria-label="Instagram" rel="noopener">I</a>
                    <a href="https://youtube.com" target="_blank" aria-label="YouTube" rel="noopener">Y</a>
                </div>
            </div>
            <div>
                <h3 class="footer-title">Quick Links</h3>
                <div class="footer-links">
                    <a href="index.php">Home</a>
                    <a href="services.php">Services</a>
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact</a>
                    <a href="privacy.php" style="color: #888;">Privacy Policy</a>
                </div>
            </div>
            <div>
                <h3 class="footer-title">Services</h3>
                <div class="footer-links">
                    <a href="book-service.php?service=Engine Repair">Engine Repair</a>
                    <a href="book-service.php?service=Oil Change">Oil Change</a>
                    <a href="book-service.php?service=Brake Repair">Brake Repair</a>
                    <a href="book-service.php?service=Diagnostics">Diagnostics</a>
                    <a href="book-service.php?service=Maintenance">Maintenance</a>
                </div>
            </div>
            <div>
                <h3 class="footer-title">Contact Info</h3>
                <div class="contact-info">
                    <div>📍 Zanzibar, Tanzania</div>
                    <div>📞 <a href="tel:<?php echo $phone_number; ?>" style="color:#bbb; text-decoration:none;"><?php echo $phone_number; ?></a></div>
                    <div>📞 <a href="tel:+255622826068" style="color:#bbb; text-decoration:none;">+255 622 826 068</a></div>
                    <div>✉ <a href="mailto:<?php echo $email_address; ?>" style="color:#bbb; text-decoration:none;"><?php echo $email_address; ?></a></div>
                    <div>🕒 Mon - Sat : 8AM - 8PM</div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div>© 2026 Rasmak Garage | All Rights Reserved.</div>
            <div style="color: #555; font-size: 12px; margin-top: 10px;">
                👥 Unique Visitors: <?php echo number_format($stats['unique']); ?>
                <?php if ($stats['daily'] > 0): ?>
                | 📅 Today: <?php echo number_format($stats['daily']); ?>
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <!-- ================= COOKIE BANNER ================= -->
    <div id="cookieBanner" class="cookie-banner">
        <p>We use cookies to enhance your experience. By continuing, you agree to our <a href="privacy.php">Privacy Policy</a>.</p>
        <button id="acceptCookies">Accept</button>
    </div>

    <!-- ================= SCRIPTS ================= -->
    <script>
        // ============================================
        // PRELOADER
        // ============================================
        window.addEventListener('load', () => {
            document.getElementById('preloader').style.display = 'none';
        });

        // ============================================
        // TOAST NOTIFICATIONS
        // ============================================
        function showToast(message, type = 'info', duration = 5000) {
            const container = document.getElementById('toastContainer');
            if (!container) {
                console.error('Toast container not found!');
                return;
            }
            
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <span>${message}</span>
                <button class="toast-close" onclick="this.parentElement.remove()" aria-label="Close notification">&times;</button>
            `;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.5s ease forwards';
                setTimeout(() => toast.remove(), 500);
            }, duration);
        }

        // ============================================
        // BOOK SERVICE
        // ============================================
        function bookService(serviceName) {
            const btn = event.target;
            const originalText = btn.innerText;
            btn.innerHTML = 'Loading...';
            btn.disabled = true;
            
            const url = 'book-service.php?service=' + encodeURIComponent(serviceName);
            window.location.href = url;
        }

        // ============================================
        // MOBILE MENU
        // ============================================
        const menuBtn = document.getElementById('menuBtn');
        const navbar = document.getElementById('navbar');

        if (menuBtn) {
            menuBtn.addEventListener('click', () => {
                const isActive = navbar.classList.toggle('active');
                menuBtn.innerHTML = isActive ? "✕" : "☰";
                menuBtn.setAttribute('aria-expanded', isActive);
            });
        }

        document.querySelectorAll('.navbar a').forEach(link => {
            link.addEventListener('click', () => {
                navbar.classList.remove('active');
                menuBtn.innerHTML = "☰";
                menuBtn.setAttribute('aria-expanded', 'false');
            });
        });

        // ============================================
        // HEADER SCROLL EFFECT
        // ============================================
        const header = document.getElementById('header');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // ============================================
        // SCROLL PROGRESS BAR
        // ============================================
        window.addEventListener('scroll', () => {
            const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            document.getElementById('scrollProgress').style.width = scrolled + '%';
        });

        // ============================================
        // BACK TO TOP        // ============================================
        const backToTop = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 400) {
                backToTop.style.display = 'flex';
                backToTop.style.alignItems = 'center';
                backToTop.style.justifyContent = 'center';
            } else {
                backToTop.style.display = 'none';
            }
        });
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // ============================================
        // MAIN SLIDER (SERVICES)
        // ============================================
        const slideContainer = document.querySelector('.slide');
        const nextBtn = document.querySelector('.next');
        const prevBtn = document.querySelector('.prev');
        const dots = document.querySelectorAll('.dot');
        const totalItems = 5;
        let currentIndex = 0;
        let autoInterval;
        let isTransitioning = false;

        function updateDots() {
            dots.forEach((dot, i) => {
                const isActive = i === currentIndex;
                dot.classList.toggle('active', isActive);
                dot.setAttribute('aria-selected', isActive);
            });
        }

        function goToNext() {
            if (isTransitioning) return;
            isTransitioning = true;
            const items = document.querySelectorAll('.slide .item');
            if (items.length === 0) {
                isTransitioning = false;
                return;
            }
            slideContainer.appendChild(items[0]);
            currentIndex = (currentIndex + 1) % totalItems;
            updateDots();
            setTimeout(() => { isTransitioning = false; }, 700);
        }

        function goToPrev() {
            if (isTransitioning) return;
            isTransitioning = true;
            const items = document.querySelectorAll('.slide .item');
            if (items.length === 0) {
                isTransitioning = false;
                return;
            }
            slideContainer.insertBefore(items[items.length - 1], items[0]);
            currentIndex = (currentIndex - 1 + totalItems) % totalItems;
            updateDots();
            setTimeout(() => { isTransitioning = false; }, 700);
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', goToNext);
        }
        if (prevBtn) {
            prevBtn.addEventListener('click', goToPrev);
        }

        autoInterval = setInterval(goToNext, 5000);

        const container = document.querySelector('.container');
        container.addEventListener('mouseenter', () => clearInterval(autoInterval));
        container.addEventListener('mouseleave', () => {
            autoInterval = setInterval(goToNext, 5000);
        });

        // ============================================
        // TEAM SLIDER LOGIC (WITH VIDEO SUPPORT)
        // ============================================
        const teamSlides = document.querySelectorAll('.team-slide-item');
        const teamNextBtn = document.querySelector('.team-next');
        const teamPrevBtn = document.querySelector('.team-prev');
        const teamDots = document.querySelectorAll('.team-dot');
        let currentTeamIndex = 0;
        let teamAutoInterval;
        let isTeamTransitioning = false;

        function updateTeamSlider(index) {
            if (isTeamTransitioning) return;
            isTeamTransitioning = true;

            // Pata video zote kwenye slides
            const allVideos = document.querySelectorAll('.team-bg-video');
            
            // Simamisha video zote kwanza kabla ya kubadilisha slide
            allVideos.forEach(v => {
                v.pause();
                v.currentTime = 0; // Rudi mwanzo wa video
                v.style.opacity = '0.2'; // Fanya video zisizo active zionekane hafifu
            });

            if (index < 0) index = teamSlides.length - 1;
            if (index >= teamSlides.length) index = 0;
            currentTeamIndex = index;

            teamSlides.forEach((slide, i) => {
                slide.classList.remove('active', 'prev-slide', 'next-slide');
                
                if (i === index) {
                    slide.classList.add('active');
                    // Cheza video ya slide hii
                    const vid = slide.querySelector('.team-bg-video');
                    if(vid) {
                        vid.style.opacity = '1'; // Fanya video ionekane
                        vid.play().catch(e => console.log("Autoplay blocked:", e)); 
                    }
                } else if (i === (index - 1 + teamSlides.length) % teamSlides.length) {
                    slide.classList.add('prev-slide');
                } else if (i === (index + 1) % teamSlides.length) {
                    slide.classList.add('next-slide');
                } else {
                    slide.style.opacity = '0';
                    slide.style.transform = 'translate(-50%, -50%) scale(0.4)';
                }
            });

            teamDots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
                dot.setAttribute('aria-selected', i === index);
            });

            setTimeout(() => { isTeamTransitioning = false; }, 700);
        }

        function nextTeamSlide() { updateTeamSlider(currentTeamIndex + 1); }
        function prevTeamSlide() { updateTeamSlider(currentTeamIndex - 1); }

        if (teamNextBtn) teamNextBtn.addEventListener('click', nextTeamSlide);
        if (teamPrevBtn) teamPrevBtn.addEventListener('click', prevTeamSlide);

        teamDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                updateTeamSlider(index);
                resetTeamAutoSlide();
            });
        });

        function startTeamAutoSlide() {
            teamAutoInterval = setInterval(nextTeamSlide, 8000); // Muda umeongezwa kidogo kwa sababu video inahitaji muda kucheza
        }
        function stopTeamAutoSlide() { clearInterval(teamAutoInterval); }
        function resetTeamAutoSlide() {
            stopTeamAutoSlide();
            startTeamAutoSlide();
        }

        const teamContainer = document.querySelector('.team-slider-container');
        if (teamContainer) {
            teamContainer.addEventListener('mouseenter', stopTeamAutoSlide);
            teamContainer.addEventListener('mouseleave', startTeamAutoSlide);
        }
        startTeamAutoSlide();

        // ============================================
        // KEYBOARD SUPPORT FOR SLIDERS
        // ============================================
        document.addEventListener('keydown', function(e) {
            // Shift + Arrow kwa Team Slider
            if (e.shiftKey && (e.key === 'ArrowLeft' || e.key === 'ArrowRight')) {
                e.preventDefault();
                if (e.key === 'ArrowLeft') prevTeamSlide();
                if (e.key === 'ArrowRight') nextTeamSlide();
                resetTeamAutoSlide();
            }
            // Arrow pekee kwa Main Slider
            if (!e.shiftKey && (e.key === 'ArrowLeft' || e.key === 'ArrowRight')) {
                e.preventDefault();
                if (e.key === 'ArrowLeft') goToPrev();
                if (e.key === 'ArrowRight') goToNext();
            }
        });

        // ============================================
        // NEWSLETTER SUBSCRIBE
        // ============================================
        function handleNewsletter(e) {
            e.preventDefault();
            
            const emailInput = document.getElementById('newsletterEmail');
            const email = emailInput.value.trim();
            const button = e.target.querySelector('button');
            
            if (!email || !email.includes('@')) {
                showToast('⚠️ Please enter a valid email address.', 'warning');
                return false;
            }
            
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
            button.disabled = true;
            
            const formData = new FormData();
            formData.append('email', email);
            
            fetch('subscribe.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Subscribe response:', data);
                
                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    emailInput.value = '';
                    button.innerHTML = '<i class="fas fa-check"></i> Subscribed!';
                    setTimeout(() => {
                        button.innerHTML = '<i class="fas fa-paper-plane"></i> Subscribe';
                        button.disabled = false;
                    }, 2000);
                } else {
                    showToast(data.message || '❌ Something went wrong. Please try again.', 'error');
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('❌ Connection error. Please try again.', 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            });
            
            return false;
        }

        // ============================================
        // COOKIE CONSENT
        // ============================================
        if (!localStorage.getItem('cookiesAccepted')) {
            document.getElementById('cookieBanner').style.display = 'block';
        }
        document.getElementById('acceptCookies').addEventListener('click', () => {
            localStorage.setItem('cookiesAccepted', 'true');
            document.getElementById('cookieBanner').style.display = 'none';
        });

        // ============================================
        // SERVICE WORKER FOR PWA
        // ============================================
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('Service Worker registered with scope:', registration.scope);
                })
                .catch(error => {
                    console.log('Service Worker registration failed:', error);
                });
        }

        // ============================================
        // CONSOLE INFO
        // ============================================
        console.log('✅ Rasmak Garage - Frontend Page Loaded (v6 - With Video Slider)');
        console.log('👨‍🔧 Team Members: <?php echo count($mechanics); ?>');
        console.log('📊 Unique Visitors: <?php echo number_format($stats['unique']); ?>');
        console.log('📅 Today\'s Visitors: <?php echo number_format($stats['daily']); ?>');
    </script>
</body>
</html>