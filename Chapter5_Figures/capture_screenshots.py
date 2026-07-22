"""Capture Chapter 5 screenshots from the running QR PC Check system."""
from pathlib import Path
from playwright.sync_api import sync_playwright

OUT = Path(__file__).resolve().parent / "Chapter5_Figures"
OUT.mkdir(parents=True, exist_ok=True)
BASE = "http://localhost/QR"

PAGES = [
    ("Figure10_Home_Page.png", f"{BASE}/index.php", None),
    ("Figure11_Login_Page.png", f"{BASE}/login.php", None),
]


def shot(page, name, full=True):
    path = OUT / name
    page.screenshot(path=str(path), full_page=full)
    print("Saved", path.name)


def login(page, email, password, user_type):
    page.goto(f"{BASE}/login.php", wait_until="networkidle")
    page.select_option('select[name="user_type"]', user_type)
    page.fill('input[name="email"]', email)
    page.fill('input[name="password"]', password)
    page.click('button[type="submit"], input[type="submit"]')
    page.wait_for_load_state("networkidle")


def main():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(viewport={"width": 1280, "height": 800})
        page = context.new_page()

        # Public pages
        page.goto(f"{BASE}/index.php", wait_until="networkidle")
        shot(page, "Figure10_Home_Page.png")

        page.goto(f"{BASE}/login.php", wait_until="networkidle")
        shot(page, "Figure11_Login_Page.png")

        # Admin flow
        login(page, "mfitumukizaeric3@gmail.com", "admin123", "Admin")
        shot(page, "Figure12_Admin_Dashboard.png")

        for name, url in [
            ("Figure13_View_Users.png", f"{BASE}/view-users.php"),
            ("Figure14_Add_Users.png", f"{BASE}/add-users.php"),
        ]:
            page.goto(url, wait_until="networkidle")
            shot(page, name)

        # Guest / Gate officer flow
        context.clear_cookies()
        page = context.new_page()
        login(page, "mimi@gmail.com", "12345", "Guest")
        shot(page, "Figure15_User_Dashboard.png")

        for name, url in [
            ("Figure16_Record_Computers.png", f"{BASE}/record-computers.php"),
            ("Figure17_View_Laptops.png", f"{BASE}/view-laptops.php"),
            ("Figure18_Report_Page.png", f"{BASE}/report.php"),
            ("Figure19_Change_Password.png", f"{BASE}/change-password.php"),
        ]:
            page.goto(url, wait_until="networkidle")
            shot(page, name)

        browser.close()
    print("Done. Figures in", OUT)


if __name__ == "__main__":
    main()
