"""Simple Word COM: Roman front matter, Arabic from Chapter One, rebuild TOC H1-H3."""
import sys
from pathlib import Path

import win32com.client

PATH = str(Path(r"C:\Users\ishim\OneDrive\Desktop\EMMY - QR\EMMY_QR_FINAL_SUBMISSION.docx"))

wdHeaderFooterPrimary = 1
wdAlignPageNumberCenter = 1
wdSectionBreakNextPage = 2
wdCollapseStart = 1
wdCollapseEnd = 0
wdLowerRoman = 2
wdArabic = 0


def log(msg):
    print(msg, flush=True)


def main():
    log("Starting Word...")
    word = win32com.client.DispatchEx("Word.Application")
    word.Visible = False
    word.DisplayAlerts = 0
    doc = None
    try:
        log("Opening document...")
        doc = word.Documents.Open(PATH, ReadOnly=False, AddToRecentFiles=False)
        log(f"Sections={doc.Sections.Count}, Paragraphs={doc.Paragraphs.Count}")

        # Find CHAPTER ONE (Heading 1)
        ch1 = None
        for i in range(1, doc.Paragraphs.Count + 1):
            p = doc.Paragraphs(i)
            txt = p.Range.Text.replace("\r", "").replace("\x07", "").strip()
            try:
                style = str(p.Style.NameLocal)
            except Exception:
                style = ""
            if txt.upper().startswith("CHAPTER ONE") and style.startswith("Heading"):
                ch1 = p
                break
        if ch1 is None:
            raise RuntimeError("CHAPTER ONE Heading not found")
        log(f"CHAPTER ONE found, style={ch1.Style.NameLocal}")

        # Ensure Chapter One starts a section
        sec = ch1.Range.Sections(1)
        first_start = sec.Range.Paragraphs(1).Range.Start
        if abs(first_start - ch1.Range.Start) > 10:
            log("Inserting section break before CHAPTER ONE...")
            r = ch1.Range.Duplicate
            r.Collapse(wdCollapseStart)
            r.InsertBreak(wdSectionBreakNextPage)
            # re-find
            ch1 = None
            for i in range(1, doc.Paragraphs.Count + 1):
                p = doc.Paragraphs(i)
                txt = p.Range.Text.replace("\r", "").strip()
                try:
                    style = str(p.Style.NameLocal)
                except Exception:
                    style = ""
                if txt.upper().startswith("CHAPTER ONE") and style.startswith("Heading"):
                    ch1 = p
                    break
            if ch1 is None:
                raise RuntimeError("CHAPTER ONE missing after break")

        body_idx = ch1.Range.Sections(1).Index
        log(f"Body section index = {body_idx} / {doc.Sections.Count}")

        # Configure each section footer page numbers
        for i in range(1, doc.Sections.Count + 1):
            section = doc.Sections(i)
            footer = section.Footers(wdHeaderFooterPrimary)
            footer.LinkToPrevious = False

            # Clear footer
            footer.Range.Text = ""

            # Add page number field centered
            footer.PageNumbers.Add(PageNumberAlignment=wdAlignPageNumberCenter, FirstPage=True)

            pn = footer.PageNumbers
            if i < body_idx:
                pn.NumberStyle = wdLowerRoman
                if i == 1:
                    pn.RestartNumberingAtSection = True
                    pn.StartingNumber = 1
                else:
                    pn.RestartNumberingAtSection = False
            else:
                pn.NumberStyle = wdArabic
                if i == body_idx:
                    pn.RestartNumberingAtSection = True
                    pn.StartingNumber = 1
                else:
                    pn.RestartNumberingAtSection = False
            log(f"Section {i}: {'Roman' if i < body_idx else 'Arabic'}")

        # Unlink heading list templates
        for name in ["Heading 1", "Heading 2", "Heading 3", "Heading 4"]:
            try:
                doc.Styles(name).LinkToListTemplate(Link=False)
            except Exception:
                pass

        # Rebuild TOC levels 1-3
        log("Rebuilding TOC...")
        while doc.TablesOfContents.Count > 0:
            doc.TablesOfContents(1).Delete()

        toc_range = None
        for i in range(1, doc.Paragraphs.Count + 1):
            txt = doc.Paragraphs(i).Range.Text.replace("\r", "").strip().upper()
            if txt == "TABLE OF CONTENTS":
                toc_range = doc.Paragraphs(i).Range
                toc_range.Collapse(wdCollapseEnd)
                break

        if toc_range is not None:
            toc = doc.TablesOfContents.Add(
                Range=toc_range,
                UseHeadingStyles=True,
                UpperHeadingLevel=1,
                LowerHeadingLevel=3,
                UseHyperlinks=True,
                IncludePageNumbers=True,
                RightAlignPageNumbers=True,
            )
            toc.Update()
            log("TOC updated.")

        log("Updating fields...")
        doc.Fields.Update()
        try:
            for i in range(1, doc.TablesOfFigures.Count + 1):
                doc.TablesOfFigures(i).Update()
        except Exception as e:
            log(f"TOF note: {e}")

        doc.Repaginate()
        doc.Save()
        log("Saved OK")
        doc.Close(False)
        doc = None
    finally:
        if doc is not None:
            try:
                doc.Close(False)
            except Exception:
                pass
        word.Quit()
        log("Word quit.")


if __name__ == "__main__":
    main()
