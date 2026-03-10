# 🎧 DJ Online Booking & Media Platform

A complete, modern, responsive **DJ Booking and Media Platform** built with PHP, MySQL, and Vanilla JavaScript.

## ✨ Features

### Public Site
- 🎵 **Music Catalog** — Stream songs with a sticky HTML5 player bar
- 📹 **YouTube Videos** — Embedded video grid with featured filter
- 📅 **Online Booking Form** — Real-time validation, AJAX submission, email confirmation
- 👤 **DJ Profile** — About page with social links and "Why Book Me" section

### Admin Panel
- 📊 **Dashboard** — Stat cards and recent activity tables
- 📋 **Booking Management** — Filter, search, approve/reject bookings
- 🎵 **Music Management** — Upload MP3/WAV, manage categories
- 📹 **Video Management** — Add YouTube videos by URL
- 👤 **Profile Editor** — Edit DJ profile including photo upload
- 💬 **Messages Inbox** — View visitor inquiries

### Security
- PDO prepared statements, CSRF tokens, password hashing, file MIME validation
- Brute-force login protection, booking rate limiting, XSS prevention

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, CSS3, Vanilla JavaScript (ES6+) |
| Backend | PHP 8+ |
| Database | MySQL (PDO) |
| Fonts | Google Fonts (Poppins + Inter) |
| Icons | Font Awesome 6 |

## 🚀 Quick Setup

1. Clone repo and place in web server root
2. Import `database/schema.sql`
3. Edit `config/database.php` with your DB credentials
4. Edit `config/config.php` with your site URL and settings
5. Set `uploads/` directory to writable (`chmod 755`)

## 🔐 Default Admin Credentials

| Field | Value |
|-------|-------|
| Username | `admin` |
| Password | `Admin@1234` |
| URL | `/login.php` |

> ⚠️ Change the password after first login!

## 📄 License

MIT License
