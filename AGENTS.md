# Workspace Rules — Financial Management System

## Mandatory Pre-Execution Rule
1. **Selalu baca dan jadikan `PRD.md` (`d:\financial-management-system\PRD.md`) sebagai acuan utama** sebelum merencanakan, menulis, mengubah, atau menguji kode di repositori ini.
2. Pastikan setiap perubahan pada `backend/` (Laravel REST API) dan `frontend/` (Nuxt.js + Vue 3 + TypeScript + Tailwind CSS) mematuhi seluruh aturan bisnis, standar arsitektur, format response API, keamanan, dan *acceptance criteria* yang tertulis di `PRD.md`.
3. Jangan menjadikan frontend sebagai sumber kebenaran (*source of truth*) untuk *business rules* atau perhitungan keuangan — seluruh validasi dan perhitungan laporan wajib bersumber dari backend.

