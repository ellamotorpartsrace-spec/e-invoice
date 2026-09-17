# 🚀 Hostinger 5-Minute Deployment Guide

This guide shows you how to deploy the **Standalone Shopee E-Invoice System** to your **Hostinger** hosting account.

---

### Step 1: Create a MySQL Database on Hostinger

1. Log in to your **Hostinger hPanel** (`https://hpanel.hostinger.com`).
2. Go to **Databases** > **MySQL Databases**.
3. Create a new database:
   * **Database Name:** e.g., `u123456789_einvoice_db`
   * **Username:** e.g., `u123456789_dbuser`
   * **Password:** Enter a strong password
4. Click **Create**.

---

### Step 2: Import the Database Schema

1. Under your database list on Hostinger, click **Enter phpMyAdmin**.
2. Click on your newly created database name on the left sidebar.
3. Click the **Import** tab at the top.
4. Click **Choose File** and select `database/schema.sql` from this folder.
5. Click **Go** (Import).
   * *You will see 3 tables created: `einv_users`, `einv_settings`, and `einv_invoices`.*

---

### Step 3: Package & Upload to Hostinger

1. On your computer, select all files inside the `e-invoice/` folder.
2. Right-click and choose **Compress to ZIP file** (name it `e-invoice.zip`).
3. In Hostinger hPanel, go to **Files** > **File Manager**.
4. Open the `public_html` directory:
   * **If you want it on a subdomain (e.g. `invoice.yourdomain.com`):**  
     Upload into the subdomain folder.
   * **If you want it on a subfolder (e.g. `yourdomain.com/e-invoice`):**  
     Create a folder named `e-invoice` and upload it there.
5. Click **Upload** > Select `e-invoice.zip`.
6. Right-click `e-invoice.zip` in File Manager and click **Extract**.

---

### Step 4: Verify Database Connection

Open `config/database.php` in Hostinger File Manager to ensure your Hostinger credentials match:

```php
// --- HOSTINGER / CLOUD PRODUCTION ---
$this->host     = "localhost";
$this->dbname   = "u123456789_einvoice_db";         // Your Hostinger DB Name
$this->username = "u123456789_dbuser";              // Your Hostinger DB User
$this->password = "YourStrongDatabasePassword123!"; // Your Hostinger DB Password
```

*Note: Replace the database credentials with the ones you created in Step 1.*

---

### Step 5: Log In and Start Invoicing!

1. Open your browser and navigate to your Hostinger URL:  
   `https://yourdomain.com/e-invoice/` (or your subdomain).
2. You will be greeted by the secure login screen.
3. Default Login Credentials:
   * **Username:** `admin`
   * **Password:** `admin123`
4. Go to **Settings** > **Change Admin Password** to set your own private password.

You are now 100% live on the cloud, completely independent from your in-store POS! 🎉
