<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Booking System</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Styling for the services showcase */
        .services-showcase {
            margin-top: 50px;
            padding: 20px 0;
            background-color: #f9f9f9;
        }
        
        .services-showcase h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .service-card {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: white;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .service-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        
        .service-info {
            padding: 15px;
        }
        
        .service-info h3 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 1.2rem;
        }
        
        .service-info p {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .view-providers {
            display: inline-block;
            padding: 8px 15px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: background-color 0.3s ease;
        }
        
        .view-providers:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Online Appointment Booking System</h1>
            <nav>
                <a href="browse_providers.php">Browse Providers</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_type'] == 'customer'): ?>
                        <a href="user_dashboard.php">Dashboard</a>
                    <?php else: ?>
                        <a href="provider_dashboard.php">Dashboard</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </header>
        
        <main>
            <section class="welcome">
                <h2>Welcome to our Appointment Booking System</h2>
                <p>A simple and efficient way to book appointments with service providers.</p>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="cta">
                        <a href="login.php" class="btn">Login</a>
                        <a href="register.php" class="btn">Register Now</a>
                    </div>
                <?php endif; ?>
            </section>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <section class="quick-access">
                    <?php if ($_SESSION['user_type'] == 'customer'): ?>
                        <a href="book_appointment.php" class="btn">Book an Appointment</a>
                    <?php else: ?>
                        <a href="set_availability.php" class="btn">Set Your Availability</a>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
            
            <!-- New Services Show case Section -->
            <section class="services-showcase">
                <h2>Our Available Services</h2>
                <div class="services-grid">
                    <!-- Kuaför Salonu	 -->
                    <div class="service-card">
                        <img src="assets\images\Barber.jpg" alt="Men's Barber Shop" class="service-image">
                        <div class="service-info">
                            <h3>Men's Barber Shop</h3>
                            <p>Professional haircuts, beard trimming, and grooming services for men.</p>
                            <a href="browse_providers.php?category=2" class="view-providers">View Providers</a>
                        </div>
                    </div>
                    
                    <!-- Fitness Eğitimi	 -->
                    <div class="service-card">
                        <img src="assets\images\fitness.jpg" alt="Fitness Center" class="service-image">
                        <div class="service-info">
                            <h3>Fitness Center</h3>
                            <p>Book sessions with personal trainers and fitness experts.</p>
                            <a href="browse_providers.php?category=6" class="view-providers">View Providers</a>
                        </div>
                    </div>
                    
                    <!-- Guzellik Salonu -->
                    <div class="service-card">
                        <img src="assets\images\BeautySalon.jpg" alt="Beauty Salon" class="service-image">
                        <div class="service-info">
                            <h3>Beauty Salon</h3>
                            <p>Complete beauty services including hair styling, makeup, and skincare.</p>
                            <a href="browse_providers.php?category=10" class="view-providers">View Providers</a>
                        </div>
                    </div>
                    
                    <!-- Tibbi Klinik -->
                    <div class="service-card">
                        <img src="assets\images\MedicalClinic.jpg" alt="Medical Clinic" class="service-image">
                        <div class="service-info">
                            <h3>Medical Clinic</h3>
                            <p>Book appointments with doctors and healthcare specialists.</p>
                            <a href="browse_providers.php?category=4" class="view-providers">View Providers</a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Online Appointment Booking System</p>
        </footer>
    </div>
</body>
</html>