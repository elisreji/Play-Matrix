<?php
session_start();
require_once 'db_connect.php';

// Check login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$currentUser = null;
if (file_exists('users.json')) {
    $users = json_decode(file_get_contents('users.json'), true);
    foreach ($users as $u) {
        if ($u['email'] === $_SESSION['user']) {
            $currentUser = $u;
            break;
        }
    }
}
$userName = $currentUser['name'] ?? 'Player';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Trainer - PlayMatrix</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg-dark: #050505;
            --bg-card: #121212;
            --bg-card-hover: #1e1e1e;
            --primary-green: #39ff14;
            --text-white: #ffffff;
            --text-gray: #a1a1a1;
            --glass-border: rgba(255, 255, 255, 0.08);
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

        /* Reusing Sidebar from Dashboard */
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
        }

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

        .brand span {
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .nav-menu {
            list-style: none;
            flex-grow: 1;
        }

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
            margin-bottom: 0.5rem;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(57, 255, 20, 0.1);
            color: var(--primary-green);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 2rem 3rem;
            min-height: 100vh;
        }

        /* Search Bar */
        .search-container {
            position: relative;
            margin-bottom: 3rem;
        }

        .search-input {
            width: 100%;
            padding: 18px 25px;
            padding-left: 55px;
            background: var(--bg-card);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            color: white;
            font-size: 1.1rem;
            transition: 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 20px rgba(57, 255, 20, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
            font-size: 1.2rem;
        }

        /* Categories Section */
        .categories-section {
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 1.5rem;
        }

        .category-card {
            aspect-ratio: 1/1;
            border-radius: 20px;
            background: linear-gradient(135deg, #1a1a1a, #0d0d0d);
            border: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: 0.3s;
            position: relative;
            overflow: hidden;
            group;
        }

        .category-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-green);
        }

        .cat-img {
            width: 60px;
            height: 60px;
            margin-bottom: 15px;
            object-fit: cover;
            filter: grayscale(100%);
            transition: 0.3s;
            opacity: 0.7;
        }

        .category-card:hover .cat-img {
            filter: grayscale(0%);
            opacity: 1;
            transform: scale(1.1);
        }

        .cat-label {
            color: var(--text-white);
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 1;
        }

        /* Filter Chips */
        .filters-row {
            display: flex;
            gap: 12px;
            margin-bottom: 2rem;
            overflow-x: auto;
            padding-bottom: 10px;
            scrollbar-width: none;
        }

        .filter-chip {
            padding: 10px 20px;
            background: var(--bg-card);
            border: 1px solid var(--glass-border);
            border-radius: 50px;
            color: var(--text-gray);
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }

        .filter-chip:hover,
        .filter-chip.active {
            background: rgba(57, 255, 20, 0.1);
            color: var(--primary-green);
            border-color: var(--primary-green);
        }

        /* Trainer Cards */
        .trainers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }

        .trainer-card {
            background: var(--bg-card);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            transition: 0.3s;
        }

        .trainer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            border-color: rgba(57, 255, 20, 0.3);
        }

        .card-header {
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .badge-icon {
            color: var(--text-gray);
            font-size: 1.1rem;
        }

        .rating-badge {
            background: #333;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .card-images {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px;
            height: 180px;
            padding: 0 15px;
        }

        .t-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
            background: #222;
        }

        .card-body {
            padding: 1.5rem;
        }

        .t-type {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-gray);
            background: #1a1a1a;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 8px;
        }

        .t-name {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: white;
        }

        .t-location {
            color: var(--text-gray);
            font-size: 0.85rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .t-audience {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: #ccc;
            margin-bottom: 1.5rem;
        }

        .connect-btn {
            width: 100%;
            padding: 14px;
            background: white;
            color: black;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .connect-btn:hover {
            background: var(--primary-green);
        }

        .card-footer {
            padding: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-gray);
        }

        /* Mock specific colors for categories */
        .bg-purple {
            background: linear-gradient(135deg, rgba(88, 28, 135, 0.4), rgba(59, 7, 100, 0.4));
        }

        .bg-cyan {
            background: linear-gradient(135deg, rgba(8, 145, 178, 0.4), rgba(21, 94, 117, 0.4));
        }

        .bg-green {
            background: linear-gradient(135deg, rgba(21, 128, 61, 0.4), rgba(20, 83, 45, 0.4));
        }

        .bg-yellow {
            background: linear-gradient(135deg, rgba(161, 98, 7, 0.4), rgba(113, 63, 18, 0.4));
        }

        .bg-blue {
            background: linear-gradient(135deg, rgba(29, 78, 216, 0.4), rgba(30, 58, 138, 0.4));
        }

        /* Dropdown Styles */
        .dropdown-container {
            position: relative;
            display: inline-block;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 110%;
            left: 0;
            background: #1a1a1a;
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 8px;
            min-width: 180px;
            z-index: 100;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }

        .dropdown-menu.show {
            display: block;
            animation: fadeInDown 0.2s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            padding: 10px 14px;
            color: var(--text-gray);
            cursor: pointer;
            border-radius: 8px;
            transition: 0.2s;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dropdown-item:hover {
            background: rgba(57, 255, 20, 0.1);
            color: var(--primary-green);
        }

        .dropdown-item.active {
            background: var(--primary-green);
            color: black;
            font-weight: 600;
        }

        .filter-chip i.fa-chevron-down {
            font-size: 0.7rem;
            margin-left: 5px;
            opacity: 0.7;
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <a href="dashboard.php" class="brand">
            <div class="brand-icon"><i class="fa-solid fa-rocket"></i></div>
            <span>PLAYMATRIX</span>
        </a>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fa-solid fa-house"></i>
                    <span>Overview</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php#coaching" class="nav-link active">
                    <i class="fa-solid fa-user-graduate"></i>
                    <span>Coaching</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="2.php" class="nav-link">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Book Venue</span>
                </a>
            </li>
            <!-- ... other links ... -->
        </ul>
    </aside>

    <main class="main-content">
        <!-- Search -->
        <div class="search-container">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" class="search-input" placeholder="Search for coaches / academies">
        </div>

        <!-- Categories -->
        <div class="categories-section">
            <div class="section-title">
                Hey! What're you looking to level up on?
            </div>
            <div class="categories-grid">
                <a href="javascript:void(0)" class="category-card bg-purple" onclick="selectFilter('services', 'Badminton')">
                    <div class="cat-img"
                        style="font-size: 2.5rem; display: flex; align-items:center; justify-content:center; color: #d8b4fe;">
                        <i class="fa-solid fa-feather-pointed"></i>
                    </div>
                    <span class="cat-label">Badminton</span>
                </a>
                <a href="javascript:void(0)" class="category-card bg-cyan" onclick="selectFilter('services', 'Swimming')">
                    <div class="cat-img"
                        style="font-size: 2.5rem; display: flex; align-items:center; justify-content:center; color: #67e8f9;">
                        <i class="fa-solid fa-person-swimming"></i>
                    </div>
                    <span class="cat-label">Swimming</span>
                </a>
                <a href="javascript:void(0)" class="category-card bg-blue" onclick="selectFilter('services', 'Pickleball')">
                    <div class="cat-img"
                        style="font-size: 2.5rem; display: flex; align-items:center; justify-content:center; color: #93c5fd;">
                        <i class="fa-solid fa-table-tennis-paddle-ball"></i>
                    </div>
                    <span class="cat-label">Pickleball</span>
                </a>
                <a href="javascript:void(0)" class="category-card bg-green" onclick="selectFilter('services', 'Football')">
                    <div class="cat-img"
                        style="font-size: 2.5rem; display: flex; align-items:center; justify-content:center; color: #86efac;">
                        <i class="fa-solid fa-futbol"></i>
                    </div>
                    <span class="cat-label">Football</span>
                </a>
                <a href="javascript:void(0)" class="category-card bg-yellow" onclick="selectFilter('services', 'Cricket')">
                    <div class="cat-img"
                        style="font-size: 2.5rem; display: flex; align-items:center; justify-content:center; color: #fde047;">
                        <i class="fa-solid fa-baseball-bat-ball"></i>
                    </div>
                    <span class="cat-label">Cricket</span>
                </a>
                <a href="javascript:void(0)" class="category-card bg-blue" onclick="selectFilter('services', 'Tennis')">
                    <div class="cat-img"
                        style="font-size: 2.5rem; display: flex; align-items:center; justify-content:center; color: #93c5fd;">
                        <i class="fa-solid fa-baseball"></i>
                    </div>
                    <span class="cat-label">Tennis</span>
                </a>
                <a href="javascript:void(0)" class="category-card bg-purple" onclick="selectFilter('services', 'Physio')">
                    <div class="cat-img"
                        style="font-size: 2.5rem; display: flex; align-items:center; justify-content:center; color: #d8b4fe;">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </div>
                    <span class="cat-label">Physio</span>
                </a>
                <a href="javascript:void(0)" class="category-card bg-green" onclick="selectFilter('services', 'Nutrition')">
                    <div class="cat-img"
                        style="font-size: 2.5rem; display: flex; align-items:center; justify-content:center; color: #86efac;">
                        <i class="fa-solid fa-apple-whole"></i>
                    </div>
                    <span class="cat-label">Nutrition</span>
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-row">
            <button class="filter-chip active" id="filter-all" onclick="resetFilters()"><i class="fa-solid fa-bars"></i> All</button>

            <div class="dropdown-container">
                <button class="filter-chip" onclick="toggleDropdown(event, 'services-menu')">
                    <i class="fa-solid fa-whistle"></i> <span id="services-label">Services</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div id="services-menu" class="dropdown-menu">
                    <div class="dropdown-item" onclick="selectFilter('services', 'Training')">Training</div>
                    <div class="dropdown-item" onclick="selectFilter('services', 'Physio')">Physio</div>
                    <div class="dropdown-item" onclick="selectFilter('services', 'Nutrition')">Nutrition</div>
                    <div class="dropdown-item" onclick="selectFilter('services', 'Weight Loss')">Weight Loss</div>
                    <div class="dropdown-item" onclick="selectFilter('services', 'Badminton')">Badminton</div>
                    <div class="dropdown-item" onclick="selectFilter('services', 'Football')">Football</div>
                </div>
            </div>

            <div class="dropdown-container">
                <button class="filter-chip" onclick="toggleDropdown(event, 'age-menu')">
                    <i class="fa-solid fa-child-reaching"></i> <span id="age-label">Age</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div id="age-menu" class="dropdown-menu">
                    <div class="dropdown-item" onclick="selectFilter('age', 'Kids')">Kids</div>
                    <div class="dropdown-item" onclick="selectFilter('age', 'Teens')">Teens</div>
                    <div class="dropdown-item" onclick="selectFilter('age', 'Adults')">Adults</div>
                    <div class="dropdown-item" onclick="selectFilter('age', 'Seniors')">Seniors</div>
                </div>
            </div>

            <div class="dropdown-container">
                <button class="filter-chip" onclick="toggleDropdown(event, 'batch-menu')">
                    <i class="fa-solid fa-users"></i> <span id="batch-label">Batch</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div id="batch-menu" class="dropdown-menu">
                    <div class="dropdown-item" onclick="selectFilter('batch', 'Morning')">Morning</div>
                    <div class="dropdown-item" onclick="selectFilter('batch', 'Afternoon')">Afternoon</div>
                    <div class="dropdown-item" onclick="selectFilter('batch', 'Evening')">Evening</div>
                    <div class="dropdown-item" onclick="selectFilter('batch', 'Weekend')">Weekend</div>
                </div>
            </div>

            <button class="filter-chip" id="filter-coach" onclick="toggleSimpleFilter('coach')"><i class="fa-solid fa-user-tie"></i> Coach Only</button>
            <button class="filter-chip" id="filter-academy" onclick="toggleSimpleFilter('academy')"><i class="fa-solid fa-school"></i> Academy Only</button>
        </div>

        <!-- Trainers Grid -->
        <h2 class="section-title">Verified Pros Near You</h2>
        <div class="trainers-grid">

            <!-- Card 1: Avinash -->
            <div class="trainer-card" data-type="coach" data-services="Training,Badminton" data-age="Adults,Kids"
                data-batch="Morning,Evening">
                <div class="card-header">
                    <i class="fa-solid fa-clipboard-list badge-icon"></i>
                    <div class="rating-badge"><i class="fa-solid fa-star" style="color: gold;"></i> New</div>
                </div>
                <div class="card-images">
                    <img src="https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&q=80&w=300"
                        class="t-img" alt="Trainer">
                    <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=300"
                        class="t-img" alt="Action">
                </div>
                <div class="card-body">
                    <span class="t-type">TRAINER</span>
                    <div class="t-name">Avinash Hebsooru</div>
                    <div class="t-location">
                        <i class="fa-solid fa-location-dot"></i> Bengaluru, Karnataka, India
                    </div>
                    <div class="t-audience">
                        <i class="fa-solid fa-user-group"></i> Adults & Kids
                    </div>
                    <button class="connect-btn">INSTANT CONNECT <i class="fa-solid fa-bolt"
                            style="color: #FFD700;"></i></button>
                </div>
                <div class="card-footer">
                    8 showed interest
                </div>
            </div>

            <!-- Card 2: Academy -->
            <div class="trainer-card" data-type="academy" data-services="Training,Football" data-age="Kids,Teens"
                data-batch="Evening,Weekend">
                <div class="card-header">
                    <i class="fa-solid fa-dumbbell badge-icon"></i>
                    <div class="rating-badge"><i class="fa-solid fa-star" style="color: gold;"></i> 4.8</div>
                </div>
                <div class="card-images">
                    <img src="https://images.unsplash.com/photo-1540497077202-7c8a3999166f?auto=format&fit=crop&q=80&w=300"
                        class="t-img" alt="Academy">
                    <img src="https://images.unsplash.com/photo-1526506118085-60ce8714f8c5?auto=format&fit=crop&q=80&w=300"
                        class="t-img" alt="Training">
                </div>
                <div class="card-body">
                    <span class="t-type">ACADEMY</span>
                    <div class="t-name">Back2Basics</div>
                    <div class="t-location">
                        <i class="fa-solid fa-location-dot"></i> Indiranagar, Bengaluru
                    </div>
                    <div class="t-audience">
                        <i class="fa-solid fa-user-group"></i> Elite Training
                    </div>
                    <button class="connect-btn">INSTANT CONNECT <i class="fa-solid fa-bolt"
                            style="color: #FFD700;"></i></button>
                </div>
                <div class="card-footer">
                    12 showed interest
                </div>
            </div>

            <!-- Card 3: Dinesh -->
            <div class="trainer-card" data-type="coach" data-services="Nutrition,Physio" data-age="Adults,Seniors"
                data-batch="Morning,Afternoon">
                <div class="card-header">
                    <i class="fa-solid fa-clipboard-user badge-icon"></i>
                    <div class="rating-badge"><i class="fa-solid fa-star" style="color: gold;"></i> 5.0</div>
                </div>
                <div class="card-images">
                    <img src="https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&q=80&w=300"
                        class="t-img" alt="Trainer">
                    <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&q=80&w=300"
                        class="t-img" alt="Food">
                </div>
                <div class="card-body">
                    <span class="t-type">TRAINER</span>
                    <div class="t-name">Dinesh M</div>
                    <div class="t-location">
                        <i class="fa-solid fa-location-dot"></i> Koramangala, Bengaluru
                    </div>
                    <div class="t-audience">
                        <i class="fa-solid fa-user-group"></i> Fitness & Nutrition
                    </div>
                    <button class="connect-btn">INSTANT CONNECT <i class="fa-solid fa-bolt"
                            style="color: #FFD700;"></i></button>
                </div>
                <div class="card-footer">
                    7 showed interest
                </div>
            </div>

            <?php
            // Dynamically load real trainers if any
            if (isset($pdo) && $pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM USERS WHERE role = 'Trainer'");
                    $realTrainers = $stmt->fetchAll();
                    foreach ($realTrainers as $rt) {
                        $name = $rt['full_name'];
                        $loc = "Online";
                        // ... render similar card ...
                    }
                } catch (Exception $e) {
                }
            }
            ?>

        </div>

    </main>

    <script>
        let currentFilters = {
            services: 'All',
            age: 'All',
            batch: 'All',
            role: 'All'
        };

        function toggleDropdown(e, menuId) {
            if (e) e.stopPropagation();
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu.id !== menuId) menu.classList.remove('show');
            });
            // Toggle current
            const menu = document.getElementById(menuId);
            if (menu) menu.classList.toggle('show');
        }

        function selectFilter(type, value) {
            currentFilters[type] = value;
            document.getElementById(type + '-label').innerText = value;
            document.getElementById(type + '-menu').classList.remove('show');

            // Set the parent button as active
            const btn = document.getElementById(type + '-label').parentElement;
            btn.classList.add('active');
            document.getElementById('filter-all').classList.remove('active');

            applyFilters();
        }

        function toggleSimpleFilter(role) {
            const btnId = 'filter-' + role;
            const btn = document.getElementById(btnId);

            if (currentFilters.role === role) {
                currentFilters.role = 'All';
                btn.classList.remove('active');
            } else {
                document.getElementById('filter-coach').classList.remove('active');
                document.getElementById('filter-academy').classList.remove('active');

                currentFilters.role = role;
                btn.classList.add('active');
            }
            document.getElementById('filter-all').classList.remove('active');
            applyFilters();
        }

        function resetFilters() {
            currentFilters = { services: 'All', age: 'All', batch: 'All', role: 'All' };

            document.getElementById('services-label').innerText = 'Services';
            document.getElementById('age-label').innerText = 'Age';
            document.getElementById('batch-label').innerText = 'Batch';

            document.querySelectorAll('.filter-chip').forEach(btn => btn.classList.remove('active'));
            document.getElementById('filter-all').classList.add('active');

            applyFilters();
        }

        function applyFilters() {
            const cards = document.querySelectorAll('.trainer-card');
            cards.forEach(card => {
                const s = card.dataset.services || "";
                const a = card.dataset.age || "";
                const b = card.dataset.batch || "";
                const r = card.dataset.type || "";

                let show = true;

                if (currentFilters.services !== 'All' && !s.includes(currentFilters.services)) show = false;
                if (currentFilters.age !== 'All' && !a.includes(currentFilters.age)) show = false;
                if (currentFilters.batch !== 'All' && !b.includes(currentFilters.batch)) show = false;
                if (currentFilters.role !== 'All' && r !== currentFilters.role) show = false;

                card.style.display = show ? 'block' : 'none';
            });
        }

        window.onclick = function (event) {
            if (!event.target.closest('.dropdown-container')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        }
    </script>
</body>

</html>