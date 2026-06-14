# 🩸 LifeFlow - Blood Bank Management System

A modern, feature-rich Blood Bank Management System built with PHP, MySQL, and modern web technologies.

![LifeFlow](https://img.shields.io/badge/Version-2.0-red)
![PHP](https://img.shields.io/badge/PHP-8.0+-blue)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange)
![License](https://img.shields.io/badge/License-MIT-green)

## ✨ Features

### 🔐 Authentication & Security
- User & Admin authentication with sessions
- Email verification with PHPMailer OTP
- Phone verification with Firebase Auth
- Real-time username availability check
- Password recovery system

### 👥 Donor Management
- Add, update, delete donor records
- Advanced search with multiple filters
- Blood group categorization

### 🩸 Blood Stock Management
- Real-time stock tracking
- Increase/decrease stock operations
- Visual stock level indicators

### 📝 Blood Request System
- Request blood with approval workflow
- Approve/decline with GSAP animations
- Guest request support

### 💬 Community Features
- Create posts with text, images, videos
- YouTube video embedding
- Facebook-style emoji reactions (👍❤️😂😢😡)
- Nested comments (reply to comments)
- Share posts
- ⭐ Star rating system for posts

### 🗺️ Blood Bank Finder
- Google Maps integration
- Search by blood type
- Radius-based filtering

### 🤖 AI Features
- TensorFlow.js eligibility predictor
- Machine learning-based assessment

### 🎨 UI/UX
- Modern dark/light theme toggle
- GSAP animations throughout
- Responsive design
- Toast notifications
- Preloader animations

## 🛠️ Tech Stack

| Technology | Purpose |
|------------|---------|
| PHP 8.0+ | Backend |
| MySQL 5.7+ | Database |
| SASS/SCSS | Styling |
| TensorFlow.js | AI/ML |
| Firebase | Phone Auth |
| PHPMailer | Email |
| GSAP | Animations |
| Flatpickr | Date Picker |
| Google Maps API | Maps |

## 📁 Project Structure

```
BBMS_WebTech/
├── api/                    # API endpoints
├── assets/
│   ├── css/               # Compiled CSS
│   ├── scss/              # SASS source files
│   └── js/                # JavaScript files
├── config/                 # Configuration files
├── includes/               # PHP includes (header, footer, auth)
├── uploads/               # User uploads
├── vendor/                # Dependencies
└── *.php                  # Page files
```

## 🚀 Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/lifeflow-bbms.git
```

2. Import database:
```bash
mysql -u root -p < database/bbms.sql
```

3. Configure database in `config.php`:
```php
$conn = mysqli_connect("localhost", "root", "", "bbms");
```

4. Configure email in `config/mail.php`:
```php
$mailConfig = [
    'host' => 'smtp.gmail.com',
    'username' => 'your-email@gmail.com',
    'password' => 'your-app-password',
];
```

5. Install PHPMailer:
```bash
cd vendor/phpmailer
# Download PHPMailer files
```

6. Start XAMPP and access:
```
http://localhost/BBMS_WebTech/
```

## 📊 Database Schema

```sql
-- Main Tables
users, admins, donor, stock, request, posts, comments, reactions, ratings
```

## 🎯 Requirements Met

- [x] ERD Diagram (create with Visio/EdrawMax)
- [x] Realtime commenting (AJAX)
- [x] Like/Reactions with counts
- [x] Realtime username verification
- [x] Email & Phone verification
- [x] Multiple textbox/dropdown
- [x] Star rating system
- [x] Pagination
- [x] YouTube embed
- [x] Google Maps
- [x] Session management
- [x] TensorFlow.js prediction
- [x] File upload & display
- [x] Datepicker (Flatpickr)
- [x] GSAP animations
- [x] SASS/SCSS
- [x] Git version control

## 👨‍💻 Author

**Forhadul Islam**
- Web Technology Lab Project
- PortCity International University

## 📄 License

This project is licensed under the MIT License.

---

<p align="center">Made with ❤️ for saving lives</p>
