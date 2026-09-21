#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Build Erfan-Kamalzadeh-Exercises.docx with native Word RTL (editable)."""
import re
from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

MD = open('erfan-kamalzadeh-exercises.md', encoding='utf-8').read()
FONT = 'Vazirmatn'
DARK, BLUE, GOLD, GRAY = '2E3A48', '4F6FA0', 'B08A3E', '6B6258'
FA_DIGITS = str.maketrans('0123456789', '۰۱۲۳۴۵۶۷۸۹')

def has_fa(t): return re.search(r'[\u0600-\u06FF]', t) is not None

def style_run(r, bold=False, italic=False, color=None, size=None, font=FONT):
    r.bold, r.italic = bold, italic
    if color: r.font.color.rgb = RGBColor.from_string(color)
    if size:  r.font.size = Pt(size)
    r.font.name = font
    rpr = r._r.get_or_add_rPr()
    rf = rpr.find(qn('w:rFonts'))
    if rf is None:
        rf = OxmlElement('w:rFonts'); rpr.insert(0, rf)
    rf.set(qn('w:cs'), font)
    if size:
        sz = rpr.find(qn('w:sz'))
        if sz is not None:
            szcs = OxmlElement('w:szCs'); szcs.set(qn('w:val'), sz.get(qn('w:val'))); rpr.append(szcs)
    if has_fa(text_ok(r.text)):
        rtl = OxmlElement('w:rtl'); rpr.append(rtl)

def text_ok(t): return t

def add_runs(p, text, color=None, size=None, bold_all=False, italic_all=False):
    for i, part in enumerate(text.split('**')):
        if not part: continue
        style_run(p.add_run(part), bold=(i % 2 == 1) or bold_all,
                  italic=italic_all, color=color, size=size)

def para(doc, before=0, after=6, align=None, shade=None, border=None,
         indent=None, keep_next=False):
    p = doc.add_paragraph()
    pf = p.paragraph_format
    pf.space_before, pf.space_after = Pt(before), Pt(after)
    ppr = p._p.get_or_add_pPr()
    if keep_next:
        kn = OxmlElement('w:keepNext'); ppr.append(kn)
    if border:
        pbdr = OxmlElement('w:pBdr')
        for side in ('top', 'left', 'bottom', 'right'):
            e = OxmlElement(f'w:{side}')
            e.set(qn('w:val'), 'single'); e.set(qn('w:sz'), '6')
            e.set(qn('w:space'), '4'); e.set(qn('w:color'), border)
            pbdr.append(e)
        ppr.append(pbdr)
    if shade:
        shd = OxmlElement('w:shd')
        shd.set(qn('w:val'), 'clear'); shd.set(qn('w:fill'), shade)
        ppr.append(shd)
    bidi = OxmlElement('w:bidi'); ppr.append(bidi)
    if indent is not None:
        ind = OxmlElement('w:ind'); ind.set(qn('w:left'), str(indent)); ppr.append(ind)
    if align: p.alignment = align
    return p

def heading(doc, text, size=14, color=BLUE, before=16, after=6, align=None, keep=True):
    p = para(doc, before=before, after=after, align=align, keep_next=keep)
    add_runs(p, text, color=color, size=size, bold_all=True)
    return p

def quote_box(doc, text, fill='FFF7E6', edge='D9C08A', indent=340):
    p = para(doc, before=2, after=8, shade=fill, border=edge, indent=indent)
    add_runs(p, text, size=10.5)
    return p

# ---------------- document setup ----------------
doc = Document()
sec = doc.sections[0]
sec.page_width, sec.page_height = Cm(21.0), Cm(29.7)
sec.left_margin = sec.right_margin = Cm(2.2)
sec.top_margin = sec.bottom_margin = Cm(2.0)

st = doc.styles['Normal']
st.font.name = FONT; st.font.size = Pt(11)
rpr = st.element.get_or_add_rPr()
rf = OxmlElement('w:rFonts')
rf.set(qn('w:ascii'), FONT); rf.set(qn('w:hAnsi'), FONT); rf.set(qn('w:cs'), FONT)
rpr.insert(0, rf)
szcs = OxmlElement('w:szCs'); szcs.set(qn('w:val'), '22'); rpr.append(szcs)

# ---------------- parse & render ----------------
lines = MD.split('\n')
i = 0
warn_words = ('هشدار بالینی', 'اورژانس')
for ln in lines:
    s = ln.strip()
    if not s or s == '---':
        continue
    # blockquote
    if s.startswith('>'):
        body = s.lstrip('> ').strip()
        fill, edge = ('FDEDEB', 'D98A80') if any(w in body for w in warn_words) else ('FFF7E6', 'D9C08A')
        if body.startswith('«'):
            fill, edge = ('EEF3FA', 'A9BDD9')
        quote_box(doc, body, fill, edge)
        continue
    # headings
    m = re.match(r'^(#{1,3})\s+(.*)$', ln)
    if m:
        lvl, t = len(m.group(1)), m.group(2).strip()
        if lvl == 1:
            p = para(doc, before=6, after=4, align=WD_ALIGN_PARAGRAPH.CENTER)
            add_runs(p, t, color=DARK, size=20, bold_all=True)
        elif t.startswith('تمرین'):
            heading(doc, t, size=13.5, color=BLUE, before=18, after=4)
        elif lvl == 2:
            heading(doc, t, size=15, color=DARK, before=14, after=6, align=WD_ALIGN_PARAGRAPH.CENTER)
        else:
            p = para(doc, before=2, after=2, align=WD_ALIGN_PARAGRAPH.CENTER)
            add_runs(p, t, color=GRAY, size=11.5, bold_all=False)
        continue
    # bullets
    if ln.lstrip().startswith('- '):
        p = para(doc, before=0, after=3, indent=397)
        style_run(p.add_run('•  '), bold=True, color=GOLD, size=11)
        add_runs(p, ln.lstrip()[2:].strip(), size=10.5)
        continue
    # numbered items -> literal Persian number (no Word auto-numbering headaches)
    m = re.match(r'^(\d+)\.\s+(.*)$', s)
    if m:
        p = para(doc, before=0, after=3, indent=397)
        style_run(p.add_run(m.group(1).translate(FA_DIGITS) + '.  '), bold=True, color=BLUE, size=10.5)
        add_runs(p, m.group(2), size=10.5)
        continue
    # bold-label paragraph  **label:** rest
    m = re.match(r'^\*\*(.+?):\*\*\s*(.*)$', s)
    if m:
        p = para(doc, before=4, after=4)
        style_run(p.add_run(m.group(1) + ':  '), bold=True, color=BLUE, size=10.5)
        add_runs(p, m.group(2), size=10.5)
        continue
    # standalone mini-heading like  قوانین:
    if s.endswith(':') and len(s) < 30 and '**' not in s:
        p = para(doc, before=6, after=3)
        add_runs(p, s, color=DARK, size=10.5, bold_all=True)
        continue
    # final italic line  *...*
    if s.startswith('*') and s.endswith('*') and not s.startswith('**'):
        p = para(doc, before=8, after=4)
        add_runs(p, s.strip('*'), size=10, italic_all=True, color=GRAY)
        continue
    # plain paragraph
    p = para(doc, before=2, after=4)
    add_runs(p, s, size=10.5)

# fix stale "۸ تمرین" subtitle if present in rendered runs
for p in doc.paragraphs:
    for r in p.runs:
        if '۸ تمرین' in r.text:
            r.text = r.text.replace('۸ تمرین', '۱۰ تمرین')

# document properties
doc.core_properties.title = 'بسته تمرین‌های شخصی‌شده — عرفان کمال‌زاده'
doc.core_properties.author = 'دکتر جواد میربلوکی'

doc.save('Erfan-Kamalzadeh-Exercises.docx')
print('DOCX saved:', 'Erfan-Kamalzadeh-Exercises.docx')
