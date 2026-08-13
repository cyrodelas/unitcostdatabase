# Nexus UCD Codex Instructions

This repository contains the Nexus Unit Cost Database Web-Based System.

## Primary Instructions

Before performing development work, read:

- `docs/Nexus_UCD_Codex_Master_Prompt.md`

Follow its architecture, development standards, database rules, and phased implementation process.

## Project Memory

For normal continuation work, do not reread the complete master prompt unless necessary.

Read these first:

- `docs/PROJECT_CONTEXT.md`
- `docs/PHASE_STATUS.md`
- `docs/MODULE_MAP.md`

Read the following only when relevant:

- `docs/DB_REFERENCE.md` — database-related work
- `docs/ROUTES.md` — routing/controller work
- `docs/DECISIONS.md` — architecture/design questions
- `docs/CHANGELOG.md` — previous phase history

## Phase Rule

Implement only the phase explicitly requested by the user.

Do not automatically proceed to another phase.

At the end of every phase:

1. Test the implementation.
2. Update the relevant project memory Markdown files.
3. Provide a concise completion summary.
4. Stop.

## Database Rule

The existing `nexus_ucd` MySQL schema is authoritative.

Do not:

- recreate existing tables;
- drop tables;
- rename existing fields;
- invent database columns;
- execute destructive SQL;

unless explicitly authorized.

Always inspect the actual schema before proposing database changes.

## Token Efficiency

Keep context usage efficient.

- Do not reread unrelated files.
- Do not dump large SQL schemas.
- Do not output unchanged files.
- Modify only affected sections.
- Keep project Markdown files concise.
- Use `PHASE_STATUS.md` as the primary continuation state.
