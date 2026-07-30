from playwright.sync_api import sync_playwright
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1280,"height":900})
    pg.goto("https://ajja.ch/wp-login.php",timeout=45000); pg.wait_for_timeout(1000)
    pg.fill("#user_login","nskozeco"); pg.fill("#user_pass","b99Q_gNv!eV"); pg.click("#wp-submit"); pg.wait_for_timeout(3000)
    pg.goto("https://ajja.ch/wp-admin/admin.php?page=ajja-submissions",timeout=40000); pg.wait_for_timeout(1800)
    pg.screenshot(path="admin_list.png")
    body=pg.inner_text('body')
    print("LIST has 'Test Kunde':", 'Test Kunde' in body)
    # open detail of first view link
    import re
    for a in pg.query_selector_all('a'):
        h=a.get_attribute('href') or ''
        if 'page=ajja-submissions&view=' in h:
            pg.goto(h,timeout=40000); pg.wait_for_timeout(1800); break
    pg.screenshot(path="admin_detail.png")
    print("DETAIL has image tag:", pg.query_selector('img[src*=\"uploads\"]') is not None or pg.query_selector('img') is not None)
    print("DETAIL title area:", pg.inner_text('h2') if pg.query_selector('h2') else 'n/a')
    b.close()
