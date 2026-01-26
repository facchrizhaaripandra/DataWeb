# DataWeb - Aplikasi Manajemen Dataset

Aplikasi web berbasis Laravel untuk manajemen dataset dengan fitur autentikasi lengkap dan import data dari Excel.

![Login System](https://private-user-images.githubusercontent.com/176025770/539717340-09f20c2d-ce15-4831-9f23-b93bb5193643.png)

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Teknologi](#-teknologi)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
- [Konfigurasi](#-konfigurasi)
- [Penggunaan](#-penggunaan)
- [Screenshot](#-screenshot)
- [Kontribusi](#-kontribusi)
- [Lisensi](#-lisensi)

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
![Dashboard](https://private-user-images.githubusercontent.com/176025770/539718110-f60acca8-5e76-4036-9f47-dfe72ce60af6.png)

### Datasets View
![Datasets](https://private-user-images.githubusercontent.com/176025770/539718441-041b3079-ae95-49d8-b24d-0cb6e42a0c28.png)

### Import Excel
![Import Excel](https://private-user-images.githubusercontent.com/176025770/539720406-a51a6a5a-98ee-4901-9334-84ec8231f6f0.png)

### Admin Panel
![Admin Panel](https://private-user-images.githubusercontent.com/176025770/539719630-5ccf31bb-c2c3-4540-9b7b-bf859b473b85.png)

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

## 🤝 Kontribusi

Kontribusi sangat diterima! Untuk berkontribusi:

1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b fitur-baru`)
3. Commit perubahan (`git commit -m 'Menambahkan fitur baru'`)
4. Push ke branch (`git push origin fitur-baru`)
5. Buat Pull Request

## 📝 Changelog

### Version 1.0.0 (Current)
- ✅ Sistem autentikasi lengkap
- ✅ CRUD Dataset
- ✅ Import Excel
- ✅ Admin panel
- ✅ Responsive UI

## 📄 Lisensi

Project ini dilisensikan di bawah [MIT License](LICENSE).

## 👨‍💻 Author

**Facchrizha Aripandra**

- GitHub: [@facchrizhaaripandra](https://github.com/facchrizhaaripandra)

## 🙏 Acknowledgments

- Laravel Framework
- Bootstrap/Tailwind CSS
- Laravel Excel
- Font Awesome Icons

## 📞 Support

Jika Anda menemukan bug atau memiliki pertanyaan, silakan:

- Buka [Issue](https://github.com/facchrizhaaripandra/DataWeb/issues)
- Email: facchrizhaaripandra@example.com

---

⭐ Jika project ini bermanfaat, berikan star pada repository ini!
