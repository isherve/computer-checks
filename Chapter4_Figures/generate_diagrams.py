"""Generate missing Chapter 4 academic diagrams: DFD Level 0, DFD Level 1, ERD."""
from pathlib import Path
import matplotlib.pyplot as plt
import matplotlib.patches as mpatches
from matplotlib.patches import FancyBboxPatch, Ellipse, FancyArrowPatch, Rectangle, Circle, Polygon
import numpy as np

OUT = Path(__file__).resolve().parent
plt.rcParams["font.family"] = "DejaVu Sans"
plt.rcParams["font.size"] = 9


def arrow(ax, p1, p2, color="#333333", style="-|>", lw=1.2, connectionstyle="arc3,rad=0"):
    ax.add_patch(
        FancyArrowPatch(
            p1, p2, arrowstyle=style, mutation_scale=12,
            color=color, lw=lw, connectionstyle=connectionstyle,
        )
    )


def text_along(ax, x, y, s, fontsize=7.5, color="#222", ha="center", va="center", rotation=0, bbox=None, fontweight="normal"):
    ax.text(x, y, s, fontsize=fontsize, color=color, ha=ha, va=va, rotation=rotation, bbox=bbox, fontweight=fontweight)


def draw_external(ax, x, y, label, w=1.6, h=0.7):
    rect = FancyBboxPatch(
        (x - w / 2, y - h / 2), w, h,
        boxstyle="square,pad=0", linewidth=1.5, edgecolor="#111", facecolor="#fff",
    )
    ax.add_patch(rect)
    # left and right double bars for external entity look
    ax.plot([x - w / 2, x - w / 2], [y - h / 2, y + h / 2], color="#111", lw=3)
    ax.plot([x + w / 2, x + w / 2], [y - h / 2, y + h / 2], color="#111", lw=3)
    ax.text(x, y, label, ha="center", va="center", fontsize=9, fontweight="bold")


def draw_process(ax, x, y, num, label, r=0.85):
    circ = Circle((x, y), r, linewidth=1.5, edgecolor="#111", facecolor="#E8F4FC")
    ax.add_patch(circ)
    ax.text(x, y + 0.18, num, ha="center", va="center", fontsize=8, fontweight="bold")
    ax.text(x, y - 0.22, label, ha="center", va="center", fontsize=7.5)


def draw_store(ax, x, y, label, w=2.2, h=0.55):
    # open-ended data store (D rectangle with open right side)
    ax.plot([x - w / 2, x - w / 2], [y - h / 2, y + h / 2], color="#111", lw=1.5)
    ax.plot([x - w / 2, x + w / 2], [y + h / 2, y + h / 2], color="#111", lw=1.5)
    ax.plot([x - w / 2, x + w / 2], [y - h / 2, y - h / 2], color="#111", lw=1.5)
    ax.add_patch(Rectangle((x - w / 2, y - h / 2), 0.35, h, facecolor="#FFF3CD", edgecolor="none"))
    ax.text(x + 0.05, y, label, ha="center", va="center", fontsize=8)


def generate_dfd0():
    fig, ax = plt.subplots(figsize=(11, 7))
    ax.set_xlim(0, 11)
    ax.set_ylim(0, 7)
    ax.set_aspect("equal")
    ax.axis("off")
    ax.set_title("DFD Level 0 – System for Personal Computer Checks Using QR Codes",
                 fontsize=12, fontweight="bold", pad=12)

    # External entities
    draw_external(ax, 1.5, 5.2, "Gate Officer")
    draw_external(ax, 1.5, 1.8, "Administrator")

    # Central process
    draw_process(ax, 5.5, 3.5, "0", "PC Check\nQR System", r=1.35)

    # Data stores on right
    draw_store(ax, 9.2, 5.0, "D1: Computer_info")
    draw_store(ax, 9.2, 3.5, "D2: Users")
    draw_store(ax, 9.2, 2.0, "D3: Logs")

    # Flows Gate Officer <-> System
    arrow(ax, (2.35, 5.2), (4.25, 4.2))
    text_along(ax, 3.2, 5.0, "Device details /\nScan request", fontsize=7)

    arrow(ax, (4.25, 4.0), (2.35, 4.7), connectionstyle="arc3,rad=0.15")
    text_along(ax, 2.9, 4.35, "QR code /\nVerification result", fontsize=7)

    # Flows Admin <-> System
    arrow(ax, (2.35, 1.8), (4.25, 2.7))
    text_along(ax, 3.1, 2.05, "User data /\nReport request", fontsize=7)

    arrow(ax, (4.25, 2.9), (2.35, 2.2), connectionstyle="arc3,rad=-0.15")
    text_along(ax, 2.9, 2.75, "Reports /\nConfirmations", fontsize=7)

    # System <-> stores
    arrow(ax, (6.7, 4.3), (8.1, 5.0))
    text_along(ax, 7.5, 4.85, "Store/retrieve\ndevice", fontsize=6.5)

    arrow(ax, (6.85, 3.5), (8.1, 3.5))
    text_along(ax, 7.45, 3.7, "Auth / users", fontsize=6.5)

    arrow(ax, (6.7, 2.7), (8.1, 2.2))
    text_along(ax, 7.5, 2.25, "Write logs", fontsize=6.5)

    fig.tight_layout()
    path = OUT / "Figure4_DFD_Level0.png"
    fig.savefig(path, dpi=200, bbox_inches="tight", facecolor="white")
    plt.close(fig)
    print("Wrote", path)


def generate_dfd1():
    fig, ax = plt.subplots(figsize=(14, 10))
    ax.set_xlim(0, 14)
    ax.set_ylim(0, 10)
    ax.axis("off")
    ax.set_title("DFD Level 1 – System for Personal Computer Checks Using QR Codes",
                 fontsize=12, fontweight="bold", pad=10)

    # External entities
    draw_external(ax, 1.3, 8.5, "Gate Officer", w=1.7, h=0.65)
    draw_external(ax, 1.3, 1.5, "Administrator", w=1.7, h=0.65)

    # Processes
    procs = [
        (4.0, 8.2, "1.0", "Authenticate\nUser"),
        (7.0, 8.2, "2.0", "Register\nComputer"),
        (10.0, 8.2, "3.0", "Generate\nQR Code"),
        (10.0, 5.2, "4.0", "Scan &\nVerify QR"),
        (7.0, 5.2, "5.0", "Log\nAction"),
        (4.0, 5.2, "6.0", "Manage\nUsers"),
        (7.0, 2.5, "7.0", "Generate\nReport"),
    ]
    for x, y, num, lab in procs:
        draw_process(ax, x, y, num, lab, r=0.78)

    # Data stores
    draw_store(ax, 12.5, 8.2, "D1: Computer_info", w=2.3)
    draw_store(ax, 12.5, 5.2, "D3: Logs", w=2.3)
    draw_store(ax, 12.5, 2.5, "D2: Users", w=2.3)

    # Gate officer flows
    arrow(ax, (2.2, 8.5), (3.25, 8.3))
    text_along(ax, 2.6, 8.85, "Login", fontsize=6.5)

    arrow(ax, (2.2, 8.2), (3.25, 7.6), connectionstyle="arc3,rad=-0.2")
    text_along(ax, 2.3, 7.7, "Device data", fontsize=6.5)

    arrow(ax, (2.2, 7.9), (9.2, 5.7), connectionstyle="arc3,rad=0.25")
    text_along(ax, 4.8, 6.6, "Scan request", fontsize=6.5)

    # Auth <-> Users
    arrow(ax, (4.7, 7.7), (11.4, 2.8), connectionstyle="arc3,rad=-0.35")
    text_along(ax, 8.2, 4.0, "Credentials", fontsize=6)

    # Register -> Computer_info
    arrow(ax, (7.75, 8.2), (11.35, 8.2))
    text_along(ax, 9.4, 8.45, "Save device", fontsize=6.5)

    # Register -> Generate QR
    arrow(ax, (7.8, 8.2), (9.2, 8.2))

    # Generate QR -> Computer_info
    arrow(ax, (10.7, 8.0), (11.35, 8.2), connectionstyle="arc3,rad=-0.2")
    text_along(ax, 11.0, 7.55, "QR link", fontsize=6)

    # Scan verify <-> Computer_info
    arrow(ax, (10.75, 5.5), (11.35, 7.9), connectionstyle="arc3,rad=0.2")
    text_along(ax, 11.6, 6.7, "Match\ndevice", fontsize=6)

    # Scan / Register -> Log
    arrow(ax, (9.25, 5.2), (8.0, 5.2))
    arrow(ax, (7.75, 5.2), (11.35, 5.2))
    text_along(ax, 9.5, 5.45, "Write log", fontsize=6.5)

    # Admin flows
    arrow(ax, (2.2, 1.7), (3.3, 4.5), connectionstyle="arc3,rad=0.15")
    text_along(ax, 2.0, 3.2, "User mgmt", fontsize=6.5, rotation=70)

    arrow(ax, (2.2, 1.5), (6.2, 2.5), connectionstyle="arc3,rad=-0.1")
    text_along(ax, 3.8, 1.7, "Report request", fontsize=6.5)

    # Manage users <-> Users store
    arrow(ax, (4.75, 5.0), (11.35, 2.7), connectionstyle="arc3,rad=0.25")
    text_along(ax, 8.5, 3.3, "CRUD users", fontsize=6)

    # Report <-> Logs / Computer_info
    arrow(ax, (7.75, 2.7), (11.35, 2.6))
    text_along(ax, 9.6, 2.9, "User info", fontsize=6)
    arrow(ax, (7.6, 3.2), (11.35, 5.0), connectionstyle="arc3,rad=-0.2")
    text_along(ax, 9.8, 4.2, "Log data", fontsize=6)

    # Response to gate officer
    arrow(ax, (9.25, 7.6), (2.2, 7.5), connectionstyle="arc3,rad=0.35")
    text_along(ax, 5.5, 7.0, "QR / Pass result", fontsize=6.5)

    # Response to admin
    arrow(ax, (6.3, 2.0), (2.2, 1.3), connectionstyle="arc3,rad=0.15")
    text_along(ax, 4.0, 1.35, "Report output", fontsize=6.5)

    fig.tight_layout()
    path = OUT / "Figure5_DFD_Level1.png"
    fig.savefig(path, dpi=200, bbox_inches="tight", facecolor="white")
    plt.close(fig)
    print("Wrote", path)


def entity_box(ax, x, y, title, fields, w=3.0, row_h=0.38):
    n = len(fields) + 1
    h = n * row_h
    # header
    ax.add_patch(FancyBboxPatch(
        (x, y - h), w, h, boxstyle="square,pad=0",
        linewidth=1.4, edgecolor="#111", facecolor="#fff",
    ))
    ax.add_patch(Rectangle((x, y - row_h), w, row_h, facecolor="#1F4E79", edgecolor="#111", linewidth=1.4))
    ax.text(x + w / 2, y - row_h / 2, title, ha="center", va="center",
            color="white", fontsize=10, fontweight="bold")
    for i, f in enumerate(fields):
        yy = y - row_h * (i + 1) - row_h / 2
        ax.text(x + 0.12, yy, f, ha="left", va="center", fontsize=8, family="DejaVu Sans Mono")
        if i < len(fields) - 1:
            ax.plot([x, x + w], [y - row_h * (i + 2), y - row_h * (i + 2)], color="#ccc", lw=0.6)
    return h


def generate_erd():
    fig, ax = plt.subplots(figsize=(13, 8))
    ax.set_xlim(0, 13)
    ax.set_ylim(0, 8)
    ax.axis("off")
    ax.set_title("Entity Relationship Diagram – PC Check QR System",
                 fontsize=12, fontweight="bold", pad=12)

    # Users
    h1 = entity_box(ax, 0.5, 7.2, "Users", [
        "PK  id",
        "    user_type",
        "UK  nid",
        "    names",
        "    email",
        "    password",
    ], w=2.8)

    # Computer_info
    h2 = entity_box(ax, 5.0, 7.2, "Computer_info", [
        "PK  id",
        "UK  sn",
        "    model",
        "    type",
        "UK  owno",
        "    owname",
        "    date",
    ], w=3.0)

    # Logs
    h3 = entity_box(ax, 9.5, 7.2, "Logs", [
        "PK  log_id",
        "FK  sn",
        "    model",
        "    type",
        "    owno",
        "    owname",
        "    action",
        "    comment",
        "    date",
    ], w=3.0)

    # Relationship diamonds
    def diamond(cx, cy, label, s=0.55):
        pts = np.array([[cx, cy + s], [cx + s * 1.3, cy], [cx, cy - s], [cx - s * 1.3, cy]])
        ax.add_patch(Polygon(pts, closed=True, facecolor="#FFF3CD", edgecolor="#111", lw=1.3))
        ax.text(cx, cy, label, ha="center", va="center", fontsize=7.5, fontweight="bold")

    diamond(3.9, 3.6, "performs")
    diamond(8.3, 3.6, "references")

    # Users -- performs -- Logs (1:N)
    arrow(ax, (3.3, 4.8), (3.5, 4.1), style="-")
    arrow(ax, (4.3, 3.6), (9.5, 4.0), style="-")
    text_along(ax, 2.9, 4.3, "1", fontsize=10, fontweight="bold")
    text_along(ax, 9.0, 4.3, "N", fontsize=10, fontweight="bold")
    text_along(ax, 6.5, 4.05, "user action logged", fontsize=7, color="#444")

    # Computer_info -- references -- Logs (1:N) via sn
    arrow(ax, (6.5, 4.5), (7.6, 4.0), style="-")
    arrow(ax, (9.0, 3.6), (10.5, 4.0), style="-")
    text_along(ax, 6.2, 4.1, "1", fontsize=10, fontweight="bold")
    text_along(ax, 10.2, 4.35, "N", fontsize=10, fontweight="bold")
    text_along(ax, 8.4, 2.85, "sn (FK)", fontsize=7.5, color="#444")

    # Legend
    ax.add_patch(FancyBboxPatch((0.5, 0.35), 5.5, 1.3, boxstyle="round,pad=0.05",
                                facecolor="#F8F9FA", edgecolor="#999", lw=1))
    ax.text(0.7, 1.4, "Legend", fontsize=9, fontweight="bold")
    ax.text(0.7, 1.05, "PK = Primary Key    UK = Unique Key    FK = Foreign Key", fontsize=8)
    ax.text(0.7, 0.7, "Cardinality: 1 = One side    N = Many side", fontsize=8)
    ax.text(0.7, 0.4, "Relationships: Users (1) —performs→ Logs (N); Computer_info (1) —references→ Logs (N)", fontsize=7.5)

    fig.tight_layout()
    path = OUT / "Figure8_ERD.png"
    fig.savefig(path, dpi=200, bbox_inches="tight", facecolor="white")
    plt.close(fig)
    print("Wrote", path)


if __name__ == "__main__":
    generate_dfd0()
    generate_dfd1()
    generate_erd()
