<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">

<style>
    :root {
        --bg-dark: #050505;
        --bg-card: #0c0c0c;
        --primary-green: #39ff14;
        --primary-green-dim: #2ca812;
        --text-white: #ffffff;
        --text-gray: #a1a1a1;
        --glass-border: rgba(255, 255, 255, 0.08);
        --input-bg: #1a1a1a;
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

    /* Navbar */
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

    /* Layout */
    .container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 2%;
    }

    /* Booking Section */
    .booking-grid {
        display: grid;
        grid-template-columns: 1.3fr 1fr;
        gap: 2rem;
        align-items: start;
    }

    /* Left Booking Card */
    .booking-card {
        background: var(--bg-card);
        border-radius: 12px;
        border: 1px solid var(--glass-border);
        overflow: hidden;
        padding: 2.5rem;
    }

    .venue-info-header {
        margin-bottom: 3rem;
    }

    .venue-title {
        font-size: 2.4rem;
        font-weight: 800;
        color: var(--text-white);
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }

    .venue-subtitle {
        color: var(--text-gray);
        font-size: 1.1rem;
        font-weight: 500;
    }

    .booking-form {
        display: flex;
        flex-direction: column;
        gap: 2.5rem;
        margin-bottom: 3rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 180px 1fr;
        align-items: center;
        gap: 1rem;
    }

    .form-label {
        color: var(--text-gray);
        font-weight: 500;
        font-size: 1.1rem;
    }

    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .form-control {
        background: var(--input-bg);
        border: 1px solid var(--glass-border);
        color: var(--text-white);
        padding: 1rem 1.2rem;
        border-radius: 10px;
        width: 100%;
        font-size: 1.1rem;
        font-weight: 500;
        outline: none;
        transition: 0.3s;
        appearance: none;
        -webkit-appearance: none;
    }

    .form-control:focus {
        border-color: var(--primary-green);
        background: #252525;
    }

    .input-wrapper i {
        position: absolute;
        right: 1.2rem;
        color: #444;
        pointer-events: none;
        font-size: 1rem;
    }

    /* Counter Control */
    .counter-wrapper {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .counter-btn {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        border: none;
        background: #222;
        color: var(--text-white);
        cursor: pointer;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }

    .counter-btn.add {
        background: var(--primary-green);
        color: black;
    }

    .counter-btn:hover {
        transform: scale(1.1);
    }

    .duration-text {
        color: var(--text-white);
        font-weight: 700;
        font-size: 1.2rem;
        min-width: 60px;
        text-align: center;
    }

    .add-to-cart-btn {
        width: 100%;
        padding: 1.4rem;
        background: var(--primary-green);
        color: #000;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: 0.3s;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .add-to-cart-btn:hover {
        background: var(--primary-green-dim);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(57, 255, 20, 0.4);
    }

    /* Right Cart Card - Styled to match dark theme with effects */
    .cart-card {
        background: rgba(18, 18, 18, 0.8);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
        overflow: hidden;
        color: var(--text-white);
        display: none;
        /* Hidden by default, shown when items added */
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        animation: slideInRight 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px) scale(0.95);
        }

        to {
            opacity: 1;
            transform: translateX(0) scale(1);
        }
    }

    .cart-empty-state {
        background: var(--bg-card);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        height: 500px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--text-gray);
        gap: 1rem;
        transition: 0.3s ease;
    }

    .empty-icon {
        font-size: 5rem;
        opacity: 0.1;
        animation: pulse 2s infinite ease-in-out;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 0.1;
        }

        50% {
            transform: scale(1.1);
            opacity: 0.2;
        }

        100% {
            transform: scale(1);
            opacity: 0.1;
        }
    }

    .cart-header {
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--glass-border);
        background: rgba(255, 255, 255, 0.03);
    }

    .cart-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-white);
        letter-spacing: 0.5px;
    }

    .cart-delete-all {
        color: #ff4d4d;
        cursor: pointer;
        font-size: 1.2rem;
        transition: 0.3s ease;
        opacity: 0.7;
    }

    .cart-delete-all:hover {
        opacity: 1;
        transform: rotate(15deg);
    }

    .cart-items {
        padding: 1.5rem;
        max-height: 400px;
        overflow-y: auto;
    }

    .cart-item {
        position: relative;
        margin-bottom: 1.5rem;
        padding: 1.2rem;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        transition: 0.3s ease;
    }

    .cart-item:hover {
        background: rgba(255, 255, 255, 0.04);
        border-color: rgba(57, 255, 20, 0.2);
        transform: translateX(5px);
    }

    .cart-item-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
        font-size: 1rem;
        font-weight: 500;
        color: var(--text-gray);
    }

    .cart-item-row i {
        width: 20px;
        color: var(--primary-green);
        opacity: 0.8;
        font-size: 0.9rem;
    }

    .cart-item-row span {
        color: #e0e0e0;
    }

    .cart-item-remove {
        position: absolute;
        right: 1rem;
        top: 1rem;
        color: #ff4d4d;
        font-size: 1.1rem;
        cursor: pointer;
        opacity: 0.5;
        transition: 0.3s;
    }

    .cart-item-remove:hover {
        opacity: 1;
        transform: scale(1.2);
    }

    .cart-footer {
        padding: 1.5rem;
        border-top: 1px solid var(--glass-border);
        background: rgba(0, 0, 0, 0.2);
    }

    .btn-proceed {
        width: 100%;
        padding: 1.2rem;
        background: var(--primary-green);
        color: #000;
        border: none;
        border-radius: 10px;
        font-weight: 800;
        font-size: 1.1rem;
        cursor: pointer;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 4px 20px rgba(57, 255, 20, 0.2);
    }

    .btn-proceed:hover {
        background: var(--primary-green-dim);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(57, 255, 20, 0.4);
    }

    .btn-proceed:active {
        transform: translateY(-1px);
    }

    /* Flatpickr Customization */
    .flatpickr-calendar {
        background: #1a1a1a !important;
        border: 1px solid var(--glass-border) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
    }

    .flatpickr-day.selected {
        background: var(--primary-green) !important;
        border-color: var(--primary-green) !important;
        color: #000 !important;
    }

    @media (max-width: 900px) {
        .booking-grid {
            grid-template-columns: 1fr;
        }

        .form-row {
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
        </div>
        <div class="nav-right">
            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>
        </div>
    </nav>

    <!-- Success Modal -->
    <div id="successModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:2000; align-items:center; justify-content:center;">
        <div style="background:var(--bg-card); border:1px solid var(--primary-green); padding:3rem; border-radius:20px; text-align:center; max-width:500px; animation: modalIn 0.5s ease-out;">
            <i class="fa-solid fa-circle-check" style="color:var(--primary-green); font-size:5rem; margin-bottom:1.5rem;"></i>
            <h2 style="font-size:2rem; margin-bottom:1rem;">Booking Confirmed!</h2>
            <p style="color:var(--text-gray); margin-bottom:2rem;">Your slot at <span id="successVenue" style="color:white; font-weight:600;"></span> has been successfully reserved.</p>
            <button onclick="window.location.href='2.php'" class="btn-main" style="padding:1rem 2rem;">Explore More</button>
        </div>
    </div>

    <style>
        @keyframes modalIn {
            from { transform: scale(0.8); opacity:0; }
            to { transform: scale(1); opacity:1; }
        }
    </style>

    <div class="container">
        <div class="booking-grid">

            <!-- Left: Booking Form -->
            <div class="booking-card">
                <div class="venue-info-header">
                    <h1 class="venue-title" id="venueName">Venue Name</h1>
                    <div class="venue-subtitle" id="venueSubtitle">Location Context</div>
                </div>

                <div class="booking-form">
                    <!-- Sport -->
                    <div class="form-row">
                        <label class="form-label">Sports</label>
                        <div class="input-wrapper">
                            <select class="form-control" id="sportSelect">
                                <!-- Populated via JS -->
                            </select>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="form-row">
                        <label class="form-label">Date</label>
                        <div class="input-wrapper">
                            <input type="text" class="form-control" id="dateInput" placeholder="Select Date">
                            <i class="fa-regular fa-calendar"></i>
                        </div>
                    </div>

                    <!-- Start Time -->
                    <div class="form-row">
                        <label class="form-label">Start Time</label>
                        <div class="input-wrapper">
                            <select class="form-control" id="timeInput">
                                <!-- Populated via JS -->
                            </select>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>

                    <!-- Duration -->
                    <div class="form-row">
                        <label class="form-label">Duration</label>
                        <div class="counter-wrapper">
                            <button class="counter-btn" onclick="updateDuration(-1)">-</button>
                            <span class="duration-text" id="durationDisplay">1 Hr</span>
                            <button class="counter-btn add" onclick="updateDuration(1)">+</button>
                        </div>
                    </div>
                </div>

                <button class="add-to-cart-btn" onclick="addToCart()">Add To Cart</button>
            </div>

            <!-- Right: Dynamic Cart -->
            <div id="cartArea">
                <div class="cart-empty-state" id="emptyCart">
                    <i class="fa-solid fa-cart-shopping empty-icon"></i>
                    <div class="cart-text">Cart Is Empty</div>
                </div>

                <div class="cart-card" id="filledCart">
                    <div class="cart-header">
                        <div class="cart-title" id="cartCountTitle">Cart (0)</div>
                        <i class="fa-regular fa-trash-can cart-delete-all" onclick="clearCart()"></i>
                    </div>

                    <div class="cart-items" id="cartItemsContainer">
                        <!-- Dynamic items here -->
                    </div>

                    <div class="cart-footer">
                        <button class="btn-proceed" id="proceedBtn">Proceed INR 0.00</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Get params from URL
        const urlParams = new URLSearchParams(window.location.search);
        const vParams = {
            id: urlParams.get('id') || 0,
            name: urlParams.get('name') || "Premier Sports Arena",
            sport: urlParams.get('sport') || "Football",
            area: urlParams.get('area') || "Central City"
        };

        // Populate Info
        document.getElementById('venueName').textContent = vParams.name;
        document.getElementById('venueSubtitle').textContent = `${vParams.area} • Sport Hub`;
        document.title = `Book ${vParams.name} - PlayMatrix`;

        // Populate Sport Dropdown
        const sportSelect = document.getElementById('sportSelect');
        const sportTypes = ["Football", "Cricket", "Badminton", "Tennis", "Swimming", "Basketball"];

        // Ensure the current sport is first, then add others
        const currentSport = vParams.sport || "Football";
        const allSports = [currentSport, ...sportTypes.filter(s => s !== currentSport)];

        allSports.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s;
            opt.text = s + (s === 'Cricket' ? ' Nets' : (s === 'Swimming' ? ' Lanes' : ' Court'));
            sportSelect.add(opt);
        });

        // Initialize Flatpickr
        const fp = flatpickr("#dateInput", {
            dateFormat: "d, F Y",
            defaultDate: "today",
            minDate: "today",
            theme: "dark"
        });

        // Populate Time Slots
        const timeSelect = document.getElementById('timeInput');
        timeSelect.innerHTML = '';
        for (let h = 7; h <= 21; h++) {
            let period = h >= 12 ? 'PM' : 'AM';
            let displayHour = h > 12 ? h - 12 : h;
            let timeString = `${displayHour}:00 ${period}`;
            let opt = document.createElement('option');
            opt.value = timeString;
            opt.textContent = timeString;
            timeSelect.appendChild(opt);
        }

        // Duration Logic
        let duration = 1;
        function updateDuration(change) {
            duration += change;
            if (duration < 1) duration = 1;
            if (duration > 48) duration = 48;
            document.getElementById('durationDisplay').textContent = duration + " Hr";
        }

        // Cart Logic
        let cart = [];
        const PRICE_PER_HOUR = 699;

        function addToCart() {
            const date = document.getElementById('dateInput').value;
            const startTimeString = document.getElementById('timeInput').value;
            const sport = document.getElementById('sportSelect').value;

            // Calculate end time
            const [time, period] = startTimeString.split(' ');
            const [hour] = time.split(':').map(Number);
            let startHour24 = (period === 'PM' && hour !== 12) ? hour + 12 : (period === 'AM' && hour === 12) ? 0 : hour;

            let endHour24 = (startHour24 + duration) % 24;
            let endPeriod = endHour24 >= 12 ? 'PM' : 'AM';
            let displayEndHour = endHour24 > 12 ? endHour24 - 12 : (endHour24 === 0 ? 12 : endHour24);
            let endTimeString = `${displayEndHour}:00 ${endPeriod}`;

            const totalPrice = duration * PRICE_PER_HOUR;

            const cartItem = {
                id: Date.now(),
                venue: vParams.name,
                court: sport,
                date: date,
                timeRange: `${startTimeString} to ${endTimeString}`,
                price: totalPrice
            };

            cart.push(cartItem);
            updateCartUI();
        }

        function removeCartItem(id) {
            cart = cart.filter(item => item.id !== id);
            updateCartUI();
        }

        function clearCart() {
            cart = [];
            updateCartUI();
        }

        function updateCartUI() {
            const emptyState = document.getElementById('emptyCart');
            const filledState = document.getElementById('filledCart');
            const container = document.getElementById('cartItemsContainer');
            const countTitle = document.getElementById('cartCountTitle');
            const proceedBtn = document.getElementById('proceedBtn');

            if (cart.length === 0) {
                emptyState.style.display = 'flex';
                filledState.style.display = 'none';
                return;
            }

            emptyState.style.display = 'none';
            filledState.style.display = 'block';

            countTitle.textContent = `Cart (${cart.length})`;
            container.innerHTML = '';

            let totalCartPrice = 0;

            cart.forEach(item => {
                totalCartPrice += item.price;
                const itemHtml = `
                    <div class="cart-item">
                        <i class="fa-regular fa-circle-xmark cart-item-remove" onclick="removeCartItem(${item.id})"></i>
                        <div class="cart-item-row">
                            <i class="fa-solid fa-hotel"></i>
                            <span>${item.court}</span>
                        </div>
                        <div class="cart-item-row">
                            <i class="fa-regular fa-calendar-check"></i>
                            <span>${item.date}</span>
                            <i class="fa-regular fa-clock" style="margin-left: 15px;"></i>
                            <span>${item.timeRange}</span>
                        </div>
                        <div class="cart-item-row">
                            <i class="fa-solid fa-money-bill-1-wave"></i>
                            <span>INR ${item.price}</span>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', itemHtml);
            });

            proceedBtn.textContent = `Proceed INR ${totalCartPrice.toFixed(2)}`;
            proceedBtn.onclick = () => {
                document.getElementById('successVenue').textContent = cart[0].venue;
                document.getElementById('successModal').style.display = 'flex';
            };
        }
    </script>
</body>

</html>