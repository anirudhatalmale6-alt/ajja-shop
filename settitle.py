from playwright.sync_api import sync_playwright
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1280,"height":800})
    pg.goto("https://ajja.ch/wp-login.php",timeout=45000); pg.wait_for_timeout(1000)
    pg.fill("#user_login","nskozeco"); pg.fill("#user_pass","b99Q_gNv!eV"); pg.click("#wp-submit"); pg.wait_for_timeout(3000)
    pg.goto("https://ajja.ch/wp-admin/options-general.php",timeout=40000); pg.wait_for_timeout(1500)
    pg.fill("#blogname","AJJA")
    try: pg.fill("#blogdescription","Ankauf & Sofort-Auszahlung")
    except: pass
    pg.click("#submit"); pg.wait_for_timeout(2000)
    print("site title set")
    b.close()
