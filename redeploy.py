from playwright.sync_api import sync_playwright
import os
ZIP=os.path.abspath("ajja-shop.zip")
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1280,"height":800})
    pg.goto("https://ajja.ch/wp-login.php",timeout=45000); pg.wait_for_timeout(1000)
    pg.fill("#user_login","nskozeco"); pg.fill("#user_pass","b99Q_gNv!eV"); pg.click("#wp-submit"); pg.wait_for_timeout(3000)
    pg.goto("https://ajja.ch/wp-admin/plugin-install.php?tab=upload",timeout=40000); pg.wait_for_timeout(1200)
    pg.set_input_files('input[type=file]', ZIP)
    pg.click('#install-plugin-submit'); pg.wait_for_timeout(5000)
    # replace link
    clicked=False
    for a in pg.query_selector_all('a'):
        t=(a.inner_text() or '').lower()
        if 'ersetzen' in t or 'replace' in t:
            a.click(); pg.wait_for_timeout(6000); clicked=True; break
    body=pg.inner_text('body')
    print("replace clicked:", clicked)
    print("aktualisiert/updated:", ('aktualisiert' in body.lower()) or ('erfolgreich' in body.lower()) or ('updated' in body.lower()))
    # ensure active
    pg.goto("https://ajja.ch/wp-admin/plugins.php",timeout=40000); pg.wait_for_timeout(1500)
    pbody=pg.inner_text('body')
    print("plugin still active (has Deaktivieren near AJJA):", 'AJJA' in pbody)
    b.close()
