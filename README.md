# # Multi-User News Portal

A simple PHP-based news website with multiple user roles and access levels.

## 👥 User Roles

- **Admin**: Manage users and news.
- **Editor**: Edit and review news.
- **Author**: Add news articles.

## 📂 Main Files

- `index.php` – Homepage  
- `login.php` / `logout.php` – Login system  
- `add_news.php` / `edit_news.php` – News management  
- `admin_dashboard.php`, `editor_dashboard.php`, `author_dashboard.php` – Role-based dashboards  
- `DBConnection.php` – Handles database connection  
- `schema.sql` – Database structure  
- `uploads/` – News attachments and images

## 🧰 Technologies

- PHP
- MySQL
- HTML/CSS

## 🚀 How to Run

1. Import the `schema.sql` file into your database.
2. Set your DB credentials in `DBConnection.php`.
3. Run the project using a local server (e.g., XAMPP or Laragon).

## 📌 Note

This project was built for the *Internet Programming* course assignment.
