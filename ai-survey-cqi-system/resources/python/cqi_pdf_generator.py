"""
resources/python/cqi_pdf_generator.py
Generates a styled CQI Report PDF using ReportLab.
Usage: python cqi_pdf_generator.py <data.json> <output.pdf>
"""

import sys
import json
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import cm
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_JUSTIFY
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle,
    HRFlowable, KeepTogether, PageBreak
)

# ── Colour palette (USC-inspired olive/sage greens + neutrals) ──────────────
OLIVE_DARK   = colors.HexColor('#4a5c2f')
OLIVE_MID    = colors.HexColor('#7a8c5a')
OLIVE_LIGHT  = colors.HexColor('#c8d4a8')
OLIVE_PALE   = colors.HexColor('#eef1e3')
SAGE_HEADER  = colors.HexColor('#8a9b6e')
CREAM        = colors.HexColor('#f8f6ed')
BORDER_GRAY  = colors.HexColor('#b0b8a0')
TEXT_DARK    = colors.HexColor('#1a1a1a')
TEXT_MUTED   = colors.HexColor('#5a5a5a')
RED_ACCENT   = colors.HexColor('#8b1a1a')
BLUE_ACCENT  = colors.HexColor('#1a3a5c')
WHITE        = colors.white

# ── Page setup ───────────────────────────────────────────────────────────────
PAGE_W, PAGE_H = A4
MARGIN = 2 * cm


def build_styles():
    base = getSampleStyleSheet()
    styles = {}

    styles['inst'] = ParagraphStyle('inst',
        fontSize=14, fontName='Helvetica-Bold',
        alignment=TA_CENTER, textColor=OLIVE_DARK,
        spaceAfter=2)
    styles['dept'] = ParagraphStyle('dept',
        fontSize=10, fontName='Helvetica',
        alignment=TA_CENTER, textColor=OLIVE_MID,
        spaceAfter=2)
    styles['report_title'] = ParagraphStyle('report_title',
        fontSize=17, fontName='Helvetica-Bold',
        alignment=TA_CENTER, textColor=OLIVE_DARK,
        spaceAfter=14)
    styles['section_header'] = ParagraphStyle('section_header',
        fontSize=11, fontName='Helvetica-Bold',
        textColor=WHITE, spaceAfter=0, spaceBefore=0)
    styles['body'] = ParagraphStyle('body',
        fontSize=9.5, fontName='Helvetica',
        textColor=TEXT_DARK, leading=14,
        alignment=TA_JUSTIFY, spaceAfter=6)
    styles['bullet'] = ParagraphStyle('bullet',
        fontSize=9.5, fontName='Helvetica',
        textColor=TEXT_DARK, leading=14,
        leftIndent=14, firstLineIndent=-10, spaceAfter=3)
    styles['table_header'] = ParagraphStyle('table_header',
        fontSize=9, fontName='Helvetica-Bold',
        textColor=WHITE, alignment=TA_CENTER)
    styles['table_cell'] = ParagraphStyle('table_cell',
        fontSize=8.5, fontName='Helvetica',
        textColor=TEXT_DARK, leading=12)
    styles['q_text'] = ParagraphStyle('q_text',
        fontSize=8.5, fontName='Helvetica',
        textColor=TEXT_DARK, leading=12)
    styles['score_good'] = ParagraphStyle('score_good',
        fontSize=9, fontName='Helvetica-Bold',
        textColor=OLIVE_DARK, alignment=TA_CENTER)
    styles['score_fair'] = ParagraphStyle('score_fair',
        fontSize=9, fontName='Helvetica-Bold',
        textColor=colors.HexColor('#7a4000'), alignment=TA_CENTER)
    styles['label_bold'] = ParagraphStyle('label_bold',
        fontSize=9, fontName='Helvetica-Bold', textColor=TEXT_DARK)

    return styles


def section_header_block(title, styles):
    """Returns a padded section header table."""
    t = Table([[Paragraph(title, styles['section_header'])]], colWidths=[PAGE_W - 2 * MARGIN])
    t.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, -1), OLIVE_DARK),
        ('TOPPADDING',    (0, 0), (-1, -1), 6),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 6),
        ('LEFTPADDING',   (0, 0), (-1, -1), 10),
        ('RIGHTPADDING',  (0, 0), (-1, -1), 10),
    ]))
    return t


def sub_section_header(title, styles):
    """Olive-mid sub-section row."""
    t = Table([[Paragraph(title, styles['section_header'])]], colWidths=[PAGE_W - 2 * MARGIN])
    t.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, -1), OLIVE_MID),
        ('TOPPADDING',    (0, 0), (-1, -1), 5),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 5),
        ('LEFTPADDING',   (0, 0), (-1, -1), 10),
        ('RIGHTPADDING',  (0, 0), (-1, -1), 10),
    ]))
    return t


def get_thresholds(data: dict) -> dict:
    """
    Pull admin-configured thresholds from the payload.
    Falls back to the original defaults if not present.
    Values are percentages (0-100); divide by 100 for ratio comparison.
    """
    t = data.get('thresholds', {})
    return {
        'excellent': float(t.get('excellent', 90)) / 100,
        'very_good': float(t.get('very_good', 80)) / 100,
        'good':      float(t.get('good',      70)) / 100,
        'fair':      float(t.get('fair',       60)) / 100,
    }


def interpret_score(score, scale_max=5, thresholds=None):
    if thresholds is None:
        thresholds = {'excellent': 0.90, 'very_good': 0.80, 'good': 0.70, 'fair': 0.60}
    try:
        numeric_score = float(score)
    except (ValueError, TypeError):
        numeric_score = 0.0

    pct = numeric_score / scale_max if scale_max else 0

    if pct >= thresholds['excellent']: return 'Excellent'
    if pct >= thresholds['very_good']: return 'Very Good'
    if pct >= thresholds['good']:      return 'Good'
    if pct >= thresholds['fair']:      return 'Fair'
    return 'Needs Improvement'


def score_style(score, scale_max, styles, thresholds=None):
    if thresholds is None:
        thresholds = {'excellent': 0.90, 'very_good': 0.80, 'good': 0.70, 'fair': 0.60}
    try:
        numeric_score = float(score)
    except (ValueError, TypeError):
        numeric_score = 0.0

    pct = numeric_score / scale_max if scale_max else 0
    return styles['score_good'] if pct >= thresholds['good'] else styles['score_fair']


def build_pdf(data: dict, output_path: str):
    doc = SimpleDocTemplate(
        output_path,
        pagesize=A4,
        leftMargin=MARGIN, rightMargin=MARGIN,
        topMargin=MARGIN, bottomMargin=MARGIN,
        title=data.get('title', 'CQI Report'),
    )

    styles = build_styles()
    thresholds_from_payload = get_thresholds(data)
    story  = []
    ai     = data.get('ai_content', {})
    stats  = data.get('statistics', {})
    scale_max = data.get('scale_max', 5)

    # ── PAGE 1: Cover / Info ────────────────────────────────────────────────
    story.append(Spacer(1, 0.5 * cm))
    story.append(Paragraph(data.get('institution', 'University'), styles['inst']))
    story.append(Paragraph(data.get('department', ''), styles['dept']))
    story.append(Paragraph("Teachers' Evaluation and CQI Report", styles['report_title']))
    story.append(HRFlowable(width="100%", thickness=2, color=OLIVE_DARK, spaceAfter=10))

    # Info table
    info_rows = [
        [Paragraph("<b>Teacher's Name</b>", styles['label_bold']),
         Paragraph(data.get('faculty_name', ''), styles['body']),
         Paragraph("<b>Name of Program</b>", styles['label_bold']),
         Paragraph(data.get('program_name', ''), styles['body'])],
        [Paragraph("<b>Academic Term</b>", styles['label_bold']),
         Paragraph(data.get('semester', ''), styles['body']),
         Paragraph("<b>Course Handled</b>", styles['label_bold']),
         Paragraph(data.get('course_code', ''), styles['body'])],
        [Paragraph("<b>Academic Year</b>", styles['label_bold']),
         Paragraph(data.get('academic_year', ''), styles['body']),
         Paragraph("<b>Group Number</b>", styles['label_bold']),
         Paragraph(str(data.get('group_number', '')), styles['body'])],
    ]
    cw = [(PAGE_W - 2 * MARGIN) / 4] * 4
    info_t = Table(info_rows, colWidths=cw)
    info_t.setStyle(TableStyle([
        ('GRID',          (0, 0), (-1, -1), 0.5, BORDER_GRAY),
        ('BACKGROUND',    (0, 0), (0, -1), OLIVE_PALE),
        ('BACKGROUND',    (2, 0), (2, -1), OLIVE_PALE),
        ('TOPPADDING',    (0, 0), (-1, -1), 6),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 6),
        ('LEFTPADDING',   (0, 0), (-1, -1), 8),
        ('RIGHTPADDING',  (0, 0), (-1, -1), 8),
        ('VALIGN',        (0, 0), (-1, -1), 'MIDDLE'),
    ]))
    story.append(info_t)
    story.append(Spacer(1, 0.6 * cm))

    # ── Overall interpretation banner ───────────────────────────────────────
    if ai.get('overall_interpretation'):
        banner_t = Table(
            [[Paragraph(ai['overall_interpretation'], ParagraphStyle('oi',
                fontSize=9.5, fontName='Helvetica', textColor=TEXT_DARK,
                leading=14, alignment=TA_JUSTIFY))]],
            colWidths=[PAGE_W - 2 * MARGIN]
        )
        banner_t.setStyle(TableStyle([
            ('BACKGROUND',    (0, 0), (-1, -1), CREAM),
            ('BOX',           (0, 0), (-1, -1), 1, BORDER_GRAY),
            ('TOPPADDING',    (0, 0), (-1, -1), 10),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 10),
            ('LEFTPADDING',   (0, 0), (-1, -1), 12),
            ('RIGHTPADDING',  (0, 0), (-1, -1), 12),
        ]))
        story.append(banner_t)
        story.append(Spacer(1, 0.5 * cm))

    # ── Survey Response Summary: Category Scores ────────────────────────────
    story.append(section_header_block("Survey Responses Summary", styles))
    story.append(Spacer(1, 0.3 * cm))

    category_scores = data.get('category_scores', {})

    # Safety net: strip any underscore meta keys that may still be present
    category_scores = {k: v for k, v in category_scores.items() if not str(k).startswith('_')}

    # Extract the hidden stats we injected in the PHP Job
    overall_stats = data.get('weighted_meta', {}).get('overall_stats', {})

    if category_scores:
        hdr = [
            Paragraph("Category", styles['table_header']),
            Paragraph("Mean Score", styles['table_header']),
            Paragraph("Interpretation", styles['table_header']),
        ]
        rows = [hdr]
        for cat, score in category_scores.items():
            try:
                numeric_score = float(score)
            except (ValueError, TypeError):
                numeric_score = 0.0

            interp = interpret_score(numeric_score, scale_max, thresholds_from_payload)
            rows.append([
                Paragraph(cat, styles['table_cell']),
                Paragraph(f"{numeric_score:.2f} / {scale_max}", score_style(numeric_score, scale_max, styles, thresholds_from_payload)),
                Paragraph(interp, score_style(numeric_score, scale_max, styles, thresholds_from_payload)),
            ])

        # Overall row
        avg = data.get('avg_rating', 0)
        try:
            numeric_avg = float(avg)
        except (ValueError, TypeError):
            numeric_avg = 0.0

        rows.append([
            Paragraph("<b>Overall Mean Score</b>", styles['label_bold']),
            Paragraph(f"<b>{numeric_avg:.2f} / {scale_max}</b>", score_style(numeric_avg, scale_max, styles, thresholds_from_payload)),
            Paragraph(f"<b>{interpret_score(numeric_avg, scale_max, thresholds_from_payload)}</b>", score_style(numeric_avg, scale_max, styles, thresholds_from_payload)),
        ])

        cw2 = [(PAGE_W - 2 * MARGIN) * r for r in [0.50, 0.25, 0.25]]
        cat_t = Table(rows, colWidths=cw2)
        cat_t.setStyle(TableStyle([
            ('BACKGROUND',    (0, 0), (-1, 0), OLIVE_MID),
            ('BACKGROUND',    (0, -1), (-1, -1), OLIVE_PALE),
            ('GRID',          (0, 0), (-1, -1), 0.5, BORDER_GRAY),
            ('ROWBACKGROUNDS',(0, 1), (-1, -2), [WHITE, CREAM]),
            ('TOPPADDING',    (0, 0), (-1, -1), 6),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 6),
            ('LEFTPADDING',   (0, 0), (-1, -1), 8),
            ('RIGHTPADDING',  (0, 0), (-1, -1), 8),
            ('VALIGN',        (0, 0), (-1, -1), 'MIDDLE'),
            ('LINEBELOW',     (0, -2), (-1, -2), 1.5, OLIVE_DARK),
        ]))
        story.append(cat_t)
        story.append(Spacer(1, 0.4 * cm))

    # ── Weighted Achievement Breakdown (only if weights were configured) ─────
    weighted_meta = data.get('weighted_meta', {})
    weights       = weighted_meta.get('weights', {})
    achievements  = weighted_meta.get('achievements', {})
    w_scores      = weighted_meta.get('weighted_scores', {})
    overall_ws    = weighted_meta.get('overall_weighted_score')

    if weights and overall_ws is not None:
        story.append(sub_section_header("Weighted Category Achievement", styles))
        story.append(Spacer(1, 0.2 * cm))

        w_hdr = [
            Paragraph("Category",     styles['table_header']),
            Paragraph("Weight",       styles['table_header']),
            Paragraph("Achievement",  styles['table_header']),
            Paragraph("Contribution", styles['table_header']),
        ]
        w_rows = [w_hdr]
        for cat, weight in weights.items():
            achievement  = float(achievements.get(cat, 0))
            contribution = float(w_scores.get(cat, 0))
            interp       = interpret_score(achievement, 100, thresholds_from_payload)
            w_rows.append([
                Paragraph(cat, styles['table_cell']),
                Paragraph(f"{float(weight):.2f}%", styles['table_cell']),
                Paragraph(f"{achievement:.1f}%  ({interp})",
                          score_style(achievement, 100, styles, thresholds_from_payload)),
                Paragraph(f"{contribution:.2f} pts",
                          score_style(achievement, 100, styles, thresholds_from_payload)),
            ])

        try:
            ows_float = float(overall_ws)
        except (ValueError, TypeError):
            ows_float = 0.0

        w_rows.append([
            Paragraph("<b>Overall Weighted Score</b>", styles['label_bold']),
            Paragraph("<b>100%</b>", styles['label_bold']),
            Paragraph("", styles['table_cell']),
            Paragraph(f"<b>{ows_float:.2f} / 100</b>",
                      score_style(ows_float, 100, styles, thresholds_from_payload)),
        ])

        cw_w = [(PAGE_W - 2 * MARGIN) * r for r in [0.40, 0.15, 0.25, 0.20]]
        w_t = Table(w_rows, colWidths=cw_w)
        w_t.setStyle(TableStyle([
            ('BACKGROUND',    (0, 0), (-1, 0), OLIVE_MID),
            ('BACKGROUND',    (0, -1), (-1, -1), OLIVE_PALE),
            ('GRID',          (0, 0), (-1, -1), 0.5, BORDER_GRAY),
            ('ROWBACKGROUNDS',(0, 1), (-1, -2), [WHITE, CREAM]),
            ('TOPPADDING',    (0, 0), (-1, -1), 6),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 6),
            ('LEFTPADDING',   (0, 0), (-1, -1), 8),
            ('RIGHTPADDING',  (0, 0), (-1, -1), 8),
            ('VALIGN',        (0, 0), (-1, -1), 'MIDDLE'),
            ('LINEBELOW',     (0, -2), (-1, -2), 1.5, OLIVE_DARK),
        ]))
        story.append(w_t)
        story.append(Spacer(1, 0.4 * cm))

    # ── Descriptive Statistics Section ──────────────────────────────────────
    overall_stats = weighted_meta.get('overall_stats', {})
    if overall_stats:
        story.append(sub_section_header("Descriptive Statistics", styles))
        stats_rows = [
            [Paragraph("<b>Median</b>", styles['label_bold']),
             Paragraph("<b>Mode</b>", styles['label_bold']),
             Paragraph("<b>Standard Deviation</b>", styles['label_bold'])],
            [Paragraph(f"{overall_stats.get('median', 0):.2f}", styles['table_cell']),
             Paragraph(f"{overall_stats.get('mode', 0):.2f}", styles['table_cell']),
             Paragraph(f"{overall_stats.get('std_dev', 0):.2f}", styles['table_cell'])]
        ]
        cw_stats = [(PAGE_W - 2 * MARGIN) / 3] * 3
        stats_t = Table(stats_rows, colWidths=cw_stats)
        stats_t.setStyle(TableStyle([
            ('GRID',          (0, 0), (-1, -1), 0.5, BORDER_GRAY),
            ('BACKGROUND',    (0, 0), (-1, 0), OLIVE_PALE),
            ('ALIGN',         (0, 0), (-1, -1), 'CENTER'),
            ('TOPPADDING',    (0, 0), (-1, -1), 8),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 8),
        ]))
        story.append(stats_t)
        story.append(Spacer(1, 0.6 * cm))

    # ── Sentiment summary ────────────────────────────────────────────────────
    pos = data.get('positive_pct', 0)
    neu = data.get('neutral_pct', 0)
    neg = data.get('negative_pct', 0)
    if pos or neu or neg:
        story.append(sub_section_header("Open-ended Response Sentiment Analysis", styles))
        sent_rows = [
            [Paragraph("<b>Positive</b>", styles['label_bold']),
             Paragraph("<b>Neutral</b>", styles['label_bold']),
             Paragraph("<b>Negative</b>", styles['label_bold']),
             Paragraph("<b>Total Respondents</b>", styles['label_bold'])],
            [Paragraph(f"{pos:.1f}%", styles['score_good']),
             Paragraph(f"{neu:.1f}%", ParagraphStyle('n', fontSize=9, fontName='Helvetica-Bold',
                 textColor=colors.HexColor('#5a5a00'), alignment=TA_CENTER)),
             Paragraph(f"{neg:.1f}%", ParagraphStyle('r', fontSize=9, fontName='Helvetica-Bold',
                 textColor=RED_ACCENT, alignment=TA_CENTER)),
             Paragraph(str(data.get('response_count', 0)), styles['label_bold'])],
        ]
        cw3 = [(PAGE_W - 2 * MARGIN) / 4] * 4
        sent_t = Table(sent_rows, colWidths=cw3)
        sent_t.setStyle(TableStyle([
            ('GRID',          (0, 0), (-1, -1), 0.5, BORDER_GRAY),
            ('BACKGROUND',    (0, 0), (-1, 0), OLIVE_PALE),
            ('BACKGROUND',    (0, 1), (0, 1), colors.HexColor('#e8f5e9')),
            ('BACKGROUND',    (2, 1), (2, 1), colors.HexColor('#fce4ec')),
            ('TOPPADDING',    (0, 0), (-1, -1), 8),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 8),
            ('ALIGN',         (0, 0), (-1, -1), 'CENTER'),
            ('VALIGN',        (0, 0), (-1, -1), 'MIDDLE'),
        ]))
        story.append(sent_t)
        story.append(Spacer(1, 0.4 * cm))

    # ── Open-ended responses ─────────────────────────────────────────────────
    open_ended = data.get('open_ended_samples', {})
    if open_ended:
        story.append(sub_section_header("Student Open-ended Responses", styles))
        story.append(Spacer(1, 0.2 * cm))
        for question, responses in open_ended.items():
            story.append(Paragraph(f"<b>{question}</b>", styles['body']))
            for resp in responses:
                story.append(Paragraph(f"• {resp}", styles['bullet']))
            story.append(Spacer(1, 0.2 * cm))

    story.append(PageBreak())

    # ── PAGE 2: AI Analysis ──────────────────────────────────────────────────
    story.append(section_header_block("Analysis of Results (OBE Perspective)", styles))
    story.append(Spacer(1, 0.3 * cm))

    if ai.get('analysis', {}).get('summary'):
        story.append(Paragraph(ai['analysis']['summary'], styles['body']))
    for h in ai.get('analysis', {}).get('highlights', []):
        story.append(Paragraph(f"• {h}", styles['bullet']))
    story.append(Spacer(1, 0.4 * cm))

    # Identified gaps table
    gaps = ai.get('identified_gaps', [])
    if gaps:
        story.append(section_header_block("Identified Gaps", styles))
        story.append(Spacer(1, 0.2 * cm))
        gap_rows = [[
            Paragraph("Area", styles['table_header']),
            Paragraph("Gap", styles['table_header']),
            Paragraph("Impact", styles['table_header']),
        ]]
        for g in gaps:
            gap_rows.append([
                Paragraph(g.get('area', ''), styles['table_cell']),
                Paragraph(g.get('gap', ''), styles['table_cell']),
                Paragraph(g.get('impact', ''), styles['table_cell']),
            ])
        cw4 = [(PAGE_W - 2 * MARGIN) * r for r in [0.25, 0.45, 0.30]]
        gap_t = Table(gap_rows, colWidths=cw4)
        gap_t.setStyle(TableStyle([
            ('BACKGROUND',    (0, 0), (-1, 0), OLIVE_MID),
            ('GRID',          (0, 0), (-1, -1), 0.5, BORDER_GRAY),
            ('ROWBACKGROUNDS',(0, 1), (-1, -1), [WHITE, CREAM]),
            ('TOPPADDING',    (0, 0), (-1, -1), 6),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 6),
            ('LEFTPADDING',   (0, 0), (-1, -1), 6),
            ('RIGHTPADDING',  (0, 0), (-1, -1), 6),
            ('VALIGN',        (0, 0), (-1, -1), 'TOP'),
        ]))
        story.append(gap_t)
        story.append(Spacer(1, 0.4 * cm))

    # Strengths + Areas for Improvement side by side
    strengths = ai.get('strengths', [])
    improvements = ai.get('areas_for_improvement', [])
    if strengths or improvements:
        story.append(section_header_block("Strengths and Areas for Improvement", styles))
        story.append(Spacer(1, 0.2 * cm))

        s_items = [Paragraph("<b>Strengths Identified</b>", styles['label_bold'])]
        s_items += [Paragraph(f"• {s}", styles['bullet']) for s in strengths]

        i_items = [Paragraph("<b>Areas for Improvement</b>", styles['label_bold'])]
        i_items += [Paragraph(f"• {i}", styles['bullet']) for i in improvements]

        col_w = (PAGE_W - 2 * MARGIN) / 2 - 0.3 * cm
        si_t = Table([[s_items, i_items]], colWidths=[col_w, col_w])
        si_t.setStyle(TableStyle([
            ('VALIGN',        (0, 0), (-1, -1), 'TOP'),
            ('LEFTPADDING',   (0, 0), (-1, -1), 6),
            ('RIGHTPADDING',  (0, 0), (-1, -1), 6),
            ('TOPPADDING',    (0, 0), (-1, -1), 4),
            ('LINEAFTER',     (0, 0), (0, -1), 0.5, BORDER_GRAY),
        ]))
        story.append(si_t)
        story.append(Spacer(1, 0.4 * cm))

    # Root cause analysis
    rca = ai.get('root_cause_analysis', [])
    if rca:
        story.append(section_header_block("Root Cause Analysis", styles))
        story.append(Spacer(1, 0.2 * cm))
        rca_rows = [[
            Paragraph("Issue", styles['table_header']),
            Paragraph("Possible Cause", styles['table_header']),
        ]]
        for r in rca:
            rca_rows.append([
                Paragraph(r.get('issue', ''), styles['table_cell']),
                Paragraph(r.get('possible_cause', ''), styles['table_cell']),
            ])
        cw5 = [(PAGE_W - 2 * MARGIN) * r for r in [0.35, 0.65]]
        rca_t = Table(rca_rows, colWidths=cw5)
        rca_t.setStyle(TableStyle([
            ('BACKGROUND',    (0, 0), (-1, 0), OLIVE_MID),
            ('GRID',          (0, 0), (-1, -1), 0.5, BORDER_GRAY),
            ('ROWBACKGROUNDS',(0, 1), (-1, -1), [WHITE, CREAM]),
            ('TOPPADDING',    (0, 0), (-1, -1), 6),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 6),
            ('LEFTPADDING',   (0, 0), (-1, -1), 6),
            ('RIGHTPADDING',  (0, 0), (-1, -1), 6),
            ('VALIGN',        (0, 0), (-1, -1), 'TOP'),
        ]))
        story.append(rca_t)
        story.append(Spacer(1, 0.4 * cm))

    # Action plan
    action_plan = ai.get('action_plan', [])
    if action_plan:
        story.append(section_header_block("Action Plan", styles))
        story.append(Spacer(1, 0.2 * cm))
        ap_rows = [[
            Paragraph("Area", styles['table_header']),
            Paragraph("Action", styles['table_header']),
            Paragraph("Responsible", styles['table_header']),
            Paragraph("Timeline", styles['table_header']),
            Paragraph("Expected Outcome", styles['table_header']),
        ]]
        for ap in action_plan:
            ap_rows.append([
                Paragraph(ap.get('area', ''), styles['table_cell']),
                Paragraph(ap.get('action', ''), styles['table_cell']),
                Paragraph(ap.get('responsible_person', ''), styles['table_cell']),
                Paragraph(ap.get('timeline', ''), styles['table_cell']),
                Paragraph(ap.get('expected_outcome', ''), styles['table_cell']),
            ])
        cw6 = [(PAGE_W - 2 * MARGIN) * r for r in [0.18, 0.26, 0.16, 0.15, 0.25]]
        ap_t = Table(ap_rows, colWidths=cw6)
        ap_t.setStyle(TableStyle([
            ('BACKGROUND',    (0, 0), (-1, 0), OLIVE_DARK),
            ('GRID',          (0, 0), (-1, -1), 0.5, BORDER_GRAY),
            ('ROWBACKGROUNDS',(0, 1), (-1, -1), [WHITE, CREAM]),
            ('TOPPADDING',    (0, 0), (-1, -1), 5),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 5),
            ('LEFTPADDING',   (0, 0), (-1, -1), 5),
            ('RIGHTPADDING',  (0, 0), (-1, -1), 5),
            ('VALIGN',        (0, 0), (-1, -1), 'TOP'),
            ('FONTSIZE',      (0, 1), (-1, -1), 8),
        ]))
        story.append(ap_t)
        story.append(Spacer(1, 0.4 * cm))

    # Monitoring
    monitoring = ai.get('monitoring', [])
    if monitoring:
        story.append(section_header_block("Monitoring and Evaluation", styles))
        story.append(Spacer(1, 0.2 * cm))
        for m in monitoring:
            story.append(Paragraph(f"• {m}", styles['bullet']))
        story.append(Spacer(1, 0.4 * cm))

    # Conclusion
    if ai.get('conclusion'):
        story.append(section_header_block("Conclusion", styles))
        story.append(Spacer(1, 0.2 * cm))
        story.append(Paragraph(ai['conclusion'], styles['body']))
        story.append(Spacer(1, 0.5 * cm))

    # Footer signature line
    story.append(HRFlowable(width="100%", thickness=1, color=BORDER_GRAY, spaceAfter=8))
    story.append(Paragraph(
        f"Generated by CQI System &nbsp;|&nbsp; {data.get('generated_at', '')} &nbsp;|&nbsp; {data.get('title', '')}",
        ParagraphStyle('footer', fontSize=7.5, fontName='Helvetica',
                       textColor=TEXT_MUTED, alignment=TA_CENTER)
    ))

    doc.build(story)
    print(f"PDF generated: {output_path}")


if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python cqi_pdf_generator.py <data.json> <output.pdf>")
        sys.exit(1)

    with open(sys.argv[1], 'r', encoding='utf-8') as f:
        payload = json.load(f)

    build_pdf(payload, sys.argv[2])