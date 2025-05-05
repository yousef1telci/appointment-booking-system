<?php
session_start();
require_once 'db/connection.php';

// Get categories for filter
$categories_query = "SELECT id, name FROM service_categories ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);

// Filter providers by category if category_id is set
$filter_condition = "";
if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
    $category_id = mysqli_real_escape_string($conn, $_GET['category_id']);
    $filter_condition = "AND u.service_category_id = $category_id";
}

// Get providers with their categories
$providers_query = "SELECT u.id, u.name, u.email, c.name as category_name 
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
</head>
<body>
    <div class="container">
        <header>
            <h1>Browse Service Providers</h1>
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
            <section class="filter-section">
                <h3>Filter by Category</h3>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
                    <div class="form-group">
                        <select name="category_id">
                            <option value="">All Categories</option>
                            <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo $category['name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn">Filter</button>
                </form>
            </section>
            
            <section class="providers-list">
                <h3>Service Providers</h3>
                
                <?php if (mysqli_num_rows($providers_result) > 0): ?>
                    <div class="providers-grid">
                        <?php while ($provider = mysqli_fetch_assoc($providers_result)): ?>
                            <div class="provider-card">
                                <h4><?php echo $provider['name']; ?></h4>
                                <p class="category-badge"><?php echo $provider['category_name']; ?></p>
                                <p><i class="email-icon">✉</i> <?php echo $provider['email']; ?></p>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'customer'): ?>
                                    <a href="book_appointment.php?provider_id=<?php echo $provider['id']; ?>" class="btn btn-book">Book Appointment</a>
                                <?php elseif (!isset($_SESSION['user_id'])): ?>
                                    <a href="login.php" class="btn">Login to Book</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No service providers found for the selected category.</p>
                <?php endif; ?>
            </section>
        </main>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Online Appointment Booking System</p>
        </footer>
    </div>
</body>
</html>