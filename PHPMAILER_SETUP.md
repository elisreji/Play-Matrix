# PHPMailer Installation Instructions

## Quick Setup (5 minutes):

### Step 1: Download PHPMailer
1. Go to: https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip
2. Download the ZIP file
3. Extract it

### Step 2: Copy Files
1. From the extracted folder, go to `PHPMailer-master/src/`
2. Copy these 3 files to `c:\xamp test\htdocs\playmatrix\PHPMailer\`:
   - PHPMailer.php
   - SMTP.php
   - Exception.php

### Step 3: Test
1. Go to http://localhost/playmatrix/forgot-password.php
2. Enter your email: elisreji2028@mca.ajce.in
3. Click "Send Reset Link"
4. Check your email inbox!

---

## Alternative: Use the file-based system
If you don't want to set up PHPMailer right now, the current system saves the reset link to `email_outbox.txt` in your project folder. You can copy the link from there and test the password reset flow.
