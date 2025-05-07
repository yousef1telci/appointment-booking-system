"# appointment-booking-system" 

📅 Online Appointment Booking System
===================================

A simple, user-friendly web application for booking and managing appointments between customers and service providers. Built with **PHP**, **MySQL**, and **HTML/CSS**.

---

🚀 Features
----------

- 👥 **User Roles**:
  - Customers: can browse services and book appointments.
  - Providers: can set availability and manage appointments.

- 📖 **Service Categories**:
  - Supports different types of services (e.g., Güzellik Salonu, Spor Salonu, Sağlık Kliniği).
  - Each provider is linked to a specific service category.

- 📆 **Appointments**:
  - Customers can book, view, and cancel appointments.
  - Providers can manage their schedule and view bookings.

- 🔐 **Authentication & Authorization**:
  - Secure login/register system for both users and providers.

- 🎨 **Responsive UI**:
  - Clean interface built using HTML, CSS, and custom styling.
  - Service showcase section with images and category links.

---

🧱 Technologies Used
--------------------

- **Frontend**: HTML5, CSS3
- **Backend**: PHP (vanilla)
- **Database**: MySQL
- **Tools**: phpMyAdmin, XAMPP

---

📂 Project Structure
--------------------

/project-root  
│  
├── assets/                  # CSS and images  
│   └── images/              # Service category images  
│  
├── browse_providers.php     # Browse providers by service category  
├── book_appointment.php     # Book appointment page  
├── user_dashboard.php       # Dashboard for customers  
├── provider_dashboard.php   # Dashboard for providers  
├── login.php                # Login system  
├── register.php             # Registration system  
├── logout.php               # Logout logic  
├── index.php                # Homepage with service showcase  
└── database.sql             # SQL file to initialize database  

---

🛠 Setup Instructions
---------------------

1. **Clone or download the project** to your local server (e.g., `htdocs` for XAMPP).
2. **Import the SQL** file into `phpMyAdmin`:
   - Create a new database (e.g., `appointment_db`).
   - Import the `database.sql` file.
3. **Update DB credentials** in your PHP files if needed:
   ```php
   $conn = new mysqli("localhost", "root", "", "appointment_db");

jibini is here

   

