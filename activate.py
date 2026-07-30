from playwright.sync_api import sync_playwright
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1280,"height":800})
    pg.goto("https://ajja.ch/wp-login.php",timeout=45000); pg.wait_for_timeout(1000)
    pg.fill("#user_login","nskozeco"); pg.fill("#user_pass","b99Q_gNv!eV"); pg.click("#wp-submit"); pg.wait_for_timeout(3000)
    # activate our plugin from plugins page
    pg.goto("https://ajja.ch/wp-admin/plugins.php",timeout=40000); pg.wait_for_timeout(1500)
    # find AJJA row activate link
    activated=False
    for a in pg.query_selector_all('a'):
        href=a.get_attribute('href') or ''
        if 'action=activate' in href and 'ajja-shop' in href:
            a.click(); pg.wait_for_timeout(4000); activated=True; print("activated via link"); break
    if not activated:
        # maybe already active; print statuses
        print("no activate link found (maybe already active)")
    # ensure permalinks are pretty (post name) so rewrite routes work
    pg.goto("https://ajja.ch/wp-admin/options-permalink.php",timeout=40000); pg.wait_for_timeout(1500)
    # select post name if available
    try:
        pg.check("#permalink-input-post-name")
    except Exception as e:
        print("postname radio err", e)
    pg.click("#submit"); pg.wait_for_timeout(2500)
    print("permalinks saved")
    b.close()
