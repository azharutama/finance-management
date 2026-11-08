# Product Requirements Document (PRD)

# Financial Management System

**Versi:** 1.2  
**Status:** Disetujui & Siap Menjadi Acuan Implementasi  
**Stack:** Laravel REST API, Maatwebsite Laravel Excel (`maatwebsite/excel`), Nuxt.js, Vue.js, TypeScript, Tailwind CSS, MySQL  

---

## 1. Ringkasan Proyek

Financial Management System adalah aplikasi web terintegrasi untuk mengelola master kategori keuangan, Chart of Accounts (COA), pencatatan transaksi pemasukan dan pengeluaran harian, serta menghasilkan laporan Profit & Loss bulanan dengan fitur export Excel terformat otomatis.

Aplikasi dibangun dengan arsitektur terpisah (*decoupled architecture*):
- **Backend:** Laravel sebagai REST API murni yang bertanggung jawab penuh atas *business logic*, validasi, integritas data, penghitungan laporan keuangan, dan akses database.
- **Frontend:** Nuxt.js dengan Vue 3 (Composition API), TypeScript (*strict mode*), dan Tailwind CSS sebagai antarmuka pengguna (*Single Page Application*).
- **Database:** MySQL sebagai penyimpanan data relasional utama.

> [!IMPORTANT]
> **Source of Truth:** Frontend tidak boleh menjadi sumber kebenaran untuk *business rules* atau perhitungan keuangan. Seluruh kalkulasi laporan, agregasi saldo, dan validasi integritas data wajib bersumber dari backend.

---

## 2. Tujuan Produk

1. **Sentralisasi Master Data:** Menyediakan manajemen master kategori dan COA yang terstruktur, aman dari inkonsistensi, dan memiliki batasan relasi yang ketat.
2. **Fleksibilitas Pencatatan Transaksi:** Memberikan kebebasan pencatatan debit dan credit pada level transaksi dengan kontrol integritas data yang kokoh.
3. **Integritas dan Audit Trail:** Menjamin keamanan riwayat historis keuangan melalui strategi penghapusan bertingkat (*Restrict Delete* pada master data dan *Soft Delete* pada transaksi).
4. **Otomatisasi Laporan Finansial:** Menghasilkan laporan Profit & Loss per bulan secara dinamis berdasarkan formula standar industri keuangan.
5. **Ekspor Data Siap Pakai:** Menyediakan fitur unduh spreadsheet Excel (`.xlsx`) dengan penataan *layout*, warna, dan *number formatting* sesuai kebutuhan bisnis.

---

## 3. Ruang Lingkup

### 3.1 Fitur Utama
- **Master Category:** Melihat, menambah, mengubah, dan menghapus kategori (menerapkan *Restrict Delete* bila masih memiliki akun COA).
- **Master Chart of Account (COA):** Melihat, menambah, mengubah, menonaktifkan (*is_active = false*), serta menghapus akun COA (menerapkan *Restrict Delete* bila telah digunakan dalam riwayat transaksi).
- **Transaction:** Melihat riwayat transaksi (dengan pagination dan filter), mencatat transaksi baru, mengubah data, serta menghapus transaksi menggunakan *Soft Delete* dengan dialog konfirmasi.
- **Profit & Loss Report:** Menampilkan matriks ringkasan bulanan kategori pemasukan, total income, kategori pengeluaran, total expense, dan net income.
- **Excel Export:** Mengekspor laporan Profit & Loss ke file format `.xlsx` menggunakan library `maatwebsite/excel` sesuai filter periode.

### 3.2 Di Luar Ruang Lingkup Awal
- Autentikasi dan login (ditiadakan sesuai instruksi kebutuhan; aplikasi berjalan dalam mode *direct access* tunggal/internal).
- Integrasi *bank feed* atau *payment gateway*.
- *Multi-currency* (seluruh nominal diasumsikan dalam mata uang IDR).
- Konsolidasi multi-perusahaan / multi-cabang.
- Pembukuan *double-entry accounting* penuh dengan jurnal penyesuaian otomatis, kecuali jika diminta pada fase selanjutnya.

---

## 4. Acuan Data dan Aturan Bisnis

### 4.0 Standar Tampilan Tabel Antarmuka Pengguna (UI Grid Field Alignment)

Sesuai spesifikasi lembar kerja acuan (spreadsheet), kolom/field yang disajikan pada tabel antarmuka pengguna (frontend) ditetapkan secara presisi: **tidak boleh kurang dan tidak boleh lebih**.

1. **Master Kategori COA (1 Field Data):**
   - Kolom tabel: `nama` (ditambah kolom Aksi operasional).
   - Menghilangkan kolom tambahan seperti `Tipe Klasifikasi` atau `Jumlah Akun COA` dari tabel utama agar persis dengan format master kategori spreadsheet.
2. **Master Chart of Account (3 Field Data):**
   - Kolom tabel: `kode`, `nama`, `kategori` (ditambah kolom Aksi operasional).
   - Menghilangkan kolom tambahan seperti `Jumlah Transaksi` atau status badge dari grid utama.
3. **Transaksi (6 Field Data):**
   - Kolom tabel: `Tanggal`, `COA Kode`, `COA Nama`, `Desc`, `Debit`, `Credit` (ditambah kolom Aksi operasional).
   - `COA Kode` dan `COA Nama` dipisah menjadi dua kolom independen (tidak digabung dengan kategori).
   - Nilai `Debit` dan `Credit` disajikan dalam dua kolom numerik terpisah sesuai format transaksi mutasi.

### 4.1 Master Category

Master kategori hanya berisi **nama** kelompok (contoh: *Salary*, *Other Income*, *Family Expense*, *Transport Expense*, *Meal Expense*). Kategori **tidak memiliki tipe** (income/expense); sisi pemasukan/pengeluaran sepenuhnya ditentukan oleh tipe transaksi (Credit/Debit).

**Aturan Bisnis Kategori:**
1. Nama kategori (`name`) bersifat unik (*case-insensitive unique*).
2. Kategori tidak memiliki field `type` — baik di database, API, maupun form antarmuka. Klasifikasi pada laporan diturunkan dari data transaksi (Credit → pemasukan, Debit → pengeluaran).
3. Field `is_active` dan `type` ditiadakan di tingkat migrasi tabel kategori agar persis 1:1 dengan format tabel lembar kerja.
4. **Kebijakan Penghapusan (*Restrict Delete*):** Kategori dilarang dihapus jika masih menaungi satu atau lebih akun COA. Kategori hanya dapat dihapus secara fisik apabila belum memiliki relasi anak sama sekali. Jika ada upaya penghapusan kategori yang masih memiliki relasi COA, sistem wajib menolak dan mengembalikan HTTP status `409 Conflict`.

### 4.2 Master Chart of Account (COA)

Setiap akun COA memiliki atribut data:
- `code`: Kode akun unik (contoh: `401`, `402`, `601`, `602`).
- `name`: Nama akun (contoh: *Gaji Karyawan*, *Biaya Sekolah*, *Bensin*).
- `category_id`: Referensi relasi ke master kategori.

**Aturan Bisnis COA:**
1. Kode akun (`code`) bersifat unik.
2. Setiap COA wajib terhubung dengan `category_id` yang valid.
3. Akun COA dapat dipindahkan ke kategori lain kapan saja karena kategori tidak memiliki tipe.
4. **Kebijakan Penghapusan (*Restrict Delete*):**
   - Pada tingkat database, foreign key dari transaksi ke COA menggunakan aturan `ON DELETE RESTRICT`.
   - Akun COA yang **sudah pernah digunakan dalam transaksi tidak boleh dihapus secara fisik** dan akan ditolak (HTTP `409 Conflict`).
   - Akun COA yang **belum pernah digunakan** dalam transaksi apa pun diizinkan untuk dihapus secara aman.

### 4.3 Transaction

Transaksi mencatat mutasi keuangan harian dengan fleksibilitas penuh di level baris transaksi.

Atribut transaksi:
- `date`: Tanggal terjadinya transaksi (`YYYY-MM-DD`).
- `coa_id`: Akun COA yang bersangkutan.
- `description`: Deskripsi rincian transaksi, sekaligus berfungsi sebagai penjelas alasan jika terdapat transaksi penyesuaian atau koreksi saldo (seperti potongan gaji, diskon, pengembalian/retur belanja).
- `debit`: Nominal sisi debit (`decimal(15, 2)`, default: `0.00`).
- `credit`: Nominal sisi credit (`decimal(15, 2)`, default: `0.00`).
- `deleted_at`: Timestamp penanda *soft delete*.

**Aturan Bisnis Transaksi & Fleksibilitas Debit/Credit:**
1. **Penempatan Kolom Murni di Transaksi:** Kolom `debit` dan `credit` hanya berada di tabel `transactions`. Kategori dan COA tidak mengunci apakah transaksi wajib berada di sisi debit atau credit secara mutlak, melainkan memberikan fleksibilitas penuh untuk mencatat transaksi penyesuaian/koreksi.
2. **Validasi Eksklusifitas Nominal:**
   - Dalam satu baris transaksi, **minimal salah satu dari nilai debit atau credit harus bernilai lebih besar dari 0**.
   - **Tidak boleh kedua sisi terisi bersamaan** (jika `debit > 0`, maka `credit` wajib `0`, dan sebaliknya jika `credit > 0`, maka `debit` wajib `0`).
   - Nilai debit maupun credit **tidak boleh negatif** (`>= 0.00`).
   - Kondisi `debit == 0 && credit == 0` dinyatakan tidak valid (HTTP `422 Unprocessable Entity`).
3. **Kebijakan Penghapusan Transaksi (*Soft Delete*):**
   - Penghapusan transaksi menerapkan mekanisme **Soft Delete** (`deleted_at IS NOT NULL`).
   - Data transaksi yang dihapus tidak dihilangkan secara fisik dari tabel database demi keamanan jejak audit (*audit trail*).
   - Di antarmuka pengguna (frontend), proses penghapusan wajib didahului dengan **dialog/modal konfirmasi** eksplisit untuk mencegah penghapusan yang tidak disengaja.

### 4.4 Profit & Loss Report

Laporan Profit & Loss menyajikan ringkasan kinerja keuangan bulanan yang dikelompokkan berdasarkan kategori.

**Logika Agregasi Perhitungan Berdasarkan Tipe Transaksi:**
1. **Penentuan Kategori Berdasarkan Tipe Transaksi (Kredit vs Debit):**
   - **Kategori Pemasukan (*Credit Categories*):** Mengambil semua kategori yang memiliki transaksi bertipe **kredit** (`credit > 0`) pada periode terpilih. Kategori-kategori ini dihitung per bulan dengan formula $\sum(\text{credit})$.
   - **Kategori Pengeluaran (*Debit Categories*):** Mengambil semua kategori yang memiliki transaksi bertipe **debit** (`debit > 0`) pada periode terpilih. Kategori-kategori ini dihitung per bulan dengan formula $\sum(\text{debit})$.
   - Apabila sebuah kategori memiliki transaksi bertipe kredit sekaligus transaksi bertipe debit pada periode yang sama, maka nilai kreditnya ditampilkan per kategori pada kelompok **Income** dan nilai debitnya ditampilkan per kategori pada kelompok **Expense**. Kategori atau COA yang belum memiliki transaksi tidak ditampilkan.
2. **Perhitungan Total Bulanan:**
   - **Total Income:** Diambil dari penjumlahan nominal seluruh kategori bertipe kredit pada bulan bersangkutan.
   - **Total Expense:** Diambil dari penjumlahan nominal seluruh kategori bertipe debit pada bulan bersangkutan.
   - **Net Income (Laba/Rugi Bersih):** $\text{Net Income} = \text{Total Income} - \text{Total Expense}$.
3. Perhitungan wajib **mengabaikan transaksi yang berstatus soft-deleted** (`deleted_at IS NULL`).
4. Periode laporan dapat difilter berdasarkan rentang bulan awal (`start_month`, format: `YYYY-MM`) dan bulan akhir (`end_month`, format: `YYYY-MM`). Bulan dalam rentang yang tidak memiliki mutasi transaksi tetap dimunculkan dengan nilai `0`.
5. Hasil ekspor Excel (`.xlsx`) wajib identik secara presisi dengan angka dan periode yang tampil di layar.


---

## 5. Pengguna dan Hak Akses

Sesuai arahan kebutuhan proyek:
- Fitur login dan autentikasi ditiadakan (*mode direct access*).
- Aplikasi dapat langsung diakses dan digunakan tanpa sesi autentikasi.
- Seluruh pengguna memiliki akses penuh (*full access*) untuk mengelola Master Kategori, Master COA, Transaksi Keuangan, melihat Laporan Profit & Loss, dan mengunduh berkas Excel.

---

## 6. Kebutuhan Fungsional

| ID | Modul | Kebutuhan Fungsional | Aturan / Validasi Terkait |
| :--- | :--- | :--- | :--- |
| **FR-01** | Category | Melihat daftar seluruh kategori | Menampilkan tabel persis 1 field: `nama` (ditambah tombol aksi operasional), tanpa field berlebih. |
| **FR-02** | Category | Menambah kategori baru | Hanya field nama (unik). Tidak ada pilihan tipe. |
| **FR-03** | Category | Mengubah kategori | Hanya field nama (unik, mengabaikan self-ID). |
| **FR-04** | Category | Menghapus kategori | **Restrict Delete:** Ditolak (HTTP 409) jika masih memiliki relasi ke COA. |
| **FR-05** | COA | Melihat daftar akun COA | Menampilkan tabel persis 3 field: `kode`, `nama`, `kategori` (ditambah tombol aksi operasional), tanpa field berlebih. |
| **FR-06** | COA | Menambah akun COA baru | Kode unik, nama wajib, kategori wajib valid. |
| **FR-07** | COA | Mengubah akun COA | Kode unik (mengabaikan self-ID), nama, dan kategori. |
| **FR-08** | COA | Menghapus akun COA | **Restrict Delete:** Ditolak (HTTP 409) jika sudah memiliki riwayat transaksi tercatat. |
| **FR-09** | Transaction | Melihat daftar mutasi transaksi | Menampilkan tabel persis 6 field: `Tanggal`, `COA Kode`, `COA Nama`, `Desc`, `Debit`, `Credit` (ditambah tombol aksi operasional). Mengabaikan data soft-deleted. |
| **FR-10** | Transaction | Mencatat transaksi baru | Salah satu dari debit/credit > 0, tidak boleh keduanya > 0, COA wajib valid, deskripsi penjelas. |
| **FR-11** | Transaction | Mengubah transaksi | Validasi nominal eksklusif tetap berlaku, tanggal dan deskripsi dapat diperbarui. |
| **FR-12** | Transaction | Menghapus transaksi | **Soft Delete:** Mengisi kolom `deleted_at`. Wajib konfirmasi modal di antarmuka sebelum eksekusi. |
| **FR-13** | Report | Melihat Laporan Profit & Loss | Matriks kategori vs bulan, formula `SUM(credit - debit)` dan `SUM(debit - credit)`. |
| **FR-14** | Report | Memfilter periode laporan | Filter rentang bulan (`start_month` s.d. `end_month`). |
| **FR-15** | Export | Export Excel Profit & Loss | Mengunduh file `.xlsx` dengan styling tabel spreadsheet via `maatwebsite/excel`. |

---

## 7. Kebutuhan Non-Fungsional

- **Integritas Data:** Penggunaan constraint database *Foreign Key Restrict*, transaksi database atomik (`DB::transaction()`), dan *pessimistic locking* (`lockForUpdate()`) untuk mutasi yang rawan *race condition*.
- **Performance:** Penerapan pagination pada semua list, penggunaan index komposit (`date`, `coa_id`, `category_id`), eager loading relasi untuk mencegah problem N+1 query, serta agregasi SQL langsung pada laporan.
- **Maintainability:** Pemisahan tanggung jawab yang tegas antara Controllers, Form Requests, Services, Models, dan API Client sesuai standar *Clean Architecture*.
- **Usability:** Antarmuka responsif, modern, *eye-friendly* (*soft muted palette*), dilengkapi *loading skeleton/indicator*, *empty state*, dan dialog konfirmasi interaktif.

---

# 8. Backend Engineering Standards (Laravel)

## 8.1 Arsitektur dan Pemisahan Tanggung Jawab

Backend Laravel dibangun dengan pola service-oriented REST API:
- **Routes (`routes/api.php`):** Hanya mendefinisikan URL endpoint, HTTP method, dan middleware rate limiting (prefix `/api/v1`).
- **Controllers (`app/Http/Controllers/Api/V1/`):** Bertanggung jawab menerima HTTP request, mengeksekusi Form Request, mendelegasikan proses ke Service, dan mengembalikan JSON response via `ApiResponse`. Controller bebas dari *business logic* database langsung.
- **Form Requests (`app/Http/Requests/`):** Bertanggung jawab memvalidasi payload request dan tipe data.
- **Services (`app/Services/`):** Mengelola seluruh alur bisnis, kalkulasi keuangan, transaksi database (`DB::transaction`), dan pengecekan aturan domain.
- **Exports (`app/Exports/`):** Mengelola pembuatan berkas Excel menggunakan `Maatwebsite\Excel` (`WithHeadings`, `WithStyles`, `ShouldAutoSize`).
- **Models (`app/Models/`):** Merepresentasikan tabel database, relasi Eloquent, dan *trait* (`SoftDeletes`).

```text
backend/app/
├── Exports/
│   └── ProfitAndLossExport.php
├── Helpers/
│   └── ApiResponse.php
├── Http/
│   ├── Controllers/Api/V1/
│   │   ├── CategoryController.php
│   │   ├── ChartOfAccountController.php
│   │   ├── TransactionController.php
│   │   └── ReportController.php
│   └── Requests/
│       ├── CategoryFilterRequest.php
│       ├── ChartOfAccountFilterRequest.php
│       ├── TransactionFilterRequest.php
│       ├── ProfitLossReportRequest.php
│       ├── StoreCategoryRequest.php
│       ├── UpdateCategoryRequest.php
│       ├── StoreChartOfAccountRequest.php
│       ├── UpdateChartOfAccountRequest.php
│       ├── StoreTransactionRequest.php
│       └── UpdateTransactionRequest.php
├── Models/
│   ├── Category.php
│   ├── ChartOfAccount.php
│   └── Transaction.php
└── Services/
    ├── CategoryService.php
    ├── ChartOfAccountService.php
    ├── TransactionService.php
    └── ProfitAndLossService.php
```

## 8.2 Skema Migrasi Database (Laravel Blueprint)

Skema migrasi database dirancang persis sesuai field lembar kerja (*spreadsheet*): **tidak kurang dan tidak lebih**. Seluruh kolom non-esensial (seperti `type` fisik pada kategori, `is_active` pada COA, dan `created_by` pada transaksi) ditiadakan dari skema database agar selaras 100% dengan tabel acuan.

### 1. Migrasi Tabel `categories`
```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100)->unique();
    $table->timestamps();
});
```

### 2. Migrasi Tabel `chart_of_accounts`
```php
Schema::create('chart_of_accounts', function (Blueprint $table) {
    $table->id();
    $table->string('code', 30)->unique();
    $table->string('name', 150);
    $table->foreignId('category_id')
          ->constrained('categories')
          ->onDelete('restrict'); // Mencegah hapus kategori jika masih menaungi akun COA
    $table->timestamps();

    $table->index('category_id');
});
```

### 3. Migrasi Tabel `transactions`
```php
Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->date('date')->index();
    $table->foreignId('coa_id')
          ->constrained('chart_of_accounts')
          ->onDelete('restrict'); // Mencegah hapus fisik COA jika sudah ada riwayat transaksi
    $table->string('description', 255);
    $table->decimal('debit', 15, 2)->default(0.00);
    $table->decimal('credit', 15, 2)->default(0.00);
    $table->timestamps();
    $table->softDeletes(); // Kolom deleted_at untuk keamanan jejak audit transaksi

    $table->index(['date', 'coa_id']);
    $table->index('deleted_at');
});
```

## 8.3 Validasi Transaksi (Form Request)

Pengecekan aturan debit dan credit dieksekusi secara ketat pada backend:
```php
// Pada StoreTransactionRequest / UpdateTransactionRequest
public function withValidator($validator): void
{
    $validator->after(function ($v) {
        $debit = (float) $this->input('debit', 0);
        $credit = (float) $this->input('credit', 0);

        if ($debit < 0 || $credit < 0) {
            $v->errors()->add('nominal', 'Nilai debit maupun credit tidak boleh bernilai negatif.');
            return;
        }

        if ($debit == 0 && $credit == 0) {
            $v->errors()->add('nominal', 'Minimal salah satu dari debit atau credit harus lebih besar dari 0.');
            return;
        }

        if ($debit > 0 && $credit > 0) {
            $v->errors()->add('nominal', 'Debit dan credit tidak boleh diisi bersamaan dalam satu baris transaksi.');
            return;
        }

        $coa = ChartOfAccount::find($this->input('coa_id'));
        if (! $coa || ! $coa->is_active) {
            $v->errors()->add('coa_id', 'Akun COA yang dipilih tidak aktif atau tidak ditemukan.');
        }
    });
}
```

## 8.4 Standard Format API Response

Seluruh endpoint menghasilkan struktur JSON terstandarisasi melalui helper `ApiResponse`:

```json
// Sukses (Single / Collection)
{
  "success": true,
  "status": 200,
  "message": "Data retrieved successfully.",
  "data": {},
  "meta": null,
  "errors": null
}

// Sukses (Pagination)
{
  "success": true,
  "status": 200,
  "message": "Transactions retrieved successfully.",
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 45,
    "last_page": 3
  },
  "errors": null
}

// Gagal Validasi (422)
{
  "success": false,
  "status": 422,
  "message": "The given data was invalid.",
  "data": null,
  "meta": null,
  "errors": {
    "nominal": ["Debit dan credit tidak boleh diisi bersamaan dalam satu baris transaksi."]
  }
}
```

---

# 9. Frontend Engineering Standards (Nuxt.js + Vue 3)

## 9.1 Arsitektur Frontend
- **Framework:** Nuxt 3 / Nuxt 4 dengan Vue 3 Composition API (`<script setup lang="ts">`).
- **Bahasa & Typing:** TypeScript dalam mode *strict*. Seluruh tipe domain, payload request, dan response didefinisikan terpusat di `app/types/index.ts`.
- **Styling:** Tailwind CSS dengan design system modern korporat.
- **Client API Terpusat:** Composable `useApi()` membungkus `$fetch` dengan penanganan header otomatis dan standardisasi error handling.

## 9.2 UI/UX Design System
- **Palet Warna:**
  - **Primary Blue:** `#0284c7` (Sky 600) untuk navigasi aktif, brand highlight, dan link.
  - **Warm Accent:** `#ea580c` (Orange 600) untuk tombol aksi utama (*CTA*) dan badge penegas.
  - **Neutral Background:** Clean White (`#ffffff`), soft slate canvas (`bg-slate-50/70`), serta border slate tipis (`border-slate-200`).
- **Sidebar Navigation:** Menggunakan **Light Mode Clean Style** (`bg-white` dengan border kanan `border-slate-200`, ikon sky-600 pada menu aktif).
- **Tabel Laporan Profit & Loss (*Soft Muted Theme*):**
  - Header tabel menggunakan warna *slate* netral lembut (`bg-slate-100/80` dan `bg-slate-50`).
  - Baris kategori menggunakan latar putih bersih dengan dot indikator warna lembut (hijau pastel untuk Income, oranye pastel untuk Expense).
  - Baris total menggunakan aksen pastel lembut (`bg-emerald-50/60` untuk Total Income, `bg-orange-50/60` untuk Total Expense).
  - Baris Net Income menggunakan latar slate tegas (`bg-slate-50/90`) dengan tipografi tebal bernuansa biru (surplus) atau merah (defisit).
- **Dialog Konfirmasi Hapus Transaksi:** Menampilkan modal konfirmasi sebelum aksi *Soft Delete* dijalankan untuk melindungi integritas data pengguna.
- **Standarisasi Komponen UI Filter (`app/components/`):**
  - `FilterCard.vue`: Kontainer filter terstandarisasi dengan latar putih, sudut melengkung `rounded-2xl`, border halus, dan bayangan lembut.
  - `FilterSearch.vue`: Input pencarian dengan ikon kaca pembesar, tombol bersihkan (*clear*), dan status fokus yang konsisten.
  - `FilterSelect.vue`: Dropdown filter dengan ikon panah kustom dan tipografi terstandar.
  - `FilterDateRange.vue`: Pemilih rentang tanggal (tanggal awal s.d. tanggal akhir) dengan ikon kalender terintegrasi.
  - `FilterPeriod.vue`: Pemilih periode bulanan (bulan awal & tahun s.d. bulan akhir & tahun) dilengkapi tombol aksi "Tampilkan" terintegrasi untuk Laporan Profit & Loss.

---

# 10. Acceptance Criteria

Sistem dinyatakan memenuhi seluruh standar arsitektur apabila:

1. **Fleksibilitas Transaksi:** Pada form transaksi, pengguna **wajib memilih Tipe Transaksi (Debit atau Credit)** secara eksplisit lalu mengisi nominal. Nilai tersimpan pada kolom yang dipilih (kolom lainnya `0`), tanpa diblokir oleh kategori/akun, asalkan mematuhi validasi eksklusif (salah satu > 0, tidak keduanya terisi, tidak bernilai negatif).
2. **Koreksi Transaksi:** Kolom `description` dapat diisi dengan penjelas alasan koreksi saldo (seperti retur, potongan, diskon).
3. **Pemberlakuan Restrict Delete:**
   - Kategori yang masih menaungi akun COA ditolak saat akan dihapus (HTTP 409).
   - Akun COA yang telah memiliki riwayat transaksi ditolak saat akan dihapus fisik (HTTP 409).
4. **Deaktivasi COA:** Akun COA yang telah memiliki transaksi dapat dinonaktifkan (`is_active = false`), sehingga tidak muncul lagi di form transaksi baru namun laporan masa lalu tetap utuh.
5. **Pemberlakuan Soft Delete Transaksi:**
   - Transaksi yang dihapus tetap tersimpan di database dengan kolom `deleted_at` terisi timestamp.
   - Halaman daftar transaksi dan laporan Profit & Loss secara otomatis mengabaikan transaksi yang berstatus soft-deleted.
6. **Akurasi Perhitungan Laporan:**
   - Kategori Pemasukan dihitung dari: $\sum (\text{credit} - \text{debit})$.
   - Kategori Pengeluaran dihitung dari: $\sum (\text{debit} - \text{credit})$.
   - Net Income terhitung tepat dari $\text{Total Income} - \text{Total Expense}$.
7. **Kesesuaian Ekspor Excel:** Format spreadsheet `.xlsx` hasil unduhan menampilkan data dan layout yang identik dengan tampilan laporan di layar.
8. **Pengujian Terotomatisasi:** Seluruh unit test dan feature test backend (PHPUnit) berjalan sukses tanpa error.

---

# 11. Urutan Implementasi

1. **Database & Migrations:** Update migrasi tabel `categories`, `chart_of_accounts`, dan `transactions` (tambahkan `softDeletes`, default decimal, dan foreign key restrict).
2. **Model & Trait:** Pasang trait `SoftDeletes` pada model `Transaction`, sesuaikan relasi pada `Category` dan `ChartOfAccount`.
3. **Form Request Validation:** Sesuaikan aturan validasi eksklusif debit/credit pada `StoreTransactionRequest` dan `UpdateTransactionRequest`.
4. **Service Layer:**
   - Perbarui `CategoryService` dan `ChartOfAccountService` dengan proteksi *restrict delete*.
   - Perbarui `TransactionService` untuk mendukung soft delete.
   - Perbarui `ProfitAndLossService` dengan formula $\sum(\text{credit} - \text{debit})$ dan $\sum(\text{debit} - \text{credit})$.
5. **Testing Backend:** Jalankan test suite PHPUnit untuk memverifikasi seluruh skenario sukses, validasi negatif, restrict delete, dan kalkulasi Profit & Loss.
6. **Frontend Updates:** Pastikan modal konfirmasi hapus transaksi terpasang di UI dan form input transaksi mendukung fleksibilitas pencatatan.
