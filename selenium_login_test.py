from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from webdriver_manager.chrome import ChromeDriverManager
import time

def test_login():
    # Configure Chrome options
    chrome_options = Options()
    # chrome_options.add_argument("--headless")  # Uncomment for headless mode
    
    # Use Service with ChromeDriverManager for automatic setup
    service = Service(ChromeDriverManager().install())
    driver = webdriver.Chrome(service=service, options=chrome_options)
    
    try:
        # 1. Open the login page
        # Replace 'http://localhost/playmatrix/login.php' with your actual local URL
        base_url = "http://localhost/playmatrix/login.php"
        driver.get(base_url)
        print(f"Navigated to: {base_url}")
        
        # 2. Find the email and password fields
        email_field = driver.find_element(By.NAME, "email")
        password_field = driver.find_element(By.NAME, "password")
        
        # 3. Enter credentials
        # Replace with a valid test user from your database/users.json
        test_email = "dinojacob2028@mca.ajce.in"
        test_password = "dino123" 
        
        email_field.send_keys(test_email)
        password_field.send_keys(test_password)
        print("Entered credentials.")
        
        # 4. Click the sign-in button
        # Using the class name from login.php: btn-primary
        submit_button = driver.find_element(By.CLASS_NAME, "btn-primary")
        submit_button.click()
        print("Clicked Sign In.")
        
        # 5. Wait for redirect or check for error
        time.sleep(3) # Simple wait to see the result
        
        current_url = driver.current_url
        print(f"Current URL after login attempt: {current_url}")
        
        if "dashboard.php" in current_url or "admin.php" in current_url:
            print("SUCCESS: Login successful and redirected.")
        else:
            # Check if an error message is displayed
            try:
                error_msg = driver.find_element(By.XPATH, "//div[contains(@style, 'color: #ff4444')]")
                print(f"FAILED: Login failed. Error: {error_msg.text}")
            except:
                print("FAILED: Login failed, but no specific error message found.")

    except Exception as e:
        print(f"An error occurred during the test: {e}")
    finally:
        # Keep the browser open for a few seconds before closing
        time.sleep(5)
        driver.quit()
        print("Browser closed.")

if __name__ == "__main__":
    test_login()
