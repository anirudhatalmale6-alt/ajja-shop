from playwright.sync_api import sync_playwright
with sync_playwright() as p:
    b=p.chromium.launch(); pg=b.new_page(viewport={"width":1280,"height":800})
    def snap(url,name,scroll=0):
        pg.goto(url,timeout=45000); pg.wait_for_timeout(2200)
        if scroll: pg.evaluate(f"window.scrollTo(0,{scroll})"); pg.wait_for_timeout(500)
        pg.screenshot(path=name)
        print(url, "->", pg.title())
    snap("https://ajja.ch/","live_home.png")
    snap("https://ajja.ch/einreichen","live_form.png")
    snap("https://ajja.ch/faq","live_faq.png")
    snap("https://ajja.ch/impressum","live_impressum.png")
    b.close()
