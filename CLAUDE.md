
# CLAUDE.md — Lead Insights (Statamic 6 Addon)

**IMPORTANT**: Read and follow all instructions in `AGENTS.md` — it contains the full project context:
structure, conventions, commands, workflow rules, git rules, testing rules, edition gating, release procedure, etc.

This file contains Claude-specific behavioral instructions only.

## Documentation-first mode

When answering questions about Statamic / PHPUnit / PHP:
1) Prefer official documentation and/or source code as the primary authority.
2) Always state the assumed version (Statamic v6, PHPUnit v12). If unknown, ask or make a clearly labeled assumption.
3) Never invent API, config keys, tags, methods, or CLI flags. If not confirmed in docs/source, say so and propose how to verify.
4) Provide:
  - The relevant doc section name (and link if available)
  - A minimal working example adapted to this codebase
  - Common pitfalls / edge cases
5) If multiple approaches exist, compare trade-offs briefly and pick the safest default for production.

Output format:
- Recommended approach
- What the docs say (short)
- Minimal example
- Pitfalls / version notes
- How to verify quickly

## Mandatory: update instructions after every change

After ANY code change (feature, refactor, fix, new convention) — update AGENTS.md, CLAUDE.md, DEVELOPMENT.md BEFORE committing.
Do not treat documentation updates as a separate step or afterthought. They are part of the task itself.

Checklist before every commit:
- Does AGENTS.md reflect the new structure, files, or conventions?
- Does CLAUDE.md need updates?
- Does DEVELOPMENT.md need updates?
- Does README.md need updates (new features, settings, commands, widgets)?

If any answer is yes — update the file first, then commit everything together. 