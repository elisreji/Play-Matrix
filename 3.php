<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venue Details - PlayMatrix</title>
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

        /* Navbar Reused */
        .navbar {
            height: var(--nav-height);
            background: rgba(5, 5, 5, 0.95);
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
            transition: 0.3s;
            text-transform: uppercase;
        }

        .nav-link:hover {
            color: var(--text-white);
        }

        .nav-link.active {
            color: var(--primary-green);
            border-bottom: 2px solid var(--primary-green);
        }

        .profile-icon {
            width: 40px;
            height: 40px;
            background: #222;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--glass-border);
            color: var(--text-white);
        }

        /* Content Layout */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 5%;
        }

        /* Header Info */
        .venue-header {
            margin-bottom: 2rem;
        }

        .venue-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-white);
        }

        .venue-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--text-gray);
            font-size: 1rem;
        }

        .rating-badge {
            background: rgba(255, 193, 7, 0.15);
            color: #ffc107;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .rate-link {
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 500;
            border-bottom: 1px dashed var(--primary-green);
        }

        /* Grid Layout */
        .details-grid {
            display: grid;
            grid-template-columns: 1.8fr 1fr;
            gap: 2.5rem;
        }

        /* Left Column: Gallery */
        .gallery-container {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            aspect-ratio: 16/9;
            background: #111;
            border: 1px solid var(--glass-border);
        }

        .gallery-slide {
            width: 100%;
            height: 100%;
            display: none;
        }

        .gallery-slide.active {
            display: block;
        }

        .gallery-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gallery-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 1rem;
            pointer-events: none;
        }

        .nav-arrow {
            width: 40px;
            height: 40px;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            pointer-events: auto;
            transition: 0.2s;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-arrow:hover {
            background: var(--primary-green);
            color: black;
        }

        .gallery-dots {
            position: absolute;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.5rem;
        }

        .dot {
            width: 8px;
            height: 8px;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            cursor: pointer;
        }

        .dot.active {
            background: var(--primary-green);
            transform: scale(1.2);
        }

        /* Right Column: Sidebar Actions */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .action-card {
            background: var(--bg-card);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 1.5rem;
        }

        .btn-main {
            width: 100%;
            padding: 1rem;
            background: var(--primary-green);
            color: #000;
            font-weight: 700;
            font-size: 1.1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-main:hover {
            background: var(--primary-green-dim);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(57, 255, 20, 0.3);
        }

        .action-row {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-outline {
            flex: 1;
            padding: 0.8rem;
            background: transparent;
            border: 1px solid var(--glass-border);
            color: var(--text-white);
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-outline:hover {
            border-color: var(--primary-green);
            color: var(--primary-green);
        }

        .info-label {
            color: var(--text-gray);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: var(--text-white);
            font-size: 1.05rem;
            font-weight: 500;
        }

        .map-preview {
            width: 100%;
            height: 180px;
            background-color: #222;
            border-radius: 8px;
            margin-top: 1rem;
            background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/b/bd/Google_Maps_Logo_2020.svg/512px-Google_Maps_Logo_2020.svg.png');
            /* Placeholder */
            background-size: cover;
            background-position: center;
            position: relative;
            overflow: hidden;
        }

        .map-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 0.5rem;
            background: rgba(0, 0, 0, 0.7);
            font-size: 0.8rem;
            text-align: center;
            color: var(--primary-green);
        }

        @media (max-width: 900px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="nav-left">
            <a href="2.php" class="logo">
                <i class="fa-solid fa-gamepad"></i>
                PLAY<span>MATRIX</span>
            </a>
            <div class="nav-links">
                <a href="2.php" class="nav-link active">Book</a>
                <a href="#" class="nav-link">Train</a>
                <a href="#" class="nav-link">Play</a>
            </div>
        </div>
        <div class="nav-left">
            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Header -->
        <div class="venue-header">
            <h1 class="venue-title" id="venueName">Loading Venue...</h1>
            <div class="venue-meta">
                <span id="venueArea">Location...</span>
                <div class="rating-badge">
                    <i class="fa-solid fa-star"></i> <span id="venueRating">0.0</span> <span
                        style="font-weight:400; opacity:0.7; margin-left:4px;">(<span id="venueReviews">0</span>
                        ratings)</span>
                </div>
                <a href="#" class="rate-link">Rate Venue</a>
            </div>
        </div>

        <div class="details-grid">
            <!-- Left: Gallery -->
            <div class="gallery-column">
                <div class="gallery-container" id="galleryContainer">
                    <!-- Images injected via JS -->
                    <div class="gallery-nav">
                        <div class="nav-arrow" onclick="prevSlide()"><i class="fa-solid fa-chevron-left"></i></div>
                        <div class="nav-arrow" onclick="nextSlide()"><i class="fa-solid fa-chevron-right"></i></div>
                    </div>
                    <div class="gallery-dots" id="dotsContainer">
                        <!-- Dots injected via JS -->
                    </div>
                </div>
            </div>

            <!-- Right: Action Sidebar -->
            <div class="sidebar">
                <!-- Action Buttons -->
                <div class="action-card">
                    <!-- Update button to navigate to 4.php with current params -->
                    <button class="btn-main" onclick="goToBooking()">Book Now</button>
                    <div class="action-row">
                        <button class="btn-outline"><i class="fa-solid fa-share-nodes"></i> Share</button>
                        <button class="btn-outline"><i class="fa-solid fa-building-user"></i> Bulk / Corporate</button>
                    </div>
                </div>

                <!-- Timing -->
                <div class="action-card">
                    <div class="info-label">Timing</div>
                    <div class="info-value">6:30 AM - 11:30 PM</div>
                </div>

                <!-- Location -->
                <div class="action-card">
                    <div class="info-label">Location</div>
                    <div class="info-value" id="venueAddress">Loading address...</div>
                    <div class="map-preview">
                        <div class="map-overlay"><i class="fa-solid fa-map-pin"></i> View on Map</div>
                        <!-- Using a static map placeholder for visual accuracy without API key -->
                        <iframe width="100%" height="100%" style="border:0; opacity: 0.6;" loading="lazy"
                            allowfullscreen
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3502.066498330768!2d77.123456789!3d28.612345678!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjjCsDM2JzQ0LjQiTiA3N8KwMDcnMjQuNCJF!5e0!3m2!1sen!2sin!4v1634567890123!5m2!1sen!2sin">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Google Maps Script -->
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBDQi8k0XTpO-WPQ4ci8tGstRJp-ZIhCHg&libraries=places"></script>

    <script>
        // State
        const urlParams = new URLSearchParams(window.location.search);
        const placeId = urlParams.get('id');
        const defaultName = urlParams.get('name') || "Premier Sports Arena";
        const defaultSport = urlParams.get('sport') || "Football";
        const defaultArea = urlParams.get('area') || "Central City";
        const defaultRating = urlParams.get('rating') || "4.5";
        const defaultReviews = urlParams.get('reviews') || "128";

        let currentSlide = 0;
        let photoshoot = [];

        // Initialize Google Places Service
        function initDetails() {
            // Initial UI update from URL params
            document.getElementById('venueName').textContent = defaultName;
            document.getElementById('venueArea').textContent = `${defaultArea} • ${defaultSport} Hub`;
            document.title = `${defaultName} - PlayMatrix`;

            if (!placeId || placeId.length < 5) {
                // Fallback for mock data (id is 0-99)
                loadMockDetails();
                return;
            }

            const service = new google.maps.places.PlacesService(document.createElement('div'));
            service.getDetails({
                placeId: placeId,
                fields: ['name', 'rating', 'formatted_address', 'photos', 'opening_hours', 'reviews', 'user_ratings_total']
            }, (place, status) => {
                if (status === google.maps.places.PlacesServiceStatus.OK) {
                    updateRealUI(place);
                } else {
                    console.error("Place details failed:", status);
                    loadMockDetails();
                }
            });
        }

        function updateRealUI(place) {
            document.getElementById('venueName').textContent = place.name;
            document.getElementById('venueAddress').textContent = place.formatted_address;
            document.getElementById('venueRating').textContent = place.rating || defaultRating;
            document.getElementById('venueReviews').textContent = place.user_ratings_total || defaultReviews;

            // Update Hours
            if (place.opening_hours && place.opening_hours.weekday_text) {
                // Find today's hours or just show the first one
                const hours = place.opening_hours.weekday_text[new Date().getDay()] || place.opening_hours.weekday_text[0];
                document.querySelector('.action-card:nth-of-type(2) .info-value').textContent = hours.split(': ')[1] || "Closed";
            } else {
                document.querySelector('.action-card:nth-of-type(2) .info-value').textContent = "6:00 AM - 10:00 PM (Estimated)";
            }

            // Update Photos
            if (place.photos && place.photos.length > 0) {
                photoshoot = place.photos.map(p => p.getUrl({ maxWidth: 1200, maxHeight: 800 }));
            } else {
                photoshoot = generateFallbackPhotos();
            }
            renderGallery();
        }

        function loadMockDetails() {
            const seed = parseInt(placeId) || 0;
            const streets = ["Gandhi Nagar", "Park Avenue", "Sector 4", "Main Road", "Lake View"];
            const landmark = "Behind City Park";
            document.getElementById('venueAddress').textContent = `${streets[seed % 5]}, ${landmark}, ${defaultArea}`;
            document.getElementById('venueRating').textContent = defaultRating;
            document.getElementById('venueReviews').textContent = defaultReviews;

            photoshoot = generateFallbackPhotos();
            renderGallery();
        }

        function generateFallbackPhotos() {
            const keywords = {
                'Football': 'soccer,field',
                'Cricket': 'cricket,ground',
                'Badminton': 'badminton,court',
                'Tennis': 'tennis,court',
                'Swimming': 'pool',
                'Basketball': 'basketball'
            };
            const kw = keywords[defaultSport] || 'sports';
            const arr = [];
            for (let i = 0; i < 5; i++) {
                arr.push(`https://loremflickr.com/1200/800/${kw}/all?lock=${(placeId || 0) + i}`);
            }
            return arr;
        }

        function renderGallery() {
            const container = document.getElementById('galleryContainer');
            const dots = document.getElementById('dotsContainer');

            // Remove old slides/dots
            const oldSlides = container.querySelectorAll('.gallery-slide');
            oldSlides.forEach(s => s.remove());
            dots.innerHTML = '';

            photoshoot.forEach((url, i) => {
                const imgDiv = document.createElement('div');
                imgDiv.className = `gallery-slide ${i === 0 ? 'active' : ''}`;
                imgDiv.innerHTML = `<img src="${url}" alt="Venue photo">`;
                container.insertBefore(imgDiv, container.querySelector('.gallery-nav'));

                const d = document.createElement('div');
                d.className = `dot ${i === 0 ? 'active' : ''}`;
                d.onclick = () => showSlide(i);
                dots.appendChild(d);
            });
        }

        function showSlide(index) {
            const slides = document.querySelectorAll('.gallery-slide');
            const dots = document.querySelectorAll('.dot');
            if (index >= slides.length) index = 0;
            if (index < 0) index = slides.length - 1;
            slides.forEach(s => s.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));
            slides[index].classList.add('active');
            dots[index].classList.add('active');
            currentSlide = index;
        }

        function nextSlide() { showSlide(currentSlide + 1); }
        function prevSlide() { showSlide(currentSlide - 1); }

        function goToBooking() {
            window.location.href = `4.php?${urlParams.toString()}`;
        }

        // Initialize on window load
        window.addEventListener('load', initDetails);
        setInterval(nextSlide, 5000);

    </script>
</body>

</html>