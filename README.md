# 📚 Library Management System

A modern, full-featured library management system built with PHP, MySQL, and iOS-inspired glassmorphism design. Manage books, members, circulation, fines, and payments with an elegant user interface.

## ✨ Features

### 📖 Book Management

- Add, edit, delete, and search books
- Upload book cover images
- Track book availability and quantity
- ISBN, author, publisher, and category management
- Published year tracking

### 👥 Member Management

- Register and manage library members
- Profile photo upload
- Member status (active/inactive/suspended)
- Membership types (standard/premium/student)
- Track borrowing history and outstanding fines
- Max books allowed per member

### 🔄 Circulation System

- Issue books to members with due dates
- Return books with condition tracking
- Search by member or book
- Automated fine calculation for overdue books
- Grace period support
- Maximum fine limit

### 💰 Fine Management

- Automatic fine calculation based on overdue days
- Fine payment processing (cash/card/online)
- Payment history with date filtering
- Outstanding fine tracking
- Configurable fine rules (per day, grace period, max amount)

### 👤 User Roles

- **Admin**: Full system access and settings management
- **Librarian**: Manage books, members, and circulation
- **Member**: View borrowed books and personal information

### 🎨 Modern UI/UX

- iOS-inspired glassmorphism design
- Responsive layout (mobile-friendly)
- Smooth animations and transitions
- SweetAlert2 for beautiful notifications
- Real-time search with debouncing
- Modal dialogs for forms

## 🛠️ Tech Stack

**Backend:**

- PHP 8+ with PDO
- MySQL Database
- Session-based authentication
- Role-based access control (RBAC)

**Frontend:**

- HTML5 & CSS3
- JavaScript (ES6+)
- Tailwind CSS 3.x (CDN)
- Font Awesome 6.4.0
- SweetAlert2 v11
- Google Fonts (Inter)

**Server:**

- Apache (WAMP/XAMPP/LAMP)

## 📋 Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache Web Server
- Web browser with modern CSS support (Chrome, Firefox, Safari, Edge)

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/Sopanha9/library-php-system.git
cd library-php-system
```

### 2. Set Up Database

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `library_database`
3. Import the database schema:
   - Run `database_members.sql` to create tables and insert sample data
   - Run `database_update.sql` if needed for additional features

### 3. Configure Database Connection

Edit `config/db.php` with your database credentials:

```php
$host = 'localhost';
$db   = 'library_database';
$user = 'root';        // Your MySQL username
$pass = '';            // Your MySQL password
```

### 4. Create Upload Directories

Create these folders in the root directory:

```
uploads/
├── books/
└── members/
```

Make sure these folders have write permissions.

### 5. Start Your Server

- **WAMP**: Start WAMP server and place project in `C:/wamp64/www/`
- **XAMPP**: Start XAMPP and place project in `C:/xampp/htdocs/`
- **LAMP**: Ensure Apache and MySQL are running

### 6. Access the Application

Open your browser and navigate to:

```
http://localhost/library-php-system/auth/login.php
```

## 🔐 Default Login Credentials

### Administrator

- **Username:** `admin`
- **Password:** `password`
- **Role:** Admin

### Librarian

- **Username:** `librarian`
- **Password:** `password`
- **Role:** Librarian

### Member

- **Username:** `member`
- **Password:** `password`
- **Role:** Member

> ⚠️ **Important:** Change these default passwords after first login!

## 📁 Project Structure

```
library-php-system/
├── admin/              # Admin dashboard and features
├── auth/               # Login, logout, registration
├── books/              # Book management (CRUD)
├── circulation/        # Issue, return, overdue, fines
├── config/             # Database configuration
├── includes/           # Header, footer, navbar, sidebar
├── librarian/          # Librarian dashboard
├── member/             # Member dashboard
├── assets/             # CSS and static assets
│   └── css/
├── uploads/            # User-uploaded files (ignored in git)
│   ├── books/         # Book cover images
│   └── members/       # Member profile photos
└── README.md
```

## 💻 Usage Guide

### For Administrators

1. **Manage Books**: Add/edit/delete books with cover images
2. **Manage Members**: Register new members, update profiles
3. **Configure Settings**: Set fine rules, grace periods, max fines
4. **View Reports**: Monitor circulation, fines, and payments

### For Librarians

1. **Issue Books**: Search members, select books, set due dates
2. **Return Books**: Process returns, calculate fines
3. **Track Overdue**: View overdue books and contact members
4. **Record Payments**: Process fine payments with multiple methods

### For Members

1. **View Borrowed Books**: See current and past borrowing history
2. **Check Fines**: View outstanding fines and payment history
3. **Update Profile**: Change password and profile information

## 🎨 Key Features Explained

### Glassmorphism Design

The entire UI uses iOS-inspired glassmorphism with:

- Frosted glass effect (`backdrop-filter: blur(20px)`)
- Semi-transparent backgrounds
- Smooth shadows and borders
- Blue color palette (#0A84FF primary)

### Search Functionality

- **Debounced Search**: 300ms delay prevents excessive database queries
- **Real-time Results**: Instant feedback as you type
- **Multi-field Search**: Search across names, emails, titles, ISBNs

### Fine Calculation

```
Fine = (Days Overdue - Grace Period) × Fine Per Day
Capped at Maximum Fine Amount
```

### SweetAlert2 Integration

- Success/Error/Warning alerts with glassmorphism styling
- Toast notifications for non-blocking feedback
- Confirmation dialogs for destructive actions

## 🔧 Configuration

### Fine Settings (Admin Panel)

- **Fine Per Day**: Amount charged per overdue day (default: 1.00 Riel)
- **Grace Period**: Days before fine starts (default: 0)
- **Max Fine**: Maximum fine per book (default: 50.00 Riel)
- **Default Borrow Days**: Loan period (default: 14 days)

### Database Tables

- `users` - System users with roles
- `books` - Book catalog
- `members` - Library members
- `issued_books` - Circulation records
- `fine_payments` - Payment history
- `library_settings` - Configurable settings

## 🐛 Troubleshooting

### Common Issues

**1. Database Connection Error**

- Verify database credentials in `config/db.php`
- Ensure MySQL service is running
- Check if `library_database` exists

**2. Images Not Uploading**

- Check `uploads/` folder exists with write permissions
- Verify file size limits in `php.ini`
- Supported formats: JPG, JPEG, PNG, GIF

**3. Login Not Working**

- Clear browser cache and cookies
- Verify user exists in `users` table
- Check role is spelled correctly (lowercase)

**4. Fines Not Calculating**

- Ensure `library_settings` table has fine configuration
- Check date formats in database
- Verify grace period settings

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is open source and available for educational purposes.

## 👨‍💻 Author

**Sopanha**

- GitHub: [@Sopanha9](https://github.com/Sopanha9)

## 📞 Support

For issues or questions:

- Open an issue on GitHub
- Check existing documentation in the `docs/` folder

## 🔮 Future Enhancements

- [ ] Email notifications for overdue books
- [ ] Advanced reporting and analytics
- [ ] Book reservation system
- [ ] Digital library card generation
- [ ] Multi-language support
- [ ] RESTful API for mobile apps
- [ ] Export data to PDF/Excel

---

⭐ **Star this repository if you find it helpful!**

📚 **Built with ❤️ for library management**
