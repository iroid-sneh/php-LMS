# PHP Leave Management System

A comprehensive leave management system built with PHP backend and HTML/CSS/Bootstrap frontend with MySQL database. This is a complete solution for office employees to manage leave requests with HR admin approval workflow.

## Features

### Employee Features

-   **User Registration & Login**: Employees can register and login to the system
-   **Apply for Leave**: Submit leave requests with detailed information
-   **View Leave History**: See all past and current leave applications
-   **Dashboard**: View personal statistics and today's office leave status
-   **Profile Management**: View personal information and employment details

### HR Admin Features

-   **Admin Login**: HR personnel can login with admin privileges
-   **Manage Leave Requests**: View all employee leave requests
-   **Approve/Reject Leaves**: Approve or reject leave requests with comments
-   **Employee Overview**: See all employees and their leave statistics
-   **Today's Leave Status**: View which employees are on leave today

### System Features

-   **Real-time Status Updates**: Leave status changes are reflected immediately
-   **Responsive Design**: Works on desktop and mobile devices
-   **Secure Authentication**: JWT-based authentication system
-   **Data Validation**: Comprehensive form validation on both frontend and backend
-   **Clean UI**: Simple, professional design with Bootstrap styling

## Tech Stack

### Backend

-   **PHP 7.4+** - Server-side scripting
-   **MySQL** - Database
-   **PDO** - Database abstraction layer
-   **JWT** - Authentication
-   **Password Hashing** - Secure password storage

### Frontend

-   **HTML5** - Markup language
-   **CSS3** - Styling
-   **Bootstrap 5** - UI framework
-   **JavaScript (ES6+)** - Client-side scripting
-   **Font Awesome** - Icons

## Installation & Setup

### Prerequisites

-   PHP 7.4 or higher
-   MySQL 5.7 or higher
-   Apache/Nginx web server
-   Composer (optional, for dependency management)

### Backend Setup

1. **Navigate to the backend directory:**

    ```bash
    cd php-LMS/backend
    ```

2. **Create a `.env` file:**

    ```bash
    cp env.example .env
    ```

3. **Configure your `.env` file:**

    ```env
    APP_NAME="Leave Management System"
    APP_ENV=local
    APP_KEY=base64:your-secret-key-here
    APP_DEBUG=true
    APP_URL=http://localhost

    DB_CONNECTION=mysql
    DB_HOST=localhost
    DB_PORT=3306
    DB_DATABASE=lms_db
    DB_USERNAME=root
    DB_PASSWORD=your_password

    JWT_SECRET=your-jwt-secret-key-here
    JWT_ALGO=HS256
    JWT_EXPIRY=604800
    ```

4. **Create the database:**

    ```sql
    CREATE DATABASE lms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    ```

5. **Import the database schema:**

    ```bash
    mysql -u root -p lms_db < database/schema.sql
    ```

6. **Configure your web server:**

    **For Apache:**

    - Ensure mod_rewrite is enabled
    - Point document root to `php-LMS/backend/public` (if using public folder) or `php-LMS/backend`

    **For Nginx:**

    ```nginx
    server {
        listen 80;
        server_name localhost;
        root /path/to/php-LMS/backend;
        index index.php;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }
    ```

### Frontend Setup

1. **Navigate to the frontend directory:**

    ```bash
    cd php-LMS/frontend
    ```

2. **Configure the API URL:**

    - Open `js/auth.js`
    - Update the `API_BASE_URL` constant to match your backend URL:

    ```javascript
    const API_BASE_URL = "http://localhost/php-LMS/backend";
    ```

3. **Serve the frontend:**
    - You can use any web server (Apache, Nginx, or even PHP's built-in server)
    - For PHP built-in server: `php -S localhost:3000`

## Usage

### For Employees

1. **Register**: Create an account with your employee details
2. **Login**: Access your dashboard
3. **Apply Leave**: Click "Apply for Leave" to submit a new request
4. **View Status**: Check "My Leaves" to see the status of your applications
5. **Dashboard**: View your leave statistics and see who's on leave today

### For HR Admins

1. **Register as HR**: Create an account with role set to "HR Admin"
2. **Login**: Access the admin dashboard
3. **Review Requests**: See all pending leave requests
4. **Approve/Reject**: Click approve or reject with optional comments
5. **Monitor**: View overall statistics and today's leave status

## API Endpoints

### Authentication

-   `POST /api/auth/register` - Register new user
-   `POST /api/auth/login` - Login user
-   `GET /api/auth/me` - Get current user

### Leaves

-   `POST /api/leaves` - Apply for leave
-   `GET /api/leaves/my-leaves` - Get user's leaves
-   `GET /api/leaves/all` - Get all leaves (HR only)
-   `GET /api/leaves/today` - Get today's leaves
-   `PUT /api/leaves/:id/approve` - Approve leave (HR only)
-   `PUT /api/leaves/:id/reject` - Reject leave (HR only)

### Users

-   `GET /api/users/employees` - Get all employees (HR only)
-   `GET /api/users/stats` - Get user statistics
-   `GET /api/users/admin-stats` - Get admin statistics (HR only)

## Database Schema

### Users Table

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('employee', 'hr') DEFAULT 'employee',
    department VARCHAR(255) NOT NULL,
    position VARCHAR(255) NOT NULL,
    employee_id VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    joining_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Leaves Table

```sql
CREATE TABLE leaves (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    leave_type ENUM('sick', 'vacation', 'personal', 'emergency', 'other') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    duration DECIMAL(5,2) NOT NULL,
    duration_unit ENUM('hours', 'days') DEFAULT 'days',
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_comment TEXT,
    rejected_reason TEXT,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);
```

## Project Structure

```
php-LMS/
├── backend/
│   ├── config/
│   │   ├── Database.php
│   │   ├── app.php
│   │   ├── database.php
│   │   └── helpers.php
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── LeaveController.php
│   │   └── UserController.php
│   ├── middleware/
│   │   └── AuthMiddleware.php
│   ├── models/
│   │   ├── User.php
│   │   └── Leave.php
│   ├── routes/
│   │   └── api.php
│   ├── database/
│   │   └── schema.sql
│   ├── .htaccess
│   ├── index.php
│   └── env.example
├── frontend/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   ├── auth.js
│   │   └── api.js
│   ├── index.html
│   ├── login.html
│   ├── admin-login.html
│   ├── register.html
│   ├── dashboard.html
│   ├── admin-dashboard.html
│   ├── apply-leave.html
│   └── my-leaves.html
└── README.md
```

## Default Credentials

The system comes with sample data:

**HR Admin:**

-   Email: admin@company.com
-   Password: password

**Employee:**

-   Email: john@company.com
-   Password: password

## Security Features

-   Password hashing using PHP's `password_hash()`
-   JWT token-based authentication
-   SQL injection prevention with prepared statements
-   XSS protection with input sanitization
-   CORS headers for API security
-   Input validation on both client and server side

## Browser Support

-   Chrome 60+
-   Firefox 55+
-   Safari 12+
-   Edge 79+

## Troubleshooting

### Common Issues

1. **Database Connection Error:**

    - Check your database credentials in `.env`
    - Ensure MySQL service is running
    - Verify database exists

2. **API Calls Failing:**

    - Check if backend is running
    - Verify API_BASE_URL in frontend JavaScript
    - Check browser console for CORS errors

3. **Authentication Issues:**

    - Clear browser localStorage
    - Check JWT_SECRET in backend configuration
    - Verify token expiration settings

4. **File Permissions:**
    - Ensure web server has read access to all files
    - Check .htaccess is working (for Apache)

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open source and available under the MIT License.

## Support

For support and questions, please create an issue in the repository or contact the development team.
