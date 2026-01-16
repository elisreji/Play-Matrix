<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play Games - PlayMatrix</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
        }

        /* Subtle Grid Background */
        .grid-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                linear-gradient(rgba(57, 255, 20, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(57, 255, 20, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: -1;
            mask-image: radial-gradient(circle at center, black 40%, transparent 100%);
            -webkit-mask-image: radial-gradient(circle at center, black 40%, transparent 100%);
        }

        /* Navbar */
        .navbar {
            height: var(--nav-height);
            background: rgba(5, 5, 5, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 3%;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--text-white);
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .logo span {
            color: var(--primary-green);
        }

        .location-box {
            background: var(--input-bg);
            border: 1px solid var(--glass-border);
            padding: 0.6rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: var(--text-white);
            font-size: 0.95rem;
            cursor: pointer;
            transition: 0.3s;
            min-width: 220px;
        }

        .location-box:hover {
            border-color: var(--primary-green);
        }

        .location-box i {
            color: var(--primary-green);
        }

        /* Main Nav Links */
        .nav-links {
            display: flex;
            gap: 1rem;
            margin-left: 2rem;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-gray);
            font-weight: 600;
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .nav-link:hover {
            color: var(--text-white);
            background: rgba(255, 255, 255, 0.05);
        }

        .nav-link.active {
            color: var(--bg-dark);
            background: var(--primary-green);
            box-shadow: 0 0 15px rgba(57, 255, 20, 0.4);
        }

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
            padding: 2rem 5%;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Header Section */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .filter-bar {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .filter-btn {
            background: var(--input-bg);
            border: 1px solid var(--glass-border);
            color: var(--text-white);
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .filter-btn:hover,
        .filter-btn.active {
            border-color: var(--primary-green);
            color: var(--primary-green);
        }

        .highlight-text {
            color: var(--primary-green);
            font-weight: 700;
        }

        /* Filter Modal */
        .filter-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .filter-modal.active {
            display: flex;
        }

        .filter-content {
            background: var(--text-white);
            border-radius: 20px;
            width: 90%;
            max-width: 900px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            animation: slideUp 0.4s ease;
        }

        .filter-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .filter-header h2 {
            color: #333;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .close-modal {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f5f5f5;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }

        .close-modal:hover {
            background: #e0e0e0;
        }

        .filter-body {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        .filter-sidebar {
            width: 250px;
            background: #f8f8f8;
            padding: 1rem 0;
            border-right: 1px solid #e0e0e0;
        }

        .filter-tab {
            padding: 1rem 2rem;
            color: #666;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            border-left: 3px solid transparent;
        }

        .filter-tab:hover {
            background: #f0f0f0;
        }

        .filter-tab.active {
            background: var(--primary-green);
            color: #000;
            border-left-color: #2cb812;
        }

        .filter-main {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .filter-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e0e0e0;
            cursor: pointer;
        }

        .filter-option:hover {
            background: #f8f8f8;
            padding-left: 1rem;
            padding-right: 1rem;
            margin: 0 -1rem;
        }

        .filter-option label {
            color: #333;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
        }

        .filter-radio {
            width: 24px;
            height: 24px;
            border: 2px solid #ccc;
            border-radius: 50%;
            cursor: pointer;
            position: relative;
        }

        .filter-radio.checked {
            border-color: var(--primary-green);
        }

        .filter-radio.checked::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 12px;
            height: 12px;
            background: var(--primary-green);
            border-radius: 50%;
        }

        .filter-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .clear-filters {
            color: #666;
            font-weight: 600;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 1rem;
        }

        .clear-filters:hover {
            color: #333;
        }

        .see-results {
            background: var(--primary-green);
            color: #000;
            padding: 1rem 3rem;
            border-radius: 30px;
            border: none;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
        }

        .see-results:hover {
            background: var(--primary-green-dim);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(57, 255, 20, 0.3);
        }

        /* Sports Modal */
        .sports-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .sports-modal.active {
            display: flex;
        }

        .sports-content {
            background: var(--text-white);
            border-radius: 20px;
            width: 90%;
            max-width: 400px;
            max-height: 80vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            animation: slideUp 0.4s ease;
        }

        .sports-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .sports-search {
            display: flex;
            align-items: center;
            background: #f5f5f5;
            border-radius: 12px;
            padding: 0.8rem 1rem;
            gap: 0.8rem;
        }

        .sports-search input {
            background: transparent;
            border: none;
            outline: none;
            width: 100%;
            color: #666;
            font-size: 1rem;
        }

        .sports-search input::placeholder {
            color: #999;
        }

        .sports-search i {
            color: #999;
            font-size: 1.2rem;
        }

        .sports-list {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .sport-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            cursor: pointer;
            border-radius: 12px;
            transition: 0.3s;
            margin-bottom: 0.5rem;
        }

        .sport-item:hover {
            background: #f8f8f8;
        }

        .sport-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .sport-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            font-size: 1.2rem;
        }

        .sport-name {
            color: #333;
            font-size: 1rem;
            font-weight: 500;
        }

        .sport-radio {
            width: 24px;
            height: 24px;
            border: 2px solid #ccc;
            border-radius: 50%;
            cursor: pointer;
            position: relative;
            transition: 0.3s;
        }

        .sport-radio.checked {
            border-color: var(--primary-green);
        }

        .sport-radio.checked::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 12px;
            height: 12px;
            background: var(--primary-green);
            border-radius: 50%;
        }

        /* Date Picker Modal */
        .date-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .date-modal.active {
            display: flex;
        }

        .date-content {
            background: var(--text-white);
            border-radius: 20px;
            width: 90%;
            max-width: 400px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            animation: slideUp 0.4s ease;
            padding: 1.5rem;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 0 0.5rem;
        }

        .calendar-nav {
            background: none;
            border: none;
            color: #333;
            font-size: 1.5rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: 0.3s;
        }

        .calendar-nav:hover {
            background: #f5f5f5;
        }

        .calendar-month {
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .weekday {
            text-align: center;
            color: #666;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.5rem;
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #333;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            font-weight: 500;
        }

        .calendar-day:hover {
            background: #f5f5f5;
        }

        .calendar-day.disabled {
            color: #ccc;
            cursor: not-allowed;
        }

        .calendar-day.disabled:hover {
            background: transparent;
        }

        .calendar-day.today {
            border: 2px solid var(--primary-green);
        }

        .calendar-day.selected {
            background: var(--primary-green);
            color: #000;
            font-weight: 700;
        }

        .calendar-footer {
            display: flex;
            justify-content: center;
            padding-top: 1rem;
            border-top: 1px solid #e0e0e0;
        }

        .today-btn {
            background: none;
            border: none;
            color: var(--primary-green);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: 0.3s;
        }

        .today-btn:hover {
            background: rgba(57, 255, 20, 0.1);
        }

        .today-btn:hover {
            background: rgba(57, 255, 20, 0.1);
        }

        /* Host Modal - Premium Dark Theme */
        .host-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(8px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .host-modal.active {
            display: flex;
        }

        .host-content {
            background: linear-gradient(145deg, #1a1a1a 0%, #0d0d0d 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            padding: 2rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        /* Glow effect container */
        .host-content::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, transparent, rgba(57, 255, 20, 0.1), transparent);
            z-index: -1;
            border-radius: 26px;
            pointer-events: none;
        }

        .host-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1rem;
        }

        .host-header h2 {
            font-size: 1.8rem;
            color: #fff;
            font-weight: 700;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
            letter-spacing: -0.5px;
        }

        .close-modal-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.3s;
            color: #fff;
        }

        .close-modal-btn:hover {
            background: rgba(255, 59, 59, 0.2);
            color: #ff3b3b;
            transform: rotate(90deg);
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            color: #aaa;
            font-weight: 500;
            margin-bottom: 0.6rem;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            outline: none;
            transition: 0.3s;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .form-input:focus,
        .form-select:focus {
            border-color: var(--primary-green);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 15px rgba(57, 255, 20, 0.2);
        }

        /* Dark Theme Select Options */
        .form-select option {
            background: #1a1a1a;
            color: #fff;
            padding: 10px;
        }

        /* Checkbox Style */
        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
        }

        .checkbox-container input {
            width: 20px;
            height: 20px;
            accent-color: var(--primary-green);
        }

        /* Custom Select Arrow Styling */
        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2339ff14' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        /* Dark Theme Calendar Icon */
        input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
            font-size: 1.2rem;
        }

        .host-btn {
            background: linear-gradient(45deg, var(--primary-green), #2db30e);
            color: #000;
            border: none;
            width: 100%;
            padding: 1.1rem;
            border-radius: 12px;
            font-weight: 800;
            font-size: 1.1rem;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(57, 255, 20, 0.3);
        }

        .host-btn:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 25px rgba(57, 255, 20, 0.5);
            background: linear-gradient(45deg, #39ff14, #39ff14);
        }

        .host-btn:active {
            transform: translateY(0);
        }

        /* Games Grid */
        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
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

        .game-card {
            background: var(--gradient-card);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            cursor: pointer;
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .game-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(57, 255, 20, 0.15);
            border-color: var(--primary-green);
        }

        .game-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.03), transparent);
            transition: 0.5s;
        }

        .game-card:hover::before {
            left: 100%;
        }

        .card-image {
            width: 100%;
            height: 200px;
            position: relative;
            overflow: hidden;
            background: #1a1a1a;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s ease;
        }

        .game-card:hover .card-image img {
            transform: scale(1.15);
        }

        .badge-container {
            position: absolute;
            top: 10px;
            left: 10px;
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .badge {
            padding: 4px 10px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 4px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            color: var(--text-white);
            border: 1px solid var(--glass-border);
        }

        .badge.booked {
            background: var(--primary-green);
            color: #000;
            border: none;
        }

        .card-content {
            padding: 1.2rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .game-name {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-white);
        }

        .game-meta {
            color: var(--text-gray);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.8rem;
        }

        .player-count {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: auto;
            padding-top: 0.8rem;
            border-top: 1px solid var(--glass-border);
        }

        .player-avatars {
            display: flex;
            margin-left: auto;
        }

        .player-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid var(--bg-card);
            margin-left: -8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .player-avatar:first-child {
            margin-left: 0;
        }

        .level-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            color: var(--primary-green);
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            border: 1px solid var(--primary-green);
            z-index: 5;
        }
    </style>
</head>

<body>

    <div class="grid-bg"></div>

    <nav class="navbar">
        <div class="nav-left">
            <a href="2.php" class="logo">
                <i class="fa-solid fa-gamepad"></i>
                PLAY<span>MATRIX</span>
            </a>

            <div class="location-box">
                <i class="fa-solid fa-location-dot"></i>
                <span>Bangalore</span>
            </div>

            <div class="nav-links">
                <a href="play.php" class="nav-link active">Play</a>
                <a href="2.php" class="nav-link">Book</a>
                <a href="#" class="nav-link">Trainer</a>
            </div>
        </div>

        <div class="nav-right">
            <div class="search-bar">
                <i class="fa-solid fa-search"></i>
                <input type="text" placeholder="Search for games, sports...">
            </div>

            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1 style="font-size: 2.5rem; line-height: 1.2; margin-bottom: 0.5rem;"><span
                        class="highlight-text">Join or create games</span><br>with players near you</h1>
            </div>
            <div class="filter-bar">
                <button class="filter-btn" onclick="openFilterModal()">
                    <i class="fa-solid fa-sliders"></i>
                    Filter & Sort By
                </button>
                <button class="filter-btn" onclick="openSportsModal()">
                    <i class="fa-solid fa-futbol"></i>
                    Sports
                    <span
                        style="background: var(--primary-green); color: #000; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; margin-left: 5px;">11</span>
                </button>
                <button class="filter-btn" onclick="openDateModal()">
                    <i class="fa-solid fa-calendar"></i>
                    Date
                </button>
                <button class="filter-btn" id="paidFilterBtn" onclick="togglePaidFilter()">
                    <i class="fa-solid fa-ticket"></i>
                    Pay & Join Game
                </button>
                <button class="filter-btn" onclick="openHostModal()">
                    <i class="fa-solid fa-circle-plus"></i>
                    Host Plays
                </button>
            </div>
        </div>

        <div class="games-grid" id="gamesContainer">
            <!-- Games will be injected here via JS -->
        </div>
    </div>

    <!-- Filter Modal (Existing code...) -->
    <!-- Sports Modal (Existing code...) -->
    <!-- Date Picker Modal (Existing code...) -->

    <!-- Host Plays Modal -->
    <div class="host-modal" id="hostModal">
        <div class="host-content">
            <div class="host-header">
                <h2>Host a Play</h2>
                <button class="close-modal-btn" onclick="closeHostModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="form-group">
                <label class="form-label">Your Name</label>
                <input type="text" id="hostName" class="form-input" placeholder="Enter your name">
            </div>

            <div class="form-group">
                <label class="form-label">Select Sport</label>
                <select id="hostSport" class="form-select">
                    <option value="" disabled selected>Choose a sport...</option>
                    <option>Badminton</option>
                    <option>Cricket</option>
                    <option>Football</option>
                    <option>Tennis</option>
                    <option>Basketball</option>
                    <option>Table Tennis</option>
                    <option>Volleyball</option>
                    <option>Squash</option>
                    <option>Swimming</option>
                    <option>Golf</option>
                    <option>Hockey</option>
                    <option>Rugby</option>
                    <option>Chess</option>
                    <option>Carrom</option>
                    <option>Pool / Snooker</option>
                    <option>Boxing</option>
                    <option>MMA</option>
                    <option>Yoga</option>
                    <option>Cycling</option>
                    <option>Running</option>
                    <option>Kabaddi</option>
                    <option>Kho Kho</option>
                    <option>Throwball</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Venue</label>
                <input type="text" id="hostVenue" class="form-input" placeholder="Search for a venue nearby...">
            </div>

            <div class="form-group">
                <label class="form-label">Date</label>
                <input type="date" id="hostDate" class="form-input">
            </div>

            <div style="display: flex; gap: 1rem;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Start Time</label>
                    <input type="time" id="hostStartTime" class="form-input">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">End Time</label>
                    <input type="time" id="hostEndTime" class="form-input">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Members Needed</label>
                <input type="number" id="hostMaxPlayers" class="form-input" min="2" value="4"
                    placeholder="Total players needed">
            </div>

            <div class="form-group">
                <label class="form-label">Game Cost</label>
                <div
                    style="display: flex; gap: 1rem; align-items: center; background: rgba(255,255,255,0.05); padding: 10px; border-radius: 12px;">
                    <label class="checkbox-container">
                        <input type="checkbox" id="hostPaidToggle" onchange="toggleHostPrice()">
                        Paid Game?
                    </label>
                    <input type="text" id="hostPrice" class="form-input" placeholder="Price (e.g. 500)"
                        style="display: none; width: 150px;">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Game Type</label>
                <select id="hostType" class="form-select">
                    <option>Regular</option>
                    <option>Mixed Doubles</option>
                    <option>Doubles Regular</option>
                    <option>Friendly Match</option>
                    <option>Tournament</option>
                    <option>Practice Session</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Skill Level</label>
                <select id="hostSkill" class="form-select">
                    <option>Beginner - Professional</option>
                    <option>Beginner</option>
                    <option>Intermediate</option>
                    <option>Advanced</option>
                    <option>Professional</option>
                </select>
            </div>

            <button class="host-btn" onclick="createGame()">Create Game</button>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="filter-modal" id="filterModal">
        <div class="filter-content">
            <div class="filter-header">
                <h2>Filter & Sort By</h2>
                <button class="close-modal" onclick="closeFilterModal()">
                    <i class="fa-solid fa-xmark" style="font-size: 1.2rem; color: #666;"></i>
                </button>
            </div>
            <div class="filter-body">
                <div class="filter-sidebar">
                    <div class="filter-tab active" onclick="switchFilterTab('sort')">SORT BY</div>
                    <div class="filter-tab" onclick="switchFilterTab('time')">TIME</div>
                    <div class="filter-tab" onclick="switchFilterTab('skill')">SKILL</div>
                    <div class="filter-tab" onclick="switchFilterTab('others')">OTHERS</div>
                </div>
                <div class="filter-main">
                    <!-- Sort By Options -->
                    <div id="sortOptions" class="filter-section">
                        <div class="filter-option" onclick="selectFilter('sort', 'time')">
                            <label>Time & Date</label>
                            <div class="filter-radio" id="radio-time"></div>
                        </div>
                    </div>
                    <!-- Time Options -->
                    <div id="timeOptions" class="filter-section" style="display: none;">
                        <div class="filter-option" onclick="selectFilter('time', 'morning')">
                            <label>Morning (6 AM - 12 PM)</label>
                            <div class="filter-radio" id="radio-morning"></div>
                        </div>
                        <div class="filter-option" onclick="selectFilter('time', 'afternoon')">
                            <label>Afternoon (12 PM - 6 PM)</label>
                            <div class="filter-radio" id="radio-afternoon"></div>
                        </div>
                        <div class="filter-option" onclick="selectFilter('time', 'evening')">
                            <label>Evening (6 PM - 12 AM)</label>
                            <div class="filter-radio" id="radio-evening"></div>
                        </div>
                    </div>
                    <!-- Skill Options -->
                    <div id="skillOptions" class="filter-section" style="display: none;">
                        <div class="filter-option" onclick="selectFilter('skill', 'beginner')">
                            <label>Beginner</label>
                            <div class="filter-radio" id="radio-beginner"></div>
                        </div>
                        <div class="filter-option" onclick="selectFilter('skill', 'intermediate')">
                            <label>Intermediate</label>
                            <div class="filter-radio" id="radio-intermediate"></div>
                        </div>
                        <div class="filter-option" onclick="selectFilter('skill', 'professional')">
                            <label>Professional</label>
                            <div class="filter-radio" id="radio-professional"></div>
                        </div>
                    </div>
                    <!-- Others Options -->
                    <div id="othersOptions" class="filter-section" style="display: none;">
                        <div class="filter-option" onclick="selectFilter('others', 'free')">
                            <label>Free Games Only</label>
                            <div class="filter-radio" id="radio-free"></div>
                        </div>
                        <div class="filter-option" onclick="selectFilter('others', 'paid')">
                            <label>Paid Games Only</label>
                            <div class="filter-radio" id="radio-paid"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="filter-footer">
                <button class="clear-filters" onclick="clearAllFilters()">CLEAR FILTERS</button>
                <button class="see-results" onclick="applyFilters()">SEE RESULTS</button>
            </div>
        </div>
    </div>

    <!-- Sports Modal -->
    <div class="sports-modal" id="sportsModal">
        <div class="sports-content">
            <div class="sports-header">
                <div class="sports-search">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="sportsSearchInput" placeholder="Search for 100+ Sports"
                        oninput="filterSports()">
                </div>
            </div>
            <div class="sports-list" id="sportsList">
                <!-- Sports will be injected here -->
            </div>
        </div>
    </div>

    <!-- Date Picker Modal -->
    <div class="date-modal" id="dateModal">
        <div class="date-content">
            <div class="calendar-header">
                <button class="calendar-nav" onclick="changeMonth(-1)">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="calendar-month" id="calendarMonth">January 2026</div>
                <button class="calendar-nav" onclick="changeMonth(1)">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
            <div class="calendar-weekdays">
                <div class="weekday">Sun</div>
                <div class="weekday">Mon</div>
                <div class="weekday">Tue</div>
                <div class="weekday">Wed</div>
                <div class="weekday">Thu</div>
                <div class="weekday">Fri</div>
                <div class="weekday">Sat</div>
            </div>
            <div class="calendar-days" id="calendarDays">
                <!-- Days will be injected here -->
            </div>
            <div class="calendar-footer">
                <button class="today-btn" onclick="selectToday()">Today</button>
            </div>
        </div>
    </div>

    <script>
        let currentDate = new Date();
        let selectedDate = new Date();

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();

            // Update month display
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'];
            document.getElementById('calendarMonth').textContent = `${monthNames[month]} ${year}`;

            // Get first day of month and number of days
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();

            // Clear previous days
            const daysContainer = document.getElementById('calendarDays');
            daysContainer.innerHTML = '';

            // Add empty cells for days before month starts
            for (let i = 0; i < firstDay; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day disabled';
                daysContainer.appendChild(emptyDay);
            }

            // Add days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                dayElement.textContent = day;

                const currentDateObj = new Date(year, month, day);

                // Check if it's today
                if (currentDateObj.toDateString() === today.toDateString()) {
                    dayElement.classList.add('today');
                }

                // Check if it's selected
                if (currentDateObj.toDateString() === selectedDate.toDateString()) {
                    dayElement.classList.add('selected');
                }

                // Add click handler
                dayElement.onclick = () => selectDate(year, month, day);

                daysContainer.appendChild(dayElement);
            }
        }

        function changeMonth(delta) {
            currentDate.setMonth(currentDate.getMonth() + delta);
            renderCalendar();
        }

        function selectDate(year, month, day) {
            selectedDate = new Date(year, month, day);
            renderCalendar();
        }

        function selectToday() {
            selectedDate = new Date();
            currentDate = new Date();
            renderCalendar();
        }

        function openDateModal() {
            document.getElementById('dateModal').classList.add('active');
            renderCalendar();
        }

        function closeDateModal() {
            document.getElementById('dateModal').classList.remove('active');
        }

        // Host Price Toggle
        function toggleHostPrice() {
            const isPaid = document.getElementById('hostPaidToggle').checked;
            const priceInput = document.getElementById('hostPrice');
            if (isPaid) {
                priceInput.style.display = 'block';
                priceInput.focus();
            } else {
                priceInput.style.display = 'none';
                priceInput.value = '';
            }
        }

        // Close modal when clicking outside
        document.getElementById('dateModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeDateModal();
            }
        });

        // Host Modal Functions
        function openHostModal() {
            document.getElementById('hostModal').classList.add('active');
        }

        function closeHostModal() {
            document.getElementById('hostModal').classList.remove('active');
        }

        function createGame() {
            // Here you would gather form data and submit to server
            // For now, just show alert and close
            alert("Game created successfully! It will appear in the listings shortly.");
            closeHostModal();
        }

        // Close modal when clicking outside
        document.getElementById('hostModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeHostModal();
            }
        });

        const sports = [
            { name: 'Badminton', icon: 'fa-solid fa-shuttlecock' },
            { name: 'Cricket', icon: 'fa-solid fa-baseball-bat-ball' },
            { name: 'Football', icon: 'fa-solid fa-futbol' },
            { name: 'Tennis', icon: 'fa-solid fa-table-tennis-paddle-ball' },
            { name: 'Basketball', icon: 'fa-solid fa-basketball' },
            { name: 'Table Tennis', icon: 'fa-solid fa-table-tennis' },
            { name: 'Volleyball', icon: 'fa-solid fa-volleyball' },
            { name: 'Squash', icon: 'fa-solid fa-circle-dot' },
            { name: 'Swimming', icon: 'fa-solid fa-person-swimming' },
            { name: 'Golf', icon: 'fa-solid fa-golf-ball-tee' },
            { name: 'Hockey', icon: 'fa-solid fa-hockey-puck' },
            { name: 'Rugby', icon: 'fa-solid fa-football' },
            { name: 'Chess', icon: 'fa-solid fa-chess' },
            { name: 'Carrom', icon: 'fa-solid fa-bullseye' },
            { name: 'Pool', icon: 'fa-solid fa-circle' },
            { name: 'Boxing', icon: 'fa-solid fa-mitten' },
            { name: 'MMA', icon: 'fa-solid fa-hand-fist' },
            { name: 'Yoga', icon: 'fa-solid fa-person-praying' },
            { name: 'Cycling', icon: 'fa-solid fa-bicycle' },
            { name: 'Running', icon: 'fa-solid fa-person-running' },
            { name: 'Kabaddi', icon: 'fa-solid fa-users' },
            { name: 'Kho Kho', icon: 'fa-solid fa-people-arrows' },
            { name: 'Throwball', icon: 'fa-solid fa-hand-holding-circle' },
            { name: 'Gym', icon: 'fa-solid fa-dumbbell' }
        ];

        let selectedSport = 'Badminton'; // Default selection for UI highlighting

        // Centralized Filter State
        let activeFilters = {
            paid: false,
            sport: null, // "Badminton", "Cricket", etc.
            date: null,  // Date string "DD MMM YYYY"
            sort: null,  // "time", "distance"
            time: null,  // "morning", "afternoon", "evening"
            skill: null, // "beginner", "intermediate", "professional"
            others: null // "free", "paid"
        };

        function renderSports(sportsToRender = sports) {
            const sportsList = document.getElementById('sportsList');
            sportsList.innerHTML = '';

            sportsToRender.forEach(sport => {
                const sportItem = document.createElement('div');
                sportItem.className = 'sport-item';
                sportItem.onclick = () => selectSport(sport.name);

                sportItem.innerHTML = `
                    <div class="sport-info">
                        <div class="sport-icon">
                            <i class="${sport.icon}"></i>
                        </div>
                        <div class="sport-name">${sport.name}</div>
                    </div>
                    <div class="sport-radio ${activeFilters.sport === sport.name ? 'checked' : ''}" id="sport-${sport.name.replace(/\s+/g, '-')}"></div>
                `;

                sportsList.appendChild(sportItem);
            });
        }

        function selectSport(sportName) {
            // Update UI
            document.querySelectorAll('.sport-radio').forEach(r => r.classList.remove('checked'));
            const radio = document.getElementById(`sport-${sportName.replace(/\s+/g, '-')}`);
            if (radio) radio.classList.add('checked');

            // Update Filter
            activeFilters.sport = sportName;

            // Update Button Text
            const sportBtn = document.querySelector('button[onclick="openSportsModal()"]');
            if (sportBtn) {
                sportBtn.innerHTML = `<i class="fa-solid fa-futbol"></i> ${sportName} <span style="background: var(--primary-green); color: #000; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; margin-left: 5px;">Filtered</span>`;
            }

            // Apply Filters
            applyAllFilters();

            // Optional: Close modal automatically
            // closeSportsModal(); 
        }

        function filterSports() {
            const query = document.getElementById('sportsSearchInput').value.toLowerCase();
            const filtered = sports.filter(sport => sport.name.toLowerCase().includes(query));
            renderSports(filtered);
        }

        function openSportsModal() {
            document.getElementById('sportsModal').classList.add('active');
            renderSports();
        }

        function closeSportsModal() {
            document.getElementById('sportsModal').classList.remove('active');
        }

        // Close modal when clicking outside
        document.getElementById('sportsModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeSportsModal();
            }
        });

        // Updated Games Data with Real Sports
        let games = [
            {
                name: "Nidhin",
                sport: "Badminton",
                type: "Regular",
                date: "Tue, 13 Jan 2026, 06:30 PM - 07:30 PM",
                venue: "Machavi Active Sports",
                distance: "~1.00 Kms",
                level: "Beginner - Professional",
                status: "BOOKED",
                players: { going: 1, total: 13 },
                paid: true,
                price: "₹500"
            }
        ];

        const container = document.getElementById('gamesContainer');

        function renderGames(gamesToRender) {
            container.innerHTML = '';

            if (gamesToRender.length === 0) {
                container.innerHTML = `
                    <div style="grid-column: 1/-1; text-align: center; padding: 4rem; color: #666;">
                        <i class="fa-solid fa-ghost" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <h3>No games found</h3>
                        <p>Try adjusting your filters or host a new game!</p>
                    </div>
                `;
                return;
            }

            gamesToRender.forEach((game, index) => {
                const card = document.createElement('div');
                card.className = 'game-card';
                card.style.animationDelay = `${index * 0.1}s`;

                // Generate random player avatars
                const playerAvatars = [];
                for (let i = 0; i < Math.min(3, game.players.going); i++) {
                    const initial = String.fromCharCode(65 + Math.floor(Math.random() * 26));
                    playerAvatars.push(`<div class="player-avatar">${initial}</div>`);
                }

                card.innerHTML = `
                    <div class="card-image">
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-${getSportIcon(game.sport)}" style="font-size: 4rem; color: rgba(255,255,255,0.3);"></i>
                        </div>
                        <div class="badge-container">
                            <span class="badge">${game.type}</span>
                            ${game.status ? `<span class="badge booked">${game.status}</span>` : ''}
                        </div>
                        <div class="level-badge">${game.level}</div>
                    </div>
                    <div class="card-content">
                        <div class="game-name">${game.players.going} Going</div>
                        <div class="game-meta">
                            <i class="fa-solid fa-user"></i>
                            ${game.name} | ${game.players.total} ${game.sport}
                        </div>
                        <div class="game-meta">
                            <i class="fa-solid fa-calendar"></i>
                            ${game.date}
                        </div>
                        <div class="game-meta">
                            <i class="fa-solid fa-location-dot"></i>
                            ${game.venue} ${game.distance ? `- ${game.distance}` : ''}
                        </div>
                        <div class="game-meta" style="color: ${game.paid ? 'var(--primary-green)' : 'var(--text-gray)'}; font-weight: 600;">
                            <i class="fa-solid fa-${game.paid ? 'indian-rupee-sign' : 'gift'}"></i>
                            ${game.price}
                        </div>
                        <div class="player-count">
                            <i class="fa-solid fa-users" style="color: var(--text-gray);"></i>
                            <span style="color: var(--text-gray); font-size: 0.85rem;">${game.players.going} / ${game.players.total}</span>
                            <div class="player-avatars">
                                ${playerAvatars.join('')}
                            </div>
                        </div>
                    </div>
                `;

                container.appendChild(card);
            });
        }

        function getSportIcon(sport) {
            const icons = {
                'Badminton': 'shuttlecock',
                'Cricket': 'baseball-bat-ball',
                'Football': 'futbol',
                'Tennis': 'table-tennis-paddle-ball',
                'Basketball': 'basketball',
                'Swimming': 'person-swimming',
                'Volleyball': 'volleyball',
                'Golf': 'golf-ball-tee',
                'Table Tennis': 'table-tennis-paddle-ball',
                'Yoga': 'person-praying',
                'Cycling': 'bicycle',
                'Running': 'person-running'
            };
            return icons[sport] || 'trophy'; // Fallback icon
        }

        // --- Core Filtering Logic ---

        function applyAllFilters() {
            let filtered = [...games];

            // 1. Payment Filter
            if (activeFilters.paid) {
                filtered = filtered.filter(g => g.paid === true);
            }

            // 2. Sport Filter
            if (activeFilters.sport) {
                filtered = filtered.filter(g => g.sport === activeFilters.sport);
            }

            // 3. Date Filter
            if (activeFilters.date) {
                // Check if game date string contains the filtered date string (e.g., "13 Jan 2026")
                filtered = filtered.filter(g => g.date.includes(activeFilters.date));
            }

            // 4. Modal Filters (Skill, Time, Others)
            if (activeFilters.skill) {
                // loose matching for "Beginner", "intermediate" etc.
                const skillKey = activeFilters.skill.toLowerCase();
                filtered = filtered.filter(g => g.level.toLowerCase().includes(skillKey));
            }

            // Time filters (Simple check based on AM/PM/Hour - simplified)
            if (activeFilters.time) {
                if (activeFilters.time === 'morning') filtered = filtered.filter(g => g.date.includes('AM'));
                if (activeFilters.time === 'evening') filtered = filtered.filter(g => g.date.includes('PM'));
            }

            // Sort logic
            if (activeFilters.sort === 'distance') {
                filtered.sort((a, b) => {
                    const distA = parseFloat(a.distance.replace(/[^0-9.]/g, '')) || 0;
                    const distB = parseFloat(b.distance.replace(/[^0-9.]/g, '')) || 0;
                    return distA - distB;
                });
            }

            renderGames(filtered);
        }

        // Toggle Paid Filter
        function togglePaidFilter() {
            activeFilters.paid = !activeFilters.paid;
            const btn = document.getElementById('paidFilterBtn');

            if (activeFilters.paid) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
            applyAllFilters();
        }

        // Initialize display
        renderGames(games);

        // --- Filter Modal Functions ---

        function openFilterModal() {
            document.getElementById('filterModal').classList.add('active');
        }

        function closeFilterModal() {
            document.getElementById('filterModal').classList.remove('active');
        }

        function switchFilterTab(tab) {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');
            document.querySelectorAll('.filter-section').forEach(s => s.style.display = 'none');

            const sections = {
                'sort': 'sortOptions',
                'time': 'timeOptions',
                'skill': 'skillOptions',
                'others': 'othersOptions'
            };
            document.getElementById(sections[tab]).style.display = 'block';
        }

        function selectFilter(category, value) {
            // Update Filter State
            if (activeFilters[category] === value) {
                // Toggle off if clicking same
                activeFilters[category] = null;
            } else {
                activeFilters[category] = value;
            }

            // Update UI (Radios)
            // 1. Clear all in this category
            const sectionId = category + 'Options'; // e.g., 'sortOptions'
            const container = document.getElementById(sectionId);
            if (container) {
                container.querySelectorAll('.filter-radio').forEach(r => r.classList.remove('checked'));
            }

            // 2. Check the selected one if it wasn't toggled off
            if (activeFilters[category]) {
                const radioId = `radio-${value}`; // e.g., 'radio-morning'
                const radio = document.getElementById(radioId);
                if (radio) radio.classList.add('checked');
            }
        }

        function clearAllFilters() {
            // Reset State
            activeFilters = {
                paid: false,
                sport: null,
                date: null,
                sort: null,
                time: null,
                skill: null,
                others: null
            };

            // Reset UI
            document.querySelectorAll('.filter-radio').forEach(r => r.classList.remove('checked'));
            document.getElementById('paidFilterBtn').classList.remove('active');

            // Reset Main Button Text
            const filterBtn = document.querySelector('button[onclick="openFilterModal()"]');
            if (filterBtn) filterBtn.innerHTML = 'Filter & Sort By <i class="fa-solid fa-sliders"></i>';

            // Re-render
            applyAllFilters();
            closeFilterModal();
        }

        function applyFilters() {
            // Apply Logic
            applyAllFilters();

            // Update Main Button with Count
            let count = 0;
            if (activeFilters.sort) count++;
            if (activeFilters.time) count++;
            if (activeFilters.skill) count++;
            if (activeFilters.others) count++;

            const filterBtn = document.querySelector('button[onclick="openFilterModal()"]');
            if (filterBtn) {
                if (count > 0) {
                    filterBtn.innerHTML = `Filter & Sort By (${count}) <i class="fa-solid fa-sliders"></i>`;
                    filterBtn.classList.add('active'); // Optional: Add active style to main button
                } else {
                    filterBtn.innerHTML = 'Filter & Sort By <i class="fa-solid fa-sliders"></i>';
                    filterBtn.classList.remove('active');
                }
            }

            closeFilterModal();
        }

        // --- Create Game Functions ---

        function createGame() {
            const name = document.getElementById('hostName').value;
            const sport = document.getElementById('hostSport').value;
            const venue = document.getElementById('hostVenue').value;
            const dateVal = document.getElementById('hostDate').value; // YYYY-MM-DD
            const startTimeVal = document.getElementById('hostStartTime').value; // HH:mm
            const endTimeVal = document.getElementById('hostEndTime').value; // HH:mm
            const type = document.getElementById('hostType').value;
            const skill = document.getElementById('hostSkill').value;

            // New Fields
            const maxPlayers = document.getElementById('hostMaxPlayers').value || 4;
            const isPaid = document.getElementById('hostPaidToggle').checked;
            let price = "Free";
            if (isPaid) {
                const p = document.getElementById('hostPrice').value;
                price = p ? `₹${p}` : "₹0";
            }

            if (!name || !sport || !venue || !dateVal || !startTimeVal || !endTimeVal) {
                alert("Please fill in all required fields (Name, Sport, Venue, Date, Start Time, End Time)");
                return;
            }

            // Format Date for display
            const d = new Date(dateVal);
            const dateStr = d.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });

            const formatTime = (timeStr) => {
                const [hours, minutes] = timeStr.split(':');
                const date = new Date();
                date.setHours(hours);
                date.setMinutes(minutes);
                return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            };

            const formattedTimeRange = `${formatTime(startTimeVal)} - ${formatTime(endTimeVal)}`;
            const formattedDate = `${dateStr}, ${formattedTimeRange}`;

            // 1. Send data to backend
            const gameData = {
                name: name,
                sport: sport,
                venue: venue,
                date: dateVal,
                startTime: startTimeVal,
                endTime: endTimeVal,
                type: type,
                skill: skill,
                maxPlayers: maxPlayers,
                price: price
            };

            fetch('create_game.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(gameData)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 2. Add to local list
                        const newGame = {
                            name: name,
                            sport: sport,
                            type: type,
                            date: formattedDate,
                            venue: venue,
                            distance: "~0.1 Kms", // New games are conceptually 'here'
                            level: skill,
                            status: "OPEN",
                            players: { going: 1, total: parseInt(maxPlayers) },
                            paid: isPaid,
                            price: price
                        };

                        // CRITICAL REQUEST: Remove other schedules, show ONLY this one
                        games = [newGame];

                        applyAllFilters();
                        closeHostModal();
                        alert("Game Hosted Successfully! Showing your new game.");

                        // Clear inputs
                        document.getElementById('hostName').value = '';
                        document.getElementById('hostVenue').value = '';
                        document.getElementById('hostDate').value = '';
                        document.getElementById('hostStartTime').value = '';
                        document.getElementById('hostEndTime').value = '';
                        document.getElementById('hostPrice').value = '';
                        document.getElementById('hostPaidToggle').checked = false;
                        toggleHostPrice(); // Reset visibility
                    } else {
                        alert("Error creating game: " + data.message);
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert("An error occurred while creating the game.");
                });
        }

        // --- Date Picker Logic (Updated) ---
        // Reuse global `currentDate` and `selectedDate` variables

        function selectDate(year, month, day) {
            selectedDate = new Date(year, month, day);
            renderCalendar();

            // Format: "13 Jan 2026"
            const dayStr = selectedDate.getDate().toString();
            const monthStr = selectedDate.toLocaleString('default', { month: 'short' });
            const yearStr = selectedDate.getFullYear().toString();

            const filterStr = `${dayStr} ${monthStr} ${yearStr}`; // "12 Jan 2026"
            activeFilters.date = filterStr;

            // Update Button
            const dateBtn = document.querySelector('button[onclick="openDateModal()"]');
            if (dateBtn) dateBtn.innerHTML = `<i class="fa-solid fa-calendar"></i> ${dayStr} ${monthStr}`;

            applyAllFilters();
            closeDateModal();
        }

        // Helper to reset date filter
        function clearDateFilter() {
            activeFilters.date = null;
            applyAllFilters();
        }
    </script>

</body>

</html>