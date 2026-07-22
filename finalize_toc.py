"""Final polish: remove leftover manual TOC lines; regenerate TOC via Word."""
from __future__ import annotations

import re
from pathlib import Path

from docx import Document
from docx.oxml.ns import qn
from docx.text.paragraph import Paragraph

DOC_PATH = Path(r"C:\Users\ishim\OneDrive\Desktop\EMMY - QR\EMMY_QR_FINAL_SUBMISSION.docx")


def delete_paragraph(paragraph: Paragraph):
    el = paragraph._element
    parent = el.getparent()
    if parent is not None:
        parent.remove(el)


def clean_manual_toc_remnants():
    doc = Document(str(DOC_PATH))
    # Patterns like "DECLARATION\ti" or "ABSTRACT\tv" or "CHAPTER...\t1"
    pat = re.compile(
        r"^(DECLARATION|CERTIFICATE|CERTFICATE|DEDICATION|ACKNOWLEDGEMENT|ABSTRACT|"
        r"LIST OF FIGURES|LIST OF TABLES|LIST OF SYMBOLS.*|TABLE OF CONTENTS|"
        r"CHAPTER\s+.+|REFERENCES|APPENDIX.*|"
        r"\d+\.\d+.*|INTRODUCTION|BACKGROUND|PROBLEM|OBJECTIVES|SCOPE|SIGNIFICANT|"
        r"General objective|Specific objectives|RELATED REVIEW|KEY TERMINOLOGIES|"
        r"Computer|Monitoring|QR-Code|Scanner|Smartphone|System|TECHNOLOGIES USED|"
        r"Database Management System|QR Code generation library|Mobile Compatibility|"
        r"DATA COLLECTION METHOD|METHODOLOGY|ANALYSIS OF EXISTING|NEW SYSTEM DESIGN|"
        r"Use Case|Flowchart|Data Flow|Data Dictionary|System requirements|"
        r"VISUALIZATION|Landing page|Login Interface|Admin Dashboard|User)"
        r".*\t+.*$",
        re.I,
    )
    deleted = 0
    for p in list(doc.paragraphs):
        t = p.text.strip()
        st = p.style.name if p.style else ""
        # never delete generated TOC/TOF entries (styles toc 1 / table of figures)
        if st.startswith("toc") or st.startswith("table of figures"):
            continue
        # leftover manual TOC lines between TABLE OF CONTENTS title and ABSTRACT heading
        if "\t" in t and (pat.match(t) or re.search(r"\t+[ivx0-9]+\s*$", t, re.I)):
            # Keep if it's an acronyms line like UTB\t:\tUniversity...
            if re.search(r"\t:\t", t):
                continue
            delete_paragraph(p)
            deleted += 1
    doc.save(str(DOC_PATH))
    print(f"Removed {deleted} leftover TOC lines.")


def regenerate_toc_word():
    import win32com.client

    word = win32com.client.DispatchEx("Word.Application")
    word.Visible = False
    word.DisplayAlerts = 0
    try:
        doc = word.Documents.Open(str(DOC_PATH))

        # Delete existing Tables of Contents and recreate cleanly
        while doc.TablesOfContents.Count > 0:
            doc.TablesOfContents(1).Delete()

        # Find "TABLE OF CONTENTS" paragraph and insert TOC after it
        toc_range = None
        for i in range(1, doc.Paragraphs.Count + 1):
            p = doc.Paragraphs(i)
            if p.Range.Text.strip().upper() == "TABLE OF CONTENTS":
                # insert after this paragraph
                toc_range = p.Range
                toc_range.Collapse(0)  # collapse to end
                break

        if toc_range is not None:
            toc = doc.TablesOfContents.Add(
                Range=toc_range,
                UseHeadingStyles=True,
                UpperHeadingLevel=1,
                LowerHeadingLevel=3,
                UseHyperlinks=True,
                HidePageNumbersInWeb=False,
                UseOutlineLevels=True,
            )
            toc.Update()

        # Update LOF / LOT fields
        doc.Fields.Update()
        try:
            for i in range(1, doc.TablesOfFigures.Count + 1):
                doc.TablesOfFigures(i).Update()
        except Exception as e:
            print("TOF update note:", e)

        doc.Repaginate()
        doc.Save()
        doc.Close(False)
        print("TOC regenerated.")
    finally:
        word.Quit()


if __name__ == "__main__":
    clean_manual_toc_remnants()
    regenerate_toc_word()
