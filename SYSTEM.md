```
erDiagram
    bendahara ||--o{ pemasukan : "mencatat"
    bendahara ||--o{ pengeluaran : "mencatat"
    bendahara ||--o{ inventaris : "mengelola"
    bendahara ||--o{ kegiatan : "mengelola"

    kategori_transaksi ||--o{ pengeluaran : "mengelompokkan"
    pengeluaran ||--o| inventaris : "menghasilkan"
    pengeluaran ||--o| kegiatan : "mendanai"

    admin {
        int id_admin PK
        string email
        string password
        string nama
    }

    bendahara {
        int id PK
        string nama
        string email
        string password
    }

    kepala_desa {
        int id PK
        string nama
        string email
        string password
    }

    kategori_transaksi {
        int id PK
        string nama_kategori
        string deskripsi
    }

    pemasukan {
        int id PK
        string sumber_dana
        decimal jumlah
        date tanggal
        string keterangan
        int id_bendahara FK
    }

    pengeluaran {
        int id PK
        int id_kategori_transaksi FK
        decimal jumlah
        date tanggal
        string keterangan
        int id_kegiatan FK
        int id_bendahara FK
    }

    inventaris {
        int id PK
        string kode_barang
        string nama_barang
        string lokasi
        date tanggal_perolehan
        decimal nilai_aset
        string kondisi
        int id_pengeluaran FK
        int id_bendahara FK
    }

    kegiatan {
        int id PK
        string nama_kegiatan
        string lokasi
        decimal anggaran
        status enum('perencanaan', 'berjalan', 'selesai')
        string foto_progres
        int id_bendahara FK
    }

```

Berikut adalah deskripsi fungsi dari setiap tabel dalam rancangan *database* berdasarkan ERD yang telah Anda buat. Agar lebih mudah dipahami, fungsinya dibagi ke dalam tiga kelompok utama:

### 1. Kelompok Aktor (Pengguna Sistem)

Tabel-tabel ini berfungsi untuk mengatur hak akses dan autentikasi pengguna aplikasi.

* **`admin`**: Menyimpan data kredensial (nama, email, *password*) untuk administrator sistem. Admin biasanya memiliki hak akses penuh terhadap konfigurasi teknis aplikasi dan manajemen akun.
* **`bendahara`**: Menyimpan data perangkat desa yang bertugas sebagai operator harian. Tabel ini terhubung dengan hampir semua tabel operasional karena merekalah yang bertanggung jawab melakukan *input* data (seperti mencatat pengeluaran, menambah aset, atau memperbarui status kegiatan).
* **`kepala_desa`**: Menyimpan data kredensial khusus untuk Kepala Desa. Tabel ini dipisah agar Kepala Desa memiliki akses khusus (biasanya *read-only* atau hanya melihat *dashboard*) untuk memantau laporan transparansi dan progres kegiatan tanpa risiko mengubah data operasional.

### 2. Kelompok Keuangan (Realisasi Dana Desa)

Tabel-tabel ini berfokus pada pencatatan arus kas desa, baik yang masuk maupun yang keluar.

* **`kategori_transaksi`**: Berfungsi sebagai tabel master (data rujukan) untuk mengelompokkan jenis pengeluaran. Contoh isinya adalah "Pembangunan Infrastruktur", "Operasional Desa", atau "Pemberdayaan Masyarakat". Ini sangat berguna saat pembuatan laporan keuangan agar dana bisa direkap berdasarkan kategorinya.
* **`pemasukan`**: Mencatat semua detail dana yang masuk ke kas desa. Informasi yang disimpan meliputi sumber dana (misal: Dana Desa APBN, ADD), jumlah uang, tanggal masuk, keterangan, serta merekam ID bendahara yang mencatat data tersebut.
* **`pengeluaran`**: Mencatat setiap detail dana yang dikeluarkan. Tabel ini sangat sentral karena menjadi sumber dana untuk kegiatan atau pembelian aset. Tabel ini mencatat jumlah uang, tanggal, mengaitkannya dengan `kategori_transaksi`, menghubungkannya ke sebuah `kegiatan` tertentu (jika dana dipakai untuk proyek), dan mencatat bendahara yang melakukan *input*.

### 3. Kelompok Aset dan Kegiatan

Tabel-tabel ini digunakan untuk melacak wujud fisik dari uang yang telah dikeluarkan, baik berupa barang (aset) maupun proyek pembangunan.

* **`kegiatan`**: Menyimpan data proyek atau kegiatan desa (misal: Pembangunan Sumur Bor, Pengaspalan Jalan). Tabel ini mencatat total anggaran yang disiapkan, lokasi proyek, status pengerjaan terkini (perencanaan, berjalan, selesai), hingga menyimpan referensi foto progres pembangunan.
* **`inventaris`**: Berfungsi sebagai buku induk barang digital. Tabel ini menyimpan wujud fisik dari aset desa, mulai dari kode barang, nama, lokasi penyimpanan, nilai aset (harga barang), dan kondisi terkini barang tersebut. Tabel ini juga berelasi dengan tabel `pengeluaran` untuk melacak dari transaksi mana barang tersebut dibeli.


### 2. Cara Mengetahui Anggaran untuk Kegiatan Tertentu
Untuk melacak berapa banyak dana yang telah digunakan untuk sebuah kegiatan, kita menggunakan **relasi antara tabel `kegiatan` dan tabel `pengeluaran**`.

Berikut adalah alur logikanya di dalam sistem:

1. **Perencanaan Anggaran:** Pertama, bendahara membuat data baru di tabel `kegiatan`. Misalnya:
* Nama: "Pembangunan Sumur Bor Dusun 1"
* Anggaran (Rencana): Rp 50.000.000
* Status: "perencanaan"

2. **Pelaksanaan & Pengeluaran:** Saat kegiatan mulai berjalan dan desa harus membeli material (misal: beli semen, pipa, bayar tukang), bendahara memasukkan data ke tabel `pengeluaran`.
3. **Pengaitan Data (Tracking):** Saat menginput di tabel `pengeluaran`, bendahara **wajib memilih kegiatan yang sedang didanai**. Di sinilah kolom `id_kegiatan` pada tabel `pengeluaran` (sebagai *Foreign Key*) berfungsi.
* Pengeluaran 1: Beli Semen Rp 5.000.000 -> dihubungkan ke `id_kegiatan` Sumur Bor.
* Pengeluaran 2: Bayar Tukang Rp 2.000.000 -> dihubungkan ke `id_kegiatan` Sumur Bor.


4. **Kalkulasi Realisasi:** Untuk mengetahui total anggaran yang sudah *terpakai* untuk "Pembangunan Sumur Bor Dusun 1", sistem (melalui *query* SQL) akan **menjumlahkan (SUM) semua kolom `jumlah` di tabel `pengeluaran` yang memiliki `id_kegiatan` yang sama**.

Dengan desain ini, Kepala Desa atau masyarakat (di halaman publik) bisa membandingkan antara **Rencana Anggaran** (dari tabel `kegiatan`) dengan **Realisasi Pengeluaran** (dari total *query* tabel `pengeluaran`), sehingga transparansi penggunaan dana sangat terjamin dan tidak ada uang yang keluar tanpa kejelasan peruntukannya.
