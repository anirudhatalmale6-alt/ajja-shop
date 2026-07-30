from playwright.sync_api import sync_playwright
with sync_playwright() as p:
    b=p.chromium.launch(); ctx=b.new_context(viewport={"width":1280,"height":800}); pg=ctx.new_page()
    pg.goto("https://ajja.ch/wp-login.php",timeout=45000); pg.wait_for_timeout(1200)
    pg.fill("#user_login","sandra"); pg.fill("#user_pass","Ajja-Sandra-2026!"); pg.click("#wp-submit"); pg.wait_for_timeout(3500)
    print("URL:", pg.url)
    print("logged in:", "/wp-admin" in pg.url and "wp-login" not in pg.url)
    # confirm AJJA menu visible
    print("sees AJJA admin:", "Einreichungen" in pg.inner_text('body'))
    b.close()
