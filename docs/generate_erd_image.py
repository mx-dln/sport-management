from __future__ import annotations

from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


OUT = Path(__file__).resolve().parent


def font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont:
    paths = [
        "C:/Windows/Fonts/arialbd.ttf" if bold else "C:/Windows/Fonts/arial.ttf",
        "C:/Windows/Fonts/calibrib.ttf" if bold else "C:/Windows/Fonts/calibri.ttf",
    ]
    for path in paths:
        if Path(path).exists():
            return ImageFont.truetype(path, size)
    return ImageFont.load_default()


F_TITLE = font(36, True)
F_SECTION = font(20, True)
F_TABLE = font(15, True)
F_FIELD = font(12)
F_FIELD_BOLD = font(12, True)
F_LABEL = font(11)

BG = "#ffffff"
PANEL = "#fffdf0"
TABLE = "#fff3a6"
HEADER = "#f3dd70"
FK = "#fff9cf"
INK = "#111111"
LINE = "#111111"


TABLES = {
    "users": ["PK id", "name", "email", "role", "status"],
    "sports": ["PK id", "name", "description", "status"],
    "teams": ["PK id", "FK sport_id", "FK coach_id", "name", "status"],
    "athletes": ["PK id", "FK user_id", "FK sport_id", "FK team_id", "student_id", "first_name", "last_name", "athlete_status"],
    "team_members": ["PK id", "FK team_id", "FK athlete_id", "assigned_at"],
    "requirement_types": ["PK id", "title", "description", "is_required"],
    "athlete_documents": ["PK id", "FK athlete_id", "FK requirement_type_id", "file_path", "status", "remarks"],
    "form_templates": ["PK id", "FK uploaded_by", "title", "file_path", "uploaded_at"],
    "training_schedules": ["PK id", "FK sport_id", "FK team_id", "FK coach_id", "training_date", "venue", "status"],
    "attendance": ["PK id", "FK schedule_id", "FK athlete_id", "FK marked_by", "status", "marked_at"],
    "announcements": ["PK id", "FK sport_id", "FK team_id", "FK created_by", "title", "body"],
    "sms_logs": ["PK id", "FK sent_by", "recipient_name", "phone_number", "message", "status"],
    "medical_records": ["PK id", "FK athlete_id", "FK recorded_by", "exam_date", "fitness_status", "clearance_status"],
    "competitions": ["PK id", "FK sport_id", "FK created_by", "name", "category", "venue", "status"],
    "competition_participants": ["PK id", "FK competition_id", "FK athlete_id", "FK coach_id", "event_name"],
    "competition_results": ["PK id", "FK competition_id", "FK athlete_id", "FK updated_by", "rank_place", "medal"],
    "athlete_histories": ["PK id", "FK athlete_id", "FK sport_id", "FK created_by", "competition_name", "result", "medal"],
}


def center(draw, box, text, fnt):
    x1, y1, x2, y2 = box
    bb = draw.textbbox((0, 0), text, font=fnt)
    draw.text((x1 + (x2 - x1 - bb[2] + bb[0]) / 2, y1 + (y2 - y1 - bb[3] + bb[1]) / 2), text, font=fnt, fill=INK)


def table(draw, x, y, name, width=225):
    fields = TABLES[name]
    height = 30 + 19 * len(fields) + 8
    draw.rectangle((x, y, x + width, y + height), fill=TABLE, outline=LINE, width=2)
    draw.rectangle((x, y, x + width, y + 30), fill=HEADER, outline=LINE, width=2)
    center(draw, (x, y, x + width, y + 30), name, F_TABLE)
    yy = y + 37
    for field in fields:
        if field.startswith("FK"):
            draw.rectangle((x + 1, yy - 2, x + width - 1, yy + 15), fill=FK)
        draw.text((x + 10, yy), field, font=F_FIELD_BOLD if field.startswith("PK") else F_FIELD, fill=INK)
        yy += 19
    return (x, y, x + width, y + height)


def panel(draw, box, title):
    draw.rounded_rectangle(box, radius=8, fill=PANEL, outline=LINE, width=2)
    draw.text((box[0] + 16, box[1] + 12), title, font=F_SECTION, fill=INK)


def arrow(draw, start, end, label):
    draw.line((start, end), fill=LINE, width=2)
    ex, ey = end
    sx, sy = start
    if abs(ex - sx) >= abs(ey - sy):
        pts = [(ex, ey), (ex - 9 if ex > sx else ex + 9, ey - 5), (ex - 9 if ex > sx else ex + 9, ey + 5)]
    else:
        pts = [(ex, ey), (ex - 5, ey - 9 if ey > sy else ey + 9), (ex + 5, ey - 9 if ey > sy else ey + 9)]
    draw.polygon(pts, fill=LINE)
    mx = (sx + ex) / 2
    my = (sy + ey) / 2
    w = draw.textlength(label, font=F_LABEL)
    draw.rectangle((mx - w / 2 - 4, my - 9, mx + w / 2 + 4, my + 7), fill=PANEL)
    draw.text((mx - w / 2, my - 8), label, font=F_LABEL, fill=INK)


def right(box):
    return box[2], (box[1] + box[3]) // 2


def left(box):
    return box[0], (box[1] + box[3]) // 2


def bottom(box):
    return (box[0] + box[2]) // 2, box[3]


def top(box):
    return (box[0] + box[2]) // 2, box[1]


def main():
    img = Image.new("RGB", (1700, 1550), BG)
    draw = ImageDraw.Draw(img)
    draw.text((50, 35), "Entity Relationship Diagram", font=F_TITLE, fill=INK)
    draw.text((52, 78), "Web-Based Sports Management Information System - based on database/sports_management.sql", font=F_FIELD, fill=INK)

    # Core profile and roster
    panel(draw, (45, 115, 825, 540), "Core Profile and Team Assignment")
    u = table(draw, 75, 175, "users")
    s = table(draw, 330, 175, "sports")
    t = table(draw, 585, 175, "teams")
    a = table(draw, 330, 360, "athletes")
    tm = table(draw, 585, 390, "team_members")
    arrow(draw, right(u), left(a), "1:M account")
    arrow(draw, right(s), left(t), "1:M")
    arrow(draw, bottom(s), top(a), "1:M")
    arrow(draw, bottom(t), right(a), "1:M")
    arrow(draw, bottom(t), top(tm), "1:M")
    arrow(draw, right(a), left(tm), "1:M")

    # Requirements and documents
    panel(draw, (875, 115, 1655, 540), "Requirements and Uploaded Files")
    rt = table(draw, 905, 195, "requirement_types")
    ad = table(draw, 1180, 195, "athlete_documents")
    aa = table(draw, 905, 385, "athletes")
    ft = table(draw, 1180, 385, "form_templates")
    uu = table(draw, 1435, 385, "users", width=190)
    arrow(draw, right(rt), left(ad), "1:M")
    arrow(draw, right(aa), left(ad), "1:M")
    arrow(draw, right(uu), right(ft), "1:M uploads")

    # Training and attendance
    panel(draw, (45, 585, 825, 1005), "Training Schedules and Attendance")
    ss = table(draw, 75, 645, "sports")
    tt = table(draw, 330, 645, "teams")
    ts = table(draw, 585, 645, "training_schedules")
    ath = table(draw, 330, 830, "athletes")
    att = table(draw, 585, 850, "attendance")
    usr = table(draw, 75, 835, "users")
    arrow(draw, right(ss), left(ts), "1:M")
    arrow(draw, right(tt), left(ts), "1:M")
    arrow(draw, bottom(ts), top(att), "1:M")
    arrow(draw, right(ath), left(att), "1:M")
    arrow(draw, right(usr), left(att), "1:M marks")

    # Competitions and history
    panel(draw, (875, 585, 1655, 1005), "Competitions and Athlete History")
    sp = table(draw, 905, 645, "sports")
    comp = table(draw, 1180, 645, "competitions")
    part = table(draw, 905, 835, "competition_participants")
    res = table(draw, 1180, 835, "competition_results")
    hist = table(draw, 1435, 735, "athlete_histories", width=190)
    ath2 = table(draw, 1435, 610, "athletes", width=190)
    arrow(draw, right(sp), left(comp), "1:M")
    arrow(draw, left(comp), right(part), "1:M")
    arrow(draw, bottom(comp), top(res), "1:M")
    arrow(draw, left(ath2), right(part), "1:M")
    arrow(draw, left(ath2), right(res), "1:M")
    arrow(draw, bottom(ath2), top(hist), "1:M")
    arrow(draw, right(sp), left(hist), "1:M")

    # Communication and medical
    panel(draw, (45, 1050, 1655, 1475), "Communication, Medical Records, and Audit Users")
    usr2 = table(draw, 75, 1115, "users")
    ann = table(draw, 330, 1115, "announcements")
    sms = table(draw, 585, 1115, "sms_logs")
    ath3 = table(draw, 850, 1115, "athletes")
    med = table(draw, 1105, 1115, "medical_records")
    sp2 = table(draw, 1360, 1115, "sports", width=220)
    arrow(draw, right(usr2), left(ann), "1:M creates")
    arrow(draw, right(usr2), left(sms), "1:M sends")
    arrow(draw, right(ath3), left(med), "1:M")
    arrow(draw, right(sp2), right(ann), "1:M targets")
    draw.text((330, 1390), "Other user audit links in schema: training_schedules.created_by, medical_records.recorded_by, competitions.created_by,", font=F_FIELD, fill=INK)
    draw.text((330, 1410), "competition_participants.coach_id, competition_results.updated_by, athlete_histories.created_by.", font=F_FIELD, fill=INK)

    draw.text((50, 1510), "Legend: PK = primary key, FK = foreign key, 1:M = one-to-many. Repeated tables keep the ERD readable.", font=F_FIELD, fill=INK)
    img.save(OUT / "chapter-3-entity-relationship-diagram.png", quality=95)
    print(OUT / "chapter-3-entity-relationship-diagram.png")


if __name__ == "__main__":
    main()
