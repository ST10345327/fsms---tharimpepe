#!/usr/bin/env python3
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1] / "app" / "views"

def fix_text(text: str) -> str:
    lines = text.splitlines(keepends=True)
    out: list[str] = []
    i = 0
    changed = False

    while i < len(lines):
        line = lines[i]
        if (
            line.strip() == "?>"
            and i + 1 < len(lines)
            and lines[i + 1].startswith("require_once __DIR__")
            and "layout-header.php" in lines[i + 1]
        ):
            changed = True
            i += 1
            out.append(lines[i])
            i += 1
            if i < len(lines) and lines[i].strip() == "?>":
                out.append(lines[i])
                i += 1
            else:
                out.append("?>\n")
            continue

        if (
            line.rstrip().endswith("?>")
            and "<?php" in line
            and i + 1 < len(lines)
            and lines[i + 1].startswith("require_once __DIR__")
            and "layout-header.php" in lines[i + 1]
        ):
            changed = True
            php = line.replace("?>", "").rstrip() + "\n"
            if not php.lstrip().startswith("<?php"):
                php = "<?php\n" + php.lstrip()
            out.append(php)
            i += 1
            out.append(lines[i])
            i += 1
            if i < len(lines) and lines[i].strip() == "?>":
                out.append(lines[i])
                i += 1
            else:
                out.append("?>\n")
            continue

        out.append(line)
        i += 1

    return ("".join(out), changed)


def main() -> None:
    fixed = []
    for path in sorted(ROOT.rglob("*.php")):
        if "includes" in path.parts:
            continue
        original = path.read_text(encoding="utf-8")
        updated, changed = fix_text(original)
        if changed and updated != original:
            path.write_text(updated, encoding="utf-8", newline="\n")
            fixed.append(path.relative_to(ROOT))

    print(f"Fixed {len(fixed)} files")
    for item in fixed:
        print(f"  - {item}")


if __name__ == "__main__":
    main()
