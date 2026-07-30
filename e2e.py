from playwright.sync_api import sync_playwright
import os
IMG=os.path.abspath("test_photo.jpg")
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1280,"height":800})
    pg.goto("https://ajja.ch/einreichen",timeout=45000); pg.wait_for_timeout(1800)
    pg.fill('input[name=name]','Test Kunde')
    pg.fill('input[name=email]','testkunde@example.com')
    pg.fill('input[name=phone]','079 123 45 67')
    pg.select_option('select[name=category]','Uhr')
    pg.fill('textarea[name=description]','Rolex-ähnliche Uhr, Edelstahl, guter Zustand - TESTEINREICHUNG')
    pg.set_input_files('input[name=\"photos[]\"]',[IMG])
    pg.click('button[name=ajja_submit]')
    pg.wait_for_timeout(5000)
    print("after submit URL:", pg.url)
    print("title:", pg.title())
    pg.screenshot(path="live_danke.png")
    b.close()
