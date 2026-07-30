from playwright.sync_api import sync_playwright
USER="sandra"; EMAIL="sandra@ajja.ch"; PW="Ajja-Sandra-2026!"
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1280,"height":900})
    pg.goto("https://ajja.ch/wp-login.php",timeout=45000); pg.wait_for_timeout(1000)
    pg.fill("#user_login","nskozeco"); pg.fill("#user_pass","b99Q_gNv!eV"); pg.click("#wp-submit"); pg.wait_for_timeout(3000)
    pg.goto("https://ajja.ch/wp-admin/user-new.php",timeout=40000); pg.wait_for_timeout(1500)
    pg.fill("#user_login",USER)
    pg.fill("#email",EMAIL)
    # set custom password: clear generated then type
    try:
        pg.fill("#pass1", PW)
    except Exception as e:
        print("pass1 fill err, trying generate toggle", e)
        try:
            pg.click(".wp-generate-pw"); pg.wait_for_timeout(500); pg.fill("#pass1", PW)
        except Exception as e2: print("still err", e2)
    # confirm weak pw checkbox if present
    try:
        if pg.is_visible(".pw-weak input#pw-weak"):
            pg.check("#pw-weak")
    except: pass
    # uncheck send notification (placeholder email)
    try:
        if pg.is_checked("#send_user_notification"): pg.uncheck("#send_user_notification")
    except Exception as e: print("notif uncheck err", e)
    # role administrator
    try: pg.select_option("#role","administrator")
    except Exception as e: print("role err", e)
    pg.click("#createusersub"); pg.wait_for_timeout(3500)
    body=pg.inner_text('body')
    print("SUCCESS notice:", 'Neuer Benutzer' in body or 'New user' in body or 'wurde erstellt' in body or 'created' in body.lower())
    print(body[:400])
    b.close()
