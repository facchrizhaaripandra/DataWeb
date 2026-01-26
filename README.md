# DataWeb - Aplikasi Manajemen Dataset

Aplikasi web berbasis Laravel untuk manajemen dataset dengan fitur autentikasi lengkap dan import data dari Excel.

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Teknologi](#-teknologi)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Penggunaan](#-penggunaan)
- [Screenshot](#-screenshot)

## ✨ Fitur Utama

### Autentikasi & Otorisasi
- ✅ Sistem login dan registrasi
- ✅ Role-based access (Admin & User)
- ✅ Manajemen profil pengguna
- ✅ Reset password
- ✅ Session management

### Manajemen Dataset
- 📊 CRUD Dataset lengkap
- 📁 Import data dari Excel (.xlsx, .xls)
- ✏️ Input manual dataset
- 🔍 View dan edit dataset
- 🗑️ Hapus dataset
- 📈 Dashboard statistik

### UI/UX
- 🎨 Responsive design
- 📱 Mobile-friendly
- 🔔 Notifikasi interaktif
- 🎯 Sidebar navigasi
- 👤 User dropdown menu

## 🛠 Teknologi

**Backend:**
- [Laravel](https://laravel.com/) - PHP Framework
- MySQL - Database
- PHP 8.x

**Frontend:**
- Blade Templates
- Bootstrap/Tailwind CSS
- JavaScript
- jQuery

**Package:**
- Laravel Excel - Import/Export Excel
- Laravel Breeze/Jetstream - Authentication

## 📦 Persyaratan Sistem

Pastikan sistem Anda memiliki:

- PHP >= 8.0
- Composer
- MySQL >= 5.7 atau MariaDB
- Node.js & NPM (untuk asset compilation)
- Web Server (Apache/Nginx)

## 📖 Penggunaan

### 1. Registrasi Akun Baru

- Klik menu "Register"
- Isi form registrasi dengan lengkap
- Klik "Daftar"

### 2. Login ke Aplikasi

- Masukkan email dan password
- Klik "Login"

### 3. Menambah Dataset (Manual)

- Klik menu "Datasets"
- Klik tombol "Tambah Dataset"
- Isi form dengan data dataset
- Klik "Simpan"

### 4. Import Dataset dari Excel

- Klik menu "Import Excel"
- Pilih file Excel (.xlsx atau .xls)
- Preview data akan muncul
- Klik "Import" untuk menyimpan

### 5. Edit Dataset

- Pada halaman daftar dataset, klik tombol "Edit"
- Ubah data yang diperlukan
- Klik "Update"

### 6. Hapus Dataset

- Pada halaman daftar dataset, klik tombol "Hapus"
- Konfirmasi penghapusan

### 7. Manajemen Admin (Admin Only)

- Akses menu "Admin Panel"
- Kelola user, role, dan permissions
- Monitor aktivitas sistem

## 📸 Screenshot

### Dashboard
<img width="1919" height="921" alt="image" src="https://github.com/user-attachments/assets/11a30d94-6e61-43d0-bcf0-5ad787b9b1c4" />

### Datasets View
<img width="1916" height="925" alt="image" src="https://github.com/user-attachments/assets/af7286d9-6194-46c3-a210-f108d9c521dc" />
<img width="1919" height="926" alt="image" src="https://github.com/user-attachments/assets/443c3aef-bbde-4438-b040-0ef595b8b689" />


### Import Excel
<img width="1919" height="923" alt="image" src="https://github.com/user-attachments/assets/5fc25bfb-c8fd-43cd-842f-d09886935b29" />
<img width="1919" height="929" alt="image" src="https://github.com/user-attachments/assets/d7827520-1af6-468f-868b-75e933787b7c" />
<img width="1898" height="920" alt="image" src="https://github.com/user-attachments/assets/0814fa0b-4912-4d6f-910c-af5717d7c6af" />

### Share Datasets
<img width="1894" height="922" alt="image" src="https://github.com/user-attachments/assets/958ce953-b810-47bc-ab8b-4c7848e1cfae" />
<img width="1894" height="709" alt="image" src="https://github.com/user-attachments/assets/987c0cb1-0e81-4ff3-bfb7-197ce8752243" />

### Admin Panel
<img width="1902" height="768" alt="image" src="https://github.com/user-attachments/assets/d86113b5-0972-4270-9064-2b2f02128fe4" />
<img width="1901" height="925" alt="image" src="https://github.com/user-attachments/assets/adb26bd0-8e40-4455-84de-4412aa6a9036" />

## 📁 Struktur Folder

```
DataWeb/
├── app/                    # Application core
│   ├── Http/
│   │   ├── Controllers/   # Controllers
│   │   └── Middleware/    # Middleware
│   └── Models/            # Eloquent models
├── config/                # Configuration files
├── database/
│   ├── migrations/        # Database migrations
│   └── seeders/          # Database seeders
├── public/               # Public assets
│   ├── css/
│   ├── js/
│   └── images/
├── resources/
│   └── views/            # Blade templates
├── routes/
│   └── web.php           # Web routes
├── storage/              # Storage files
└── tests/                # Tests
```

## 🔧 Troubleshooting

### Error: Class not found

```bash
composer dump-autoload
```

### Error: Permission denied pada storage

```bash
chmod -R 775 storage bootstrap/cache
```

### Error saat migrate

```bash
# Drop semua table dan migrate ulang
php artisan migrate:fresh
```

### Assets tidak muncul

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Compile ulang assets
npm run dev
```

---

⭐ Jika project ini bermanfaat, berikan star pada repository ini!
