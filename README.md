# Complaint & Request Management System

A complete, professional Complaint and Request Management System built with HTML, CSS, JavaScript, Core PHP (8+), and MySQL without any frameworks.

## 1. Project Structure

The project has been organized professionally:
- `/assets` - Contains CSS (style.css), JS (script.js) and images.
- `/config` - Contains database connection logic.
- `/includes` - Shared components (header, footer, navbar, sidebar, auth/utility functions).
- `/auth` - Login, registration, logout logic.
- `/user` - User dashboard and submission logic.
- `/staff` - Staff dashboard and assignment management.
- `/admin` - Admin dashboard, user/staff management, category management.
- `/uploads` - Attached images/documents for complaints and requests.
- `/database` - Database schema and seed data.

## 2. Setup Instructions (XAMPP / Apache)

1. **Move Project**: Ensure the `complaint-management` folder is placed inside your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\complaint-management\`).
2. **Start Server**: Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. **Database Import**:
   - Go to `http://localhost/phpmyadmin/`
   - Click "New" on the left sidebar to create a database named `complaint_management_system`.
   - Select the newly created database, go to the "Import" tab.
   - Choose the file `complaint-management/database/database.sql` and click "Import" (or "Go").
4. **Update Passwords**:
   - For security, passwords in the database need to be properly hashed by your server.
   - Open your browser and navigate to: `http://localhost/complaint-management/setup_passwords.php`
   - You should see a success message. (You can delete `setup_passwords.php` afterward).
5. **Launch Application**:
   - Open your browser and visit: `http://localhost/complaint-management/`

## 3. Default Login Credentials

**Admin User:**
- Email: `admin@gmail.com`
- Password: `Admin@123`

**Staff Member:**
- Email: `staff@gmail.com`
- Password: `Staff@123`

**Normal User:**
- Email: `user@gmail.com`
- Password: `User@123`

## 4. Features Implemented

* **Secure Authentication**: Registration, Login, Logout, with password hashing, and role-based protection.
* **Dashboards**: Separate, customized dashboards for Users, Staff, and Admins.
* **Complaint & Request Modules**: Detailed forms with file attachment support (up to 5MB, JPG/PNG/PDF/DOC) and priority setting.
* **Assignment System**: Admin can assign complaints/requests to staff. Status is tracked automatically.
* **Timeline & Status Tracking**: Visual history of status changes and remarks.
* **Communication Chat/Comments**: Two-way communication between users and staff/admin.
* **Notification System**: Users receive notifications when status updates or items are assigned.
* **Feedback Mechanism**: Users can provide 1-5 star ratings and comments once a ticket is resolved to close it.
* **Charts**: Visual data charts (using Chart.js) for the administration.
* **Responsive UI**: Custom CSS using CSS Grid/Flexbox without frameworks like Bootstrap. It includes a modern sidebar and a clean color scheme.
* **Security Measures**: SQL injection protection (PDO prepared statements), XSS prevention (`htmlspecialchars`), robust file upload validation, and direct access prevention.

## 5. Testing Instructions

1. **User Flow**: Register a new user account -> Login -> Submit a Complaint -> Upload a sample image.
2. **Admin Flow**: Login as admin -> View new complaint -> Go to "All Complaints" -> Assign it to "Staff Member". Check charts in Dashboard.
3. **Staff Flow**: Login as staff -> Check "Assigned Complaints" -> Update status to "In Progress" -> Leave a remark -> Go to Chat and send a message.
4. **User Follow-up**: Login as user -> Check notification bell -> View timeline and reply to staff -> Wait for staff to mark as "Resolved" -> Provide a 5-star rating to close the ticket.
