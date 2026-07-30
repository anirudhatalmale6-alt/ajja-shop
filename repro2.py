from playwright.sync_api import sync_playwright
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":390,"height":780})  # phone-ish
    pg.goto("https://ajja.ch/einreichen",timeout=45000); pg.wait_for_timeout(1500)
    # fill everything EXCEPT photo
    pg.fill('input[name=name]','Kein Foto Test')
    pg.fill('input[name=email]','nofoto@example.com')
    pg.fill('textarea[name=description]','test ohne foto')
    pg.click('button[name=ajja_submit]')
    pg.wait_for_timeout(3000)
    # check if validation blocked
    blocked = pg.evaluate("""() => {
        const f=document.querySelector('input[name=\"photos[]\"]');
        return { url: location.href, invalid: f? !f.validity.valid : null, validationMsg: f? f.validationMessage : null };
    }""")
    print("NO-PHOTO:", blocked)
    b.close()
