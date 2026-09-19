from __future__ import annotations

import math
import textwrap
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


OUT = Path(__file__).resolve().parent


def font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont:
    candidates = [
        "C:/Windows/Fonts/arialbd.ttf" if bold else "C:/Windows/Fonts/arial.ttf",
        "C:/Windows/Fonts/calibrib.ttf" if bold else "C:/Windows/Fonts/calibri.ttf",
    ]
    for candidate in candidates:
        if Path(candidate).exists():
            return ImageFont.truetype(candidate, size)
    return ImageFont.load_default()


F_TITLE = font(38, True)
F_NODE = font(18)
F_BOLD = font(18, True)
F_SMALL = font(14)
F_TINY = font(12)

BG = "#ffffff"
YELLOW = "#fff3a6"
GREY = "#d9d9d9"
LINE = "#111111"
INK = "#111111"


def wrap(text: str, width: int) -> list[str]:
    return textwrap.wrap(text, width=width, break_long_words=False) or [""]


def center_text(draw: ImageDraw.ImageDraw, box, text: str, fnt=F_NODE, width=22):
    lines = wrap(text, width)
    line_h = fnt.getbbox("Ag")[3] - fnt.getbbox("Ag")[1] + 6
    total_h = line_h * len(lines)
    x1, y1, x2, y2 = box
    y = y1 + ((y2 - y1) - total_h) / 2
    for line in lines:
        bb = draw.textbbox((0, 0), line, font=fnt)
        draw.text((x1 + ((x2 - x1) - (bb[2] - bb[0])) / 2, y), line, fill=INK, font=fnt)
        y += line_h


def label(draw: ImageDraw.ImageDraw, xy, text: str, width=30):
    x, y = xy
    lines = wrap(text, width)
    line_h = 17
    for i, line in enumerate(lines):
        bb = draw.textbbox((0, 0), line, font=F_TINY)
        draw.rectangle((x - 3, y + i * line_h - 1, x + bb[2] + 3, y + i * line_h + 15), fill=BG)
        draw.text((x, y + i * line_h), line, fill=INK, font=F_TINY)


def entity(draw, box, text):
    draw.rectangle(box, fill=YELLOW, outline=LINE, width=2)
    center_text(draw, box, text, F_NODE, 24)


def process(draw, box, number, text):
    x1, y1, x2, y2 = box
    draw.rounded_rectangle(box, radius=16, fill=YELLOW, outline=LINE, width=2)
    header_h = 28
    draw.line((x1, y1 + header_h, x2, y1 + header_h), fill=LINE, width=2)
    draw.line((x1 + 42, y1, x1 + 42, y1 + header_h), fill=LINE, width=2)
    center_text(draw, (x1, y1, x1 + 42, y1 + header_h), str(number), F_NODE, 3)
    center_text(draw, (x1 + 42, y1 + header_h, x2, y2), text, F_NODE, 25)


def datastore(draw, box, text):
    x1, y1, x2, y2 = box
    tag_w = 42
    draw.rectangle((x1, y1, x2, y2), fill=YELLOW, outline=LINE, width=2)
    draw.rectangle((x1, y1, x1 + tag_w, y2), fill=GREY, outline=LINE, width=2)
    center_text(draw, (x1, y1, x1 + tag_w, y2), "D", F_NODE, 2)
    center_text(draw, (x1 + tag_w, y1, x2, y2), text, F_NODE, 22)


def arrowhead(draw, start, end):
    angle = math.atan2(end[1] - start[1], end[0] - start[0])
    size = 10
    pts = [
        end,
        (end[0] - size * math.cos(angle - math.pi / 7), end[1] - size * math.sin(angle - math.pi / 7)),
        (end[0] - size * math.cos(angle + math.pi / 7), end[1] - size * math.sin(angle + math.pi / 7)),
    ]
    draw.polygon(pts, fill=LINE)


def flow(draw, points, text="", text_at=None):
    draw.line(points, fill=LINE, width=2)
    arrowhead(draw, points[-2], points[-1])
    if text and text_at:
        label(draw, text_at, text)


def title(draw, text):
    draw.text((40, 28), text, font=F_TITLE, fill=INK)


def level0():
    img = Image.new("RGB", (1400, 900), BG)
    d = ImageDraw.Draw(img)
    title(d, "Level 0 Data Flow Diagram")

    entity(d, (70, 130, 330, 205), "Admin / Sports Coordinator")
    entity(d, (70, 330, 330, 405), "Coach")
    entity(d, (70, 530, 330, 605), "Athlete")
    entity(d, (1050, 530, 1310, 605), "Semaphore SMS Service")
    process(d, (560, 290, 870, 450), "0", "Sports Management Information System")

    flow(d, [(330, 160), (470, 160), (470, 335), (560, 335)], "user accounts, sports, teams, schedules", (350, 135))
    flow(d, [(560, 365), (455, 365), (455, 190), (330, 190)], "dashboards, reports, document status", (350, 210))
    flow(d, [(330, 360), (560, 375)], "roster updates, attendance, announcements", (350, 335))
    flow(d, [(560, 405), (330, 390)], "assigned teams, athlete lists, schedules", (350, 410))
    flow(d, [(330, 560), (470, 560), (470, 420), (560, 420)], "registration, biodata, document uploads", (350, 535))
    flow(d, [(870, 365), (965, 365), (965, 555), (1050, 555)], "SMS message request", (900, 500))
    flow(d, [(1050, 590), (945, 590), (945, 420), (870, 420)], "send result / delivery status", (895, 610))
    flow(d, [(870, 335), (965, 335), (965, 580), (330, 580)], "profile, schedules, attendance status", (900, 310))

    img.save(OUT / "chapter-3-dfd-level-0.png", quality=95)
    return img


def level1():
    img = Image.new("RGB", (1700, 1280), BG)
    d = ImageDraw.Draw(img)
    title(d, "Level 1 Data Flow Diagram")

    # Duplicate external entities are used to keep lines straight and readable.
    lanes = [
        {
            "y": 120,
            "entity": ("Admin / Sports Coordinator", "account and setup data", "dashboard and reports"),
            "process": ("1", "User Access and Registration"),
            "stores": [("Users", "user record")],
        },
        {
            "y": 330,
            "entity": ("Athlete", "registration, biodata, documents", "profile and document status"),
            "process": ("2", "Athlete Profile and Document Management"),
            "stores": [("Athletes", "athlete biodata/profile"), ("Requirement Documents", "document requirements/status")],
        },
        {
            "y": 540,
            "entity": ("Coach", "team and attendance updates", "team schedules and rosters"),
            "process": ("3", "Sports, Teams, Schedules and Attendance"),
            "stores": [("Sports / Teams", "sport, team and roster data"), ("Schedules / Attendance", "schedule and attendance data")],
        },
        {
            "y": 750,
            "entity": ("Athlete / Coach", "announcement or SMS request", "announcements and notifications"),
            "process": ("4", "Announcements and SMS Notification"),
            "stores": [("Announcements / SMS Logs", "announcement and SMS log")],
        },
        {
            "y": 960,
            "entity": ("Admin / Sports Coordinator", "medical, competition and report request", "medical, competition and report output"),
            "process": ("5", "Medical, Competition and Reports"),
            "stores": [("Medical Records", "medical data"), ("Competitions / Histories", "competition and history data"), ("System Settings", "school and app settings")],
        },
    ]

    process_boxes = []
    for lane in lanes:
        y = lane["y"]
        entity(d, (55, y, 305, y + 60), lane["entity"][0])
        pbox = (525, y - 5, 835, y + 100)
        process_boxes.append(pbox)
        process(d, pbox, lane["process"][0], lane["process"][1])

        flow(d, [(305, y + 22), (525, y + 22)], lane["entity"][1], (325, y - 2))
        flow(d, [(525, y + 70), (305, y + 48)], lane["entity"][2], (325, y + 60))

        store_x = 1040
        for index, (store_name, data_label) in enumerate(lane["stores"]):
            sy = y - 5 + index * 74
            datastore(d, (store_x, sy, store_x + 290, sy + 55), store_name)
            flow(d, [(835, y + 36 + index * 20), (store_x, sy + 28)], data_label, (865, sy + 8))

    # Process-to-process outputs use one center line.
    for index in range(len(process_boxes) - 1):
        upper = process_boxes[index]
        lower = process_boxes[index + 1]
        flow(
            d,
            [(680, upper[3]), (680, lower[1])],
            [
                "verified athlete account",
                "eligible athlete/team details",
                "training recipients",
                "communication logs",
            ][index],
            (695, upper[3] + 36),
        )

    # SMS service is beside process 4 on a direct lane.
    entity(d, (1370, 670, 1625, 730), "Semaphore SMS Service")
    flow(d, [(835, 780), (940, 780), (940, 700), (1370, 700)], "SMS message request", (1040, 678))
    flow(d, [(1370, 725), (955, 725), (955, 825), (835, 825)], "send result", (1055, 735))

    d.text((55, 1230), "Legend: repeated external entities keep the DFD readable; D boxes are data stores.", fill=INK, font=F_SMALL)
    img.save(OUT / "chapter-3-dfd-level-1.png", quality=95)
    return img


def combined():
    a = Image.open(OUT / "chapter-3-dfd-level-0.png")
    b = Image.open(OUT / "chapter-3-dfd-level-1.png")
    w = max(a.width, b.width)
    img = Image.new("RGB", (w, a.height + b.height + 80), BG)
    img.paste(a, ((w - a.width) // 2, 0))
    img.paste(b, ((w - b.width) // 2, a.height + 80))
    img.save(OUT / "chapter-3-dfd-level-0-to-1.png", quality=95)


if __name__ == "__main__":
    level0()
    level1()
    combined()
    print(OUT / "chapter-3-dfd-level-0.png")
    print(OUT / "chapter-3-dfd-level-1.png")
    print(OUT / "chapter-3-dfd-level-0-to-1.png")
