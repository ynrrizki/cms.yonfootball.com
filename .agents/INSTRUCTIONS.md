---
applyTo: "**/*"
description: |
  Workspace agent instruction: Ikuti kebijakan TDD di `docs/AGENT_TDD.md` untuk semua perubahan kode fungsional. TDD wajib kecuali user secara eksplisit menyatakan pengecualian.
---

Rules:

- enforce_tdd: true
- tdd_document: docs/AGENT_TDD.md
- require_tests_with_changes: true

Behavior:

- Ketika diminta membuat perubahan kode, agen harus:
  1. Menulis test yang menggambarkan perilaku baru (fail).
  2. Implementasi minimal agar test lulus (pass).
  3. Menjalankan test dan melaporkan hasil singkat.
