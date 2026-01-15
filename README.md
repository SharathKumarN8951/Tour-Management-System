
#  Tour Management System (PHP & MySQL) Live project URL :https://travelindia-dev-sharath.kesug.com

A full-stack **web-based Tour Management System** developed using **PHP, MySQL, HTML, CSS, JavaScript, and Bootstrap**.  
This project allows users to browse tour packages, make bookings, and complete payments online, while admins manage tours and bookings through a dashboard.

---

##  Features

###  User Module
- User Registration & Secure Login
- Browse Tour Packages
- Book Tour Packages
- Online Payment Gateway Integration
- View Booking History
- Email Notifications (PHPMailer – SMTP)

###  Admin Module
- Admin Login
- Add / Update / Delete Tour Packages
- View All Bookings
- Manage Users
- Dashboard Overview

---

##  Security Features
- Password hashing
- Session-based authentication
- Secure SMTP email handling (credentials excluded from GitHub)
- Sensitive configuration files ignored using `.gitignore`

---

##  Tech Stack

| Layer        | Technology |
|--------------|------------|
| Backend      | PHP |
| Database     | MySQL |
| Frontend    | HTML, CSS, JavaScript |
| UI Framework | Bootstrap |
| Mail Service | PHPMailer (SMTP) |
| Server       | Apache (XAMPP / WAMP) |

---
## How to Run 
1. Clone the repository
2. Import the SQL file from /database
3. Configure database in config.php
4. Run using XAMPP / WAMP

## 👨‍💻 Author

**Sharath Kumar N**  
GitHub: https://github.com/SharathKumarN8951
LinkedIn: www.linkedin.com/in/sharath-kumar-n-ai
 
##  Project Structure

```text
tour-management-system/
│
├── admin/
├── user/
├── payments/
├── uploads/
│   └── .gitkeep
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── database/
│   └── tour_management.sql
├── config/
│   ├── config.example.php
│   └── mail_config.example.php
├── index.php
├── login.php
├── register.php
├── README.md
└── .gitignore


