# 🎮 NexusReview — Gaming Hub Review Platform

A secure, full-stack gaming review platform with Role-Based Access Control (RBAC), parameterized queries, and token rotation to prevent SQL injection and session hijacking.

## 📋 Features

- **🔐 Secure Authentication** — Bcrypt password hashing, session regeneration, parameterized queries
- **👥 Role-Based Access Control** — Separate admin and user flows
- **🎮 Game Reviews & Ratings** — Submit and browse community reviews
- **📰 Gaming News** — Latest industry news and updates
- **📊 Top Charts & Trending** — Dynamic ranking and trending games
- **🔍 Advanced Search** — Real-time game search with filters
- **📱 Responsive Design** — Works seamlessly on desktop, tablet, and mobile
- **🎨 Dark Theme UI** — Modern gaming-focused aesthetic

## 🛠️ Tech Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Architecture:** MVC Pattern with API endpoints

## 📦 Prerequisites

Before you begin, ensure you have the following installed:

1. **XAMPP** (includes Apache, PHP, MySQL)
   - Download: [Apache Friends XAMPP](https://www.apachefriends.org/)
   - Recommended version: 8.0 or higher

2. **MySQL Workbench** (optional but recommended for database management)
   - Download: [MySQL Workbench](https://www.mysql.com/products/workbench/)

3. **Git** (for cloning the repository)
   - Download: [Git](https://git-scm.com/)

## 🚀 Installation & Setup

### Step 1: Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Start **Apache** and **MySQL** services
3. Verify both are running (green indicators)

### Step 2: Create Database

1. Open **phpMyAdmin** in your browser: `http://localhost/phpmyadmin`
2. Create a new database named `nexusreview`
3. Select the `nexusreview` database
4. Import the database schema (if `setup.php` is available):
   - Navigate to `http://localhost/nexusreview/setup.php` in your browser
   - Or manually create tables using the provided SQL schema

### Step 3: Clone the Repository

```bash
# Navigate to XAMPP's htdocs directory
cd C:\xampp\htdocs  # Windows
# or
cd /opt/lampp/htdocs  # Linux
# or
cd /Applications/XAMPP/htdocs  # macOS

# Clone the repository
git clone https://github.com/YoussefShaher/Game-Hub-Review-Platform.git
cd Game-Hub-Review-Platform
```

### Step 4: Configure Database Connection

Edit `config/db.php` and verify the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Default XAMPP user
define('DB_PASS', '');          // Default XAMPP password (empty)
define('DB_NAME', 'nexusreview');
```

> **Note:** The default XAMPP MySQL user is `root` with no password. Change these in production.

### Step 5: Access the Application

Open your browser and navigate to:

```
http://localhost/Game-Hub-Review-Platform/
```

Or if you renamed the folder:

```
http://localhost/nexusreview/
```

## 📁 Project Structure

```
Game-Hub-Review-Platform/
├── index.php              # Main landing page
├── news.php              # News hub page
├── config/
│   └── db.php            # Database connection configuration
├── api/
│   ├── auth.php          # Authentication (register, login, logout)
│   ├── games.php         # Games API (fetch, filter)
│   ├── reviews.php       # Reviews API (fetch, submit)
│   └── news.php          # News API (fetch articles)
├── css/
│   ├── style.css         # Main stylesheet
│   └── news.css          # News page styles
├── js/
│   ├── main.js           # Core JavaScript functionality
│   └── news.js           # News page interactivity
├── images/               # Game and news images
└── setup.php             # Database initialization script (if available)
```

## 🗄️ Database Schema

### Users Table
```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') DEFAULT 'user',
  avatar VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Games Table
```sql
CREATE TABLE games (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(100) NOT NULL,
  genre VARCHAR(50),
  developer VARCHAR(100),
  cover_image VARCHAR(255),
  icon VARCHAR(50),
  bg_class VARCHAR(50),
  release_date DATE,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Reviews Table
```sql
CREATE TABLE reviews (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  game_id INT NOT NULL,
  score INT CHECK (score BETWEEN 1 AND 100),
  body TEXT,
  platform VARCHAR(50),
  helpful INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (game_id) REFERENCES games(id)
);
```

### News Table
```sql
CREATE TABLE news (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(200) UNIQUE NOT NULL,
  category VARCHAR(50),
  excerpt TEXT,
  body TEXT,
  image VARCHAR(255),
  emoji VARCHAR(10),
  bg_class VARCHAR(50),
  author_name VARCHAR(100),
  read_time VARCHAR(20),
  published BOOLEAN DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Consoles Table
```sql
CREATE TABLE consoles (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) UNIQUE NOT NULL
);
```

### Game_Consoles Table (Junction)
```sql
CREATE TABLE game_consoles (
  game_id INT NOT NULL,
  console_id INT NOT NULL,
  PRIMARY KEY (game_id, console_id),
  FOREIGN KEY (game_id) REFERENCES games(id),
  FOREIGN KEY (console_id) REFERENCES consoles(id)
);
```

## 🔒 Security Features

- **Password Hashing:** Bcrypt algorithm (PASSWORD_BCRYPT)
- **SQL Injection Prevention:** Prepared statements with parameterized queries
- **Session Security:** Session ID regeneration after login
- **CSRF Protection:** Implement tokens in production
- **Input Validation:** Email validation and password length requirements
- **Output Encoding:** HTML special characters escaping

## 📝 API Endpoints

### Authentication
```
POST /api/auth.php?action=register
POST /api/auth.php?action=login
POST /api/auth.php?action=logout
```

### Games
```
GET /api/games.php?id=1                    # Get single game
GET /api/games.php?genre=Action            # Filter by genre
GET /api/games.php?console=PS5             # Filter by console
```

### Reviews
```
GET /api/reviews.php?game_id=1             # Get reviews for a game
GET /api/reviews.php                       # Get latest reviews (global feed)
POST /api/reviews.php                      # Submit a new review
```

### News
```
GET /api/news.php?slug=article-slug        # Get single article
GET /api/news.php?category=Industry        # Filter by category
GET /api/news.php                          # Get all articles
```

## 🎨 Design Features

- **Color Scheme:**
  - Primary: `#e8ff3a` (Electric Yellow)
  - Secondary: `#ff3a6e` (Pink)
  - Tertiary: `#3af0ff` (Cyan)
  - Background: `#0a0a0f` (Dark)

- **Fonts:**
  - Display: Bebas Neue
  - Body: Barlow
  - Condensed: Barlow Condensed

## 🐛 Troubleshooting

### Issue: "Database connection failed"
- **Solution:** Verify MySQL is running in XAMPP Control Panel
- Check database credentials in `config/db.php`
- Ensure `nexusreview` database exists in phpMyAdmin

### Issue: "404 Page not found"
- **Solution:** Check your folder path in XAMPP htdocs
- Verify the URL matches your folder structure
- Clear browser cache (Ctrl+Shift+Del)

### Issue: "Blank page or JavaScript errors"
- **Solution:** Open browser console (F12) to check for errors
- Verify all CSS and JS files are loading (check Network tab)
- Check PHP error logs in XAMPP

### Issue: "Images not loading"
- **Solution:** Verify image paths in `images/` folder
- Check file permissions (should be readable)
- Use relative paths instead of absolute paths

## 📱 Responsive Breakpoints

- **Desktop:** 1400px and above
- **Tablet:** 900px to 1399px
- **Mobile:** Below 900px

## 🚀 Deployment Guide

For production deployment:

1. **Security:**
   - Change default database credentials
   - Implement HTTPS/SSL
   - Add CSRF tokens
   - Set strong session timeouts

2. **Performance:**
   - Enable output caching
   - Minify CSS and JavaScript
   - Optimize images
   - Use a CDN for assets

3. **Hosting:**
   - Use a managed hosting service (Bluehost, SiteGround, etc.)
   - Configure environment variables
   - Set up automatic backups
   - Enable error logging

## 📖 Usage Examples

### Register a New User
```javascript
fetch('/api/auth.php?action=register', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    username: 'GamerTag_123',
    email: 'user@example.com',
    password: 'SecurePass123'
  })
});
```

### Submit a Review
```javascript
fetch('/api/reviews.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    game_id: 1,
    user_id: 5,
    score: 85,
    body: 'Amazing game!',
    platform: 'PS5'
  })
});
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👤 Author

**Youssef Shaher**
- GitHub: [@YoussefShaher](https://github.com/YoussefShaher)
- Repository: [Game-Hub-Review-Platform](https://github.com/YoussefShaher/Game-Hub-Review-Platform)

## 📞 Support

For issues, questions, or suggestions:
- Open an [Issue](https://github.com/YoussefShaher/Game-Hub-Review-Platform/issues)
- Check existing documentation
- Review the API endpoints section

## 🎯 Roadmap

- [ ] Admin dashboard for content management
- [ ] User profiles and follow system
- [ ] Advanced filtering and search algorithms
- [ ] Social sharing features
- [ ] Push notifications
- [ ] Mobile app (React Native)
- [ ] Multiplayer gaming features
- [ ] User badges and achievements system

---

**Made with ❤️ for gamers, by gamers**
