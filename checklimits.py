from playwright.sync_api import sync_playwright
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1280,"height":800})
    pg.goto("https://ajja.ch/wp-login.php",timeout=45000); pg.wait_for_timeout(1000)
    pg.fill("#user_login","nskozeco"); pg.fill("#user_pass","b99Q_gNv!eV"); pg.click("#wp-submit"); pg.wait_for_timeout(3000)
    pg.goto("https://ajja.ch/wp-admin/media-new.php",timeout=40000); pg.wait_for_timeout(1500)
    body=pg.inner_text('body')
    import re
    m=re.search(r'(Maximale? Upload-?Dateigr\S+e?:?\s*[^\n]+)', body)
    print("MAX UPLOAD:", m.group(1) if m else "not found on media-new")
    # site health info page has php details
    b.close()
