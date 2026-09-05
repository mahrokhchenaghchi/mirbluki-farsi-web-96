/**
 * Builds a fully self-contained, single-file digital business card:
 *   public/javad-mirbolouki-card.html
 * Everything is inlined (fonts, photos, logos, QR-SVGs, CSS, JS) so the file
 * opens and works identically on Android, iOS, Windows, macOS — no internet
 * needed to render; links need internet only when followed.
 */
const fs = require("fs");
const path = require("path");
const crypto = require("crypto");
const React = require("react");
const { renderToStaticMarkup } = require("react-dom/server");
const sharp = require("sharp");
const { QRCodeSVG } = require("qrcode.react");
const lucide = require("lucide-react");

const ROOT = path.join(__dirname, "..");
const OUT = path.join(ROOT, "public", "javad-mirbolouki-card.html");

const e = React.createElement;

/* ---------------- icons (rendered once, reused) ---------------- */
function icon(name, color) {
  const C = lucide[name];
  let svg = renderToStaticMarkup(e(C, { color: color || "currentColor", strokeWidth: 2.2 }));
  svg = svg.replace('width="24"', 'width="1em"').replace('height="24"', 'height="1em"');
  // strip xmlns noise is fine to keep; ensure inherits size via CSS
  return svg;
}
const I = Object.fromEntries(
  [
    "Globe", "Instagram", "Send", "MessageCircle", "MessageSquare", "BookOpen",
    "Plane", "Sparkles", "ScanLine", "Wifi", "UserPlus", "Share2", "RefreshCw",
    "Flower2", "Library", "CalendarRange", "BarChart3", "HeartHandshake",
    "NotebookPen", "Phone",
  ].map((n) => [n, icon(n)])
);

/* ---------------- data URIs ---------------- */
async function imgDataURI(file, size, quality) {
  const buf = await sharp(file)
    .resize(size, size, { fit: "cover", position: "attention" })
    .jpeg({ quality, mozjpeg: true })
    .toBuffer();
  return `data:image/jpeg;base64,${buf.toString("base64")}`;
}
async function patternDataURI(file) {
  const buf = await sharp(file).resize(760, null).jpeg({ quality: 52, mozjpeg: true }).toBuffer();
  return `data:image/jpeg;base64,${buf.toString("base64")}`;
}
function fontDataURI(rel) {
  const buf = fs.readFileSync(path.join(ROOT, rel));
  return `data:font/woff2;base64,${buf.toString("base64")}`;
}

/* ---------------- QR (real, verified) ---------------- */
function qrSvg(url) {
  let svg = renderToStaticMarkup(
    e(QRCodeSVG, { value: url, size: 220, bgColor: "#ffffff", fgColor: "#0B2E59", level: "M", includeMargin: false })
  );
  svg = svg.replace('width="220"', 'width="100%"').replace('height="220"', 'height="100%"');
  return svg;
}

(async () => {
  const photo = await imgDataURI(path.join(ROOT, "src/assets/dr-javad-mirbluki.jpg"), 560, 84);
  const logo = await imgDataURI(path.join(ROOT, "src/assets/joma-logo.jpg"), 560, 88);
  const pattern = await patternDataURI(path.join(ROOT, "src/assets/persian-pattern-bg.jpg"));
  const fontFa = fontDataURI("node_modules/@fontsource-variable/vazirmatn/files/vazirmatn-arabic-wght-normal.woff2");
  const fontEn = fontDataURI("node_modules/@fontsource-variable/vazirmatn/files/vazirmatn-latin-wght-normal.woff2");
  const qrFront = qrSvg("https://mirbolouki.com");
  const qrBack = qrSvg("https://joma.mirbolouki.com");

  const VCARD = JSON.stringify(
    [
      "BEGIN:VCARD",
      "VERSION:3.0",
      "N:میربلوکی;جواد;;;",
      "FN:جواد میربلوکی | Javad Mirbolouki",
      "TITLE:روانشناس، زوج‌درمانگر و مشاور تخصصی روابط",
      "ORG:Mirbolouki.com ; Joma Planner",
      "TEL;TYPE=CELL,VOICE:+98996797947",
      "URL;TYPE=WORK:https://mirbolouki.com",
      "URL:https://joma.mirbolouki.com",
      "item1.URL:https://instagram.com/javad_mirbolouki",
      "item1.X-ABLABEL:Instagram",
      "item2.URL:https://instagram.com/joma.mirbolouki",
      "item2.X-ABLABEL:Instagram Joma",
      "END:VCARD",
    ].join("\r\n")
  );

  const html = `<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>\u06A9\u0627\u0631\u062A \u0648\u06CC\u0632\u06CC\u062A \u062F\u06CC\u062C\u06CC\u062A\u0627\u0644 | \u062C\u0648\u0627\u062F \u0645\u06CC\u0631\u0628\u0644\u0648\u06A9\u06CC \u00D7 \u062C\u0648\u0645\u0627</title>
<style>
@font-face{font-family:'Vazirmatn';font-style:normal;font-weight:100 900;font-display:swap;src:url(${fontFa}) format('woff2');unicode-range:U+0600-06FF,U+0750-077F,U+08A0-08FF,U+FB50-FDFF,U+FE70-FEFF,U+200C-200D,U+0660-0669,U+06F0-06F9;}
@font-face{font-family:'Vazirmatn';font-style:normal;font-weight:100 900;font-display:swap;src:url(${fontEn}) format('woff2');unicode-range:U+0000-00FF,U+2000-206F;}
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html,body{min-height:100%}
body{font-family:'Vazirmatn',Tahoma,Arial,sans-serif;background:#050e1d;color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 14px;position:relative;overflow-x:hidden}
.blob{position:fixed;border-radius:50%;filter:blur(90px);pointer-events:none;z-index:0}
.b1{width:34rem;height:34rem;top:-8rem;right:-10%;background:rgba(22,115,232,.25);animation:da 16s ease-in-out infinite}
.b2{width:30rem;height:30rem;bottom:-15%;left:-8%;background:rgba(15,208,192,.2);animation:db 21s ease-in-out infinite}
.b3{width:26rem;height:26rem;left:50%;top:33%;background:rgba(242,193,78,.1);animation:dc 26s ease-in-out infinite}
.pattern-bg{position:fixed;inset:0;background-image:url(${pattern});background-size:48rem;background-position:center;opacity:.05;pointer-events:none;z-index:0}
@keyframes da{0%,100%{transform:translate(-6%,-4%) scale(1)}50%{transform:translate(7%,6%) scale(1.18)}}
@keyframes db{0%,100%{transform:translate(6%,8%) scale(1.12)}50%{transform:translate(-8%,-6%) scale(.94)}}
@keyframes dc{0%,100%{transform:translate(0,6%) scale(.95)}50%{transform:translate(4%,-8%) scale(1.1)}}
@keyframes enter{from{opacity:0;transform:translateY(34px) rotateX(10deg) scale(.96)}to{opacity:1;transform:translateY(0) rotateX(0) scale(1)}}
@keyframes floaty{0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)}}
main{position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;width:100%;max-width:44rem}
.masthead{text-align:center;margin-bottom:18px}
.pill{display:inline-flex;align-items:center;gap:8px;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.05);border-radius:999px;padding:6px 16px;font-size:12px;color:rgba(255,255,255,.7);font-weight:500}
.pill svg{color:#F2C14E}
h1.title{font-size:26px;font-weight:800;margin-top:12px}
h1.title em{font-style:normal;color:#F2C14E;margin:0 4px}
.hint{display:flex;align-items:center;gap:8px;background:#F2C14E;color:#08203f;font-size:12px;font-weight:700;border-radius:999px;padding:7px 16px;margin-bottom:18px;box-shadow:0 8px 24px -8px rgba(242,193,78,.5);animation:floaty 2.6s ease-in-out infinite;transition:opacity .5s}
.scene{width:100%;max-width:40rem;perspective:1800px;animation:enter .9s cubic-bezier(.22,1,.36,1) both}
.card{position:relative;width:100%;aspect-ratio:7/4;transform-style:preserve-3d;cursor:pointer;user-select:none;font-size:clamp(10.5px,2.45vw,16px);outline:none}
.card:focus-visible{box-shadow:0 0 0 3px rgba(242,193,78,.6);border-radius:1.6em}
.sheen{position:absolute;inset:0;z-index:30;border-radius:1.6em;pointer-events:none;background:radial-gradient(38em circle at var(--mx,50%) var(--my,50%),rgba(255,255,255,.14),transparent 55%)}
.face{position:absolute;inset:0;border-radius:1.6em;overflow:hidden;backface-visibility:hidden;-webkit-backface-visibility:hidden;box-shadow:0 45px 90px -25px rgba(0,0,0,.75)}
.face.back{transform:rotateY(180deg)}
.f-front{background:linear-gradient(135deg,hsl(216,72%,15%) 0%,hsl(210,100%,37%) 52%,hsl(190,95%,37%) 100%)}
.f-back{background:linear-gradient(215deg,hsl(216,70%,14%) 0%,hsl(196,90%,32%) 50%,hsl(170,75%,30%) 100%)}
.face .pat{position:absolute;inset:0;background-image:url(${pattern});background-size:cover;background-position:center;opacity:.05}
.face .glow{position:absolute;border-radius:50%;filter:blur(24px)}
.wm{position:absolute;font-weight:900;line-height:1;color:rgba(255,255,255,.05);pointer-events:none;user-select:none;direction:ltr}
.frame{position:absolute;inset:.55em;border:1px solid rgba(255,255,255,.15);border-radius:1.1em;pointer-events:none}
.corner{position:absolute;width:1.6em;height:1.6em;pointer-events:none}
.grid{position:relative;display:grid;grid-template-columns:1fr auto;align-items:center;gap:1.3em;height:100%;padding:1.25em 1.6em}
.txtcol{display:flex;flex-direction:column;justify-content:space-between;height:100%;min-width:0;padding:.15em 0}
.brandrow{display:flex;align-items:center;gap:.7em}
.line{height:.1em;width:2.4em;border-radius:99px}
.site-chip{font-size:.68em;font-weight:600;letter-spacing:.22em;color:rgba(255,255,255,.75);direction:ltr}
.name-fa{font-size:1.85em;font-weight:800;line-height:1.15;margin-top:.35em}
.name-en{font-size:.62em;font-weight:700;letter-spacing:.34em;color:rgba(255,255,255,.55);direction:ltr;margin-top:.15em}
.role{font-size:.8em;font-weight:600;color:#F7CE5F;margin-top:.55em}
.quote{display:flex;gap:.5em;align-items:flex-start;margin-top:.7em}
.quote svg{color:#F2C14E;flex-shrink:0;margin-top:.35em}
.quote p{font-size:.68em;line-height:1.9;color:rgba(255,255,255,.88)}
.badges{display:flex;flex-wrap:wrap;gap:.45em;margin-top:.6em}
.badge{display:flex;align-items:center;gap:.4em;border:1px solid rgba(242,193,78,.35);background:rgba(242,193,78,.1);border-radius:99px;padding:.3em .7em;font-size:.6em;font-weight:500;color:#FBE3A6}
.chips{display:flex;flex-wrap:wrap;gap:.4em;margin-top:.7em}
.chip{display:flex;align-items:center;gap:.4em;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.1);border-radius:99px;padding:.34em .75em;font-size:.62em;font-weight:500;color:rgba(255,255,255,.9);text-decoration:none;transition:background .2s}
.chip:hover{background:rgba(255,255,255,.25);color:#fff}
.chip svg{color:#F7CE5F;flex-shrink:0}
.photocol{display:flex;flex-direction:column;align-items:center;gap:.9em}
.ring{background:conic-gradient(from 140deg,#F2C14E,rgba(255,255,255,.35),#3fd8d2,#F2C14E);border-radius:50%;padding:.26em;position:relative}
.ring img{width:7em;height:7em;border-radius:50%;object-fit:cover;border:.16em solid rgba(255,255,255,.7);display:block}
.nfc-badge{position:absolute;bottom:-.35em;left:-.35em;background:#F2C14E;color:#08203f;border-radius:50%;padding:.38em;box-shadow:0 6px 16px rgba(0,0,0,.4)}
.nfc-badge svg{transform:rotate(90deg);display:block}
.qr{background:#fff;border-radius:.9em;padding:.7em;display:flex;flex-direction:column;align-items:center;gap:.35em;box-shadow:0 10px 30px -10px rgba(0,0,0,.55);text-decoration:none;transition:transform .2s}
.qr:hover{transform:scale(1.05)}
.qr .q{width:5.3em;height:5.3em;display:block}
.qr .q svg{width:100%;height:100%;display:block}
.qr span{font-size:.55em;font-weight:700;letter-spacing:.06em;color:rgba(11,46,89,.75);direction:ltr}
/* back specifics */
.tag{font-size:.8em;font-weight:600;color:#8FF0E6;margin-top:.15em}
.tag .sep{color:rgba(255,255,255,.4);margin:0 .3em}
.motto-en{font-size:.58em;font-weight:600;letter-spacing:.3em;color:rgba(255,255,255,.45);direction:ltr;margin-top:.2em}
.desc{font-size:.64em;line-height:1.9;color:rgba(255,255,255,.85);margin-top:.55em;max-width:34em}
.feat{display:grid;grid-template-columns:1fr 1fr;gap:.4em;margin-top:.5em}
.feat span{display:flex;align-items:center;gap:.45em;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.08);border-radius:.6em;padding:.4em .6em;font-size:.57em;font-weight:500;color:rgba(255,255,255,.9)}
.feat svg{color:#8FF0E6;flex-shrink:0}
.tracks{display:flex;flex-wrap:wrap;gap:.4em;margin-top:.5em}
.tracks span{display:flex;align-items:center;gap:.4em;border:1px solid rgba(242,193,78,.4);background:rgba(242,193,78,.1);border-radius:99px;padding:.32em .7em;font-size:.58em;font-weight:600;color:#FBE3A6}
.joma-wm{display:flex;align-items:center;gap:.6em;margin-top:.15em}
.joma-wm img{width:2.1em;height:2.1em;border-radius:50%;object-fit:cover;border:.09em solid rgba(212,175,55,.8);box-shadow:0 6px 20px -6px rgba(0,0,0,.6)}
.joma-wm h2{font-size:1.9em;font-weight:900;line-height:1.1}
.joma-wm small{font-size:.6em;font-weight:700;letter-spacing:.4em;color:rgba(255,255,255,.5);direction:ltr}
.logocol{display:flex;flex-direction:column;align-items:center;gap:.7em}
.ring-teal{background:conic-gradient(from 140deg,#3fd8d2,rgba(255,255,255,.35),#F2C14E,#3fd8d2);border-radius:50%;padding:.22em}
.ring-teal img{width:5.2em;height:5.2em;border-radius:50%;object-fit:cover;border:.14em solid rgba(255,255,255,.7);display:block}
.of-collection{font-size:.52em;font-weight:500;color:rgba(255,255,255,.5)}
/* dock */
.dock{display:flex;flex-wrap:wrap;justify-content:center;gap:10px;margin-top:36px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.06);backdrop-filter:blur(14px);border-radius:16px;padding:12px}
.dock button,.dock a{display:flex;align-items:center;gap:8px;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.1);color:rgba(255,255,255,.9);border-radius:12px;padding:10px 16px;font-family:inherit;font-size:14px;font-weight:500;cursor:pointer;transition:all .2s;text-decoration:none}
.dock button:hover,.dock a:hover{background:rgba(255,255,255,.2);color:#fff}
.dock button:active{transform:scale(.95)}
.dock .gold{border-color:rgba(242,193,78,.4);background:rgba(242,193,78,.15);color:#FBE3A6}
.dock .gold:hover{background:rgba(242,193,78,.25)}
.dock svg{color:#8FF0E6}
.dock .gold svg{color:#F2C14E}
.links{display:flex;flex-wrap:wrap;justify-content:center;gap:8px 20px;margin-top:24px;font-size:14px}
.links a{color:rgba(255,255,255,.6);text-decoration:none;border-bottom:1px dotted transparent}
.links a:hover{color:#F2C14E;border-bottom-color:#F2C14E}
.nfc-note{display:flex;align-items:center;justify-content:center;gap:8px;max-width:28rem;text-align:center;font-size:12px;line-height:1.9;color:rgba(255,255,255,.4);margin-top:26px}
.nfc-note svg{transform:rotate(90deg);flex-shrink:0}
.dl-note{margin-top:14px;font-size:11px;color:rgba(255,255,255,.35);text-align:center}
.toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(80px);background:#0f2547;border:1px solid rgba(255,255,255,.15);color:#fff;padding:10px 20px;border-radius:12px;font-size:13px;opacity:0;transition:all .35s;z-index:50;box-shadow:0 10px 30px rgba(0,0,0,.5)}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
@media (max-width:420px){body{padding:24px 10px}h1.title{font-size:22px}}
</style>
</head>
<body>
<div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div>
<div class="pattern-bg"></div>
<main>
  <header class="masthead">
    <div class="pill">${I.Sparkles} \u06A9\u0627\u0631\u062A \u0648\u06CC\u0632\u06CC\u062A \u062F\u06CC\u062C\u06CC\u062A\u0627\u0644 \u062A\u0639\u0627\u0645\u0644\u06CC <span dir="ltr" style="color:rgba(255,255,255,.4)">DIGITAL CARD</span></div>
    <h1 class="title">\u062C\u0648\u0627\u062F \u0645\u06CC\u0631\u0628\u0644\u0648\u06A9\u06CC <em>\u00D7</em> \u062C\u0648\u0645\u0627</h1>
  </header>

  <div class="hint" id="hint">${I.ScanLine} \u0628\u0631\u0627\u06CC \u062F\u06CC\u062F\u0646 \u067E\u0634\u062A \u06A9\u0627\u0631\u062A\u060C \u0631\u0648\u06CC \u0622\u0646 \u0636\u0631\u0628\u0647 \u0628\u0632\u0646\u06CC\u062F</div>

  <div class="scene" id="scene">
    <div class="card" id="card" role="button" tabindex="0" aria-label="\u06A9\u0627\u0631\u062A \u0648\u06CC\u0632\u06CC\u062A - \u06A9\u0644\u06CC\u06A9 \u06A9\u0646\u06CC\u062F \u062A\u0627 \u0628\u0686\u0631\u062E\u062F">
      <div class="sheen"></div>

      <!-- FRONT : Javad Mirbolouki -->
      <section class="face f-front">
        <div class="pat"></div>
        <div class="glow" style="width:17em;height:17em;left:-7em;top:-7em;background:radial-gradient(circle,rgba(242,193,78,.35),transparent 70%)"></div>
        <div class="glow" style="width:16em;height:16em;right:-5em;bottom:-8em;background:radial-gradient(circle,rgba(56,224,210,.28),transparent 70%)"></div>
        <div class="wm" style="font-size:5.4em;left:.15em;bottom:-.42em;letter-spacing:-.02em">MIRBOLOUKI</div>
        <div class="frame"></div>
        <div class="corner" style="left:.55em;top:.55em;border-right:0;border-bottom:0;border-left:.14em solid rgba(242,193,78,.8);border-top:.14em solid rgba(242,193,78,.8);border-top-left-radius:1.1em"></div>
        <div class="corner" style="right:.55em;bottom:.55em;border-left:0;border-top:0;border-right:.14em solid rgba(242,193,78,.8);border-bottom:.14em solid rgba(242,193,78,.8);border-bottom-right-radius:1.1em"></div>

        <div class="grid">
          <div class="txtcol">
            <div>
              <div class="brandrow"><span class="line" style="background:linear-gradient(to left,transparent,#F2C14E)"></span><span class="site-chip">MIRBOLOUKI.COM</span></div>
              <div class="name-fa">\u062C\u0648\u0627\u062F \u0645\u06CC\u0631\u0628\u0644\u0648\u06A9\u06CC</div>
              <div class="name-en">JAVAD MIRBOLOUKI</div>
              <div class="role">\u0631\u0648\u0627\u0646\u0634\u0646\u0627\u0633 \u00B7 \u0632\u0648\u062C\u200C\u062F\u0631\u0645\u0627\u0646\u06AF\u0631 \u00B7 \u0645\u0634\u0627\u0648\u0631 \u062A\u062E\u0635\u0635\u06CC \u0631\u0648\u0627\u0628\u0637</div>
              <div class="quote">${I.Sparkles}<p>\u00AB\u0631\u0627\u0628\u0637\u0647\u200C\u0647\u0627 \u0628\u0647 \u0628\u0646\u200C\u0628\u0633\u062A \u0646\u0645\u06CC\u200C\u0631\u0633\u0646\u062F\u061B \u0641\u0642\u0637 \u06AF\u0627\u0647\u06CC \u0631\u0627\u0647\u0650 \u062F\u0648\u0628\u0627\u0631\u0647 \u062F\u06CC\u062F\u0646\u0650 \u0647\u0645 \u0631\u0627 \u06AF\u0645 \u0645\u06CC\u200C\u06A9\u0646\u0646\u062F.\u00BB</p></div>
            </div>
            <div>
              <div class="badges">
                <span class="badge">${I.BookOpen} \u0646\u0648\u06CC\u0633\u0646\u062F\u0647\u0654 \u06A9\u062A\u0627\u0628 \u00AB\u0639\u0634\u0642 \u0648 \u0631\u0627\u0628\u0637\u0647\u00BB</span>
                <span class="badge">${I.Plane} \u062E\u0644\u0628\u0627\u0646 \u0647\u0648\u0627\u067E\u06CC\u0645\u0627\u06CC \u0633\u0628\u06A9</span>
              </div>
              <div class="chips">
                <a class="chip" href="https://mirbolouki.com" target="_blank" rel="noreferrer">${I.Globe} <span dir="ltr">Mirbolouki.com</span></a>
                <a class="chip" href="https://instagram.com/javad_mirbolouki" target="_blank" rel="noreferrer">${I.Instagram} <span dir="ltr">@javad_mirbolouki</span></a>
                <a class="chip" href="https://ble.ir/mirbolouki" target="_blank" rel="noreferrer">${I.Send} \u0628\u0644\u0647</a>
                <a class="chip" href="https://rubika.ir/Mirbolouki_com" target="_blank" rel="noreferrer">${I.MessageCircle} \u0631\u0648\u0628\u06CC\u06A9\u0627</a>
                <a class="chip" href="sms:+98996797947">${I.MessageSquare} \u067E\u06CC\u0627\u0645\u06A9</a>
              </div>
            </div>
          </div>
          <div class="photocol">
            <div class="ring">
              <img src="${photo}" alt="\u062C\u0648\u0627\u062F \u0645\u06CC\u0631\u0628\u0644\u0648\u06A9\u06CC" />
              <div class="nfc-badge" title="\u0642\u0627\u0628\u0644 \u0627\u062A\u0635\u0627\u0644 \u0628\u0647 NFC">${I.Wifi}</div>
            </div>
            <a class="qr" href="https://mirbolouki.com" target="_blank" rel="noreferrer" title="\u0628\u0627\u0632 \u06A9\u0631\u062F\u0646 \u0633\u0627\u06CC\u062A">
              <span class="q">${qrFront}</span><span>mirbolouki.com</span>
            </a>
          </div>
        </div>
      </section>

      <!-- BACK : JOMA Planner -->
      <section class="face f-back">
        <div class="pat"></div>
        <div class="glow" style="width:17em;height:17em;right:-7em;top:-7em;background:radial-gradient(circle,rgba(63,216,210,.34),transparent 70%)"></div>
        <div class="glow" style="width:16em;height:16em;left:-5em;bottom:-8em;background:radial-gradient(circle,rgba(242,193,78,.28),transparent 70%)"></div>
        <div class="wm" style="font-size:6.2em;right:.15em;bottom:-.35em;letter-spacing:.04em">JOMA</div>
        <div class="frame"></div>
        <div class="corner" style="right:.55em;top:.55em;border-left:0;border-bottom:0;border-right:.14em solid rgba(63,216,210,.8);border-top:.14em solid rgba(63,216,210,.8);border-top-right-radius:1.1em"></div>
        <div class="corner" style="left:.55em;bottom:.55em;border-right:0;border-top:0;border-left:.14em solid rgba(63,216,210,.8);border-bottom:.14em solid rgba(63,216,210,.8);border-bottom-left-radius:1.1em"></div>

        <div class="grid">
          <div class="txtcol">
            <div>
              <div class="brandrow"><span class="line" style="background:linear-gradient(to left,transparent,#3fd8d2)"></span><span class="site-chip" style="font-size:.66em;letter-spacing:.18em">JOMA.MIRBOLOUKI.COM</span></div>
              <div class="joma-wm">
                <img src="${logo}" alt="\u0644\u0648\u06AF\u0648\u06CC \u062C\u0648\u0645\u0627" />
                <h2>\u062C\u0648\u0645\u0627</h2><small>JOMA</small>
              </div>
              <div class="tag">\u067E\u0644\u0646\u0631 \u0647\u0648\u0634\u0645\u0646\u062F \u0631\u0648\u0627\u0646\u0634\u0646\u0627\u0633\u06CC <span class="sep">|</span> \u0628\u0631\u0646\u0627\u0645\u0647. \u0627\u062C\u0631\u0627. \u0641\u0647\u0645.</div>
              <div class="motto-en">PLAN \u00B7 DO \u00B7 UNDERSTAND</div>
              <p class="desc">\u067E\u0644\u0646\u0631 \u0647\u0648\u0634\u0645\u0646\u062F \u062E\u0648\u062F\u0645\u062F\u06CC\u0631\u06CC\u062A\u06CC \u0631\u0648\u0627\u0646\u0634\u0646\u0627\u062E\u062A\u06CC\u061B \u062B\u0628\u062A \u0641\u0639\u0627\u0644\u06CC\u062A \u0648 \u062D\u0627\u0644 \u0631\u0648\u0632\u0627\u0646\u0647 \u0648 \u06AF\u0632\u0627\u0631\u0634 \u0628\u0631 \u067E\u0627\u06CC\u0647\u0654 \u062F\u0627\u062F\u0647\u0654 \u0648\u0627\u0642\u0639\u06CC</p>
            </div>
            <div>
              <div class="feat">
                <span>${I.Flower2} \u062B\u0628\u062A \u062D\u0627\u0644 \u0631\u0648\u0632\u0627\u0646\u0647 \u0628\u0627 \u0627\u0633\u062A\u06CC\u06A9\u0631</span>
                <span>${I.Library} \u06A9\u062A\u0627\u0628\u062E\u0627\u0646\u0647\u0654 \u06F1\u06F0\u06F7+ \u062A\u0645\u0631\u06CC\u0646 \u0622\u0645\u0627\u062F\u0647</span>
                <span>${I.CalendarRange} \u062F\u0648\u0631\u0647\u200C\u0647\u0627\u06CC \u0634\u0645\u0633\u06CC \u0645\u0633\u062A\u0642\u0644</span>
                <span>${I.BarChart3} \u06AF\u0632\u0627\u0631\u0634 \u0627\u0632 \u062F\u0627\u062F\u0647\u0654 \u0648\u0627\u0642\u0639\u06CC</span>
              </div>
              <div class="tracks">
                <span>${I.HeartHandshake} \u0632\u0648\u062C\u200C\u062F\u0631\u0645\u0627\u0646\u06CC \u00B7 \u06F3\u06F5 \u062A\u0645\u0631\u06CC\u0646 \u062A\u062E\u0635\u0635\u06CC</span>
                <span>${I.NotebookPen} \u0637\u0631\u062D\u0648\u0627\u0631\u0647\u200C\u062F\u0631\u0645\u0627\u0646\u06CC \u00B7 \u06F2\u06F7 \u062A\u0645\u0631\u06CC\u0646 \u062A\u062E\u0635\u0635\u06CC</span>
              </div>
            </div>
          </div>
          <div class="logocol">
            <div class="ring-teal"><img src="${logo}" alt="\u0644\u0648\u06AF\u0648\u06CC \u067E\u0644\u0646\u0631 \u062C\u0648\u0645\u0627" /></div>
            <a class="qr" href="https://joma.mirbolouki.com" target="_blank" rel="noreferrer" title="\u0628\u0627\u0632 \u06A9\u0631\u062F\u0646 \u062C\u0648\u0645\u0627">
              <span class="q">${qrBack}</span><span>joma.mirbolouki.com</span>
            </a>
            <a class="chip" href="https://instagram.com/joma.mirbolouki" target="_blank" rel="noreferrer">${I.Instagram} <span dir="ltr">@joma.mirbolouki</span></a>
            <span class="of-collection">\u0627\u0632 \u0645\u062C\u0645\u0648\u0639\u0647\u0654 Mirbolouki.com</span>
          </div>
        </div>
      </section>
    </div>
  </div>

  <div class="dock">
    <button type="button" class="gold" onclick="flip()">${I.RefreshCw} \u0628\u0631\u06AF\u0631\u062F\u0627\u0646\u062F\u0646 \u06A9\u0627\u0631\u062A</button>
    <button type="button" onclick="saveContact()">${I.UserPlus} \u0630\u062E\u06CC\u0631\u0647 \u0645\u062E\u0627\u0637\u0628</button>
    <button type="button" onclick="shareCard()">${I.Share2} \u0627\u0634\u062A\u0631\u0627\u06A9\u200C\u06AF\u0630\u0627\u0631\u06CC</button>
    <a href="tel:+98996797947">${I.Phone} \u062A\u0645\u0627\u0633</a>
  </div>

  <nav class="links">
    <a href="https://ble.ir/mirbolouki" target="_blank" rel="noreferrer">\u0628\u0644\u0647</a>
    <a href="https://rubika.ir/Mirbolouki_com" target="_blank" rel="noreferrer">\u0631\u0648\u0628\u06CC\u06A9\u0627</a>
    <a href="sms:+98996797947">\u067E\u06CC\u0627\u0645\u06A9</a>
    <a href="https://instagram.com/javad_mirbolouki" target="_blank" rel="noreferrer">\u0627\u06CC\u0646\u0633\u062A\u0627\u06AF\u0631\u0627\u0645 \u062C\u0648\u0627\u062F</a>
    <a href="https://instagram.com/joma.mirbolouki" target="_blank" rel="noreferrer">\u0627\u06CC\u0646\u0633\u062A\u0627\u06AF\u0631\u0627\u0645 \u062C\u0648\u0645\u0627</a>
    <a href="https://mirbolouki.com" target="_blank" rel="noreferrer" dir="ltr">Mirbolouki.com</a>
    <a href="https://joma.mirbolouki.com" target="_blank" rel="noreferrer" dir="ltr">joma.mirbolouki.com</a>
  </nav>

  <p class="nfc-note">${I.Wifi} \u06A9\u0627\u0631\u062A \u0641\u06CC\u0632\u06CC\u06A9\u06CC NFC \u062F\u0627\u0631\u06CC\u062F\u061F \u0622\u062F\u0631\u0633 \u0627\u06CC\u0646 \u0635\u0641\u062D\u0647 \u0631\u0627 \u0631\u0648\u06CC \u0622\u0646 \u0628\u0646\u0648\u06CC\u0633\u06CC\u062F \u062A\u0627 \u0628\u0627 \u0646\u0632\u062F\u06CC\u06A9 \u06A9\u0631\u062F\u0646 \u06AF\u0648\u0634\u06CC\u060C \u0647\u0645\u06CC\u0646 \u06A9\u0627\u0631\u062A \u0628\u0627\u06CC\u0633\u062A\u06CC \u0628\u0627\u0632 \u0634\u0648\u062F.</p>
</main>
<div class="toast" id="toast"></div>

<script>
(function(){
  var card=document.getElementById('card'),scene=document.getElementById('scene'),hint=document.getElementById('hint');
  var flipped=false,tx=0,ty=0,cx=0,cy=0;
  function loop(){cx+=(tx-cx)*0.11;cy+=(ty-cy)*0.11;card.style.transform='rotateY('+((flipped?180:0)+cy)+'deg) rotateX('+cx+'deg)';requestAnimationFrame(loop);}
  loop();
  scene.addEventListener('pointermove',function(ev){var r=scene.getBoundingClientRect();var px=(ev.clientX-r.left)/r.width-0.5,py=(ev.clientY-r.top)/r.height-0.5;ty=px*11;tx=-py*9;card.style.setProperty('--mx',(px+0.5)*100+'%');card.style.setProperty('--my',(py+0.5)*100+'%');});
  scene.addEventListener('pointerleave',function(){tx=0;ty=0;});
  window.flip=function(){flipped=!flipped;hint.style.opacity='0';if(navigator.vibrate){try{navigator.vibrate(15);}catch(_){}}};
  card.addEventListener('click',function(ev){if(ev.target.closest('a,button'))return;window.flip();});
  card.addEventListener('keydown',function(ev){if(ev.key==='Enter'||ev.key===' '){ev.preventDefault();window.flip();}});
  window.toast=function(msg){var t=document.getElementById('toast');t.textContent=msg;t.className='toast show';setTimeout(function(){t.className='toast';},2600);};
  window.saveContact=function(){
    var vcf=${VCARD};
    var blob=new Blob([vcf],{type:'text/vcard;charset=utf-8'});
    var url=URL.createObjectURL(blob);
    var a=document.createElement('a');a.href=url;a.download='Javad-Mirbolouki.vcf';document.body.appendChild(a);a.click();
    setTimeout(function(){URL.revokeObjectURL(url);a.remove();},800);
    toast('\u0645\u062E\u0627\u0637\u0628 \u0630\u062E\u06CC\u0631\u0647 \u0634\u062F \u2014 \u0641\u0627\u06CC\u0644 vCard \u062F\u0627\u0646\u0644\u0648\u062F \u0634\u062F');
  };
  window.shareCard=function(){
    var data={title:'\u06A9\u0627\u0631\u062A \u0648\u06CC\u0632\u06CC\u062A \u062C\u0648\u0627\u062F \u0645\u06CC\u0631\u0628\u0644\u0648\u06A9\u06CC',text:'\u06A9\u0627\u0631\u062A \u0648\u06CC\u0632\u06CC\u062A \u062F\u06CC\u062C\u06CC\u062A\u0627\u0644 \u062C\u0648\u0627\u062F \u0645\u06CC\u0631\u0628\u0644\u0648\u06A9\u06CC \u0648 \u067E\u0644\u0646\u0631 \u062C\u0648\u0645\u0627',url:'https://mirbolouki.com'};
    if(navigator.share){navigator.share(data).catch(function(){});}
    else if(navigator.clipboard){navigator.clipboard.writeText(data.url).then(function(){toast('\u0644\u06CC\u0646\u06A9 \u06A9\u067E\u06CC \u0634\u062F');},function(){fallbackCopy(data.url);});}
    else{fallbackCopy(data.url);}
  };
  function fallbackCopy(text){var ta=document.createElement('textarea');ta.value=text;document.body.appendChild(ta);ta.select();try{document.execCommand('copy');toast('\u0644\u06CC\u0646\u06A9 \u06A9\u067E\u06CC \u0634\u062F');}catch(_){toast(text);}ta.remove();}
})();
</script>
</body>
</html>`;

  fs.mkdirSync(path.dirname(OUT), { recursive: true });
  fs.writeFileSync(OUT, html);
  const kb = (fs.statSync(OUT).size / 1024).toFixed(0);
  const sha = crypto.createHash("sha1").update(html).digest("hex").slice(0, 8);
  console.log(`OK  ${OUT}  ${kb} KB  sha:${sha}`);
})();
