# ☕ Coffee Inventory Management System

A comprehensive web-based inventory management system designed for coffee shops to track inventory, manage stock transactions, handle supplier orders, and monitor user activities. Built with PHP and MySQL, this system provides real-time inventory tracking with automatic low-stock alerts.

## 📌 Project Branches

This project is divided into two main branches based on academic terms:

- **Midterm Branch**: [View Midterm Version](https://github.com/sudo-paoo/coffee-inventory-management-system/tree/midterm)
- **Final Term Branch (Current)**: [View Final Term Version](https://github.com/sudo-paoo/coffee-inventory-management-system/tree/finals)

## ✨ Key Features

- **Dashboard Analytics** - Real-time overview of inventory value, stock levels, and alerts
- **Inventory Management** - Track coffee beans, dairy products, syrups, pastries, and equipment
- **Smart Stock Tracking** - Automatic status updates (in-stock, low-stock, out-of-stock)
- **Supplier Management** - Manage supplier information and order tracking
- **Order Processing** - Create and track purchase orders with automatic stock updates
- **Transaction History** - Complete audit trail of stock-in, stock-out, damaged, and expired items
- **User Management** - Role-based access control (Admin/Staff)
- **Low Stock Alerts** - Automatic notifications when items reach reorder levels
- **Image Upload** - Attach product images for visual inventory management
- **Responsive Design** - Works seamlessly on desktop and mobile devices

## 🛠️ Technologies Used

- **Backend**: PHP 8.2+ with PDO
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Server**: Apache (XAMPP)
- **Icons**: Font Awesome

## 📋 Prerequisites

- [XAMPP](https://www.apachefriends.org/download.html) (includes Apache, PHP 8.2+, and MySQL)
- Web browser (Chrome, Firefox, Edge, or Safari)
- Git (for cloning the repository)

## 🗺️ Entity-Relationship Diagram

The ER diagram below illustrates the database structure and the relationships between the core entities:

![ER Diagram](/database/er-diagram.png)

## 🚀 Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/sudo-paoo/coffee-inventory-management-system.git
cd coffee-inventory-management-system
```

**To use the final term version (with images):**
```bash
git checkout finals
```

### 2. Move to XAMPP Directory

Move or copy the project folder to your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\coffee-inventory-management-system
```

### 3. Start XAMPP Services

- Open XAMPP Control Panel
- Start **Apache** and **MySQL** modules

### 4. Create Database

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `store_management_system`
3. Import the database schema:
   - Click on the newly created database
   - Go to the **Import** tab
   - Choose file: `database/no-data.sql`
   - Click **Go** to import

### 5. Configure Database Connection

Edit `config/database.php` if needed (default settings work with XAMPP):

```php
$host = 'localhost';
$dbname = 'store_management_system';
$username = 'root';
$password = ''; // Leave empty for XAMPP
```

### 6. Create Initial Admin User

Execute this SQL in phpMyAdmin to create your first admin account:

```sql
INSERT INTO users (first_name, last_name, email, contact_number, role, password_hash, is_active)
VALUES ('Admin', 'User', 'admin@example.com', '09123456789', 'admin', 
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);
```

**Default credentials:**
- Email: `admin@example.com`
- Password: `password`

> ⚠️ **Important**: Change the default password after first login!

## 🎯 Usage

### Access the Application

Open your web browser and navigate to:
```
http://localhost/coffee-inventory-management-system/public/
```

### Login

Use the default admin credentials:
- **Email**: `admin@example.com`
- **Password**: `password`

### Main Features

1. **Dashboard** - View inventory summary and recent activities
2. **Inventory** - Add, edit, delete, and search items with image uploads
3. **Transactions** - Record stock movements (stock-in, stock-out, damaged, expired)
4. **Orders** - Create supplier orders and mark as received
5. **Users** - Manage user accounts and roles (Admin only)
6. **Settings** - Configure system settings and preferences

## 📁 Project Structure

```
coffee-inventory-management-system/
├── assets/              # Images and static resources
│   ├── coffee-beans/   # Coffee product images
│   ├── dairies/        # Dairy product images
│   ├── equipments/     # Equipment images
│   ├── pastries/       # Pastry images
│   └── syrups/         # Syrup images
├── config/
│   └── database.php    # Database configuration
├── css/                # Stylesheets
│   ├── globals.css     # Global styles
│   ├── pages/          # Page-specific styles
│   └── components/     # Component styles
├── database/
│   └── no-data.sql     # Database schema
├── includes/
│   ├── auth.php        # Authentication functions
│   └── functions.php   # Helper functions
├── views/              # Page views
│   ├── layouts/        # Layout templates
│   └── partials/       # Reusable components
├── index.php           # Main entry point & router
├── inventory_actions.php   # Inventory CRUD operations
├── order_actions.php       # Order management
├── stock_transaction_actions.php  # Transaction handling
└── user_actions.php        # User management
```

## 🔐 User Roles & Permissions

### Admin
- Full access to all features
- User management
- System settings
- All inventory operations

### Staff
- Dashboard access
- Inventory management
- Transaction recording
- Order processing
- Cannot manage users or system settings

## 🧪 Example Workflow

1. **Add Suppliers**: Go to Settings → Add supplier information
2. **Add Categories**: Create categories (e.g., Coffee Beans, Dairy, Syrups)
3. **Add Items**: Navigate to Inventory → Add new items with images
4. **Create Order**: Go to Orders → Create purchase order from supplier
5. **Receive Order**: Mark order as received to update stock levels
6. **Record Transactions**: Use Transactions page to record stock-out or damaged items
7. **Monitor Dashboard**: View real-time inventory status and alerts

## 🤝 Contributing

This is an academic project. If you'd like to contribute:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is created for educational purposes.

## 👥 Authors

<a href="https://github.com/sudo-paoo/coffee-inventory-management-system/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=sudo-paoo/coffee-inventory-management-system" />
</a>

## 🐛 Known Issues & Troubleshooting

### Common Issues

**Problem**: "Unable to connect to database"
- **Solution**: Ensure MySQL is running in XAMPP and database name is correct

**Problem**: Images not displaying
- **Solution**: Check that `assets/` folder has proper permissions and images are uploaded

**Problem**: Login fails with correct credentials
- **Solution**: Verify user exists in database and `is_active` is set to 1

**Problem**: 404 errors on navigation
- **Solution**: Ensure mod_rewrite is enabled in Apache and .htaccess is properly configured

---

**Note**: Remember to change default credentials in production and keep your system updated!