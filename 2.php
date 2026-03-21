<?php
session_start();
require_once 'db_connect.php';

$dbVenues = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM TRAINER_VENUES WHERE approval_status = 'Approved' ORDER BY created_at DESC");
        $stmt->execute();
        $dbVenues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Fallback to empty
    }
}
$venueCount = count($dbVenues);

// Fetch user data for sidebar/header
$userName = 'Player One';
$email = $_SESSION['user'] ?? '';
if ($pdo && $email) {
    try {
        $stmt = $pdo->prepare("SELECT full_name FROM USERS WHERE email = ?");
        $stmt->execute([$email]);
        $userName = $stmt->fetchColumn() ?: 'Player One';
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Sports Venues - PlayMatrix</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet (Free Map) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        :root {
            --bg-dark: #050505;
            --bg-card: #121212;
            --primary-green: #39ff14;
            --primary-green-dim: #2cb812;
            --text-white: #ffffff;
            --text-gray: #a1a1a1;
            --glass-border: rgba(255, 255, 255, 0.08);
            --gradient-card: linear-gradient(145deg, #1a1a1a, #0d0d0d);
            --input-bg: #0a0a0a;
            --nav-height: 80px;
            --sidebar-width: 260px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-white);
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            background: #0a0a0a;
            border-right: 1px solid var(--glass-border);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            padding: 2rem 1.5rem;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(57, 255, 20, 0.2); border-radius: 10px; }
        .sidebar::-webkit-scrollbar-thumb:hover { background: var(--primary-green); }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 3rem;
            text-decoration: none;
            color: white;
        }

        .brand-icon {
            background: var(--primary-green);
            color: var(--bg-dark);
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 1.2rem;
            box-shadow: 0 0 15px rgba(57, 255, 20, 0.3);
        }

        .brand span { font-size: 1.4rem; font-weight: 700; letter-spacing: 1px; }

        .nav-menu { list-style: none; flex-grow: 1; }
        .nav-item { margin-bottom: 0.5rem; }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 15px;
            color: var(--text-gray);
            text-decoration: none;
            border-radius: 10px;
            transition: 0.3s;
            font-weight: 500;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(57, 255, 20, 0.1);
            color: var(--primary-green);
        }

        .nav-link i { font-size: 1.1rem; width: 20px; text-align: center; }
        .logout-section { border-top: 1px solid var(--glass-border); padding-top: 1.5rem; }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background: var(--input-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 0.5rem 1rem;
            width: 300px;
        }

        .search-bar input {
            background: transparent;
            border: none;
            color: var(--text-white);
            width: 100%;
            margin-left: 10px;
            outline: none;
        }

        .search-bar i {
            color: var(--text-gray);
        }

        .profile-icon {
            width: 40px;
            height: 40px;
            background: #222;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 1px solid var(--glass-border);
            transition: 0.3s;
        }

        .profile-icon:hover {
            border-color: var(--primary-green);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 2rem 3rem;
            min-height: 100vh;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            background: #121212;
            padding: 8px 15px;
            border-radius: 50px;
            border: 1px solid var(--glass-border);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #39ff14, #00d2ff);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #000;
        }

        /* Header Section */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 1.5rem;
        }

        .page-title h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-title p {
            color: var(--text-gray);
            font-size: 1.1rem;
        }

        /* Categories Bar Styles */
        .categories-bar {
            display: flex;
            gap: 2.5rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 1px;
        }

        .category-item {
            text-decoration: none;
            color: var(--text-white);
            font-size: 1.1rem;
            font-weight: 500;
            padding-bottom: 12px;
            position: relative;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .category-item:hover {
            color: var(--primary-green);
        }

        .category-item.active {
            color: var(--primary-green);
            font-weight: 700;
        }

        .category-item.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--primary-green);
            border-radius: 2px 2px 0 0;
            box-shadow: 0 -2px 10px rgba(57, 255, 20, 0.4);
        }

        .count {
            color: var(--text-gray);
            font-size: 1rem;
            font-weight: 400;
        }

        .category-item.active .count {
            color: var(--primary-green-dim);
        }

        /* Venue Grid */
        .venues-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .venue-card {
            background: var(--gradient-card);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 280px;
            opacity: 0;
            /* Hidden initially for animation */
            animation: fadeInUp 0.6s ease-out forwards;
            cursor: pointer;
        }

        .venue-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(57, 255, 20, 0.15);
            border-color: var(--primary-green);
        }

        .venue-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.03), transparent);
            transition: 0.5s;
        }

        .venue-card:hover::before {
            left: 100%;
        }

        .card-image {
            width: 100%;
            height: 180px;
            position: relative;
            overflow: hidden;
            background: #1a1a1a;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s ease;
            opacity: 0;
            animation: fadeIn 0.5s forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        .venue-card:hover .card-image img {
            transform: scale(1.15);
        }

        .badge-container {
            position: absolute;
            top: 10px;
            left: 10px;
            display: flex;
            gap: 5px;
        }

        .badge {
            padding: 4px 10px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 4px;
        }

        .badge-sport {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            color: var(--text-white);
            border: 1px solid var(--glass-border);
        }

        .badge-bookable {
            background: var(--primary-green);
            color: #000;
        }

        .card-content {
            padding: 1.2rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .venue-name {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-white);
        }

        .venue-location {
            color: var(--text-gray);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.8rem;
        }

        .rating-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .rating-box {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .rating-count {
            color: var(--text-gray);
            font-size: 0.8rem;
        }

        .dist-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            color: var(--primary-green);
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 700;
            border: 1px solid var(--primary-green);
            z-index: 5;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .location-box.searching {
            animation: pulse-border 1.5s infinite;
        }

        @keyframes pulse-border {
            0% {
                box-shadow: 0 0 0 0 rgba(57, 255, 20, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(57, 255, 20, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(57, 255, 20, 0);
            }
        }

        .load-more {
            display: none;
            width: fit-content;
            margin: 3rem auto;
            padding: 1rem 3rem;
            background: var(--input-bg);
            border: 1px solid var(--glass-border);
            color: var(--text-white);
            border-radius: 30px;
            cursor: pointer;
            transition: 0.3s;
            font-weight: 600;
        }

        .load-more:hover {
            border-color: var(--primary-green);
            color: var(--primary-green);
            box-shadow: 0 0 20px rgba(57, 255, 20, 0.2);
            transform: translateY(-2px);
        }

        /* Map UI Styles */
        #map-container {
            position: fixed;
            top: 100px;
            right: 20px;
            bottom: 20px;
            width: 450px;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--primary-green);
            z-index: 1000;
            background: #000;
            transition: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(120%);
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.8);
        }

        #map-container.active {
            transform: translateX(0);
        }

        #map {
            width: 100%;
            height: 100%;
        }

        .map-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid var(--primary-green);
            color: var(--primary-green);
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 1001;
        }

        .map-toggle-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary-green);
            color: #000;
            padding: 12px 24px;
            border-radius: 50px;
            border: none;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            z-index: 999;
            box-shadow: 0 5px 20px rgba(57, 255, 20, 0.4);
            transition: 0.3s;
        }

        .map-toggle-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(57, 255, 20, 0.6);
        }

        /* Google Autocomplete Dark Theme */
        .pac-container {
            background-color: #1a1a1a !important;
            border: 1px solid var(--glass-border) !important;
            border-top: none !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
            border-radius: 0 0 12px 12px !important;
            margin-top: 5px !important;
            z-index: 2000 !important;
        }

        .pac-item {
            border-top: 1px solid var(--glass-border) !important;
            padding: 10px 15px !important;
            cursor: pointer !important;
            color: #fff !important;
        }

        .pac-item:hover {
            background-color: #252525 !important;
        }

        .pac-item-query {
            color: var(--primary-green) !important;
            font-size: 1rem !important;
        }
    </style>
</head>

<body>

    <div class="grid-bg"></div>

    <!-- Visual Map Component -->
    <div id="map-container" class="map-hidden">
        <div id="map"></div>
        <button class="map-close" onclick="toggleMap()"><i class="fa-solid fa-xmark"></i></button>
    </div>



    <aside class="sidebar">
        <a href="dashboard.php" class="brand">
            <div class="brand-icon"><i class="fa-solid fa-rocket"></i></div>
            <span>PLAYMATRIX</span>
        </a>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php?show=overview" class="nav-link">
                    <i class="fa-solid fa-house"></i>
                    <span>Overview</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php?show=play" class="nav-link">
                    <i class="fa-solid fa-play"></i>
                    <span>Play</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php?show=tournaments" class="nav-link">
                    <i class="fa-solid fa-trophy"></i>
                    <span>Tournaments</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php?show=coaching" class="nav-link">
                    <i class="fa-solid fa-user-graduate"></i>
                    <span>Coaching</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php?show=payments" class="nav-link">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Payment History</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="2.php" class="nav-link active">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Book Venue</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php?show=mygames" class="nav-link">
                    <i class="fa-solid fa-gamepad"></i>
                    <span>My Games</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php?show=membership" class="nav-link">
                    <i class="fa-solid fa-id-card"></i>
                    <span>Membership</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php?show=refunds" class="nav-link">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Refund Request</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php?show=requests" class="nav-link">
                    <i class="fa-solid fa-envelope-open-text"></i>
                    <span>Help & Requests</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php?show=settings" class="nav-link">
                    <i class="fa-solid fa-gears"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>

        <div class="logout-section">
            <a href="login.php" class="nav-link">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header>
            <div class="welcome-text">
                <h1 id="pageHeading">Sports Venues 🏟️</h1>
                <p>Discover and book premium venues near you</p>
            </div>
            <div style="display: flex; align-items: center; gap: 20px;">
                <div class="search-bar" style="width: 250px;">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" placeholder="Search venues...">
                </div>
                <div class="user-profile">
                    <span><?php echo htmlspecialchars($userName); ?></span>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($userName, 0, 1)); ?>
                    </div>
                </div>
            </div>
        </header>



        <div class="venues-grid" id="venuesContainer">
            <?php
            if (count($dbVenues) > 0) {
                foreach ($dbVenues as $v) {
                    $img = !empty($v['pic1']) ? $v['pic1'] : 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&q=80&w=600';
                    $vname = htmlspecialchars($v['venue_name']);
                    $vloc = htmlspecialchars($v['location']);
                    $vsport = htmlspecialchars($v['sports_available']);
                    $vid = $v['id'];
                    $vtype = 'trainer';
                    
                    echo "
                    <div class='venue-card' style='opacity:1; animation:none; cursor:pointer;' onclick=\"window.location.href='3.php?id={$vid}&type={$vtype}&name=" . urlencode($vname) . "&sport=" . urlencode($vsport) . "&area=" . urlencode($vloc) . "'\">
                        <div class='card-image'>
                            <img src='{$img}' style='opacity:1;'>
                            <div class='dist-badge'><i class='fa-solid fa-location-dot'></i> Local</div>
                            <div class='badge-container'><span class='badge badge-sport'>{$vsport}</span></div>
                        </div>
                        <div class='card-content'>
                            <h3 class='venue-name'>{$vname}</h3>
                            <p style='color:var(--text-gray); font-size:0.85rem;'><i class='fa-solid fa-location-dot' style='color:var(--primary-green); margin-right:5px;'></i>{$vloc}</p>
                            <div style='display:flex; justify-content:space-between; align-items:center; margin-top:10px;'>
                                <span style='color:var(--primary-green); font-size:0.8rem; font-weight:700;'>4.8 <i class='fa-solid fa-star'></i></span>
                                <span style='color:var(--text-gray); font-size:0.8rem;'>24 Reviews</span>
                            </div>
                        </div>
                    </div>";
                }
            } else {
                echo '<div style="grid-column: 1/-1; text-align:center; padding: 3rem; color:var(--text-gray);">No sports venues available for booking yet.</div>';
            }
            ?>
        </div>

        <button class="load-more">Load More</button>
    </div>

    <script>
        // State management
        const allVenues = <?php echo json_encode(array_map(function($v) {
            return [
                'id' => $v['id'],
                'name' => (string)$v['venue_name'],
                'sport' => (string)$v['sports_available'],
                'area' => (string)$v['location'],
                'rating' => '4.8',
                'reviews' => (int)rand(5, 45),
                'dist' => 'Local',
                'imgUrl' => !empty($v['pic1']) ? $v['pic1'] : 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&q=80&w=600',
                'type' => 'trainer'
            ];
        }, $dbVenues), JSON_UNESCAPED_UNICODE) ?: '[]'; ?>;
        
        let displayedCount = <?php echo count($dbVenues); ?>;
        const BATCH_SIZE = 25;
        let currentVenues = [...allVenues];

        const container = document.getElementById('venuesContainer');
        const loadMoreBtn = document.querySelector('.load-more');
        const searchInput = document.querySelector('.search-bar input');

        let mapInitialized = false;

        // Detect Google Maps Authentication/Billing Errors
        window.gm_authFailure = function () {
            console.error("Google Maps failed to load (Key/Billing issue). Switching to Matrix Fallback Mode.");
            document.getElementById('map-container').style.display = 'none';
            document.querySelector('.map-toggle-btn').style.display = 'none';

            // Show a helpful notification in the console or UI
            if (allVenues.length === 0) {
                generateFallbackData();
                renderBatch();
            }
        };

        window.addEventListener('load', () => {
            if (allVenues.length === 0) {
                generateFallbackData();
                renderBatch();
            }
        });

        loadMoreBtn.addEventListener('click', renderBatch);

        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const query = e.target.value;
                if (query.trim()) {
                    // Perform a fresh Google search for this keyword at current location
                    startGoogleSearch(currentLat, currentLng, query);
                }
            }
        });

        // Keep local filtering for fast typing
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            if (!query) {
                currentVenues = [...allVenues];
            } else {
                currentVenues = allVenues.filter(venue =>
                    venue.name.toLowerCase().includes(query) ||
                    venue.sport.toLowerCase().includes(query) ||
                    venue.area.toLowerCase().includes(query)
                );
            }
            container.innerHTML = '';
            displayedCount = 0;
            renderBatch();
        });

        let currentLat, currentLng;

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        let mainMap;
        let markers = [];
        const citySearchInput = document.getElementById('citySearch');

        function toggleMap() {
            document.getElementById('map-container').classList.toggle('active');
            if (mainMap) mainMap.invalidateSize();
        }

        // Initialize Free Leaflet Map
        function initMap() {
            mapInitialized = true;

            mainMap = L.map('map', {
                zoomControl: false,
                attributionControl: false
            }).setView([12.9716, 77.5946], 13); // Default Bangalore

            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(mainMap);

            detectLocation();
        }

        function searchByCityName(cityName) {
            const locationBox = document.querySelector('.location-box');
            locationBox.classList.add('searching');

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(cityName)}&limit=1`)
                .then(res => res.json())
                .then(data => {
                    locationBox.classList.remove('searching');
                    if (data && data[0]) {
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);
                        const name = data[0].display_name.split(',')[0];
                        handleNewLocation(lat, lon, name);
                    }
                });
        }

        function handleNewLocation(lat, lng, cityName) {
            updateLocationUI(cityName);
            startOSMSearch(lat, lng);
        }

        function detectLocation() {
            citySearchInput.value = "Detecting...";

            fetch("https://ipapi.co/json/")
                .then(res => res.json())
                .then(data => {
                    handleNewLocation(data.latitude, data.longitude, data.city);
                })
                .catch(() => {
                    handleNewLocation(12.9716, 77.5946, "Bangalore");
                });
        }

        function startOSMSearch(lat, lng, customKeyword = null) {
            currentLat = lat;
            currentLng = lng;
            if (mainMap) mainMap.setView([lat, lng], 13);
            
            // Overpass API Query disabled as per user request to only show added venues
        }

        function useFallback(reason) {
            console.log("Fallback: " + reason);
            generateFallbackData();
            renderBatch();
        }

        function processOSMResults(elements, userLoc) {
            allVenues.length = 0;
            markers.forEach(m => mainMap.removeLayer(m));
            markers = [];

            if (!elements || elements.length === 0) {
                useFallback("No venues found in this area");
                return;
            }

            elements.forEach((el, i) => {
                const elLat = el.lat || el.center.lat;
                const elLon = el.lon || el.center.lon;

                const dist = calculateDistance(userLoc.lat, userLoc.lng, elLat, elLon).toFixed(1);

                const name = el.tags.name || `${el.tags.sport || 'Sports'} Arena`;
                const sport = el.tags.sport ? el.tags.sport.charAt(0).toUpperCase() + el.tags.sport.slice(1) : "Sports";

                allVenues.push({
                    id: el.id,
                    name: name,
                    sport: sport,
                    area: el.tags["addr:suburb"] || el.tags["addr:city"] || "Nearby",
                    rating: (4.0 + Math.random()).toFixed(1),
                    reviews: Math.floor(Math.random() * 200),
                    dist: dist,
                    imgUrl: `https://loremflickr.com/600/400/${sport.toLowerCase().replace(/ /g, ',')}/all?lock=${i}`,
                    lat: elLat,
                    lng: elLon
                });

                const marker = L.circleMarker([elLat, elLon], {
                    radius: 8,
                    fillColor: "#39ff14",
                    color: "#000",
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 1
                }).addTo(mainMap);

                marker.bindPopup(`<b>${name}</b><br>${sport}`);
                markers.push(marker);
            });

            allVenues.sort((a, b) => parseFloat(a.dist) - parseFloat(b.dist));
            currentVenues = [...allVenues];
            displayedCount = 0;
            container.innerHTML = '';
            renderBatch();
        }

        if (tStr.includes('football') || tStr.includes('turf') || tStr.includes('stadium')) return "Football";
        if (tStr.includes('gym') || tStr.includes('fitness') || tStr.includes('health')) return "Fitness";
        return "Sports";
        }

        function updateLocationUI(cityName) {
            citySearchInput.value = cityName;
            const pageHeading = document.getElementById('pageHeading');
            pageHeading.textContent = `Sports Venues in ${cityName}`;
        }

        function generateFallbackData() {
            // Mock data disabled to only show added venues
            // currentVenues = [...allVenues];
        }

        function renderBatch() {
            const venuesToRender = currentVenues.slice(displayedCount, displayedCount + BATCH_SIZE);

            if (venuesToRender.length === 0 && displayedCount === 0) {
                container.innerHTML = '<div style="color:var(--text-gray); grid-column: 1/-1; text-align:center; padding: 2rem;">No venues found matching your search.</div>';
                loadMoreBtn.style.display = 'none';
                return;
            }

            let htmlFragment = '';

            venuesToRender.forEach((venue, index) => {
                // Staggered animation delay based on index in this batch
                const delay = (index % BATCH_SIZE) * 0.05;

                // Create query string for details page
                const queryParams = new URLSearchParams({
                    id: venue.id,
                    name: venue.name,
                    sport: venue.sport,
                    area: venue.area,
                    rating: venue.rating,
                    reviews: venue.reviews
                }).toString();

                htmlFragment += `
                    <div class="venue-card" data-id="${venue.id}" style="animation-delay: ${delay}s" onclick="window.location.href='3.php?${queryParams}&type=${venue.type}'">
                        <div class="card-image">
                            <img src="${venue.imgUrl}" loading="lazy" alt="${venue.sport}">
                            <div class="dist-badge">
                                <i class="fa-solid fa-person-running"></i> ${venue.dist} km
                            </div>
                            <div class="badge-container">
                                <span class="badge badge-sport">${venue.sport}</span>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3 class="venue-name">${venue.name}</h3>
                            <div class="venue-location">
                                <i class="fa-solid fa-location-dot" style="color:var(--primary-green)"></i>
                                <span>${venue.area}</span>
                            </div>
                            <div class="rating-row">
                                <div class="rating-box">
                                    <i class="fa-solid fa-star"></i> ${venue.rating}
                                </div>
                                <span class="rating-count">(${venue.reviews} Reviews)</span>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.insertAdjacentHTML('beforeend', htmlFragment);
            displayedCount += venuesToRender.length;

            if (displayedCount >= currentVenues.length) {
                loadMoreBtn.style.display = 'none';
            } else {
                loadMoreBtn.style.display = 'block';
            }
        }
    </script>
    <script>
        window.onload = initMap;
    </script>
</body>

</html>