from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import Select
from webdriver_manager.chrome import ChromeDriverManager
import time

# ─────────────────────────────────────────
# CONFIG
# ─────────────────────────────────────────
BASE_URL    = "http://localhost/playmatrix"
TEST_EMAIL  = "dinojacob2028@mca.ajce.in"
TEST_PASS   = "dino123"
WAIT        = 6   # seconds for WebDriverWait

# ─────────────────────────────────────────
# HELPER
# ─────────────────────────────────────────
def setup_driver():
    opts = Options()
    # opts.add_argument("--headless")   # uncomment to run headlessly
    opts.add_argument("--start-maximized")
    driver = webdriver.Chrome(
        service=Service(ChromeDriverManager().install()),
        options=opts
    )
    return driver

def login(driver):
    """Login helper reused by every test."""
    driver.get(f"{BASE_URL}/login.php")
    wait = WebDriverWait(driver, WAIT)
    wait.until(EC.presence_of_element_located((By.NAME, "email"))).send_keys(TEST_EMAIL)
    driver.find_element(By.NAME, "password").send_keys(TEST_PASS)
    driver.find_element(By.CLASS_NAME, "btn-primary").click()
    wait.until(EC.url_contains("dashboard.php"))
    print("  [✓] Logged in successfully")

def result(label, passed, detail=""):
    icon = "[PASS]" if passed else "[FAIL]"
    print(f"  {icon} - {label}" + (f": {detail}" if detail else ""))

# ─────────────────────────────────────────
# TEST 1: BOOK VENUE
# Flow: login → Book Venue (2.php) → click first venue card
#       → venue detail (3.php) → click "Book Now" → reach booking page (4.php)
# ─────────────────────────────────────────
def test_book_venue():
    print("\n══════════════════════════════════════════")
    print("  TEST 1: Book Venue")
    print("══════════════════════════════════════════")
    driver = setup_driver()
    wait   = WebDriverWait(driver, WAIT)
    try:
        login(driver)

        # 1. Navigate to Book Venue page
        driver.get(f"{BASE_URL}/2.php")
        wait.until(EC.url_contains("2.php"))
        result("Navigated to Book Venue page (2.php)", True)
        time.sleep(2)   # let venue cards render

        # 2. Click the first venue card
        venue_cards = driver.find_elements(By.CLASS_NAME, "venue-card")
        if not venue_cards:
            result("Found venue cards", False, "No venue cards found on the page")
            return
        result(f"Found {len(venue_cards)} venue card(s)", True)
        venue_cards[0].click()

        # 3. Should land on venue detail page (3.php)
        wait.until(EC.url_contains("3.php"))
        result("Navigated to Venue Detail page (3.php)", True)
        time.sleep(2)

        # 4. Click "Book Now" button
        book_btn = wait.until(EC.element_to_be_clickable((By.XPATH, "//button[contains(text(),'Book Now')]")))
        driver.execute_script("arguments[0].click();", book_btn)

        # 5. Should land on booking/checkout page (4.php)
        wait.until(EC.url_contains("4.php"))
        result("Navigated to Booking page (4.php) — Book Now works!", True)
        time.sleep(2)

        # 6. Verify the sport select and date input exist
        sport_select = wait.until(EC.presence_of_element_located((By.ID, "sportSelect")))
        date_input   = driver.find_element(By.ID, "dateInput")
        result("Booking form elements (Sport, Date) are present", bool(sport_select and date_input))

        # 7. Click "Add To Cart"
        add_cart_btn = driver.find_element(By.CLASS_NAME, "add-to-cart-btn")
        add_cart_btn.click()
        time.sleep(1)

        # 8. Cart should now show (filledCart becomes visible)
        filled_cart = driver.find_element(By.ID, "filledCart")
        cart_visible = filled_cart.is_displayed()
        result("Cart populated after clicking 'Add To Cart'", cart_visible)

    except Exception as e:
        result("Test 1 encountered an error", False, str(e))
    finally:
        time.sleep(3)
        driver.quit()
        print("  Browser closed.\n")


# ─────────────────────────────────────────
# TEST 2: APPLY TO BECOME A TRAINER
# Flow: login → dashboard → coaching section → Apply Now modal opens
#       → fill form → submit
# ─────────────────────────────────────────
def test_apply_trainer():
    print("\n══════════════════════════════════════════")
    print("  TEST 2: Apply to Become a Trainer")
    print("══════════════════════════════════════════")
    driver = setup_driver()
    wait   = WebDriverWait(driver, WAIT)
    try:
        login(driver)

        # 1. Navigate to dashboard coaching section via URL param
        driver.get(f"{BASE_URL}/dashboard.php?show=coaching")
        wait.until(EC.url_contains("dashboard.php"))
        time.sleep(2)
        result("Navigated to Coaching section of dashboard", True)

        # 2. Check if we have already applied
        apply_btn = wait.until(EC.presence_of_element_located((By.ID, "becomeTrainerBtn")))
        btn_text = apply_btn.text.strip().lower()

        if "under review" in btn_text:
            result("Application is already pending ('Under Review'). Skipping submission.", True)
            return
        elif "rejected" in btn_text:
            result("Previous application was rejected. Re-applying...", True)

        # Click "Apply Now" button inside the Become a Trainer card
        # We wait for clickability in case it's enabled
        apply_btn = wait.until(EC.element_to_be_clickable((By.ID, "becomeTrainerBtn")))
        driver.execute_script("arguments[0].click();", apply_btn)
        result("Clicked 'Apply Now' button", True)
        time.sleep(1)

        # 3. Trainer modal should now be visible
        modal = wait.until(EC.visibility_of_element_located((By.ID, "trainerModal")))
        result("Trainer Application modal is visible", modal.is_displayed())

        # 4. Fill in the form
        # Full name is pre-filled; fill specialization and experience
        spec_select = Select(wait.until(EC.presence_of_element_located(
            (By.CSS_SELECTOR, "#trainerModal select[name='specialization']"))))
        spec_select.select_by_value("Football")
        result("Selected specialization: Football", True)

        exp_input = driver.find_element(By.CSS_SELECTOR, "#trainerModal input[name='experience']")
        exp_input.clear()
        exp_input.send_keys("3")
        result("Entered experience: 3 years", True)

        # 5. Upload a dummy certificate file via the file input
        import os
        dummy_path = os.path.join(os.getcwd(), "elis.txt")   # reuse existing small file
        if os.path.exists(dummy_path):
            file_input = driver.find_element(By.CSS_SELECTOR, "#trainerModal input[name='certificate']")
            file_input.send_keys(dummy_path)
            result("Uploaded dummy certificate file", True)
        else:
            result("Skipped file upload (test file not found)", False, "elis.txt missing")

        # 6. Submit the form
        submit_btn = driver.find_element(By.CSS_SELECTOR, "#trainerForm button[type='submit']")
        driver.execute_script("arguments[0].click();", submit_btn)
        time.sleep(2)

        # 7. Check for success or error message
        msg_el = driver.find_element(By.ID, "trainerMsg")
        msg_text = msg_el.text.strip()
        if msg_text:
            passed = "error" not in msg_text.lower() and msg_text != ""
            result(f"Submission response received", passed, f'"{msg_text}"')
        else:
            result("Submission response", False, "No message returned by server")

    except Exception as e:
        result("Test 2 encountered an error", False, str(e))
    finally:
        time.sleep(3)
        driver.quit()
        print("  Browser closed.\n")


# ─────────────────────────────────────────
# TEST 3: WALLET — ADD FUNDS BUTTON VISIBLE
# Flow: login → dashboard → membership section → wallet card visible
#       → "Add Funds" button is present and clickable
# ─────────────────────────────────────────
def test_wallet():
    print("\n══════════════════════════════════════════")
    print("  TEST 3: Wallet / Add Funds")
    print("══════════════════════════════════════════")
    driver = setup_driver()
    wait   = WebDriverWait(driver, WAIT)
    try:
        login(driver)

        # 1. Navigate to membership section
        driver.get(f"{BASE_URL}/dashboard.php?show=membership")
        wait.until(EC.url_contains("dashboard.php"))
        time.sleep(2)
        result("Navigated to Membership section", True)

        # 2. Scroll membership section into view
        membership_div = wait.until(EC.presence_of_element_located((By.ID, "membership")))
        driver.execute_script("arguments[0].scrollIntoView(true);", membership_div)
        time.sleep(1)

        # 3. Wallet balance is displayed
        wallet_el = wait.until(EC.presence_of_element_located((By.ID, "walletBalanceMembership")))
        balance_text = wallet_el.text.strip()
        result("Wallet balance is displayed", bool(balance_text), balance_text)

        # 4. "Add Funds" button is present
        add_funds_btn = driver.find_element(By.XPATH,
            "//div[@id='membership']//button[contains(text(),'Add Funds')]")
        result("'Add Funds' button is present", add_funds_btn.is_displayed())

        # 5. Click "Add Funds" button
        driver.execute_script("arguments[0].click();", add_funds_btn)
        time.sleep(1)

        # 6. Overview wallet balance is also shown on the main card
        driver.get(f"{BASE_URL}/dashboard.php")
        wait.until(EC.presence_of_element_located((By.ID, "walletBalanceOverview")))
        overview_wallet = driver.find_element(By.ID, "walletBalanceOverview").text.strip()
        result("Wallet balance shown in Overview section", bool(overview_wallet), overview_wallet)

    except Exception as e:
        result("Test 3 encountered an error", False, str(e))
    finally:
        time.sleep(3)
        driver.quit()
        print("  Browser closed.\n")


# ─────────────────────────────────────────
# ENTRY POINT
# ─────────────────────────────────────────
if __name__ == "__main__":
    print("\n------------------------------------------")
    print("   PlayMatrix - Selenium Test Suite       ")
    print("   User: dinojacob2028@mca.ajce.in        ")
    print("------------------------------------------")

    test_book_venue()
    test_apply_trainer()
    test_wallet()

    print("\n------------------------------------------")
    print("           All Tests Completed            ")
    print("------------------------------------------\n")
