
# 💎 Project Top Up Game Online - DiamondStore

Sistem informasi pemesanan top up game online berbasis web menggunakan **PHP Native**, **MySQL**, **HTML/CSS**, dan **Bootstrap**. Proyek ini merupakan bagian dari tugas akhir mata kuliah **Sistem Manajemen Basis Data**.

## 🧩 Fitur Utama

- 💰 Pemesanan top up berbagai game online
- 📦 Paket diamond tersedia dan dapat dipilih pengguna
- 🧾 Halaman konfirmasi transaksi
- 🛠️ Admin dashboard untuk mengelola:
  - Data pelanggan
  - Data game
  - Paket diamond
  - Transaksi
- 📊 Statistik transaksi
- 🔒 Autentikasi login untuk admin
- 📤 Cek status transaksi berdasarkan ID

## 🏗️ Teknologi yang Digunakan

- **PHP Native**
- **MySQL (phpMyAdmin)**
- **Bootstrap 5**
- **HTML/CSS**
- **Stored Procedure**, **Trigger**, dan **View** di database MySQL

## 🧪 Fitur Database (Sesuai Kriteria Proyek)

- ✔️ View
- ✔️ Join antar tabel
- ✔️ Seleksi kondisi dalam SQL
- ✔️ Stored Procedure (dengan parameter & looping)
- ✔️ Trigger (otomatisasi saat transaksi)

## 📁 Struktur Direktori Utama

```
game-topup/
├── admin/
│   ├── dashboard.php
│   ├── data_game.php
│   ├── data_paket.php
│   └── ...
├── assets/
│   ├── css/
│   └── img/
├── includes/
│   ├── auth.php
│   ├── config.php
│   └── functions.php
├── index.php
├── order.php
├── cek_transaksi.php
└── ...
```

## 🚀 Cara Menjalankan Proyek

1. **Clone repositori:**

   ```bash
   git clone https://github.com/dony230441100041/Project-TopUp-Game-Online.git
   ```

2. **Pindahkan ke `htdocs` jika menggunakan XAMPP:**

   ```
   C:\xampp\htdocs\Project-TopUp-Game-Online
   ```

3. **Import database:**

   - Buka `phpMyAdmin`
   - Import file `.sql` dari folder `database/`

4. **Jalankan di browser:**

   ```
   http://localhost/Project-TopUp-Game-Online
   ```

5. **Login Admin:**
   - Username: `admin`
   - Password: `admin` (atau cek di database jika berubah)

## 📸 Tampilan Antarmuka

![Dashboard Admin](screenshots/admin-dashboard.png)
![Form Order User](screenshots/order-form.png)

## 🧑‍💻 Kontributor

- Ar'raffi Abqori Nur Azizi - 230441100026
- Dony Eka Octavian Putra - 230441100041

## 📜 Lisensi

Proyek ini dibuat untuk kebutuhan akademik dalam Konteks Project Akhir Praktikum SMBD Warga Lab Sistem informasi. 
Silakan modifikasi sesuai kebutuhan.
