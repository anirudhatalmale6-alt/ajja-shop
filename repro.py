from playwright.sync_api import sync_playwright
import os
def test(size_mb):
    path=os.path.abspath(f"dummy_{size_mb}.jpg")
    from playwright.sync_api import sync_playwright
    with sync_playwright() as p:
        b=p.chromium.launch(); pg=b.new_page(viewport={"width":1280,"height":800})
        pg.goto("https://ajja.ch/einreichen",timeout=60000); pg.wait_for_timeout(1500)
        pg.fill('input[name=name]',f'Size Test {size_mb}MB')
        pg.fill('input[name=email]','sizetest@example.com')
        pg.fill('textarea[name=description]',f'size test {size_mb}MB')
        pg.set_input_files('input[name="photos[]"]',[path])
        try:
            pg.click('button[name=ajja_submit]',timeout=8000)
        except Exception as e:
            print(size_mb,"MB -> click err",e); b.close(); return
        pg.wait_for_timeout(8000)
        url=pg.url
        print(f"{size_mb}MB -> URL: {url}  ({'SUCCESS' if 'danke' in url else 'FAILED/stayed'})")
        b.close()
for mb in (3,12,30):
    test(mb)
