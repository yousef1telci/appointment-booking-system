<?php
session_start();
require_once 'db/connection.php';

// Get categories for filter
$categories_query = "SELECT id, name FROM service_categories ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);

// Filter providers by category if category_id is set
$filter_condition = "";
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category_id = mysqli_real_escape_string($conn, $_GET['category']);
    $filter_condition = "AND u.service_category_id = $category_id";
}

// Get providers with their categories
$providers_query = "SELECT u.id, u.name, u.email, c.name as category_name, c.id as category_id 
                  FROM users u
                  JOIN service_categories c ON u.service_category_id = c.id
                  WHERE u.user_type = 'provider' $filter_condition
                  ORDER BY c.name ASC, u.name ASC";
$providers_result = mysqli_query($conn, $providers_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Providers - Appointment Booking System</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Styling for providers section - matching index.php style */
        .filter-section {
            margin: 30px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 600px;
        }
        
        .filter-section h3 {
            margin-top: 0;
            color: #2c3e50;
            text-align: center;
        }
        
        .filter-section form {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
        }
        
        .filter-section select {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.95rem;
            flex-grow: 1;
            max-width: 300px;
        }
        
        .filter-section .btn {
            padding: 8px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .filter-section .btn:hover {
            background-color: #2980b9;
        }
        
        .providers-list {
            margin-top: 50px;
            padding: 20px 0;
        }
        
        .providers-list h3 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .providers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .provider-card {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .provider-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .provider-card h4 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 1.3rem;
        }
        
        .category-badge {
            display: inline-block;
            background-color:rgb(229, 229, 229);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            margin: 10px 0;
        }
        
        .provider-card p {
            color: #7f8c8d;
            margin-bottom: 15px;
        }
        
        .email-icon {
            color: #3498db;
            margin-right: 5px;
        }
        
        .btn-book {
            margin-top: auto;
            display: inline-block;
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        
        .btn-book:hover {
            background-color: #2980b9;
        }
        
        .no-providers {
            text-align: center;
            color: #7f8c8d;
            font-size: 1.1rem;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Online Appointment Booking System</h1>
            <nav>
                <a href="index.php">Home</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_type'] == 'customer'): ?>
                        <a href="user_dashboard.php">Dashboard</a>
                        <a href="book_appointment.php">Book Appointment</a>
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
                <h2>Browse Service Providers</h2>
                <p>Find and book appointments with our qualified service providers.</p>
            </section>
            
            <section class="filter-section">
                <h3>Filter by Category</h3>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php mysqli_data_seek($categories_result, 0); ?>
                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="btn">Filter</button>
                </form>
            </section>
            
            <section class="providers-list">
                <h3>Available Service Providers</h3>
                
                <?php if (mysqli_num_rows($providers_result) > 0): ?>
                    <div class="providers-grid">
                        <?php while ($provider = mysqli_fetch_assoc($providers_result)): ?>
                            <div class="provider-card">
                                <h4><?php echo $provider['name']; ?></h4>
                                <p class="category-badge"><?php echo $provider['category_name']; ?></p>
                                <p><i class="email-icon">✉</i> <?php echo $provider['email']; ?></p>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'customer'): ?>
                                    <a href="book_appointment.php?provider_id=<?php echo $provider['id']; ?>" class="btn-book">Book Appointment</a>
                                <?php elseif (!isset($_SESSION['user_id'])): ?>
                                    <a href="login.php" class="btn-book">Login to Book</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="no-providers">No service providers found for the selected category.</p>
                <?php endif; ?>
            </section>
        </main>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Online Appointment Booking System</p>
        </footer>
    </div>
</body>
</html>