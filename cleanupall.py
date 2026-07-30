from playwright.sync_api import sync_playwright
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1280,"height":800})
    pg.on("dialog", lambda d: d.accept())
    pg.goto("https://ajja.ch/wp-login.php",timeout=45000); pg.wait_for_timeout(1000)
    pg.fill("#user_login","nskozeco"); pg.fill("#user_pass","b99Q_gNv!eV"); pg.click("#wp-submit"); pg.wait_for_timeout(3000)
    for _ in range(30):
        pg.goto("https://ajja.ch/wp-admin/admin.php?page=ajja-submissions",timeout=40000); pg.wait_for_timeout(1200)
        link=None
        for a in pg.query_selector_all('a'):
            if 'delete=' in (a.get_attribute('href') or ''): link=a; break
        if not link:
            break
        link.click(); pg.wait_for_timeout(2500)
    body=pg.inner_text('body')
    print("empty list:", 'Noch keine Einreichungen' in body)
    b.close()
