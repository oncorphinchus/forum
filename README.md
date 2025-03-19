# PHP Forum System

A modern, secure, and feature-rich forum system built with PHP and MySQL. This system includes user authentication, topic management, commenting, and administrative features.

## Features

- User Authentication System
  - Secure registration and login
  - Password hashing
  - Session management
  - CSRF protection
  - Remember me functionality

- Forum Features
  - Categories and topics
  - Comments and discussions
  - User profiles with avatars
  - Topic views tracking
  - Rich text formatting
  - File attachments

- Administrative Features
  - User management
  - Category management
  - Content moderation
  - Statistics dashboard
  - System settings

- Security Features
  - SQL injection prevention
  - XSS protection
  - CSRF protection
  - Input validation
  - Secure password storage

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP (or similar web server package)
- Modern web browser

## Installation

1. Clone or download this repository to your XAMPP's htdocs directory:
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/
   git clone [repository-url] forum
   ```

2. Create a MySQL database named 'login_system' (or update the name in config.php)

3. Import the database schema:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named 'login_system'
   - Import the database.sql file

4. Configure the database connection:
   - Open `config.php`
   - Update the database credentials if needed:
     ```php
     $db_host = 'localhost';
     $db_user = 'root';
     $db_pass = '';
     $db_name = 'login_system';
     ```

5. Set up the file permissions:
   ```bash
   chmod 755 /Applications/XAMPP/xamppfiles/htdocs/forum
   chmod 755 /Applications/XAMPP/xamppfiles/htdocs/forum/uploads
   ```

6. Access the forum:
   - Open your web browser
   - Navigate to http://localhost/forum
   - Register a new account or log in with the default admin account:
     - Username: admin
     - Password: admin123

## Directory Structure

```
forum/
├── admin/                 # Administrative interface
│   ├── index.php         # Admin dashboard
│   ├── categories.php    # Category management
│   ├── users.php         # User management
│   └── settings.php      # System settings
├── css/                  # Stylesheets
│   └── style.css        # Custom styles
├── includes/             # PHP includes
│   ├── header.php       # Common header
│   ├── footer.php       # Common footer
│   └── functions.php    # Helper functions
├── uploads/             # User uploads
│   └── avatars/        # User avatar images
├── config.php           # Configuration file
├── index.php           # Forum homepage
├── login.php           # Login page
├── register.php        # Registration page
├── profile.php         # User profile
├── topic.php           # Topic view
└── category.php        # Category view
```

## Security Considerations

1. Always keep PHP and MySQL updated to the latest stable versions
2. Use HTTPS in production
3. Regularly backup your database
4. Monitor error logs
5. Implement rate limiting for login attempts
6. Keep file upload directory outside web root if possible
7. Regularly review user permissions and roles

## Customization

1. Styling
   - Modify css/style.css for custom styling
   - Update Bootstrap theme in header.php

2. Features
   - Add new features by creating new PHP files
   - Extend existing functionality in includes/functions.php

3. Configuration
   - Adjust settings in config.php
   - Modify database schema in database.sql

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please:
1. Check the documentation
2. Search for existing issues
3. Create a new issue if needed

## Credits

- Bootstrap 4.5.2
- Font Awesome 5.15.4
- jQuery 3.5.1 