from pathlib import Path
from zipfile import ZipFile

from lxml import etree

src = Path(r"C:\Users\micha\Downloads\FINALCHAP_123rev.docx")
out = Path(r"C:\Users\micha\OneDrive\Documents\Sites\sports-management\.codex_doc_review")
media_dir = out / "media"
media_dir.mkdir(parents=True, exist_ok=True)

ns = {
    "w": "http://schemas.openxmlformats.org/wordprocessingml/2006/main",
    "a": "http://schemas.openxmlformats.org/drawingml/2006/main",
    "r": "http://schemas.openxmlformats.org/officeDocument/2006/relationships",
}

with ZipFile(src) as z:
    for name in z.namelist():
        if name.startswith("word/media/"):
            (media_dir / Path(name).name).write_bytes(z.read(name))

    doc = etree.fromstring(z.read("word/document.xml"))
    rels = etree.fromstring(z.read("word/_rels/document.xml.rels"))
    relmap = {rel.get("Id"): rel.get("Target") for rel in rels}

body = doc.find("w:body", ns)
lines = []
image_counter = 0

for child in body:
    tag = etree.QName(child).localname
    if tag == "p":
        text = "".join(child.xpath(".//w:t/text()", namespaces=ns)).strip()
        embeds = child.xpath(".//a:blip/@r:embed", namespaces=ns)
        if text:
            lines.append(text)
        for rel_id in embeds:
            image_counter += 1
            lines.append(f"[IMAGE {image_counter}: {relmap.get(rel_id, rel_id)}]")
    elif tag == "tbl":
        rows = []
        for row in child.xpath("./w:tr", namespaces=ns):
            cells = []
            for cell in row.xpath("./w:tc", namespaces=ns):
                cell_text = " ".join(
                    item.strip()
                    for item in cell.xpath(".//w:t/text()", namespaces=ns)
                    if item.strip()
                )
                cells.append(cell_text)
            rows.append(" | ".join(cells))
        lines.append("[TABLE]\n" + "\n".join(rows))

(out / "body_with_images.txt").write_text("\n".join(lines), encoding="utf-8")
print(f"images in body order: {image_counter}")
print(f"media files: {len(list(media_dir.iterdir()))}")
print(out / "body_with_images.txt")
