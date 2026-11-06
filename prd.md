# Product Requirements Document (PRD)

# Financial Management System

**Versi:** 1.1\
**Status:** Draft -- siap menjadi acuan implementasi\
**Stack:** Laravel REST API, Nuxt.js, Vue.js, TypeScript, Tailwind CSS,
MySQL

------------------------------------------------------------------------

## 1. Ringkasan Proyek

Financial Management System adalah aplikasi web untuk mengelola kategori
keuangan, Chart of Accounts (COA), transaksi pemasukan dan pengeluaran,
serta menghasilkan laporan Profit & Loss berdasarkan periode bulanan.

Aplikasi terdiri dari dua aplikasi terpisah: - **Backend:** Laravel
sebagai REST API, business logic, validasi, autentikasi, otorisasi, dan
akses database. - **Frontend:** Nuxt.js dengan Vue.js dan TypeScript
sebagai antarmuka pengguna. - **Database:** MySQL sebagai penyimpanan
data utama.

Frontend tidak boleh menjadi sumber kebenaran untuk business rules
keuangan. Perhitungan dan validasi penting harus dilakukan di backend.

## 2. Tujuan Produk

1.  Memusatkan pengelolaan master kategori, COA, dan transaksi keuangan.
2.  Mempermudah pencatatan pemasukan dan pengeluaran berdasarkan COA.
3.  Menghasilkan laporan Profit & Loss per bulan.
4.  Memungkinkan pengguna mengekspor laporan ke Excel.
5.  Menyediakan aplikasi yang aman, konsisten, mudah dipelihara, dan
    dapat dikembangkan.

## 3. Ruang Lingkup

### 3.1 Fitur utama

-   Master Category: melihat, menambah, mengubah, dan menghapus
    kategori.
-   Master Chart of Account (COA): melihat, menambah, mengubah, dan
    menghapus akun COA.
-   Transaction: melihat, menambah, mengubah, dan menghapus transaksi.
-   Profit & Loss Report: menampilkan ringkasan pemasukan, pengeluaran,
    dan net income berdasarkan bulan/periode.
-   Excel Export: mengekspor laporan Profit & Loss sesuai filter
    periode.
-   Authentication dan authorization sesuai kebutuhan peran pengguna.

### 3.2 Di luar ruang lingkup awal

-   Integrasi bank atau payment gateway.
-   Multi-currency.
-   Konsolidasi banyak perusahaan/cabang.
-   Akuntansi double-entry penuh, kecuali secara eksplisit ditambahkan
    pada kebutuhan berikutnya.

## 4. Acuan Data dan Aturan Bisnis

Aplikasi mengikuti struktur data pada contoh spreadsheet yang diberikan.

### 4.1 Master Category

Master kategori menyimpan kelompok transaksi, misalnya: - Salary - Other
Income - Family Expense - Transport Expense - Meal Expense

Nama kategori harus unik sesuai aturan bisnis yang ditetapkan.

### 4.2 Master Chart of Account (COA)

Setiap COA memiliki: - `code`: kode akun. - `name`: nama akun. -
`category_id`: kategori yang menaungi akun.

Contoh: - 401 -- Gaji Karyawan -- Salary - 402 -- Gaji Ketua MPR --
Salary - 403 -- Profit Trading -- Other Income - 601 -- Biaya Sekolah --
Family Expense - 602 -- Bensin -- Transport Expense - 603 -- Parkir --
Transport Expense - 604 -- Makan Siang -- Meal Expense - 605 -- Makanan
Pokok Bulanan -- Meal Expense

Aturan: - Kode COA harus unik. - COA harus terhubung dengan kategori
yang valid. - COA yang sudah digunakan transaksi tidak boleh dihapus
secara sembarangan. Gunakan soft delete, penonaktifan, atau larangan
penghapusan sesuai keputusan implementasi. - Perubahan master tidak
boleh merusak histori transaksi.

### 4.3 Transaction

Setiap transaksi minimal menyimpan: - Tanggal transaksi. - Referensi
COA. - Deskripsi. - Debit. - Credit.

Aturan penting berdasarkan spreadsheet: - Transaksi pemasukan dicatat
pada kolom **Credit**. - Transaksi pengeluaran dicatat pada kolom
**Debit**. - Satu transaksi hanya boleh memiliki salah satu nilai debit
atau credit yang lebih besar dari nol. - Nilai nominal tidak boleh
negatif. - Transaksi harus mengacu pada COA yang valid dan aktif. -
Nama/kode COA pada tampilan dapat diperoleh melalui relasi COA, bukan
dipercaya dari input bebas pengguna. - Zona waktu dan format tanggal
harus konsisten antara frontend, backend, dan database.

**Catatan domain:** pola debit/credit pada spreadsheet ini digunakan
sebagai klasifikasi pemasukan dan pengeluaran sesuai kebutuhan aplikasi.
Jangan mengasumsikan sistem sudah menerapkan prinsip double-entry
accounting atau aturan saldo akuntansi lengkap tanpa kebutuhan bisnis
tambahan.

### 4.4 Profit & Loss

Laporan ditampilkan berdasarkan kategori dan periode bulanan.

Rumus dasar: - Total Income = jumlah nilai Credit untuk kategori
pemasukan pada periode. - Total Expense = jumlah nilai Debit untuk
kategori pengeluaran pada periode. - Net Income = Total Income - Total
Expense.

Aturan: - Pengelompokan transaksi mengikuti kategori COA. - Filter
periode menggunakan rentang tanggal yang jelas. - Bulan tanpa transaksi
tetap dapat ditampilkan dengan nilai nol jika dibutuhkan tampilan
laporan. - Nilai laporan harus dihitung backend dan menjadi sumber data
frontend. - Total per kategori dan total keseluruhan harus konsisten. -
Export Excel harus mengikuti filter dan hasil perhitungan yang sama
dengan laporan di layar.

## 5. Pengguna dan Hak Akses

Peran pengguna perlu ditentukan sebelum implementasi autentikasi. Untuk
versi awal, sistem dapat dimulai dengan satu peran administrator apabila
belum ada matriks peran lain.

Aturan: - Setiap endpoint yang dilindungi harus memeriksa autentikasi. -
Hak akses diperiksa di backend melalui middleware/policy/gate. -
Menyembunyikan tombol di frontend bukan mekanisme authorization. -
Pengguna hanya boleh melakukan aksi sesuai hak aksesnya.

## 6. Kebutuhan Fungsional

  -------------------------------------------------------------------------
  ID                      Fitur                   Kebutuhan
  ----------------------- ----------------------- -------------------------
  FR-01                   Category                Melihat daftar kategori

  FR-02                   Category                Menambah kategori

  FR-03                   Category                Mengubah kategori

  FR-04                   Category                Menghapus/menonaktifkan
                                                  kategori sesuai aturan
                                                  relasi

  FR-05                   COA                     Melihat daftar COA
                                                  beserta kategori

  FR-06                   COA                     Menambah COA

  FR-07                   COA                     Mengubah COA

  FR-08                   COA                     Menghapus/menonaktifkan
                                                  COA sesuai aturan relasi

  FR-09                   Transaction             Melihat daftar transaksi

  FR-10                   Transaction             Menambah transaksi

  FR-11                   Transaction             Mengubah transaksi

  FR-12                   Transaction             Menghapus transaksi

  FR-13                   Transaction             Memfilter transaksi
                                                  berdasarkan periode, COA,
                                                  atau kategori

  FR-14                   Report                  Melihat laporan Profit &
                                                  Loss per bulan

  FR-15                   Report                  Melihat total income,
                                                  expense, dan net income

  FR-16                   Report                  Memfilter laporan
                                                  berdasarkan periode

  FR-17                   Export                  Mengunduh laporan Profit
                                                  & Loss dalam format Excel

  FR-18                   Auth                    Login dan logout sesuai
                                                  strategi autentikasi yang
                                                  dipilih
  -------------------------------------------------------------------------

## 7. Kebutuhan Non-Fungsional

-   Keamanan: validasi server-side, autentikasi, authorization,
    perlindungan dari SQL injection dan XSS, serta konfigurasi CORS/CSRF
    yang benar.
-   Maintainability: pemisahan tanggung jawab, clean code, dan struktur
    modular.
-   Performance: pagination, indexing, eager loading, pemilihan kolom
    seperlunya, dan query agregasi database.
-   Reliability: transaksi database untuk operasi atomik dan penanganan
    concurrency.
-   Usability: UI konsisten, responsif, serta menyediakan loading,
    empty, success, dan error states.
-   Testability: unit test dan feature test backend serta
    unit/component/E2E test frontend sesuai risiko fitur.

------------------------------------------------------------------------

# 8. Backend Engineering Standards

## 8.1 Arsitektur dan pemisahan tanggung jawab

Backend menggunakan Laravel sebagai REST API dengan prinsip separation
of concerns.

Struktur tanggung jawab: - **Routes:** mendefinisikan endpoint dan
middleware. - **Controllers:** menerima request, memanggil service, dan
mengembalikan response. Controller tidak menampung business logic
kompleks. - **Form Requests:** menangani validasi input dan
authorization request-level bila sesuai. - **Services:** menampung
business logic dan orkestrasi proses bisnis. - **Models:**
merepresentasikan entitas dan relasi Eloquent. -
**Resources/Transformers:** menstandarkan bentuk data yang dikirim
melalui API. - **Events/Listeners:** digunakan ketika ada proses
lanjutan yang layak dipisahkan dari alur utama. - **Jobs/Queues:**
digunakan untuk pekerjaan berat atau asynchronous, misalnya export besar
atau notifikasi jika kelak dibutuhkan. - **Policies/Gates:** menangani
authorization berbasis resource.

Contoh struktur:

``` text
backend/
└── app/
    ├── Http/
    │   ├── Controllers/Api/V1/
    │   ├── Requests/
    │   └── Resources/
    ├── Models/
    ├── Services/
    ├── Events/
    ├── Listeners/
    ├── Jobs/
    └── Policies/
```

Jangan membuat layer atau class hanya demi mengikuti pola. Tambahkan
abstraksi ketika memang mengurangi coupling atau kompleksitas.

## 8.2 Validasi

-   Seluruh validasi penting wajib dilakukan di backend menggunakan
    Laravel Form Request.
-   Jangan mengandalkan validasi frontend sebagai perlindungan data.
-   Validasi tipe, panjang, format, rentang nominal, keunikan, dan
    foreign key.
-   Validasi aturan debit/credit: salah satu sisi harus bernilai positif
    dan sisi lainnya nol.
-   Gunakan pesan validasi yang konsisten dan mudah dipahami.
-   Validasi ulang seluruh input yang berasal dari client, termasuk
    query parameter dan filter.

## 8.3 Keamanan backend

-   Gunakan Eloquent atau Query Builder dengan parameter binding;
    hindari raw SQL yang menggabungkan input pengguna.
-   Jika raw SQL diperlukan, gunakan binding parameter dan tinjau secara
    khusus.
-   Terapkan authentication dan authorization pada endpoint privat.
-   Hash password menggunakan mekanisme Laravel yang direkomendasikan.
-   Lindungi aplikasi dari mass assignment dengan `$fillable` atau
    `$guarded` yang tepat.
-   Jangan mengekspos stack trace, credential, token, atau detail
    internal pada production response.
-   Simpan secret di environment variable/secret manager, bukan
    repository.
-   Terapkan rate limiting pada endpoint yang relevan, terutama
    autentikasi.
-   Atur CORS, CSRF, cookie, dan HTTPS sesuai strategi
    autentikasi/deployment.
-   Batasi ukuran payload, tipe file, dan validasi file jika fitur
    upload ditambahkan.
-   Gunakan prinsip least privilege untuk akun database.
-   Catat aktivitas penting tanpa mencatat password, token, atau data
    sensitif.

## 8.4 Database design dan integrity

-   Gunakan migration untuk seluruh perubahan skema.
-   Tentukan primary key, foreign key, unique constraint, dan nullable
    constraint secara eksplisit.
-   Gunakan tipe data yang sesuai untuk nominal uang dan tanggal.
-   Hindari menyimpan data turunan yang dapat dihitung kecuali ada
    alasan performa yang terukur.
-   Pastikan penghapusan master tidak merusak histori transaksi.
-   Gunakan database constraint sebagai lapisan perlindungan tambahan,
    bukan hanya validasi aplikasi.

## 8.5 Database performance

-   Gunakan pagination pada endpoint list; jangan mengambil seluruh data
    tanpa batas.
-   Tambahkan index berdasarkan pola query nyata, seperti tanggal
    transaksi, foreign key COA, dan kolom filter yang sering digunakan.
-   Hindari index berlebihan karena menambah biaya write dan storage.
-   Gunakan eager loading (`with`) untuk relasi yang diperlukan guna
    mencegah N+1.
-   Pilih kolom yang dibutuhkan saja.
-   Gunakan agregasi SQL untuk perhitungan laporan yang sesuai.
-   Evaluasi query menggunakan `EXPLAIN` dan profiling sebelum melakukan
    optimasi.
-   Gunakan cache hanya jika diperlukan dan tentukan strategi
    invalidasinya.

## 8.6 Transaction dan concurrency

-   Gunakan `DB::transaction()` untuk operasi multi-query yang harus
    berhasil atau gagal sebagai satu kesatuan.
-   Terapkan unique constraint untuk mencegah data duplikat pada level
    database.
-   Untuk proses read-modify-write yang rawan konflik, gunakan locking
    yang sesuai seperti `lockForUpdate()` di dalam database transaction.
-   Tentukan urutan penguncian yang konsisten untuk mengurangi risiko
    deadlock.
-   Gunakan idempotency key bila operasi yang sama berpotensi dikirim
    ulang dan dapat menimbulkan duplikasi.
-   Jangan menganggap frontend disable button sebagai perlindungan race
    condition.
-   Tambahkan concurrency test untuk alur yang berisiko jika relevan.

## 8.7 Standard API Response

Semua endpoint harus mengembalikan format JSON yang konsisten melalui
API Response helper/kelas terpusat. Jangan menduplikasi format response
di setiap controller.

Struktur sukses:

``` json
{
  "success": true,
  "status": 200,
  "message": "Request processed successfully.",
  "data": {},
  "meta": null,
  "errors": null
}
```

Struktur gagal:

``` json
{
  "success": false,
  "status": 422,
  "message": "The given data was invalid.",
  "data": null,
  "meta": null,
  "errors": {
    "name": ["The name field is required."]
  }
}
```

Status HTTP yang harus ditangani secara konsisten:

  Status   Nama                     Penggunaan
  -------- ------------------------ ---------------------------------------------
  200      OK                       Request berhasil
  201      Created                  Resource berhasil dibuat
  202      Accepted                 Proses diterima untuk diproses asynchronous
  204      No Content               Berhasil tanpa response body
  400      Bad Request              Request tidak valid secara umum
  401      Unauthorized             Belum terautentikasi
  403      Forbidden                Tidak memiliki izin
  404      Not Found                Resource tidak ditemukan
  405      Method Not Allowed       HTTP method tidak didukung
  409      Conflict                 Konflik data atau state
  413      Payload Too Large        Payload melebihi batas
  415      Unsupported Media Type   Content type tidak didukung
  422      Unprocessable Entity     Validasi gagal
  429      Too Many Requests        Rate limit terlampaui
  500      Internal Server Error    Kesalahan internal tak terduga
  502      Bad Gateway              Gateway menerima response tidak valid
  503      Service Unavailable      Layanan sementara tidak tersedia
  504      Gateway Timeout          Gateway timeout

Catatan: - Status HTTP aktual harus sesuai dengan status pada body. -
Response 204 tidak boleh dipaksakan memiliki body. - Jangan mengirim
detail exception internal kepada client. - Format `meta` digunakan untuk
pagination atau metadata lain yang relevan. - Format `errors` digunakan
untuk detail error terstruktur.

## 8.8 Testing backend

Gunakan PHPUnit atau Pest sesuai pilihan project.

-   Unit test untuk service dan fungsi bisnis yang dapat diuji secara
    terisolasi.
-   Feature test untuk setiap endpoint dan alur fitur.
-   Uji validasi positif dan negatif.
-   Uji authentication dan authorization.
-   Uji response status serta struktur JSON.
-   Uji relasi, constraint, dan integritas data.
-   Uji perhitungan Profit & Loss dengan dataset yang mencakup
    pemasukan, pengeluaran, bulan kosong, dan batas periode.
-   Uji export Excel.
-   Uji transaksi rollback dan concurrency untuk proses yang relevan.
-   Gunakan database testing terpisah, misalnya SQLite in-memory atau
    database testing khusus yang kompatibel dengan fitur yang diuji.
-   Jangan menguji detail implementasi internal jika perilaku eksternal
    sudah cukup.

------------------------------------------------------------------------

# 9. Frontend Engineering Standards

## 9.1 Technology standards

-   Nuxt.js sebagai framework aplikasi frontend.
-   Vue.js dengan Composition API.
-   TypeScript dengan strict mode.
-   Tailwind CSS untuk styling.
-   REST API Laravel sebagai sumber data utama.
-   Gunakan `<script setup lang="ts">` untuk komponen Vue.
-   Gunakan library tambahan hanya jika memberikan manfaat yang jelas.

## 9.2 Frontend architecture dan separation of concerns

Pisahkan tanggung jawab berdasarkan jenis kode:

  -----------------------------------------------------------------------
  Layer                               Tanggung jawab
  ----------------------------------- -----------------------------------
  Pages                               Menyusun halaman, mengatur
                                      route-level data, dan menghubungkan
                                      komponen

  Components                          Menampilkan UI dan menangani
                                      interaksi komponen

  Composables                         Reusable stateful logic dan
                                      lifecycle-related logic

  Services                            Komunikasi HTTP/API ke Laravel

  Types                               Definisi tipe request, response,
                                      dan domain frontend

  Utils                               Fungsi helper murni dan reusable

  Stores                              Shared/global state bila memang
                                      diperlukan

  Middleware                          Pemeriksaan navigasi, misalnya
                                      status autentikasi
  -----------------------------------------------------------------------

Aturan: - Jangan menulis HTTP request langsung di komponen UI. - Jangan
menempatkan seluruh logic halaman dalam satu file page. - Jangan
mencampur formatting, business rules backend, dan markup UI. - Jangan
membuat abstraksi atau folder yang belum dibutuhkan. - Business rules
keuangan tetap menjadi tanggung jawab Laravel.

## 9.3 Component standards

-   Setiap komponen memiliki satu tanggung jawab utama.
-   Gunakan reusable components untuk pola UI yang berulang.
-   Gunakan props untuk menerima data dan emits untuk mengirim event.
-   Definisikan props, emits, dan tipe data dengan TypeScript.
-   Hindari prop drilling yang berlebihan; pertimbangkan composable atau
    store bila benar-benar diperlukan.
-   Pecah komponen kompleks menjadi komponen yang lebih kecil.
-   Hindari komponen generik dengan terlalu banyak props, kondisi, dan
    mode.
-   Gunakan nama komponen yang deskriptif dan konsisten.
-   Batas 100--200 baris per komponen hanya pedoman untuk meninjau
    kompleksitas, bukan batas absolut.

## 9.4 TypeScript standards

-   Aktifkan strict mode.
-   Hindari `any`; gunakan tipe konkret, `unknown`, generic, atau
    narrowing.
-   Definisikan tipe untuk API request dan response.
-   Definisikan tipe props, emits, dan return value jika membantu
    kejelasan.
-   Hindari type assertion tanpa alasan yang kuat.
-   Jangan menduplikasi definisi tipe yang sama di banyak tempat.
-   Pastikan tipe frontend mengikuti kontrak API backend.

## 9.5 API integration

-   Gunakan satu centralized API client.
-   Pisahkan API service berdasarkan domain/resource, misalnya category,
    COA, transaction, report, dan auth.
-   Simpan base URL melalui runtime config/environment variable.
-   Jangan menyimpan secret backend di konfigurasi publik Nuxt.
-   Tangani loading, success, empty, dan error state.
-   Tangani status 401, 403, 404, 409, 422, 429, dan 5xx secara
    konsisten.
-   Hindari duplicate request dan race condition pada pencarian/filter.
-   Gunakan cancellation atau mekanisme stale-response protection jika
    request dapat saling mendahului.
-   Pertahankan kontrak response JSON yang disepakati dengan backend.

## 9.6 State management

-   Gunakan `ref()` untuk local state.
-   Gunakan `computed()` untuk derived state.
-   Gunakan composables untuk reusable stateful logic.
-   Gunakan `useAsyncData` atau mekanisme Nuxt yang sesuai untuk data
    fetching dan SSR.
-   Gunakan Pinia hanya ketika shared/global state memang membutuhkan
    pengelolaan terpusat.
-   Jangan menyimpan semua response API dalam global store tanpa alasan.
-   Hindari duplikasi state yang sebenarnya dapat dihitung.
-   Pastikan state diperbarui atau di-invalidate setelah operasi CRUD.
-   Hindari watcher dan lifecycle hook yang memicu request berulang.

## 9.7 Form handling dan validation

-   Gunakan reusable form controls jika pola input berulang.
-   Lakukan validasi frontend untuk UX, tetapi backend tetap menjadi
    sumber validasi utama.
-   Tampilkan error validasi dari HTTP 422 pada field yang sesuai.
-   Tampilkan loading state ketika submit berlangsung.
-   Cegah double submission di UI, tanpa menganggapnya sebagai pengganti
    proteksi backend.
-   Pertahankan input pengguna ketika request gagal.
-   Gunakan confirmation dialog untuk operasi destruktif.
-   Pastikan label, instruksi, dan pesan error terhubung dengan field
    yang benar.
-   Jangan mengimplementasikan ulang business rules kompleks hanya di
    frontend.

## 9.8 Frontend security

-   Hindari `v-html` untuk data yang tidak tepercaya.
-   Jangan menaruh API secret, private key, atau credential di bundle
    frontend.
-   Jangan menganggap middleware Nuxt sebagai pengganti authorization
    backend.
-   Laravel wajib memeriksa izin pada setiap endpoint terlindungi.
-   Jangan menyimpan password atau secret token di localStorage.
-   Pilih strategi autentikasi yang aman dan konsisten. Jika menggunakan
    Laravel Sanctum SPA cookie-based, konfigurasi domain, cookie, CORS,
    dan CSRF harus benar.
-   Jangan menampilkan detail internal server atau data sensitif pada UI
    maupun console.
-   Validasi dan encode data sesuai konteks penggunaan.
-   Gunakan HTTPS pada production.

## 9.9 Performance optimization

-   Gunakan pagination server-side untuk tabel transaksi dan data besar.
-   Gunakan debounce pada input pencarian.
-   Hindari request berulang yang tidak diperlukan.
-   Gunakan lazy loading untuk komponen/fitur berat jika bermanfaat.
-   Optimalkan gambar dan aset statis.
-   Hindari computed/watch yang tidak diperlukan.
-   Gunakan dynamic import untuk bagian aplikasi yang layak dimuat
    secara terpisah.
-   Pilih SSR, prerendering, atau client rendering berdasarkan kebutuhan
    halaman; jangan menerapkan satu mode secara membabi buta.
-   Pastikan filter dan pagination tidak mengunduh seluruh dataset ke
    browser.

## 9.10 UI/UX dan accessibility

-   Terapkan design system untuk warna, typography, spacing, border,
    radius, dan komponen.
-   Gunakan layout navigasi yang konsisten.
-   Pastikan tampilan responsif pada desktop, tablet, dan mobile.
-   Sediakan loading, empty, error, dan success states.
-   Gunakan toast untuk feedback non-blocking dan dialog untuk
    konfirmasi penting.
-   Gunakan semantic HTML.
-   Sediakan label input, keyboard navigation, focus state, dan kontras
    warna yang memadai.
-   Jangan menggunakan warna sebagai satu-satunya pembeda status.
-   Format nominal menggunakan IDR dan tanggal secara konsisten.
-   Gunakan tabel yang mudah dibaca, filter periode, pencarian, dan
    pagination yang jelas.
-   Hindari animasi berlebihan yang mengganggu pekerjaan administratif.

## 9.11 Financial data presentation

-   Gunakan `Intl.NumberFormat` untuk menampilkan mata uang IDR.
-   Ikuti representasi nominal yang disepakati dengan backend.
-   Hindari perhitungan nominal menggunakan floating-point yang dapat
    menghasilkan pembulatan tidak akurat.
-   Perhitungan Profit & Loss dilakukan backend; frontend menampilkan
    hasil API.
-   Tampilkan debit sebagai pengeluaran dan credit sebagai pemasukan
    sesuai konvensi spreadsheet proyek.
-   Tampilkan nilai nol dan bulan tanpa transaksi secara konsisten.
-   Pastikan filter periode yang dipilih tercermin pada tabel,
    ringkasan, dan hasil export.

## 9.12 Frontend testing dan code quality

Tools yang direkomendasikan: - ESLint untuk linting. - Prettier untuk
formatting. - TypeScript compiler/type checking. - Vitest untuk unit
testing. - Vue Test Utils untuk component testing. - Playwright untuk
end-to-end testing.

Aturan: - Unit test untuk utility dan composable yang memiliki logic. -
Component test untuk komponen dengan interaksi atau kondisi kompleks. -
Uji validasi form, event emits, loading, error, empty, dan success
state. - E2E test untuk alur utama: autentikasi, CRUD category, CRUD
COA, CRUD transaction, laporan, dan export. - Uji akses route
berdasarkan status autentikasi. - Gunakan mock API pada unit/component
test; jangan bergantung pada layanan production. - Jalankan lint, type
check, dan test sebelum perubahan dinyatakan selesai.

## 9.13 Struktur folder frontend

Struktur target yang disarankan:

``` text
frontend/
├── app/
│   ├── assets/
│   │   └── css/
│   ├── components/
│   │   ├── ui/
│   │   ├── layout/
│   │   ├── categories/
│   │   ├── coa/
│   │   ├── transactions/
│   │   └── reports/
│   ├── composables/
│   ├── layouts/
│   ├── middleware/
│   ├── pages/
│   │   ├── login.vue
│   │   ├── categories/
│   │   ├── coa/
│   │   ├── transactions/
│   │   └── reports/
│   ├── services/
│   ├── stores/
│   ├── types/
│   └── utils/
├── public/
├── nuxt.config.ts
├── tsconfig.json
└── package.json
```

Catatan: - Struktur tersebut adalah panduan, bukan kewajiban membuat
semua folder sejak awal. - Tambahkan file/folder ketika fitur
membutuhkannya. - Ikuti konvensi direktori Nuxt yang sesuai dengan versi
Nuxt yang digunakan. - Hindari membuat `stores` jika belum ada kebutuhan
global state.

------------------------------------------------------------------------

# 10. API Contract dan Integrasi Frontend-Backend

-   Backend dan frontend harus menyepakati endpoint, HTTP method,
    request schema, response schema, status code, pagination, dan error
    format.
-   Gunakan prefix versi API, misalnya `/api/v1`.
-   Dokumentasikan endpoint dan contoh payload.
-   Frontend tidak boleh mengasumsikan field yang tidak dijamin kontrak
    API.
-   Perubahan response yang breaking harus dikoordinasikan dan diberi
    versi bila diperlukan.
-   Tanggal, timezone, decimal, dan nominal harus memiliki format yang
    disepakati.

Contoh response pagination:

``` json
{
  "success": true,
  "status": 200,
  "message": "Transactions retrieved successfully.",
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 0,
    "last_page": 1
  },
  "errors": null
}
```

# 11. Environment dan Development Workflow

-   Backend dan frontend berada di direktori terpisah.
-   Backend lokal: `http://127.0.0.1:8000`.
-   Frontend lokal: `http://localhost:3000`.
-   API base URL frontend dikonfigurasi melalui Nuxt runtime config.
-   File `.env` tidak boleh masuk version control.
-   Sediakan `.env.example` tanpa secret.
-   Gunakan Git dengan commit message yang deskriptif.
-   Perubahan database harus melalui migration.
-   Perubahan API harus memperbarui dokumentasi/kontrak.
-   Jalankan test dan lint sebelum merge atau menyelesaikan fitur.

# 12. Acceptance Criteria

Sistem dinyatakan memenuhi standar awal apabila:

1.  Pengguna dapat mengelola Category, COA, dan Transaction sesuai
    aturan bisnis.
2.  Pemasukan dan pengeluaran tersimpan sesuai konvensi credit/debit
    proyek.
3.  Laporan Profit & Loss menampilkan nilai per kategori dan periode
    secara akurat.
4.  Export Excel konsisten dengan filter dan laporan di layar.
5.  Backend menggunakan Form Request, Service, dan API response
    terstandar.
6.  Endpoint terlindungi autentikasi dan authorization sesuai hak akses.
7.  Query list menggunakan pagination dan relasi yang diperlukan
    menggunakan eager loading.
8.  Operasi multi-query yang membutuhkan atomicity menggunakan database
    transaction.
9.  Constraint database dan concurrency control melindungi data dari
    duplikasi atau konflik yang relevan.
10. Frontend memisahkan pages, components, composables, services, types,
    dan utils sesuai tanggung jawabnya.
11. Frontend menangani loading, empty, success, dan error state.
12. UI responsif dan memiliki pola interaksi yang konsisten.
13. Unit, feature, component, dan E2E tests tersedia sesuai ruang
    lingkup dan risiko.
14. Linting dan type checking berjalan tanpa error yang belum
    diselesaikan.
15. Tidak ada secret atau data sensitif yang terekspos melalui frontend
    maupun repository.

# 13. Implementation Order

1.  Finalisasi aturan bisnis dan skema database.
2.  Buat migration, model, relationship, factory, dan seeder.
3.  Implementasikan API response standard.
4.  Implementasikan backend services, Form Requests, controllers,
    resources, dan routes.
5.  Implementasikan authentication dan authorization.
6.  Implementasikan CRUD Category.
7.  Implementasikan CRUD COA.
8.  Implementasikan CRUD Transaction.
9.  Implementasikan Profit & Loss dan Excel export.
10. Buat centralized API client dan TypeScript API contracts di Nuxt.
11. Buat layout, design system, dan reusable UI components.
12. Implementasikan halaman CRUD frontend.
13. Implementasikan laporan dan export frontend.
14. Tambahkan unit, feature, component, dan E2E tests.
15. Lakukan security review, performance review, dan acceptance testing.

------------------------------------------------------------------------

## 14. Prinsip Pengembangan

-   Prioritaskan solusi sederhana yang memenuhi kebutuhan.
-   Jangan menambahkan library, layer, atau abstraksi tanpa alasan
    teknis.
-   Jangan mengorbankan keamanan dan integritas data demi kecepatan
    implementasi.
-   Business rules dan perhitungan keuangan harus konsisten dan
    bersumber dari backend.
-   Setiap fitur harus memiliki skenario sukses, gagal, dan batasan yang
    jelas.
-   Dokumentasikan keputusan teknis yang berdampak pada kontrak API,
    database, keamanan, dan proses bisnis.
