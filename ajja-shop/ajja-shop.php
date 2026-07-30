<?php
/**
 * Plugin Name: AJJA — Ankauf & Sofort-Auszahlung
 * Description: AJJA Website: Startseite, Einreiche-Formular (Fotos/Video), Bewertungs-/Offerten-Verwaltung, FAQ, Kontakt, Newsletter, rechtliche Platzhalter. Dunkelblau + Gold.
 * Version: 1.0.0
 * Author: Speedy
 */

if (!defined('ABSPATH')) exit;

define('AJJA_VER', '1.0.0');

/* ============================================================
   ACTIVATION — tables + rewrite flush
   ============================================================ */
register_activation_hook(__FILE__, 'ajja_activate');
function ajja_activate() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $subs = $wpdb->prefix . 'ajja_submissions';
    dbDelta("CREATE TABLE $subs (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NULL,
        name VARCHAR(190) NOT NULL DEFAULT '',
        email VARCHAR(190) NOT NULL DEFAULT '',
        phone VARCHAR(80) NOT NULL DEFAULT '',
        category VARCHAR(120) NOT NULL DEFAULT '',
        description TEXT NULL,
        media LONGTEXT NULL,
        offer_price VARCHAR(60) NOT NULL DEFAULT '',
        status VARCHAR(40) NOT NULL DEFAULT 'neu',
        admin_note TEXT NULL,
        PRIMARY KEY (id)
    ) $charset;");

    $nl = $wpdb->prefix . 'ajja_newsletter';
    dbDelta("CREATE TABLE $nl (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        email VARCHAR(190) NOT NULL,
        name VARCHAR(190) NOT NULL DEFAULT '',
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        subscribed_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY email (email)
    ) $charset;");

    ajja_register_routes();
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, function(){ flush_rewrite_rules(); });

/* ============================================================
   ROUTES
   ============================================================ */
function ajja_routes() {
    return array('einreichen','danke','faq','kontakt','impressum','agb','datenschutz');
}
function ajja_register_routes() {
    foreach (ajja_routes() as $r) {
        add_rewrite_rule('^' . $r . '/?$', 'index.php?ajja_page=' . $r, 'top');
    }
}
add_action('init', 'ajja_register_routes');
add_filter('query_vars', function($v){ $v[] = 'ajja_page'; return $v; });

add_action('template_redirect', function(){
    $page = get_query_var('ajja_page');
    if (!$page && (is_front_page() || is_home())) $page = 'home';
    if ($page) { ajja_render($page); exit; }
});

/* ============================================================
   CONTENT OPTIONS (editable by Sandra)
   ============================================================ */
function ajja_defaults() {
    return array(
        'hero_title'   => "Ihre Wertsachen.\nSofort zu Bargeld.",
        'hero_sub'     => "Fotos hochladen, faire Offerte erhalten, sofort ausbezahlt bekommen — und aus Gutwill innert 14 Tagen wieder zurückerhalten, wenn Sie möchten.",
        'surcharge_1'  => "5",
        'surcharge_2'  => "10",
        'surcharge_3'  => "15",
        'contact_email'=> "info@ajja.ch",
        'contact_phone'=> "",
        'contact_text' => "Haben Sie eine Frage? Schreiben Sie uns — wir melden uns schnell zurück.",
        'faq'          => array(
            array('q'=>"Wie funktioniert der Ankauf?", 'a'=>"Sie laden 1–3 Fotos und ein kurzes Video Ihres Gegenstands mit einer Beschreibung hoch. Wir begutachten alles und machen Ihnen eine faire, unverbindliche Offerte. Nehmen Sie an, wird sofort ausbezahlt."),
            array('q'=>"Wie werde ich ausbezahlt?", 'a'=>"Nach Ihrer Zusage per TWINT Instant, bar bei Übergabe oder per Banküberweisung — ganz wie es Ihnen am besten passt."),
            array('q'=>"Was bedeutet die Rückgabe-Garantie?", 'a'=>"Aus reinem Gutwill können Sie den verkauften Gegenstand innert 14 Tagen wieder zurückerhalten — zum Verkaufspreis plus einem klaren, gestaffelten Aufschlag. Nach 14 Tagen läuft die Garantie ab."),
            array('q'=>"Kostet mich das Einreichen etwas?", 'a'=>"Nein. Das Einreichen und die Offerte sind kostenlos und unverbindlich. Sie entscheiden."),
        ),
        'impressum'    => "AJJA\n[Vor- und Nachname]\n[Strasse Nr.]\n[PLZ Ort], Kanton Zürich\n\nE-Mail: info@ajja.ch\n\n(Impressum — Adresse wird ergänzt, sobald verfügbar.)",
        'agb'          => "Allgemeine Geschäftsbedingungen (Platzhalter)\n\nHier folgen die AGB von AJJA. Bitte vor dem Live-Gang durch die Betreiberin / eine Fachperson ergänzen und prüfen lassen.",
        'datenschutz'  => "Datenschutzerklärung (Platzhalter)\n\nHier folgt die Datenschutzerklärung von AJJA. Bitte vor dem Live-Gang ergänzen und prüfen lassen.",
    );
}
function ajja_opt($key) {
    $o = get_option('ajja_content', array());
    $d = ajja_defaults();
    if (is_array($o) && array_key_exists($key, $o) && $o[$key] !== '' && $o[$key] !== null) return $o[$key];
    return isset($d[$key]) ? $d[$key] : '';
}

/* ============================================================
   SHARED MARKUP (nav / head / foot / css)
   ============================================================ */
function ajja_logo($light = false) {
    // light=true -> for dark backgrounds (gold A + white wordmark)
    $wordcol = $light ? '#ffffff' : '#15294d';
    $tag = $light ? 'rgba(255,255,255,.6)' : '#8792a6';
    return '<a class="ajja-brand" href="' . esc_url(home_url('/')) . '">
        <span class="ajja-mk">A</span>
        <span class="ajja-wm" style="color:' . $wordcol . '">AJJA<small style="color:' . $tag . '">ANKAUF · SOFORT BARGELD</small></span>
    </a>';
}
function ajja_css() { ?>
<style>
  :root{
    --ink:#141b2e;--ink-soft:#4c5670;--paper:#f7f5f0;--paper-2:#ece7dc;--card:#ffffff;
    --navy:#15294d;--navy-2:#1f3c6e;--gold:#e0a52a;--gold-2:#f4bf4a;--line:#e5ded1;
    --shadow:0 18px 50px -24px rgba(20,27,46,.30);
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html{scroll-behavior:smooth}
  body.ajja{font-family:"Manrope",system-ui,-apple-system,"Segoe UI",sans-serif;color:var(--ink);background:var(--paper);line-height:1.6;-webkit-font-smoothing:antialiased}
  .ajja .serif{font-family:"Fraunces",Georgia,serif}
  .ajja-wrap{max-width:1180px;margin:0 auto;padding:0 28px}
  .ajja a{color:inherit;text-decoration:none}
  .ajja .btn{display:inline-flex;align-items:center;gap:.5em;background:var(--navy);color:#fff;padding:15px 26px;border-radius:999px;font-weight:600;font-size:15px;border:0;cursor:pointer;box-shadow:0 12px 26px -12px rgba(21,41,77,.7);transition:.2s;font-family:inherit}
  .ajja .btn:hover{background:var(--navy-2);transform:translateY(-2px)}
  .ajja .btn.gold{background:var(--gold);color:#241a02}
  .ajja .btn.gold:hover{background:var(--gold-2)}
  .ajja .btn.ghost{background:transparent;color:var(--navy);box-shadow:none;border:1.5px solid var(--line)}
  .ajja .btn.ghost:hover{border-color:var(--navy);background:#fff}
  .ajja .eyebrow{font-size:12.5px;font-weight:700;letter-spacing:.22em;text-transform:uppercase;color:var(--gold)}

  .ajja-brand{display:flex;align-items:center;gap:11px}
  .ajja-mk{width:36px;height:36px;border-radius:9px;background:linear-gradient(150deg,var(--navy),var(--navy-2));display:grid;place-items:center;color:var(--gold);font-family:"Fraunces",serif;font-weight:800;font-size:20px;box-shadow:0 8px 18px -8px rgba(21,41,77,.8)}
  .ajja-wm{font-family:"Fraunces",serif;font-weight:600;font-size:22px;letter-spacing:.4px;line-height:1}
  .ajja-wm small{display:block;font-family:"Manrope",sans-serif;font-size:9px;font-weight:700;letter-spacing:.18em;margin-top:3px}

  header.ajja-nav{position:sticky;top:0;z-index:50;background:rgba(247,245,240,.88);backdrop-filter:blur(10px);border-bottom:1px solid var(--line)}
  .ajja-nav-in{display:flex;align-items:center;justify-content:space-between;height:74px}
  nav.ajja-links{display:flex;gap:28px;align-items:center;font-size:14.5px;font-weight:500;color:var(--ink-soft)}
  nav.ajja-links a:hover{color:var(--navy)}
  @media(max-width:880px){nav.ajja-links{display:none}}

  .ajja section.pad{padding:82px 0}
  .ajja .head{max-width:640px;margin-bottom:46px}
  .ajja .head h2{font-size:clamp(28px,3.6vw,40px);font-weight:500;line-height:1.1;letter-spacing:-.4px;margin:14px 0 0}
  .ajja .lead{font-size:19px;color:var(--ink-soft)}

  /* hero */
  .ajja-hero{position:relative;overflow:hidden}
  .ajja-hero:before{content:"";position:absolute;right:-12%;top:-30%;width:60%;height:150%;background:radial-gradient(closest-side,rgba(224,165,42,.16),transparent 70%);pointer-events:none}
  .ajja-hero-in{display:grid;grid-template-columns:1.08fr .92fr;gap:54px;align-items:center;padding:76px 0 68px}
  @media(max-width:920px){.ajja-hero-in{grid-template-columns:1fr;gap:36px;padding:50px 0}}
  .ajja-hero h1{font-size:clamp(38px,5.4vw,60px);line-height:1.04;font-weight:500;letter-spacing:-.5px}
  .ajja-hero h1 em{font-style:italic;color:var(--navy)}
  .ajja-hero p.lead{margin:22px 0 30px;max-width:34ch}
  .ajja-hero-cta{display:flex;gap:14px;flex-wrap:wrap;align-items:center}
  .paycheck{margin-top:30px;display:flex;gap:22px;flex-wrap:wrap;color:var(--ink-soft);font-size:13.5px;font-weight:600}
  .paycheck span{display:flex;align-items:center;gap:7px}.paycheck .dot{width:8px;height:8px;border-radius:50%;background:var(--navy-2)}

  .quote-card{background:var(--card);border:1px solid var(--line);border-radius:22px;box-shadow:var(--shadow);padding:26px}
  .qc-top{display:flex;align-items:center;justify-content:space-between;border-bottom:1px dashed var(--line);padding-bottom:16px;margin-bottom:16px}
  .qc-item{display:flex;gap:13px;align-items:center}
  .qc-thumb{width:56px;height:56px;border-radius:12px;background:linear-gradient(135deg,var(--paper-2),#ddd3bf);display:grid;place-items:center;font-size:24px}
  .qc-badge{font-size:11px;font-weight:700;color:var(--navy);background:rgba(31,60,110,.1);padding:5px 11px;border-radius:999px}
  .qc-offer{text-align:center;padding:8px 0 4px}
  .qc-offer small{display:block;color:var(--ink-soft);font-size:12.5px;letter-spacing:.05em;text-transform:uppercase;font-weight:700}
  .qc-offer .amt{font-family:"Fraunces",serif;font-size:44px;color:var(--navy);font-weight:600;letter-spacing:-1px}
  .qc-actions{display:flex;gap:10px;margin-top:16px}
  .qc-actions .b1{flex:1;text-align:center;background:var(--navy);color:#fff;padding:12px;border-radius:11px;font-weight:600;font-size:14px}
  .qc-actions .b2{flex:1;text-align:center;background:var(--paper-2);color:var(--ink);padding:12px;border-radius:11px;font-weight:600;font-size:14px}
  .qc-foot{margin-top:14px;text-align:center;font-size:12px;color:var(--ink-soft)}

  .strip{background:var(--navy);color:#dfe6f2}
  .strip-in{display:flex;justify-content:space-around;flex-wrap:wrap;gap:20px;padding:22px 0;text-align:center}
  .strip b{font-family:"Fraunces",serif;font-size:26px;color:#fff;display:block}
  .strip span{font-size:12.5px;letter-spacing:.05em;opacity:.85}

  .steps{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
  @media(max-width:920px){.steps{grid-template-columns:repeat(2,1fr)}}
  @media(max-width:520px){.steps{grid-template-columns:1fr}}
  .step{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:26px 22px;transition:.2s}
  .step:hover{transform:translateY(-4px);box-shadow:var(--shadow)}
  .step .n{font-family:"Fraunces",serif;font-size:15px;color:var(--gold);font-weight:600}
  .step .ic{width:48px;height:48px;border-radius:12px;background:var(--paper-2);display:grid;place-items:center;font-size:23px;margin:12px 0 16px}
  .step h3{font-size:18px;font-weight:600;margin-bottom:7px}
  .step p{font-size:14px;color:var(--ink-soft)}

  .feat{background:var(--paper-2)}
  .feat-in{display:grid;grid-template-columns:1fr 1fr;gap:54px;align-items:center}
  @media(max-width:920px){.feat-in{grid-template-columns:1fr;gap:34px}}
  .feat ul{list-style:none;margin-top:22px;display:grid;gap:14px}
  .feat li{display:flex;gap:13px;align-items:flex-start;font-size:15.5px}
  .feat li .ck{flex:none;width:24px;height:24px;border-radius:50%;background:var(--navy);color:#fff;display:grid;place-items:center;font-size:13px;margin-top:2px}
  .timeline{background:var(--card);border:1px solid var(--line);border-radius:20px;box-shadow:var(--shadow);padding:30px}
  .tl-row{display:flex;align-items:center;justify-content:space-between;padding:15px 0;border-bottom:1px solid var(--line)}
  .tl-row:last-child{border-bottom:0}
  .tl-row .pct{font-family:"Fraunces",serif;font-weight:600;color:var(--gold);font-size:20px}
  .tl-row small{color:var(--ink-soft);font-size:12.5px;display:block}

  .pay{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
  @media(max-width:820px){.pay{grid-template-columns:1fr}}
  .pay .c{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:28px}
  .pay .c .ic{font-size:26px;margin-bottom:12px}.pay .c h3{font-size:17px;font-weight:600;margin-bottom:6px}.pay .c p{font-size:14px;color:var(--ink-soft)}

  .cta{background:var(--navy);color:#fff;border-radius:26px;padding:56px 46px;text-align:center;position:relative;overflow:hidden}
  .cta:before{content:"";position:absolute;inset:0;background:radial-gradient(closest-side at 80% -10%,rgba(224,165,42,.4),transparent 60%)}
  .cta h2{font-size:clamp(28px,3.8vw,42px);font-weight:500;position:relative;letter-spacing:-.4px}
  .cta p{opacity:.9;margin:14px auto 26px;max-width:46ch;position:relative}
  .nlform{display:flex;gap:10px;max-width:460px;margin:0 auto;position:relative;flex-wrap:wrap;justify-content:center}
  .nlform input{flex:1;min-width:220px;padding:14px 18px;border-radius:999px;border:0;font-size:15px;font-family:inherit}

  /* forms / pages */
  .ajja-page{padding:56px 0 80px}
  .ajja-page h1{font-size:clamp(30px,4vw,46px);font-weight:500;letter-spacing:-.4px;margin-bottom:10px}
  .ajja-panel{background:var(--card);border:1px solid var(--line);border-radius:20px;box-shadow:var(--shadow);padding:34px}
  .field{margin-bottom:18px}
  .field label{display:block;font-weight:600;font-size:14px;margin-bottom:7px}
  .field input[type=text],.field input[type=email],.field input[type=tel],.field select,.field textarea{width:100%;padding:13px 15px;border:1.5px solid var(--line);border-radius:12px;font-size:15px;font-family:inherit;background:#fff}
  .field textarea{min-height:120px;resize:vertical}
  .field .hint{font-size:12.5px;color:var(--ink-soft);margin-top:6px}
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:18px}
  @media(max-width:640px){.grid2{grid-template-columns:1fr}}
  .ajja-note{background:#fffdf3;border:1px solid var(--gold);color:#6b5410;border-radius:12px;padding:13px 16px;font-size:13.5px;margin-bottom:22px}
  .faq-item{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:20px 22px;margin-bottom:14px}
  .faq-item h3{font-size:17px;font-weight:600;margin-bottom:6px}
  .faq-item p{color:var(--ink-soft);font-size:15px}
  .legal{white-space:pre-wrap;color:var(--ink-soft);font-size:15px;line-height:1.75}
  .ajja-success{background:#eef7f0;border:1px solid #bfe0c8;color:#1f5c39;border-radius:14px;padding:18px 20px;margin-bottom:22px;font-weight:600}
  .ajja-error{background:#fdeeee;border:1px solid #f0c4c4;color:#a13030;border-radius:14px;padding:14px 18px;margin-bottom:18px;font-weight:600}

  footer.ajja-foot{background:var(--ink);color:#c3cbdb;padding:56px 0 30px}
  .foot-in{display:grid;grid-template-columns:1.4fr 1fr 1fr 1fr;gap:32px}
  @media(max-width:820px){.foot-in{grid-template-columns:1fr 1fr}}
  .ajja-foot h4{color:#fff;font-size:13px;letter-spacing:.12em;text-transform:uppercase;margin-bottom:14px;font-weight:700}
  .ajja-foot a{display:block;color:#9aa4ba;font-size:14px;padding:5px 0}.ajja-foot a:hover{color:#fff}
  .foot-bottom{border-top:1px solid #262d40;margin-top:38px;padding-top:20px;font-size:12.5px;color:#7d879c;display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px}
</style>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
<?php }

function ajja_head($title) {
    ?><!doctype html><html <?php language_attributes(); ?>><head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html($title); ?> · AJJA</title>
    <?php ajja_css(); wp_head(); ?>
    </head><body <?php body_class('ajja'); ?>>
    <header class="ajja-nav"><div class="ajja-wrap ajja-nav-in">
        <?php echo ajja_logo(false); ?>
        <nav class="ajja-links">
            <a href="<?php echo esc_url(home_url('/#so')); ?>">So funktioniert's</a>
            <a href="<?php echo esc_url(home_url('/#rueckgabe')); ?>">Rückgabe</a>
            <a href="<?php echo esc_url(home_url('/faq')); ?>">FAQ</a>
            <a href="<?php echo esc_url(home_url('/kontakt')); ?>">Kontakt</a>
        </nav>
        <a href="<?php echo esc_url(home_url('/einreichen')); ?>" class="btn">Gegenstand einreichen</a>
    </div></header>
    <?php
}

function ajja_foot() {
    $mail = ajja_opt('contact_email'); $phone = ajja_opt('contact_phone');
    ?>
    <footer class="ajja-foot"><div class="ajja-wrap">
        <div class="foot-in">
            <div>
                <?php echo ajja_logo(true); ?>
                <p style="font-size:14px;max-width:32ch;margin-top:14px">Diskreter Ankauf von Wertgegenständen mit fairer Offerte, Sofort-Auszahlung und 14 Tagen Rückgabe-Garantie aus Gutwill.</p>
            </div>
            <div><h4>Service</h4>
                <a href="<?php echo esc_url(home_url('/#so')); ?>">So funktioniert's</a>
                <a href="<?php echo esc_url(home_url('/#rueckgabe')); ?>">Rückgabe-Garantie</a>
                <a href="<?php echo esc_url(home_url('/einreichen')); ?>">Gegenstand einreichen</a>
                <a href="<?php echo esc_url(home_url('/faq')); ?>">FAQ</a>
            </div>
            <div><h4>Rechtliches</h4>
                <a href="<?php echo esc_url(home_url('/impressum')); ?>">Impressum</a>
                <a href="<?php echo esc_url(home_url('/agb')); ?>">AGB</a>
                <a href="<?php echo esc_url(home_url('/datenschutz')); ?>">Datenschutz</a>
            </div>
            <div><h4>Kontakt</h4>
                <?php if ($mail): ?><a href="mailto:<?php echo esc_attr($mail); ?>"><?php echo esc_html($mail); ?></a><?php endif; ?>
                <?php if ($phone): ?><a href="tel:<?php echo esc_attr(preg_replace('/\s+/','',$phone)); ?>"><?php echo esc_html($phone); ?></a><?php endif; ?>
                <a href="<?php echo esc_url(home_url('/kontakt')); ?>">Nachricht senden</a>
                <span style="color:#7d879c;font-size:14px;display:block;padding:5px 0">Kanton Zürich</span>
            </div>
        </div>
        <div class="foot-bottom">
            <span>© <?php echo esc_html(date('Y')); ?> AJJA · Alle Rechte vorbehalten</span>
            <span>TWINT · Bar · Banküberweisung</span>
        </div>
    </div></footer>
    <?php wp_footer(); ?>
    </body></html>
    <?php
}

/* ============================================================
   RENDER ROUTER
   ============================================================ */
function ajja_render($page) {
    nocache_headers();
    switch ($page) {
        case 'home':        ajja_page_home(); break;
        case 'einreichen':  ajja_page_form(); break;
        case 'danke':       ajja_page_danke(); break;
        case 'faq':         ajja_page_faq(); break;
        case 'kontakt':     ajja_page_kontakt(); break;
        case 'impressum':   ajja_page_legal('Impressum', ajja_opt('impressum')); break;
        case 'agb':         ajja_page_legal('AGB', ajja_opt('agb')); break;
        case 'datenschutz': ajja_page_legal('Datenschutz', ajja_opt('datenschutz')); break;
        default: status_header(404); ajja_page_legal('Seite nicht gefunden', "Diese Seite existiert nicht.\n\nZurück zur Startseite."); break;
    }
}

/* ---------- HOME ---------- */
function ajja_page_home() {
    $title = ajja_opt('hero_title');
    $lines = preg_split('/\n/', $title);
    $s1 = ajja_opt('surcharge_1'); $s2 = ajja_opt('surcharge_2'); $s3 = ajja_opt('surcharge_3');
    ajja_head('Ihre Wertsachen. Sofort zu Bargeld.');
    ?>
    <section class="ajja-hero"><div class="ajja-wrap ajja-hero-in">
        <div>
            <span class="eyebrow">Diskret · Fair · Sofort ausbezahlt</span>
            <h1 class="serif"><?php
                foreach ($lines as $i => $ln) {
                    $ln = esc_html(trim($ln));
                    echo $i === 0 ? $ln : '<br><em>' . $ln . '</em>';
                }
            ?></h1>
            <p class="lead"><?php echo esc_html(ajja_opt('hero_sub')); ?></p>
            <div class="ajja-hero-cta">
                <a href="<?php echo esc_url(home_url('/einreichen')); ?>" class="btn">Jetzt Gegenstand einreichen →</a>
                <a href="#so" class="btn ghost">So funktioniert's</a>
            </div>
            <div class="paycheck"><span><i class="dot"></i> TWINT Instant</span><span><i class="dot"></i> Bar</span><span><i class="dot"></i> Banküberweisung</span></div>
        </div>
        <div class="quote-card">
            <div class="qc-top">
                <div class="qc-item"><div class="qc-thumb">⌚</div><div><b>Herren-Uhr, Edelstahl</b><br><small style="color:var(--ink-soft)">3 Fotos · 1 Video eingereicht</small></div></div>
                <span class="qc-badge">Offerte bereit</span>
            </div>
            <div class="qc-offer"><small>Ihr Offerten-Preis</small><div class="amt">CHF 480.–</div></div>
            <div class="qc-actions"><div class="b1">Annehmen</div><div class="b2">Ablehnen</div></div>
            <div class="qc-foot">Bei Annahme sofortige Auszahlung · 14 Tage Rückgabe-Garantie</div>
        </div>
    </div></section>

    <div class="strip"><div class="ajja-wrap strip-in">
        <div><b>Sofort</b><span>Auszahlung bei Annahme</span></div>
        <div><b>14 Tage</b><span>Rückgabe-Garantie</span></div>
        <div><b>100% diskret</b><span>Ihre Daten bleiben privat</span></div>
        <div><b>Kostenlos</b><span>Einreichen &amp; Offerte</span></div>
    </div></div>

    <section class="pad" id="so"><div class="ajja-wrap">
        <div class="head"><span class="eyebrow">In 4 einfachen Schritten</span><h2 class="serif">So funktioniert AJJA</h2></div>
        <div class="steps">
            <div class="step"><div class="n">Schritt 01</div><div class="ic">📷</div><h3>Einreichen</h3><p>Laden Sie 1–3 Fotos und ein kurzes Video Ihres Gegenstands hoch, mit einer kurzen Beschreibung.</p></div>
            <div class="step"><div class="n">Schritt 02</div><div class="ic">🔎</div><h3>Bewertung</h3><p>Wir begutachten Ihren Artikel persönlich und machen Ihnen eine faire, unverbindliche Offerte.</p></div>
            <div class="step"><div class="n">Schritt 03</div><div class="ic">💸</div><h3>Sofort-Auszahlung</h3><p>Nehmen Sie an, wird sofort ausbezahlt — per TWINT, bar oder Überweisung. Der Gegenstand geht in Eigentum über.</p></div>
            <div class="step"><div class="n">Schritt 04</div><div class="ic">↩️</div><h3>Rückgabe-Garantie</h3><p>Aus Gutwill können Sie den Gegenstand innert 14 Tagen gegen einen gestaffelten Aufschlag wieder zurückerhalten.</p></div>
        </div>
    </div></section>

    <section class="pad feat" id="rueckgabe"><div class="ajja-wrap feat-in">
        <div>
            <span class="eyebrow">Ihre Sicherheit</span>
            <h2 class="serif">14 Tage Zeit, es sich anders zu überlegen</h2>
            <ul>
                <li><span class="ck">✓</span> Nach dem Verkauf haben Sie <b>volle 14 Tage</b> Rückgabe-Recht — aus reinem Gutwill.</li>
                <li><span class="ck">✓</span> Rückgabe zum Verkaufspreis plus einem klaren, <b>gestaffelten Aufschlag</b>.</li>
                <li><span class="ck">✓</span> Keine versteckten Kosten — Sie sehen den Aufschlag vorher.</li>
                <li><span class="ck">✓</span> Nach 14 Tagen läuft die Rückgabe-Garantie ab.</li>
            </ul>
        </div>
        <div class="timeline">
            <div class="tl-row"><div><b>Tag 1–5</b><small>Frühe Rückgabe</small></div><div class="pct">+ <?php echo esc_html($s1); ?>%</div></div>
            <div class="tl-row"><div><b>Tag 6–10</b><small>Mittlere Frist</small></div><div class="pct">+ <?php echo esc_html($s2); ?>%</div></div>
            <div class="tl-row"><div><b>Tag 11–14</b><small>Letzte Frist</small></div><div class="pct">+ <?php echo esc_html($s3); ?>%</div></div>
            <div class="tl-row"><div><b>Ab Tag 15</b><small>Garantie abgelaufen</small></div><div class="pct" style="color:var(--ink-soft)">—</div></div>
        </div>
    </div></section>

    <section class="pad" id="auszahlung"><div class="ajja-wrap">
        <div class="head"><span class="eyebrow">Flexibel &amp; sofort</span><h2 class="serif">Wie Sie ausbezahlt werden</h2></div>
        <div class="pay">
            <div class="c"><div class="ic">⚡</div><h3>TWINT Instant</h3><p>Das Geld ist in Sekunden auf Ihrem Handy — schnell und unkompliziert.</p></div>
            <div class="c"><div class="ic">💵</div><h3>Bar</h3><p>Lieber Bargeld in der Hand? Auch das ist bei Übergabe jederzeit möglich.</p></div>
            <div class="c"><div class="ic">🏦</div><h3>Banküberweisung</h3><p>Klassisch und sicher direkt auf Ihr Konto überwiesen.</p></div>
        </div>
    </div></section>

    <section class="pad" style="padding-top:20px"><div class="ajja-wrap">
        <div class="cta">
            <h2 class="serif">Bereit? Reichen Sie Ihren Gegenstand ein.</h2>
            <p>Kostenlos und unverbindlich. Sie erhalten Ihre Offerte — Sie entscheiden.</p>
            <div class="ajja-hero-cta" style="justify-content:center;margin-bottom:30px"><a href="<?php echo esc_url(home_url('/einreichen')); ?>" class="btn gold">Gegenstand einreichen →</a></div>
            <p style="margin-bottom:14px"><b style="color:#fff">Newsletter:</b> Angebote &amp; Neuigkeiten — jederzeit abbestellbar.</p>
            <?php echo ajja_newsletter_form(); ?>
        </div>
    </div></section>
    <?php
    ajja_foot();
}

/* ---------- EINREICHEN (form) ---------- */
function ajja_page_form() {
    $err = ''; $ok = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajja_submit'])) {
        $res = ajja_handle_submission();
        if ($res === true) { wp_safe_redirect(home_url('/danke')); exit; }
        $err = $res;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        // POST arrived but PHP discarded it -> upload exceeded server limit
        $err = 'Die hochgeladenen Dateien sind leider zu gross für den Server. Bitte wählen Sie kleinere Fotos oder lassen Sie das Video weg und versuchen Sie es erneut.';
    }
    ajja_head('Gegenstand einreichen');
    ?>
    <div class="ajja-page"><div class="ajja-wrap" style="max-width:760px">
        <span class="eyebrow">Kostenlos &amp; unverbindlich</span>
        <h1 class="serif">Gegenstand einreichen</h1>
        <p class="lead" style="margin-bottom:26px">Laden Sie ein paar Fotos und ein kurzes Video hoch. Wir melden uns mit einer fairen Offerte.</p>
        <?php if ($err): ?><div class="ajja-error"><?php echo esc_html($err); ?></div><?php endif; ?>
        <div class="ajja-panel">
            <div id="ajja-jserr" class="ajja-error" style="display:none"></div>
            <form method="post" enctype="multipart/form-data" id="ajja-form" novalidate>
                <?php wp_nonce_field('ajja_submit', 'ajja_nonce'); ?>
                <input type="hidden" name="ajja_submit" value="1">
                <div class="grid2">
                    <div class="field"><label>Ihr Name *</label><input type="text" name="name" required value="<?php echo isset($_POST['name'])?esc_attr(wp_unslash($_POST['name'])):''; ?>"></div>
                    <div class="field"><label>E-Mail *</label><input type="email" name="email" required value="<?php echo isset($_POST['email'])?esc_attr(wp_unslash($_POST['email'])):''; ?>"></div>
                </div>
                <div class="grid2">
                    <div class="field"><label>Telefon</label><input type="tel" name="phone" value="<?php echo isset($_POST['phone'])?esc_attr(wp_unslash($_POST['phone'])):''; ?>"></div>
                    <div class="field"><label>Art des Gegenstands</label>
                        <select name="category">
                            <option value="">Bitte wählen…</option>
                            <?php foreach (array('Uhr','Schmuck','Elektronik','Foto/Kamera','Musikinstrument','Fahrzeug/Zubehör','Sammlerstück','Anderes') as $c) echo '<option>' . esc_html($c) . '</option>'; ?>
                        </select>
                    </div>
                </div>
                <div class="field"><label>Beschreibung *</label>
                    <textarea name="description" required placeholder="Marke, Modell, Zustand, Alter, Zubehör …"><?php echo isset($_POST['description'])?esc_textarea(wp_unslash($_POST['description'])):''; ?></textarea>
                </div>
                <div class="field"><label>Fotos (1–3, empfohlen)</label>
                    <input type="file" name="photos[]" accept="image/*" multiple>
                    <div class="hint">JPG oder PNG, bis zu 3 Fotos. Fotos helfen sehr bei der Bewertung — Sie können aber auch ohne einreichen und die Fotos nachreichen.</div>
                </div>
                <div class="field"><label>Video (optional)</label>
                    <input type="file" name="video" accept="video/*">
                    <div class="hint">Kurzes Video hilft bei der Bewertung (max. ~50 MB).</div>
                </div>
                <div class="ajja-note">Mit dem Absenden stimmen Sie zu, dass wir Ihre Angaben zur Bewertung verwenden. Es entstehen keine Kosten, die Offerte ist unverbindlich.</div>
                <button type="submit" name="ajja_submit" value="1" class="btn gold" style="width:100%;justify-content:center">Einreichen &amp; Offerte anfragen</button>
            </form>
        </div>
    </div></div>
    <script>
    (function(){
        var f = document.getElementById('ajja-form'); if (!f) return;
        var box = document.getElementById('ajja-jserr');
        var g = function(n){ return f.querySelector('[name="' + n + '"]'); };
        f.addEventListener('submit', function(e){
            var errs = [];
            var name = (g('name').value || '').trim();
            var email = (g('email').value || '').trim();
            var desc = (g('description').value || '').trim();
            if (!name) errs.push('Bitte geben Sie Ihren Namen ein.');
            if (!email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) errs.push('Bitte geben Sie eine gültige E-Mail-Adresse ein.');
            if (!desc) errs.push('Bitte beschreiben Sie den Gegenstand kurz.');
            if (errs.length) {
                e.preventDefault();
                box.innerHTML = errs.join('<br>');
                box.style.display = 'block';
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return false;
            }
            var btn = f.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.textContent = 'Wird gesendet …'; }
        });
    })();
    </script>
    <?php
    ajja_foot();
}

function ajja_handle_submission() {
    if (!isset($_POST['ajja_nonce']) || !wp_verify_nonce($_POST['ajja_nonce'], 'ajja_submit')) return 'Sicherheitsprüfung fehlgeschlagen. Bitte erneut versuchen.';
    $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
    $category = sanitize_text_field(wp_unslash($_POST['category'] ?? ''));
    $desc = sanitize_textarea_field(wp_unslash($_POST['description'] ?? ''));
    if (!$name || !is_email($email) || !$desc) return 'Bitte füllen Sie Name, E-Mail und Beschreibung aus.';

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $media = array();
    // photos
    if (!empty($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
        $count = count($_FILES['photos']['name']);
        for ($i = 0; $i < $count && $i < 3; $i++) {
            if (!isset($_FILES['photos']['name'][$i]) || $_FILES['photos']['name'][$i] === '') continue;
            $file = array(
                'name' => $_FILES['photos']['name'][$i],
                'type' => $_FILES['photos']['type'][$i],
                'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                'error' => $_FILES['photos']['error'][$i],
                'size' => $_FILES['photos']['size'][$i],
            );
            $id = ajja_sideload($file);
            if ($id) $media[] = $id;
        }
    }
    // video
    if (!empty($_FILES['video']) && $_FILES['video']['name'] !== '') {
        $id = ajja_sideload($_FILES['video']);
        if ($id) $media[] = $id;
    }

    global $wpdb;
    $wpdb->insert($wpdb->prefix . 'ajja_submissions', array(
        'created_at' => current_time('mysql'),
        'name' => $name, 'email' => $email, 'phone' => $phone,
        'category' => $category, 'description' => $desc,
        'media' => wp_json_encode($media), 'status' => 'neu',
    ));
    $sid = $wpdb->insert_id;

    // notify site admin (best effort)
    $admin = get_option('admin_email');
    if ($admin) {
        $body = "Neue Einreichung bei AJJA:\n\nName: $name\nE-Mail: $email\nTelefon: $phone\nArt: $category\n\nBeschreibung:\n$desc\n\nIm Adminbereich ansehen: " . admin_url('admin.php?page=ajja-submissions&view=' . $sid);
        @wp_mail($admin, 'AJJA — neue Einreichung von ' . $name, $body);
    }
    return true;
}

function ajja_sideload($file) {
    $overrides = array('test_form' => false);
    $moved = wp_handle_upload($file, $overrides);
    if (!$moved || isset($moved['error'])) return 0;
    $filetype = wp_check_filetype($moved['file']);
    $attach = array(
        'post_mime_type' => $filetype['type'],
        'post_title' => sanitize_file_name(basename($moved['file'])),
        'post_content' => '',
        'post_status' => 'inherit',
    );
    $attach_id = wp_insert_attachment($attach, $moved['file']);
    if (!is_wp_error($attach_id) && $attach_id) {
        $meta = wp_generate_attachment_metadata($attach_id, $moved['file']);
        wp_update_attachment_metadata($attach_id, $meta);
        return $attach_id;
    }
    return 0;
}

/* ---------- DANKE ---------- */
function ajja_page_danke() {
    ajja_head('Vielen Dank');
    ?>
    <div class="ajja-page"><div class="ajja-wrap" style="max-width:680px;text-align:center">
        <div style="font-size:56px;margin-bottom:10px">✅</div>
        <h1 class="serif">Vielen Dank!</h1>
        <p class="lead" style="margin:14px auto 26px">Ihre Einreichung ist bei uns eingegangen. Wir schauen sie uns an und melden uns mit einer fairen Offerte — meist innert kurzer Zeit.</p>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn">Zurück zur Startseite</a>
    </div></div>
    <?php
    ajja_foot();
}

/* ---------- FAQ ---------- */
function ajja_page_faq() {
    $faq = ajja_opt('faq'); if (!is_array($faq)) $faq = array();
    ajja_head('FAQ');
    ?>
    <div class="ajja-page"><div class="ajja-wrap" style="max-width:820px">
        <span class="eyebrow">Häufige Fragen</span>
        <h1 class="serif">FAQ</h1>
        <p class="lead" style="margin-bottom:30px">Die wichtigsten Antworten rund um AJJA.</p>
        <?php foreach ($faq as $item): ?>
            <div class="faq-item"><h3><?php echo esc_html($item['q']); ?></h3><p><?php echo esc_html($item['a']); ?></p></div>
        <?php endforeach; ?>
        <div style="margin-top:24px"><a href="<?php echo esc_url(home_url('/kontakt')); ?>" class="btn ghost">Weitere Frage? Kontakt →</a></div>
    </div></div>
    <?php
    ajja_foot();
}

/* ---------- KONTAKT ---------- */
function ajja_page_kontakt() {
    $sent = false; $err = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajja_contact'])) {
        if (!isset($_POST['ajja_nonce']) || !wp_verify_nonce($_POST['ajja_nonce'], 'ajja_contact')) {
            $err = 'Sicherheitsprüfung fehlgeschlagen.';
        } else {
            $n = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
            $e = sanitize_email(wp_unslash($_POST['email'] ?? ''));
            $m = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));
            if (!$n || !is_email($e) || !$m) { $err = 'Bitte alle Felder ausfüllen.'; }
            else {
                $to = ajja_opt('contact_email') ?: get_option('admin_email');
                @wp_mail($to, 'AJJA Kontakt von ' . $n, "Von: $n <$e>\n\n$m", array('Reply-To: ' . $e));
                $sent = true;
            }
        }
    }
    ajja_head('Kontakt');
    ?>
    <div class="ajja-page"><div class="ajja-wrap" style="max-width:640px">
        <span class="eyebrow">Wir sind für Sie da</span>
        <h1 class="serif">Kontakt</h1>
        <p class="lead" style="margin-bottom:26px"><?php echo esc_html(ajja_opt('contact_text')); ?></p>
        <?php if ($sent): ?><div class="ajja-success">Danke! Ihre Nachricht ist unterwegs. Wir melden uns bald.</div><?php endif; ?>
        <?php if ($err): ?><div class="ajja-error"><?php echo esc_html($err); ?></div><?php endif; ?>
        <div class="ajja-panel">
            <form method="post">
                <?php wp_nonce_field('ajja_contact', 'ajja_nonce'); ?>
                <div class="grid2">
                    <div class="field"><label>Name *</label><input type="text" name="name" required></div>
                    <div class="field"><label>E-Mail *</label><input type="email" name="email" required></div>
                </div>
                <div class="field"><label>Nachricht *</label><textarea name="message" required></textarea></div>
                <button type="submit" name="ajja_contact" value="1" class="btn" style="width:100%;justify-content:center">Nachricht senden</button>
            </form>
            <?php $mail = ajja_opt('contact_email'); if ($mail): ?>
            <p style="margin-top:18px;font-size:14px;color:var(--ink-soft)">Oder direkt per E-Mail: <a style="color:var(--navy);font-weight:600" href="mailto:<?php echo esc_attr($mail); ?>"><?php echo esc_html($mail); ?></a></p>
            <?php endif; ?>
        </div>
    </div></div>
    <?php
    ajja_foot();
}

/* ---------- LEGAL ---------- */
function ajja_page_legal($title, $body) {
    ajja_head($title);
    ?>
    <div class="ajja-page"><div class="ajja-wrap" style="max-width:820px">
        <h1 class="serif"><?php echo esc_html($title); ?></h1>
        <div class="legal" style="margin-top:18px"><?php echo esc_html($body); ?></div>
        <div style="margin-top:26px"><a href="<?php echo esc_url(home_url('/')); ?>" class="btn ghost">← Startseite</a></div>
    </div></div>
    <?php
    ajja_foot();
}

/* ============================================================
   NEWSLETTER (signup form + handler)
   ============================================================ */
function ajja_newsletter_form() {
    ob_start(); ?>
    <form class="nlform" method="post" action="<?php echo esc_url(home_url('/')); ?>">
        <?php wp_nonce_field('ajja_nl', 'ajja_nl_nonce'); ?>
        <input type="email" name="ajja_nl_email" placeholder="Ihre E-Mail-Adresse" required>
        <button class="btn gold" type="submit" name="ajja_nl_signup" value="1">Anmelden</button>
    </form>
    <?php return ob_get_clean();
}
add_action('template_redirect', function(){
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajja_nl_signup'])) {
        if (isset($_POST['ajja_nl_nonce']) && wp_verify_nonce($_POST['ajja_nl_nonce'], 'ajja_nl')) {
            global $wpdb; $t = $wpdb->prefix . 'ajja_newsletter';
            $email = sanitize_email(wp_unslash($_POST['ajja_nl_email'] ?? ''));
            if (is_email($email)) {
                $ex = $wpdb->get_var($wpdb->prepare("SELECT id FROM $t WHERE email=%s", $email));
                if (!$ex) $wpdb->insert($t, array('email'=>$email,'status'=>'active','subscribed_at'=>current_time('mysql')));
            }
        }
        wp_safe_redirect(home_url('/?nl=ok')); exit;
    }
}, 5);

/* ============================================================
   ADMIN
   ============================================================ */
add_action('admin_menu', function(){
    add_menu_page('AJJA', 'AJJA', 'manage_options', 'ajja-submissions', 'ajja_admin_submissions', 'dashicons-money-alt', 30);
    add_submenu_page('ajja-submissions', 'Einreichungen', 'Einreichungen', 'manage_options', 'ajja-submissions', 'ajja_admin_submissions');
    add_submenu_page('ajja-submissions', 'Newsletter', 'Newsletter', 'manage_options', 'ajja-newsletter', 'ajja_admin_newsletter');
    add_submenu_page('ajja-submissions', 'Inhalte', 'Inhalte', 'manage_options', 'ajja-inhalte', 'ajja_admin_inhalte');
});

function ajja_status_options() {
    return array('neu'=>'Neu','offeriert'=>'Offeriert','angenommen'=>'Angenommen','abgelehnt'=>'Abgelehnt','ausbezahlt'=>'Ausbezahlt','zurueckgegeben'=>'Zurückgegeben');
}

function ajja_admin_submissions() {
    global $wpdb; $t = $wpdb->prefix . 'ajja_submissions';

    // update
    if (isset($_POST['ajja_update']) && check_admin_referer('ajja_update_sub')) {
        $id = intval($_POST['id']);
        $wpdb->update($t, array(
            'offer_price' => sanitize_text_field(wp_unslash($_POST['offer_price'] ?? '')),
            'status' => sanitize_text_field(wp_unslash($_POST['status'] ?? 'neu')),
            'admin_note' => sanitize_textarea_field(wp_unslash($_POST['admin_note'] ?? '')),
            'updated_at' => current_time('mysql'),
        ), array('id' => $id));
        echo '<div class="notice notice-success"><p>Einreichung #' . $id . ' gespeichert.</p></div>';
    }
    if (isset($_GET['delete']) && check_admin_referer('ajja_del_' . intval($_GET['delete']))) {
        $wpdb->delete($t, array('id' => intval($_GET['delete'])));
        echo '<div class="notice notice-success"><p>Einreichung gelöscht.</p></div>';
    }

    $view = isset($_GET['view']) ? intval($_GET['view']) : 0;
    echo '<div class="wrap"><h1>AJJA — Einreichungen</h1>';

    if ($view) {
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t WHERE id=%d", $view));
        if ($row) {
            $opts = ajja_status_options();
            echo '<p><a href="' . esc_url(admin_url('admin.php?page=ajja-submissions')) . '">&larr; Zurück zur Liste</a></p>';
            echo '<div style="display:flex;gap:24px;flex-wrap:wrap;align-items:flex-start">';
            echo '<div style="flex:1;min-width:300px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:20px">';
            echo '<h2 style="margin-top:0">' . esc_html($row->name) . ' <span style="font-weight:400;color:#666">#' . $row->id . '</span></h2>';
            echo '<p><strong>E-Mail:</strong> <a href="mailto:' . esc_attr($row->email) . '">' . esc_html($row->email) . '</a><br>';
            echo '<strong>Telefon:</strong> ' . esc_html($row->phone ?: '—') . '<br>';
            echo '<strong>Art:</strong> ' . esc_html($row->category ?: '—') . '<br>';
            echo '<strong>Eingereicht:</strong> ' . esc_html($row->created_at) . '</p>';
            echo '<p><strong>Beschreibung:</strong><br>' . nl2br(esc_html($row->description)) . '</p>';
            // media
            $media = json_decode($row->media, true); if (!is_array($media)) $media = array();
            if ($media) {
                echo '<p><strong>Medien:</strong></p><div style="display:flex;gap:10px;flex-wrap:wrap">';
                foreach ($media as $mid) {
                    $url = wp_get_attachment_url($mid);
                    $mime = get_post_mime_type($mid);
                    if (!$url) continue;
                    if (strpos($mime, 'video') === 0) {
                        echo '<video src="' . esc_url($url) . '" controls style="max-width:220px;border-radius:8px"></video>';
                    } else {
                        $thumb = wp_get_attachment_image_url($mid, 'medium') ?: $url;
                        echo '<a href="' . esc_url($url) . '" target="_blank"><img src="' . esc_url($thumb) . '" style="width:140px;height:140px;object-fit:cover;border-radius:8px;border:1px solid #ddd"></a>';
                    }
                }
                echo '</div>';
            } else {
                echo '<p><em>Keine Medien.</em></p>';
            }
            echo '</div>';
            // offer form
            echo '<div style="width:320px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:20px">';
            echo '<form method="post">';
            wp_nonce_field('ajja_update_sub');
            echo '<input type="hidden" name="id" value="' . $row->id . '">';
            echo '<p><label><strong>Offerten-Preis (CHF)</strong><br><input type="text" name="offer_price" value="' . esc_attr($row->offer_price) . '" class="regular-text" placeholder="z.B. 480"></label></p>';
            echo '<p><label><strong>Status</strong><br><select name="status">';
            foreach ($opts as $k => $v) echo '<option value="' . esc_attr($k) . '"' . selected($row->status, $k, false) . '>' . esc_html($v) . '</option>';
            echo '</select></label></p>';
            echo '<p><label><strong>Interne Notiz</strong><br><textarea name="admin_note" rows="4" class="large-text">' . esc_textarea($row->admin_note) . '</textarea></label></p>';
            echo '<p><button class="button button-primary" name="ajja_update" value="1">Speichern</button></p>';
            echo '</form></div>';
            echo '</div>';
        }
        echo '</div>';
        return;
    }

    // list
    $rows = $wpdb->get_results("SELECT * FROM $t ORDER BY id DESC");
    $opts = ajja_status_options();
    echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>#</th><th>Datum</th><th>Name</th><th>Art</th><th>Status</th><th>Offerte</th><th>Aktion</th></tr></thead><tbody>';
    if (!$rows) echo '<tr><td colspan="7">Noch keine Einreichungen.</td></tr>';
    foreach ((array)$rows as $r) {
        $del = wp_nonce_url(admin_url('admin.php?page=ajja-submissions&delete=' . $r->id), 'ajja_del_' . $r->id);
        echo '<tr>';
        echo '<td>' . $r->id . '</td>';
        echo '<td>' . esc_html(mysql2date('d.m.Y H:i', $r->created_at)) . '</td>';
        echo '<td><strong>' . esc_html($r->name) . '</strong><br><small>' . esc_html($r->email) . '</small></td>';
        echo '<td>' . esc_html($r->category ?: '—') . '</td>';
        echo '<td>' . esc_html($opts[$r->status] ?? $r->status) . '</td>';
        echo '<td>' . ($r->offer_price ? 'CHF ' . esc_html($r->offer_price) : '—') . '</td>';
        echo '<td><a class="button button-small" href="' . esc_url(admin_url('admin.php?page=ajja-submissions&view=' . $r->id)) . '">Ansehen</a> ';
        echo '<a class="button button-small" style="color:#b32d2e" href="' . esc_url($del) . '" onclick="return confirm(\'Wirklich löschen?\')">Löschen</a></td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}

function ajja_admin_newsletter() {
    global $wpdb; $t = $wpdb->prefix . 'ajja_newsletter';

    // silent import
    if (isset($_POST['ajja_add_subs']) && check_admin_referer('ajja_add', '_wpnonce_add')) {
        $raw = (string)($_POST['ajja_add_emails'] ?? '');
        $lines = preg_split('/[\r\n]+/', $raw);
        $added = 0; $dupe = 0; $invalid = 0;
        foreach ($lines as $line) {
            $line = trim($line); if ($line === '') continue;
            $parts = preg_split('/[,;\t]+/', $line, 2);
            $email = sanitize_email(trim($parts[0]));
            $name = isset($parts[1]) ? sanitize_text_field(trim($parts[1])) : '';
            if (!is_email($email)) { $invalid++; continue; }
            $ex = $wpdb->get_var($wpdb->prepare("SELECT id FROM $t WHERE email=%s", $email));
            if ($ex) { $dupe++; continue; }
            $wpdb->insert($t, array('email'=>$email,'name'=>$name,'status'=>'active','subscribed_at'=>current_time('mysql')));
            $added++;
        }
        echo '<div class="notice notice-success"><p>Import: <strong>' . $added . '</strong> hinzugefügt' . ($dupe?', '.$dupe.' bereits vorhanden':'') . ($invalid?', '.$invalid.' ungültig':'') . '. Es wurde KEINE E-Mail versendet.</p></div>';
    }
    // send
    if (isset($_POST['ajja_send']) && check_admin_referer('ajja_send_nl')) {
        $subject = sanitize_text_field(wp_unslash($_POST['nl_subject'] ?? ''));
        $body = wpautop(wp_kses_post(wp_unslash($_POST['nl_body'] ?? '')));
        $subs = $wpdb->get_results("SELECT email,name FROM $t WHERE status='active'");
        $sent = 0;
        $html = '<div style="font-family:Arial,sans-serif;max-width:600px;margin:auto"><div style="background:#15294d;color:#fff;padding:22px 28px;font-size:20px;font-weight:bold">AJJA</div><div style="padding:28px;font-size:15px;line-height:1.7;color:#1a1a2e">' . $body . '</div><div style="padding:16px 28px;color:#888;font-size:12px">AJJA · Ankauf &amp; Sofort-Auszahlung</div></div>';
        add_filter('wp_mail_content_type', function(){ return 'text/html'; });
        foreach ((array)$subs as $s) { if (@wp_mail($s->email, $subject, $html)) $sent++; }
        remove_all_filters('wp_mail_content_type');
        echo '<div class="notice notice-success"><p>Newsletter an ' . $sent . ' Empfänger gesendet.</p></div>';
    }
    if (isset($_GET['del_sub']) && check_admin_referer('ajja_delsub_' . intval($_GET['del_sub']))) {
        $wpdb->delete($t, array('id'=>intval($_GET['del_sub'])));
    }

    $subs = $wpdb->get_results("SELECT * FROM $t ORDER BY id DESC");
    $active = $wpdb->get_var("SELECT COUNT(*) FROM $t WHERE status='active'");
    echo '<div class="wrap"><h1>AJJA — Newsletter</h1>';
    echo '<p><strong>' . intval($active) . '</strong> aktive Abonnenten.</p>';

    echo '<div style="display:flex;gap:24px;flex-wrap:wrap;align-items:flex-start">';
    // compose
    echo '<div style="flex:1;min-width:340px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:20px">';
    echo '<h2 style="margin-top:0">Newsletter senden</h2><form method="post">';
    wp_nonce_field('ajja_send_nl');
    echo '<p><label><strong>Betreff</strong><br><input type="text" name="nl_subject" class="large-text" required></label></p>';
    echo '<p><label><strong>Text</strong></label></p>';
    wp_editor('', 'ajjanlbody', array('textarea_name'=>'nl_body','textarea_rows'=>10,'media_buttons'=>false,'teeny'=>true,'tinymce'=>array('toolbar1'=>'bold,italic,underline,bullist,numlist,link,unlink,undo,redo')));
    echo '<p style="margin-top:12px"><button class="button button-primary" name="ajja_send" value="1" onclick="return confirm(\'Newsletter jetzt an alle aktiven Abonnenten senden?\')">Jetzt senden</button></p>';
    echo '</form></div>';
    // import + list
    echo '<div style="width:360px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:20px">';
    echo '<h2 style="margin-top:0">Adressen importieren (ohne E-Mail)</h2>';
    echo '<form method="post"><p style="color:#666;font-size:13px">Eine E-Mail pro Zeile, optional ", Name". Es wird KEINE Mail versendet.</p>';
    wp_nonce_field('ajja_add', '_wpnonce_add');
    echo '<textarea name="ajja_add_emails" rows="5" class="large-text" placeholder="max@beispiel.ch, Max Muster"></textarea>';
    echo '<p><button class="button" name="ajja_add_subs" value="1">Zur Liste hinzufügen</button></p></form><hr>';
    echo '<h3>Abonnenten</h3><div style="max-height:320px;overflow:auto">';
    if (!$subs) echo '<p>Noch keine.</p>';
    foreach ((array)$subs as $s) {
        $d = wp_nonce_url(admin_url('admin.php?page=ajja-newsletter&del_sub=' . $s->id), 'ajja_delsub_' . $s->id);
        echo '<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #eee;font-size:13px"><span>' . esc_html($s->email) . '</span><a href="' . esc_url($d) . '" style="color:#b32d2e">×</a></div>';
    }
    echo '</div></div>';
    echo '</div></div>';
}

function ajja_admin_inhalte() {
    if (isset($_POST['ajja_save_content']) && check_admin_referer('ajja_content')) {
        $c = get_option('ajja_content', array()); if (!is_array($c)) $c = array();
        foreach (array('hero_title','hero_sub','surcharge_1','surcharge_2','surcharge_3','contact_email','contact_phone','contact_text','impressum','agb','datenschutz') as $k) {
            if (isset($_POST[$k])) $c[$k] = ($k==='hero_title'||$k==='impressum'||$k==='agb'||$k==='datenschutz'||$k==='hero_sub'||$k==='contact_text') ? sanitize_textarea_field(wp_unslash($_POST[$k])) : sanitize_text_field(wp_unslash($_POST[$k]));
        }
        // FAQ
        $faq = array();
        if (!empty($_POST['faq_q']) && is_array($_POST['faq_q'])) {
            foreach ($_POST['faq_q'] as $i => $q) {
                $q = sanitize_text_field(wp_unslash($q));
                $a = sanitize_textarea_field(wp_unslash($_POST['faq_a'][$i] ?? ''));
                if ($q !== '') $faq[] = array('q'=>$q,'a'=>$a);
            }
        }
        $c['faq'] = $faq;
        update_option('ajja_content', $c);
        echo '<div class="notice notice-success"><p>Inhalte gespeichert.</p></div>';
    }
    $faq = ajja_opt('faq'); if (!is_array($faq)) $faq = array();
    $faq[] = array('q'=>'','a'=>''); // one empty row to add
    echo '<div class="wrap"><h1>AJJA — Inhalte bearbeiten</h1>';
    echo '<p>Hier können Sie die Texte der Website anpassen, ohne das Design zu verändern.</p>';
    echo '<form method="post" style="max-width:760px">';
    wp_nonce_field('ajja_content');

    echo '<h2>Startseite</h2>';
    echo '<table class="form-table">';
    echo '<tr><th>Titel (2 Zeilen)</th><td><textarea name="hero_title" rows="2" class="large-text">' . esc_textarea(ajja_opt('hero_title')) . '</textarea></td></tr>';
    echo '<tr><th>Untertitel</th><td><textarea name="hero_sub" rows="3" class="large-text">' . esc_textarea(ajja_opt('hero_sub')) . '</textarea></td></tr>';
    echo '<tr><th>Aufschlag Tag 1–5 (%)</th><td><input type="text" name="surcharge_1" value="' . esc_attr(ajja_opt('surcharge_1')) . '" class="small-text"></td></tr>';
    echo '<tr><th>Aufschlag Tag 6–10 (%)</th><td><input type="text" name="surcharge_2" value="' . esc_attr(ajja_opt('surcharge_2')) . '" class="small-text"></td></tr>';
    echo '<tr><th>Aufschlag Tag 11–14 (%)</th><td><input type="text" name="surcharge_3" value="' . esc_attr(ajja_opt('surcharge_3')) . '" class="small-text"></td></tr>';
    echo '</table>';

    echo '<h2>Kontakt</h2><table class="form-table">';
    echo '<tr><th>E-Mail</th><td><input type="text" name="contact_email" value="' . esc_attr(ajja_opt('contact_email')) . '" class="regular-text"></td></tr>';
    echo '<tr><th>Telefon</th><td><input type="text" name="contact_phone" value="' . esc_attr(ajja_opt('contact_phone')) . '" class="regular-text"></td></tr>';
    echo '<tr><th>Kontakt-Text</th><td><textarea name="contact_text" rows="2" class="large-text">' . esc_textarea(ajja_opt('contact_text')) . '</textarea></td></tr>';
    echo '</table>';

    echo '<h2>FAQ</h2><div id="ajja-faq">';
    foreach ($faq as $item) {
        echo '<p><input type="text" name="faq_q[]" value="' . esc_attr($item['q']) . '" class="large-text" placeholder="Frage"></p>';
        echo '<p><textarea name="faq_a[]" rows="2" class="large-text" placeholder="Antwort">' . esc_textarea($item['a']) . '</textarea></p><hr>';
    }
    echo '</div><p><em>Leere Frage = wird ignoriert. Für eine neue Frage einfach die letzte (leere) ausfüllen und speichern.</em></p>';

    echo '<h2>Rechtliche Texte</h2><table class="form-table">';
    echo '<tr><th>Impressum</th><td><textarea name="impressum" rows="6" class="large-text">' . esc_textarea(ajja_opt('impressum')) . '</textarea></td></tr>';
    echo '<tr><th>AGB</th><td><textarea name="agb" rows="6" class="large-text">' . esc_textarea(ajja_opt('agb')) . '</textarea></td></tr>';
    echo '<tr><th>Datenschutz</th><td><textarea name="datenschutz" rows="6" class="large-text">' . esc_textarea(ajja_opt('datenschutz')) . '</textarea></td></tr>';
    echo '</table>';

    echo '<p><button class="button button-primary button-large" name="ajja_save_content" value="1">Alle Inhalte speichern</button></p>';
    echo '</form></div>';
}
