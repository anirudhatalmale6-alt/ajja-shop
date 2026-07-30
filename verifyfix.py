from playwright.sync_api import sync_playwright
import os
IMG=os.path.abspath("test_photo.jpg")
with sync_playwright() as p:
    b=p.chromium.launch()
    # TEST 1: no photo, all text -> should now SUCCEED
    pg=b.new_page(viewport={"width":390,"height":780})
    pg.goto("https://ajja.ch/einreichen",timeout=45000); pg.wait_for_timeout(1500)
    pg.fill('input[name=name]','Ohne Foto'); pg.fill('input[name=email]','of@example.com'); pg.fill('textarea[name=description]','test ohne foto neu')
    pg.click('button[name=ajja_submit]'); pg.wait_for_timeout(5000)
    print("TEST1 no-photo ->", pg.url, "(SUCCESS)" if 'danke' in pg.url else "(FAIL)")
    pg.close()
    # TEST 2: missing name -> JS error banner, stays
    pg=b.new_page(viewport={"width":390,"height":780})
    pg.goto("https://ajja.ch/einreichen",timeout=45000); pg.wait_for_timeout(1500)
    pg.fill('input[name=email]','x@example.com'); pg.fill('textarea[name=description]','no name')
    pg.click('button[name=ajja_submit]'); pg.wait_for_timeout(2000)
    err=pg.evaluate("()=>{var e=document.getElementById('ajja-jserr');return {shown:e&&e.style.display!=='none',txt:e?e.innerText:''};}")
    print("TEST2 missing-name -> url:",pg.url,"| errShown:",err['shown'],"| msg:",err['txt'])
    pg.close()
    # TEST 3: full valid with photo -> success
    pg=b.new_page(viewport={"width":390,"height":780})
    pg.goto("https://ajja.ch/einreichen",timeout=45000); pg.wait_for_timeout(1500)
    pg.fill('input[name=name]','Voll Test'); pg.fill('input[name=email]','voll@example.com'); pg.fill('textarea[name=description]','komplett mit foto')
    pg.set_input_files('input[name="photos[]"]',[IMG])
    pg.click('button[name=ajja_submit]'); pg.wait_for_timeout(6000)
    print("TEST3 full+photo ->", pg.url, "(SUCCESS)" if 'danke' in pg.url else "(FAIL)")
    b.close()
