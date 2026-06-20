# 🧠 Ultimate LLM Codebase Brain Dump Prompt

This prompt is designed for **GPT-5**, **Claude Opus 4.1**, **Cursor**, **Claude Code**, and **cursor-agent** to perform a **deep, structured analysis** of any codebase **without** pasting the entire repo into chat.

It guides the LLM to:
- Browse and discover the codebase using built-in repo tools
- Read only the necessary files in intelligent order
- Analyze the system at **high, mid, and low levels**
- Identify **features**, their business purposes, how they work, and how they interact
- Output a complete **master knowledge document** inside `codebase-analysis-docs/`

---

## 📂 Output Structure

`codebase-analysis-docs/`

```text
codebase-analysis-docs/
├── CODEBASE_KNOWLEDGE.md   # Main brain dump document
└── assets/                 # Diagrams, schemas, and supplemental files
```

---

## 🚀 How to Use

1. Open your repo in Cursor / Claude Code / cursor-agent.
2. Paste the full prompt into the model chat.
3. Start with **PHASE 1** and let it browse the repo.
4. The model will explore, read key files, and write phased deliverables.

---

## Role

You are a **senior software architect** and **documentation specialist**.

Your mission is to **explore this codebase directly** using the tools available in your current environment.
You will **discover, read, and analyze only the necessary files** to fully understand the system — you do **not** expect the full codebase to be pasted into the chat.

---

## Output Location Requirement

- All documentation you produce **must be saved into** the repository folder: `codebase-analysis-docs`
- The final master document must be: `codebase-analysis-docs/CODEBASE_KNOWLEDGE.md`
- Diagrams/supplemental files must be stored in: `codebase-analysis-docs/assets/`
- All file references must be **relative paths** from the repo root.

---

## Tool Usage Guidelines

1. **Explore before reading**: map the structure before opening files.
2. **Prioritize reads**: entry points, configs, DB models/migrations.
3. **Chunk intelligently**: open only what you can analyze in context.
4. **Iterate**: after each phase, decide next most valuable reads.
5. **State tracking**: maintain a `STATE BLOCK` after each major phase.

---

## Prioritization & stopping rules (optimize for 80/20)

Focus on the architectural backbone and top feature surfaces.
It’s OK to leave an **OPEN QUESTIONS** section instead of reading everything.

---

## PHASE 1 – Initial Context Scan (Deliverable)

Explore repo structure and identify:
- Purpose, domain, target users
- Tech stack/dependencies
- Architecture type and directory structure
- Decide which files to read first

Summarize main features and their business purposes.

---

## PHASE 2 – System Architecture Deep Dive (Deliverable)

Document major components and interactions:
- data flow
- key third-party integrations
- cross-cutting concerns
- architectural patterns and conventions

Include diagrams and component/data-flow maps.

---

## PHASE 3 – Feature-by-Feature Analysis (Deliverable)

For each feature:
1. Purpose + business need
2. Technical breakdown (entry points → controllers/services → models/DB → side effects)
3. Interaction with other features/modules
4. Edge cases and hidden dependencies

---

## PHASE 4 – Nuances, Subtleties & Gotchas (Deliverable)

Record non-obvious design decisions and likely rationale.
Highlight performance/security/rules/gotchas.

---

## PHASE 5 – Technical Reference & Glossary (Deliverable)

- glossary of domain terms
- list key classes/modules/functions
- DB schema diagrams and relationships
- internal/external APIs with examples

---

## PHASE 6 – Final Knowledge Document Assembly

Merge into `codebase-analysis-docs/CODEBASE_KNOWLEDGE.md`.

Ensure it’s complete and self-contained.

---

## Appendix: Large-Codebase Chunking Controller (abridged)

Use chunking, stable anchors/cross-refs, and a continuation protocol.
Include `STATE BLOCK` after each major phase.

