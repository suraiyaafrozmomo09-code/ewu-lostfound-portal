# EWU Lost & Found Portal

## Project Overview
A modern web application for East West University's Lost & Found system.

## Setup Instructions
1. Start XAMPP (Apache on 8080, MySQL on 3307)
2. Import `ewu_lostfound.sql` to phpMyAdmin
3. Access via `http://localhost:8080/ewu-lostfound/`
4. Login with demo accounts

## CRUD Operations
- **Create:** Register, Report Items
- **Read:** View Items, Search, Dashboard
- **Update:** Edit Items, Claim Items
- **Delete:** Admin delete operations

## Features Implemented
1. User Registration & Authentication
2. Report Lost Items
3. Report Found Items
4. Search & Browse Items
5. User Dashboard
6. Admin Panel
7. Item Claim System
8. Responsive Modern Desig

 ## Access Information
- **URL:** `http://localhost:8080/ewu-lostfound/`
- **Student Demo:** `student1@std.ewu.bd` / `password123`
- **Admin Demo:** `admin@ewu.edu.bd` / `password123`
- **Database:** MySQL on port 3307
- **Web Server:** Apache on port 8080


- ## File Structure
ewu-lostfound/
├── 📄 index.php              # Homepage
├── 📄 login.php              # Login page
├── 📄 register.php           # Registration page
├── 📄 dashboard.php          # User dashboard
├── 📄 admin_dashboard.php    # Admin panel
├── 📄 search.php             # Search functionality
├── 📄 report_lost.php        # Report lost item
├── 📄 report_found.php       # Report found item
├── 📄 view_item.php          # View item details
├── 📄 logout.php             # Logout
├── 📄 manage_users.php       # Admin: Manage users
├── 📄 manage_items.php       # Admin: Manage items
├── 📄 manage_categories.php  # Admin: Manage categories
├── 📄 reports.php            # Admin: Reports & analytics
├── 📄 export_data.php        # Data export
├── 📄 database.sql           # Database structure & data
├── 📄 README.md              # This file
├── 📁 config/
│   └── 📄 database.php       # Database configuration
├── 📁 includes/
│   ├── 📄 header.php         # Header component
│   └── 📄 footer.php         # Footer component
└── 📁 assets/
    └── 📁 css/
        └── 📄 style.css      # Main stylesheet


## Technology Stack
Component	Technology
Frontend	HTML5, CSS3, JavaScript
Backend	PHP 7.4+
Database	MySQL 8.0
Server	Apache (XAMPP)
Icons	Font Awesome 6
Fonts	Google Fonts (Inter)
Design	Glassmorphism UI

