#!/usr/bin/env python3
"""Migrate legacy FSMS views to the unified layout-header/footer pattern."""

from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
VIEWS = ROOT / "app" / "views"
SKIP_PARTS = {
    "includes",
    "login.php",
    "login-simple.php",
    "register.php",
}
SKIP_SUFFIX = "-mobile.php"

LAYOUT_HEADER = 'require_once __DIR__ . "/../includes/layout-header.php";'
LAYOUT_HEADER_ROOT = 'require_once __DIR__ . "/includes/layout-header.php";'
LAYOUT_FOOTER = 'require_once __DIR__ . "/../includes/layout-footer.php";'
LAYOUT_FOOTER_ROOT = 'require_once __DIR__ . "/includes/layout-footer.php";'

NAV_PATTERNS = [
    r'<\?php\s+include\s+__DIR__\s*\.\s*"/\.\./includes/navbar\.php"\s*;\s*\?>',
    r'<\?php\s+require_once\s+__DIR__\s*\.\s*"/\.\./includes/layout-header\.php"\s*;\s*\?>',
    r'<\?php\s+include\s+__DIR__\s*\.\s*\'/\.\./includes/navbar\.php\'\s*;\s*\?>',
]

END_PATTERNS = re.compile(
    r'(?:<\?php\s+include\s+__DIR__\s*\.\s*["\']/\.\./includes/footer\.php["\']\s*;\s*\?>\s*)?'
    r'(?:<script[^>]*bootstrap[^>]*></script>\s*)?'
    r'</body>\s*</html>\s*$',
    re.IGNORECASE | re.DOTALL,
)


def should_skip(path: Path) -> bool:
    if any(part in SKIP_PARTS for part in path.parts):
        return True
    if path.name in SKIP_PARTS:
        return True
    if path.name.endswith(SKIP_SUFFIX):
        return True
    return False


def depth_prefix(path: Path) -> str:
    rel = path.relative_to(VIEWS)
    depth = len(rel.parts) - 1
    return "/".join([".."] * depth) if depth else "."


def layout_paths(path: Path) -> tuple[str, str]:
    prefix = depth_prefix(path)
    if prefix == ".":
        return (
            'require_once __DIR__ . "/includes/layout-header.php";',
            'require_once __DIR__ . "/includes/layout-footer.php";',
        )
    return (
        f'require_once __DIR__ . "/{prefix}/includes/layout-header.php";',
        f'require_once __DIR__ . "/{prefix}/includes/layout-footer.php";',
    )


def extract_page_title(content: str) -> str | None:
    if m := re.search(r"\$pageTitle\s*=\s*['\"]([^'\"]+)['\"]", content):
        return m.group(1)
    if m := re.search(r"<title>([^<]+)</title>", content, flags=re.I):
        title = m.group(1).strip()
        title = re.sub(r"\s*[-·]\s*FSMS.*$", "", title, flags=re.I)
        title = re.sub(r"\s*·\s*Tharimpepe.*$", "", title, flags=re.I)
        return title.strip()
    return None


def strip_legacy_shell(content: str) -> str | None:
    if "<!DOCTYPE html>" not in content and "<html" not in content.lower():
        return None

    cut = None
    for pattern in NAV_PATTERNS:
        m = re.search(pattern, content, flags=re.I)
        if m:
            cut = m.end()
            break

    if cut is None:
        return None

    body = content[cut:].lstrip("\r\n")
    body = re.sub(r"<!--\s*Navigation\s*-->\s*", "", body, count=1, flags=re.I)
    body = re.sub(r"<!--\s*Page Header\s*-->\s*", "", body, count=1, flags=re.I)
    body = re.sub(r"<!--\s*Main Content\s*-->\s*", "", body, count=1, flags=re.I)
    body = re.sub(r"<!--\s*Footer\s*-->\s*", "", body, count=1, flags=re.I)
    body = body.replace('class="page-header"', 'class="fsms-page-header"')
    body = body.replace("class='page-header'", "class='fsms-page-header'")

    body = END_PATTERNS.sub("", body).rstrip() + "\n"
    return body


def build_header_php(path: Path, content: str, body: str) -> str:
    header_req, footer_req = layout_paths(path)
    title = extract_page_title(content) or "FSMS"

    # Preserve leading PHP blocks from original file (before doctype)
    leading = ""
    if content.lstrip().startswith("<?php"):
        end = content.find("<!DOCTYPE")
        if end == -1:
            end = content.find("<html")
        if end > 0:
            leading = content[:end].strip() + "\n"
            if "$pageTitle" not in leading:
                leading += f'$pageTitle = {json_escape(title)};\n'

    if not leading:
        leading = f"<?php\n$pageTitle = {json_escape(title)};\n"

    if not leading.rstrip().endswith("?>"):
        leading = leading.rstrip() + "\n"

    if "$pageTitle" not in leading:
        leading += f'$pageTitle = {json_escape(title)};\n'

    if header_req not in leading:
        leading += header_req + "\n?>\n\n"
    else:
        if "?>" not in leading:
            leading += "?>\n\n"

    return leading + body + f"<?php {footer_req} ?>\n"


def json_escape(value: str) -> str:
    return "'" + value.replace("\\", "\\\\").replace("'", "\\'") + "'"


def migrate_file(path: Path) -> bool:
    original = path.read_text(encoding="utf-8")
    body = strip_legacy_shell(original)
    if body is None:
        return False

    updated = build_header_php(path, original, body)
    if updated == original:
        return False

    path.write_text(updated, encoding="utf-8", newline="\n")
    return True


def main() -> None:
    changed = []
    for path in sorted(VIEWS.rglob("*.php")):
        if should_skip(path):
            continue
        if migrate_file(path):
            changed.append(path.relative_to(ROOT))

    print(f"Migrated {len(changed)} files:")
    for item in changed:
        print(f"  - {item}")


if __name__ == "__main__":
    main()
