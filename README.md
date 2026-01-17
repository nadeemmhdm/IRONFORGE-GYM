# IronForge Gym Management System

A comprehensive, full-stack Gym Management Website built with PHP and MySQL. This system streamlines member management, fee collection (Monthly/Yearly), workout tracking, and administrative tasks with a premium, dark-mode design.

## 🚀 Key Features

### 🌟 Public Landing Page
- **Modern UI**: Dark-themed, neon-accented responsive design.
- **Dynamic Pricing**: Membership fees are fetched directly from the database settings.
- **Opening Hours**: Clear display of gym timings.

### 🔐 User Portal
- **Registration**: Multi-step form with **Locked QR Code** generation for exact fee payment.
- **Dashboard**: View membership status, fee history, and workout stats.
- **Fee Payments**: 
  - Dynamic QR Code generation for Monthly, Yearly, or Advance payments.
  - Users scan a locked-amount QR code to prevent payment errors.
  - Upload payment screenshots for Admin approval.
- **Workout Tracker**: Log daily exercises (Sets, Reps, Notes).

### 🛡️ Admin Portal
- **Dashboard**: Real-time stats with animated counters (Active Members, Revenue, etc.).
- **User Management**: Approve/Reject registrations, Block/Unblock users, and Archive inactive accounts.
- **Fee Management**:
  - **Payment Approvals**: Review payment screenshots and approve/reject transactions.
  - **Defaulter Detection**: Automatically identify members who haven't paid fees for the current month or year.
- **System Settings**:
  - Configure Registration, Monthly, and Yearly fee amounts.
  - **UPI & QR Settings**: Set up UPI ID, Merchant Name, and Bank details for dynamic QR generation.

### ⚙️ System Automation
- **Auto-Archive**: Automatically archives users who haven't paid fees for 2 months.
- **Smart QR**: Generates UPI QR codes with locked amounts (`&am=XXX`) to ensure correct payments.

## 🛠️ Technology Stack
- **Frontend**: HTML5, CSS3 (Custom Dark Theme), JavaScript
- **Backend**: PHP (Vanilla)
- **Database**: MySQL
- **Gateway**: UPI Static/Dynamic QR Integration

## 📥 Installation

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/nadeemmhdm/IRONFORGE-GYM.git
    ```

2.  **Configure Database**
    *   Create a MySQL database (e.g., `gym_db`).
    *   Edit `db_connect.php`:
        ```php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "gym_db";
        ```

3.  **Run Setup Script**
    *   Open your browser and navigate to: `http://localhost/IRONFORGE-GYM/setup_database.php`
    *   This will automatically create all necessary tables (`users`, `payments`, `settings`, etc.) and the default Admin account.

4.  **Delete Setup Script** (Optional but recommended for security)
    *   Remove `setup_database.php` after successful initialization.

## 🔑 Default Credentials

**Admin Login**
- **URL**: `/admin_login.php`
- **Email**: `admin@gym.com`
- **Password**: `admin123`

## 📸 Usage

1.  **Setup Fees**: Log in as Admin -> Go to **Fee Settings** -> Set your fees and UPI ID.
2.  **User Registration**: Users sign up on the public page, scan the QR to pay the registration fee, and upload proof.
3.  **Approval**: Admin approves the user from the **Dashboard** or **User Management** page.
4.  **Monthly Cycle**: 
    *   Users login and pay monthly fees via the **Payments** page.
    *   Admin monitors **Defaulters** list to track unpaid members.

## 📄 License
This project is open-source and available for educational and commercial use.
