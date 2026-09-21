#!/usr/bin/env python3
"""Build a clean RTL Persian PDF for Erfan using fpdf2 + Vazirmatn.
Fixes: correct fill-before-text ordering; cover fits on one page; page numbers."""
import re, os
from fpdf import FPDF
from fpdf.enums import XPos, YPos
import arabic_reshaper
from bidi.algorithm import get_display

OUT_PDF = '/home/user/mirbluki-farsi-web-96/Erfan-Kamalzadeh-Exercises.pdf'
FONT_DIR = '/home/user/fonts'
MD_PATH = '/home/user/mirbluki-farsi-web-96/erfan-kamalzadeh-exercises.md'

EMOJI_MAP = {
    '🦉':'☻','🌬':'≈','🔨':'▲','📏':'|','👁':'◉','✉':'✉','✅':'✓',
    '🧵':'~','🍽':'◠','🔖':'❖','🪁':'✦','🥚':'○','🐣':'●','🌾':'✦',
    '💧':'◆','🏠':'■','🛁':'❀','🫧':'◦','🪞':'◎','☀️':'☀','🫂':'♥',
    '🕯':'⚘','🌙':'☾','🌱':'✿','🍼':'♡','🪟':'▢','📝':'✎','🔑':'⌘','🧭':'◈'
}

def fa(s):
    if s is None: return ''
    s = str(s)
    if not s: return ''
    for k,v in EMOJI_MAP.items(): s = s.replace(k, v)
    s = s.replace('\ufe0f','').replace('🚿','دوش')
    if re.search(r'[\u0600-\u06FF]', s):
        return get_display(arabic_reshaper.reshape(s))
    return s

def to_fa(n):
    return str(n).translate(str.maketrans('0123456789','۰۱۲۳۴۵۶۷۸۹'))

class PDF(FPDF):
    def __init__(self):
        super().__init__(orientation='P', unit='mm', format='A4')
        self.set_auto_page_break(auto=True, margin=16)
        self.set_margins(16, 16, 16)
        self.add_font('Fa', '', os.path.join(FONT_DIR,'Vazirmatn-Regular.ttf'))
        self.add_font('Fa', 'B', os.path.join(FONT_DIR,'Vazirmatn-Bold.ttf'))
        self.add_font('FaM', '', os.path.join(FONT_DIR,'Vazirmatn-Medium.ttf'))
        self.add_font('FaSB', '', os.path.join(FONT_DIR,'Vazirmatn-SemiBold.ttf'))
        self.add_font('FaEB', '', os.path.join(FONT_DIR,'Vazirmatn-ExtraBold.ttf'))
        self.add_font('Dej', '', '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf')
        self.add_font('Dej', 'B', '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf')
        self.set_fallback_fonts(['Dej'])
        self._total = None

    def use_font(self, style='', size=11):
        key = {'':'Fa','B':'Fa','M':'FaM','SB':'FaSB','EB':'FaEB'}.get(style,'Fa')
        self.set_font(key, '' if style in ('M','SB','EB') else style, size)

    def header(self):
        if self.page_no() == 1: return
        self.use_font('', 8); self.set_text_color(138,127,115)
        self.cell(0, 5, fa('بسته تمرین‌های شخصی‌شده · عرفان کمال‌زاده'), align='C',
                  new_x=XPos.LMARGIN, new_y=YPos.NEXT)
        self.ln(2)

    def footer(self):
        self.set_y(-12)
        self.use_font('', 8); self.set_text_color(138,127,115)
        # We'll use a special placeholder that fpdf2 replaces; but fpdf2 replaces {nb} with Latin digits.
        # Simpler: just show "صفحه N" without total (clean, professional).
        self.cell(0, 5, fa(f'صفحه {to_fa(self.page_no())}'), align='C')

    def h1(self, t):
        self.use_font('EB', 18); self.set_text_color(46,58,72)
        self.multi_cell(0, 10, fa(t), align='R', new_x=XPos.LMARGIN, new_y=YPos.NEXT); self.ln(1)

    def h2(self, t):
        if self.get_y() > 235: self.add_page()
        self.ln(2); self.use_font('B', 13); self.set_text_color(63,111,160)
        self.multi_cell(0, 8, fa(t), align='R', new_x=XPos.LMARGIN, new_y=YPos.NEXT)
        y = self.get_y(); self.set_draw_color(232,182,74); self.set_line_width(0.7)
        self.line(self.l_margin, y+0.5, self.w - self.r_margin, y+0.5); self.ln(4)

    def para(self, text, size=10.5, bold=False, color=(42,42,51), align='J', lh=6.5):
        self.use_font('B' if bold else '', size); self.set_text_color(*color)
        self.multi_cell(0, lh, fa(text), align=align, new_x=XPos.LMARGIN, new_y=YPos.NEXT)

    def label_para(self, label, rest):
        self.use_font('B', 10); self.set_text_color(63,111,160); self.write(6, fa(label + ':'))
        self.use_font('', 10); self.set_text_color(42,42,51)
        self.multi_cell(0, 6, fa(rest), align='J', new_x=XPos.LMARGIN, new_y=YPos.NEXT); self.ln(0.5)

    def bullet_list(self, items, size=10):
        self.use_font('', size); self.set_text_color(42,42,51)
        for it in items:
            self.set_x(self.r_margin + 3); self.cell(4, 6, fa('•'))
            w = self.w - self.l_margin - self.r_margin - 7
            self.multi_cell(w, 6, fa(it), align='J', new_x=XPos.LMARGIN, new_y=YPos.NEXT)

    def numbered_list(self, items, size=10.5):
        self.use_font('', size); self.set_text_color(42,42,51)
        for i,it in enumerate(items, 1):
            self.set_x(self.r_margin + 3)
            self.use_font('B', size); self.set_text_color(79,111,160)
            self.cell(7, 7, fa(to_fa(i) + '.'))
            self.use_font('', size); self.set_text_color(42,42,51)
            w = self.w - self.l_margin - self.r_margin - 10
            self.multi_cell(w, 7, fa(it), align='J', new_x=XPos.LMARGIN, new_y=YPos.NEXT)

    # boxes: compute height with a dry-run, then draw fill, then draw text
    def _measure(self, text, size, lh, pad_w, font_style=''):
        """Return height needed for multi_cell text."""
        x = self.get_x(); y = self.get_y()
        self.use_font(font_style, size)
        # Use multi_cell dry-run by enabling and checking
        # trick: render to a dummy page? simpler: estimate by chars per line (avg ~43 chars per line for 180mm width at 10.5pt)
        # We'll use actual multi_cell in a 'dry' mode by moving back
        start_y = self.get_y()
        page_before = self.page_no()
        # temporarily disable page break and write to off-screen? Instead we estimate precisely by duplicating a simple measure
        # Use write-then-erase: multi_cell returns the number of lines only when used with split_only
        lines = self.multi_cell(0, lh, fa(text), align='J', dry_run=True, output='LINES')
        return len(lines) * lh

    def note_box(self, text, bg, border, rbar, text_color=(74,66,54), size=10.5):
        if self.get_y() > 245: self.add_page()
        x = self.l_margin; w = self.w - self.l_margin - self.r_margin; pad = 4
        lh = 6.3
        h = self._measure(text, size, lh, w - 2*pad) + 2*pad + 1
        y0 = self.get_y()
        self.set_fill_color(*bg); self.set_draw_color(*border); self.set_line_width(0.3)
        self.rect(x, y0, w, h, 'DF')
        self.set_fill_color(*rbar); self.rect(x+w-2.5, y0, 2.5, h, 'F')
        self.set_xy(x + pad, y0 + pad); self.set_text_color(*text_color)
        self.use_font('', size)
        self.multi_cell(w - 2*pad, lh, fa(text), align='J', new_x=XPos.LMARGIN, new_y=YPos.NEXT)
        self.set_xy(x, y0 + h + 2); self.ln(1)

    def gold_card(self, big, small):
        if self.get_y() > 220: self.add_page()
        x = self.l_margin; w = self.w - self.l_margin - self.r_margin; pad = 7
        # measure
        self.use_font('B', 13)
        big_lines = self.multi_cell(w-2*pad, 8.5, fa(big), align='C', dry_run=True, output='LINES')
        self.use_font('', 10)
        sml_lines = self.multi_cell(w-2*pad, 6.5, fa(small), align='C', dry_run=True, output='LINES')
        h = len(big_lines)*8.5 + len(sml_lines)*6.5 + 2*pad + 2
        y0 = self.get_y()
        self.set_fill_color(255,247,234); self.set_draw_color(229,213,180); self.set_line_width(0.4)
        self.rect(x, y0, w, h, 'DF')
        self.set_xy(x+pad, y0+pad); self.use_font('B',13); self.set_text_color(46,58,72)
        self.multi_cell(w-2*pad, 8.5, fa(big), align='C', new_x=XPos.LMARGIN, new_y=YPos.NEXT)
        self.use_font('',10); self.set_text_color(138,127,115)
        self.multi_cell(w-2*pad, 6.5, fa(small), align='C', new_x=XPos.LMARGIN, new_y=YPos.NEXT)
        self.set_xy(x, y0+h+2); self.ln(3)

    def exercise(self, idx, emoji, title, time_lbl, target, blocks):
        # Estimate if content will fit
        if self.get_y() > 165: self.add_page()
        x = self.l_margin; w = self.w - self.l_margin - self.r_margin; pad = 5
        badge_d = 12
        # Build content lines first to measure
        # Header: title takes maybe 2 lines (14pt lh=7)
        # Compute approximate height
        self.use_font('B', 11.5); self.set_text_color(46,58,72)
        title_w = w - badge_d - 30
        title_lines = self.multi_cell(title_w, 7, fa(f'{emoji}  {title}'), align='R', dry_run=True, output='LINES')
        h_top = len(title_lines)*7 + 10  # + chip and target chip
        h_body = 0
        for typ, c in blocks:
            if typ == 'p':
                self.use_font('', 10)
                ls = self.multi_cell(w-2*pad, 6, fa(c), align='J', dry_run=True, output='LINES')
                h_body += len(ls)*6 + 1
            elif typ == 'label_p':
                lab, rest = c.split(':',1) if ':' in c else (c,'')
                self.use_font('', 10)
                ls = self.multi_cell(w-2*pad - 18, 6, fa(lab + ':' + rest), align='J', dry_run=True, output='LINES')
                h_body += len(ls)*6 + 1
            elif typ == 'ul':
                for it in c:
                    self.use_font('',10)
                    ls = self.multi_cell(w-2*pad - 7, 6, fa(it), align='J', dry_run=True, output='LINES')
                    h_body += len(ls)*6
                h_body += 1
        h_total = h_top + h_body + 4
        # Page break if needed
        if self.get_y() + h_total > 277:
            self.add_page()

        y0 = self.get_y()
        # Frame fill
        self.set_fill_color(251,249,245); self.set_draw_color(236,227,212); self.set_line_width(0.3)
        self.rect(x, y0, w, h_total, 'DF')

        # Layout content
        y_cursor = y0 + 4
        # Title (right)
        self.set_xy(x + pad, y_cursor)
        self.use_font('B', 11.5); self.set_text_color(46,58,72)
        self.multi_cell(title_w, 7, fa(f'{emoji}  {title}'), align='R', new_x=XPos.LMARGIN, new_y=YPos.NEXT)
        # Meta chip (left, same y as title top)
        self.set_xy(x + pad, y_cursor)
        self.use_font('', 8); self.set_fill_color(237,235,248); self.set_text_color(109,117,184)
        mw = self.get_string_width(fa(time_lbl)) + 5
        self.cell(mw, 5.5, fa(time_lbl), fill=True, align='C')
        # Badge (far right circle)
        cx = x + w - badge_d/2 - 2; cy = y_cursor + badge_d/2
        self.set_fill_color(79,111,160)
        self.ellipse(cx - badge_d/2, cy - badge_d/2, badge_d, badge_d, style='F')
        self.set_xy(cx - badge_d/2, cy - 3)
        self.use_font('B', 10.5); self.set_text_color(255,255,255)
        self.cell(badge_d, 6, str(idx), align='C')
        # Target chip, right-aligned below title
        y_after_title = y_cursor + len(title_lines)*7
        self.set_text_color(42,42,51)
        self.set_xy(x + pad, y_after_title + 1)
        tgt = 'مقصد: ' + target
        self.use_font('B', 9); self.set_fill_color(253,236,230); self.set_text_color(196,91,74)
        tw = self.get_string_width(fa(tgt)) + 5
        self.set_x(x + w - pad - tw)
        self.cell(tw, 5, fa(tgt), fill=True, align='C', new_x=XPos.LMARGIN, new_y=YPos.NEXT)
        # Body
        yb = self.get_y() + 1.5
        self.set_xy(x + pad, yb)
        for typ, c in blocks:
            if typ == 'p':
                self.use_font('',10); self.set_text_color(42,42,51)
                self.multi_cell(w-2*pad, 6, fa(c), align='J', new_x=XPos.LMARGIN, new_y=YPos.NEXT)
            elif typ == 'label_p':
                lab, rest = c.split(':',1) if ':' in c else (c,'')
                self.use_font('B',10); self.set_text_color(63,111,160)
                self.write(6, fa(lab + ':'))
                self.use_font('',10); self.set_text_color(42,42,51)
                self.multi_cell(0,6,fa(rest),align='J',new_x=XPos.LMARGIN,new_y=YPos.NEXT)
            elif typ == 'ul':
                self.use_font('',10); self.set_text_color(42,42,51)
                for it in c:
                    self.set_x(x+pad+3); self.cell(4,6,fa('•'))
                    self.multi_cell(w-2*pad-7,6,fa(it),align='J',new_x=XPos.LMARGIN,new_y=YPos.NEXT)
            self.ln(0.3)
        self.set_xy(x, y0 + h_total + 1); self.ln(4)


def parse_blocks(text):
    blocks = []; lines = text.split('\n'); i = 0
    while i < len(lines):
        ln = lines[i].rstrip()
        if not ln.strip(): i+=1; continue
        if ln.startswith('**') and ':**' in ln:
            buf = ln; i+=1
            while i<len(lines) and lines[i].strip() and not lines[i].startswith('- ') and not lines[i].startswith('**') and not lines[i].startswith('##'):
                buf += ' ' + lines[i].rstrip(); i+=1
            buf = re.sub(r'\*\*([^*]+)\*\*', r'\1', buf)
            blocks.append(('label_p', buf)); continue
        if ln.startswith('- '):
            items = []
            while i<len(lines) and lines[i].startswith('- '):
                items.append(re.sub(r'\*\*([^*]+)\*\*', r'\1', lines[i][2:].strip())); i+=1
            blocks.append(('ul', items)); continue
        buf = ln; i+=1
        while i<len(lines) and lines[i].strip() and not lines[i].startswith('- ') and not lines[i].startswith('**') and not lines[i].startswith('##'):
            buf += ' ' + lines[i].rstrip(); i+=1
        buf = re.sub(r'\*\*([^*]+)\*\*', r'\1', buf)
        blocks.append(('p', buf))
    return blocks

# -------- Build --------
pdf = PDF()

# COVER
pdf.add_page()
pdf.ln(14)
pdf.use_font('M', 10); pdf.set_text_color(109,117,184)
pdf.cell(0, 6, fa('جغد دانا · بسته تمرین‌های شخصی‌شده'), align='C',
         new_x=XPos.LMARGIN, new_y=YPos.NEXT)
pdf.ln(6)
bar_w = 30
pdf.set_fill_color(79,111,160); pdf.rect((pdf.w-bar_w)/2, pdf.get_y(), bar_w/2, 2.3, 'F')
pdf.set_fill_color(124,108,231); pdf.rect(pdf.w/2, pdf.get_y(), bar_w/2, 2.3, 'F')
pdf.ln(10)
pdf.use_font('EB', 46); pdf.set_text_color(46,58,72)
pdf.cell(0, 22, '☻', align='C', new_x=XPos.LMARGIN, new_y=YPos.NEXT)
pdf.ln(6)
pdf.use_font('EB', 22); pdf.set_text_color(46,58,72)
pdf.multi_cell(0, 12, fa('ده تمرین برای\nروزهای معمولی'), align='C',
               new_x=XPos.LMARGIN, new_y=YPos.NEXT)
pdf.ln(2)
pdf.use_font('M', 13); pdf.set_text_color(89,100,180)
pdf.multi_cell(0, 9, fa('ترکیبی از ACT و نگاه فرویدی\nبرای طرحواره‌های فعال'), align='C',
               new_x=XPos.LMARGIN, new_y=YPos.NEXT)
pdf.ln(12)

bw, bh = 100, 28
bx = (pdf.w - bw)/2; by = pdf.get_y()
pdf.set_fill_color(255,247,234); pdf.set_draw_color(201,179,135); pdf.set_line_width(0.4)
pdf.rect(bx, by, bw, bh, 'DF')
pdf.set_xy(bx, by+3)
pdf.use_font('B',13); pdf.set_text_color(46,58,72)
pdf.cell(bw, 7, fa('عرفان کمال‌زاده'), align='C', new_x=XPos.LMARGIN, new_y=YPos.NEXT)
pdf.set_x(bx); pdf.use_font('',10); pdf.set_text_color(90,74,52)
pdf.cell(bw, 6, fa('۲۷ ساله · گزارش RPT-INI-000014'), align='C', new_x=XPos.LMARGIN, new_y=YPos.NEXT)
pdf.set_x(bx)
pdf.cell(bw, 6, fa('تاریخ ارزیابی: ۱۵ شهریور ۱۴۰۵'), align='C', new_x=XPos.LMARGIN, new_y=YPos.NEXT)
pdf.ln(28)

pdf.use_font('', 10); pdf.set_text_color(107,98,88)
pdf.multi_cell(0, 7, fa('تهیه‌شده توسط  دکتر جواد میربلوکی\nوب‌سایت و پلنر تمرین روزانه:  joma.mirbolouki.com\nپشتیبانی:  ۰۹۹۶۷۹۷۹۴۷۱  (پیامک و پیام‌رسان بله)'),
               align='C', new_x=XPos.LMARGIN, new_y=YPos.NEXT)
pdf.ln(8)
pdf.set_draw_color(230,221,209); pdf.set_line_width(0.3)
y = pdf.get_y(); pdf.line(50, y, pdf.w-50, y); pdf.ln(3)
pdf.use_font('', 9); pdf.set_text_color(138,127,115)
pdf.multi_cell(0, 6, fa('این بسته مکمل جلسات درمانی است، نه جایگزین آن.\nلطفاً هفته‌ای فقط دو تمرین را انتخاب کنید.'),
               align='C', new_x=XPos.LMARGIN, new_y=YPos.NEXT)

# CONTENT
pdf.add_page()
pdf.h1('یادداشت آغاز')
pdf.note_box(
    'عرفان عزیز: این تمرین‌ها تکلیف مدرسه نیستند. هیچ‌کدام از شما «بهتر شدن» یا «قوی‌تر شدن» نمی‌خواهند. هر کدام یک ذره‌بین کوچک هستند که در لحظات معمولی روز به دست می‌گیری تا چیزی را که سال‌ها در پس‌زمینه بوده، یک لحظه ببینی. یک یا دو تمرین را این هفته انتخاب کن، نه همه را. آن‌هایی که به نظرت «احمقانه است» معمولاً درست همان‌هایی‌اند که بیشترین تماس را با طرحواره دارند.',
    (244,239,230), (232,200,140), (232,182,74))
pdf.note_box(
    'هشدار بالینی: این تمرین‌ها جای جلسه روان‌درمانی و دارو نیستند. تصویرسازی خاطره، کار روی بدرفتاری کودکی و تکنیک‌های عمیق باید در حضور درمانگر انجام شود. این‌جا ما به چیزی پنجه نمی‌کشیم، فقط به آن سلام می‌کنیم.',
    (255,240,240), (232,191,191), (196,91,74), text_color=(107,45,37))
pdf.ln(1)
pdf.gold_card(
    '«آنچه من فکر می‌کنم دربارهٔ من،\nهمیشه صدای من نیست.»',
    'این جمله را این هفته با خودت حمل کن. وقتی صدای قاضی درونت بلند شد، یک بار در ذهنت تکرارش کن.')

with open(MD_PATH) as f: md = f.read()
pat = re.compile(r'^## تمرین (\S+)\s*—\s*(.+?)\s*\(([^)]+)\)\s*\n(.*?)(?=\n---\s*\n## (?:تمرین |🪁)|\Z)', re.M|re.S)
fe = {'۱':1,'۲':2,'۳':3,'۴':4,'۵':5,'۶':6,'۷':7,'۸':8,'۹':9,'۱۰':10}
exs = []
for m in pat.finditer(md):
    nfa, head, tm, body = m.groups()
    # Split off trailing emoji/symbol: take last non-Persian/space token as emoji
    toks = head.rstrip().split()
    # find the first token from end that is NOT a Persian/Arabic word; that's the emoji
    emoji = ''
    if toks:
        # EMOJI_MAP keys (replaced) are BMP symbols like ≈▲|◉✉✓~◠❖✦
        last = toks[-1]
        if not re.search(r'[\u0600-\u06FF]', last):
            emoji = last
            toks = toks[:-1]
    title = ' '.join(toks)
    tgt_m = re.search(r'\*\*مقصد:\*\*\s*(.+)', body)
    target = tgt_m.group(1).strip() if tgt_m else ''
    if tgt_m: body = body.replace(tgt_m.group(0),'').strip()
    exs.append((fe.get(nfa), title, emoji, tm, target, parse_blocks(body)))

pdf.h2('ده تمرین')
for ex in exs:
    pdf.exercise(*ex)

# Rules
pdf.add_page()
pdf.h2('🪁 قواعد این بسته')
pdf.numbered_list([
    'هر هفته فقط دو تمرین را انتخاب کن از همین‌جا و همان را بکن. زیاد کردن تمرین‌ها خودش یک فرار دیگر به سمت «انجام کامل» است.',
    'اگر دو روز پشت سر هم فراموش کردی، شکست نیست. دقیقاً طبیعی است. فردا دوباره یک بار انجام بده.',
    'دفتر یادداشت طولانی ننویس. ناخودآگاه با کلمه‌های زیاد پیچیده می‌شود. چند خط، چند ثانیه، یک حس در بدن — کافی است.',
    'اگر یک تمرین حس گریه، خشم، یا تنگی نفس ایجاد کرد: آن را متوقف کن و در جلسه بعد با درمانگر مطرح کن. بعضی درها باید با همراهی باز شوند.',
    'هیچ‌کدام از تمرین‌ها از تو نمی‌خواهد کسی را ببخشی، به خاطر بسپاری، فراموش کنی، یا درمانی شوی. هر کدام یک ذره‌بین است در دست تو.',
])
pdf.ln(4)

# Contact (dark) - single-column centered
if pdf.get_y() > 225: pdf.add_page()
x = pdf.l_margin; w = pdf.w - pdf.l_margin - pdf.r_margin; pad=7
y0 = pdf.get_y()
h_card = 38
pdf.set_fill_color(46,58,72); pdf.rect(x,y0,w,h_card,'F')
pdf.set_xy(x+pad, y0+pad); pdf.use_font('B',11); pdf.set_text_color(232,182,74)
pdf.cell(w-2*pad,7,fa('✉  تماس با درمانگر'),align='C',new_x=XPos.LMARGIN,new_y=YPos.NEXT)
pdf.ln(2)
pdf.use_font('B',11); pdf.set_text_color(255,255,255)
pdf.cell(w-2*pad,7,fa('دکتر جواد میربلوکی'),align='C',new_x=XPos.LMARGIN,new_y=YPos.NEXT)
pdf.use_font('',9.5); pdf.set_text_color(210,210,210)
pdf.cell(w-2*pad,6,fa('وب‌سایت و پلنر تمرین روزانه:  joma.mirbolouki.com'),align='C',new_x=XPos.LMARGIN,new_y=YPos.NEXT)
pdf.cell(w-2*pad,6,fa('پشتیبانی (پیامک و پیام‌رسان بله):  ۰۹۹۶۷۹۷۹۴۷۱'),align='C',new_x=XPos.LMARGIN,new_y=YPos.NEXT)
pdf.set_xy(x, y0+h_card); pdf.ln(5)

# Disclaimer
pdf.use_font('',8.5); pdf.set_text_color(138,127,115)
pdf.set_draw_color(236,227,212); y=pdf.get_y()
pdf.line(pdf.l_margin,y,pdf.w-pdf.r_margin,y); pdf.ln(3)
pdf.multi_cell(0,5.5,fa('جوما ابزار خودمدیریتی روزانه است، نه درمان. اگر در بحران، خشونت، یا فکر آسیب به خود/دیگران هستی، همین حالا با اورژانس اجتماعی ۱۲۳ یا یک متخصص تماس بگیر.\nاین تمرین‌ها برای هفته‌های اول کار روی خودآگاهی‌اند. پس از آن بسته به اینکه کدام طرحواره فعال‌تر می‌ماند، بسته بعدی عمیق‌تر خواهد شد.\nشناسه پرونده: RPT-INI-000014 · نسخه ۱.۰ · تهیه‌شده در ۳۰ شهریور ۱۴۰۵'),
               align='C', new_x=XPos.LMARGIN, new_y=YPos.NEXT)

# Close and set total pages
pdf._total = pdf.pages_count
pdf.output(OUT_PDF)
print('PDF saved:', OUT_PDF, os.path.getsize(OUT_PDF), 'bytes, pages:', pdf.pages_count)
