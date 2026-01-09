<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlayMatrix - The Arena</title>
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
            /* Neon Green */
            --primary-green-dim: #2cb812;
            --text-white: #ffffff;
            --text-gray: #a1a1a1;
            --glass-border: rgba(255, 255, 255, 0.08);
            --gradient-card: linear-gradient(145deg, #1a1a1a, #0d0d0d);
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
            flex-direction: column;
            overflow-x: hidden;
            position: relative;
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        /* Logo Section */
        .brand-header {
            margin-top: 1rem;
            margin-bottom: 3rem;
            text-align: center;
            opacity: 0;
            animation: fadeIn 1s ease-out forwards;
        }

        .brand-icon {
            font-size: 2.5rem;
            color: var(--bg-dark);
            background: var(--primary-green);
            width: 60px;
            height: 60px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            margin-bottom: 1rem;
            box-shadow: 0 0 20px rgba(57, 255, 20, 0.4);
        }

        .brand-title {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .brand-subtitle {
            color: var(--primary-green);
            font-size: 0.8rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 5px;
        }

        /* Feature Cards (Three Column) */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            width: 100%;
            margin-bottom: 4rem;
        }

        .feature-card {
            background: var(--gradient-card);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            opacity: 0;
            animation: slideUp 0.6s ease-out forwards;
            cursor: default;
        }

        /* Staggered Delay */
        .feature-card:nth-child(1) {
            animation-delay: 0.2s;
        }

        .feature-card:nth-child(2) {
            animation-delay: 0.3s;
        }

        .feature-card:nth-child(3) {
            animation-delay: 0.4s;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            border-color: var(--primary-green);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .card-visual {
            height: 200px;
            width: 100%;
            border-radius: 12px;
            background-color: #222;
            margin-bottom: 1.5rem;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Gradient backgrounds for card visuals instead of images for a clean tech look */
        .visual-1 {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .visual-2 {
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
        }

        .visual-3 {
            background: linear-gradient(135deg, #FDC830 0%, #F37335 100%);
        }

        .card-visual i {
            font-size: 4rem;
            color: rgba(255, 255, 255, 0.2);
            z-index: 1;
            transition: 0.3s;
        }

        .feature-card:hover .card-visual i {
            transform: scale(1.1) rotate(-5deg);
            color: rgba(255, 255, 255, 0.4);
        }

        .card-icon-badge {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            color: var(--primary-green);
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .card-text h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .card-text p {
            font-size: 0.9rem;
            color: var(--text-gray);
            line-height: 1.5;
        }

        /* Bottom Section */
        .action-section {
            text-align: center;
            opacity: 0;
            animation: fadeIn 1s ease-out forwards 0.8s;
        }

        .main-headline {
            font-size: 2.2rem;
            margin-bottom: 2rem;
            font-weight: 700;
        }

        .btn-container {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary-green);
            color: var(--bg-dark);
            border: 2px solid var(--primary-green);
            box-shadow: 0 0 15px rgba(57, 255, 20, 0.3);
        }

        .btn-primary:hover {
            background-color: var(--primary-green-dim);
            border-color: var(--primary-green-dim);
            transform: translateY(-2px);
            box-shadow: 0 0 25px rgba(57, 255, 20, 0.5);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--text-white);
            border: 2px solid #333;
        }

        .btn-secondary:hover {
            background-color: #333;
            border-color: #444;
            transform: translateY(-2px);
        }

        /* Social/Footer */
        .divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            color: var(--text-gray);
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            width: 100%;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
        }

        .divider::before,
        .divider::after {
            content: '';
            height: 1px;
            background: #333;
            flex: 1;
        }

        .social-icons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .social-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #1a1a1a;
            border: 1px solid #333;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-white);
            font-size: 1.2rem;
            cursor: pointer;
            transition: 0.3s;
        }

        .social-btn:hover {
            background: #252525;
            border-color: var(--text-gray);
            color: var(--primary-green);
        }

        footer {
            margin-top: auto;
            color: #555;
            font-size: 0.8rem;
            padding-bottom: 1rem;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Dynamic Glow behind header */
        .glow-spot {
            position: absolute;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 400px;
            background: radial-gradient(circle, rgba(57, 255, 20, 0.15) 0%, transparent 70%);
            filter: blur(60px);
            z-index: -2;
            pointer-events: none;
        }
    </style>
</head>

<body>

    <div class="grid-bg"></div>
    <div class="glow-spot"></div>

    <div class="container">
        <!-- Logo -->
        <div class="brand-header">
            <div class="brand-icon">
                <i class="fa-solid fa-cube"></i>
            </div>
            <h1 class="brand-title">Play Matrix</h1>
            <div class="brand-subtitle">The Future of Sports</div>
        </div>

        <!-- Cards -->
        <div class="features-grid">
            <!-- Card 1 -->
            <div class="feature-card">
                <div class="card-visual visual-1">
                    <i class="fa-solid fa-futbol"></i>
                    <div class="card-icon-badge"><i class="fa-solid fa-calendar-check"></i></div>
                </div>
                <div class="card-text">
                    <h3>Instant Booking</h3>
                    <p>Book courts and fields in seconds near you. Real-time availability.</p>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="feature-card">
                <div class="card-visual visual-2">
                    <i class="fa-solid fa-dumbbell"></i>
                    <div class="card-icon-badge"><i class="fa-solid fa-user-group"></i></div>
                </div>
                <div class="card-text">
                    <h3>Pro Trainers</h3>
                    <p>Connect with certified professionals to level up your game.</p>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="feature-card">
                <div class="card-visual visual-3">
                    <i class="fa-solid fa-mountain-sun"></i>
                    <div class="card-icon-badge"><i class="fa-solid fa-person-hiking"></i></div>
                </div>
                <div class="card-text">
                    <h3>Host Adventures</h3>
                    <p>Organize matches and outdoor treks. Create your own community.</p>
                </div>
            </div>
        </div>

        <!-- Bottom Action -->
        <div class="action-section">
            <h2 class="main-headline">Your Arena. Your Rules.</h2>

            <div class="btn-container">
                <a href="register.php" class="btn btn-primary">
                    Get Started <i class="fa-solid fa-arrow-right"></i>
                </a>
                <a href="login.php" class="btn btn-secondary">
                    I have an account
                </a>
            </div>

            <div class="divider">Or continue with</div>

            <div class="social-icons">
                <a href="register.php" class="social-btn"
                    style="background: white; color: #333; border: none; transition: transform 0.3s; display: flex; text-decoration: none;"
                    title="Sign up with Google">
                    <i class="fa-brands fa-google"></i>
                </a>
            </div>

            <footer>
                By continuing, you agree to our Terms of Service & Privacy Policy.
            </footer>
        </div>
    </div>

</body>

</html>