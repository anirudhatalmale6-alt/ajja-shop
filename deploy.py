from playwright.sync_api import sync_playwright
import os
ZIP = os.path.abspath("ajja-shop.zip")
with sync_playwright() as p:
    b = p.chromium.launch()
    pg = b.new_page(viewport={"width":1280,"height":800})
    # login
    pg.goto("https://ajja.ch/wp-login.php", timeout=45000); pg.wait_for_timeout(1200)
    pg.fill("#user_login","nskozeco"); pg.fill("#user_pass","b99Q_gNv!eV"); pg.click("#wp-submit")
    pg.wait_for_timeout(3500)
    # go to upload plugin
    pg.goto("https://ajja.ch/wp-admin/plugin-install.php?tab=upload", timeout=40000); pg.wait_for_timeout(1500)
    pg.set_input_files('input[type=file]', ZIP)
    pg.click('#install-plugin-submit')
    pg.wait_for_timeout(6000)
    body = pg.inner_text('body')
    print("---AFTER UPLOAD---")
    print(body[:1500])
    # if replace prompt appears
    links = pg.query_selector_all('a')
    for l in links:
        t=(l.inner_text() or '').lower()
        if 'ersetzen' in t or 'replace' in t:
            print("Clicking replace link:", t)
            l.click(); pg.wait_for_timeout(6000)
            print(pg.inner_text('body')[:1200])
            break
    pg.screenshot(path="deploy1.png")
    b.close()
