# Program Bengkel & Service Management System — Jomoto

Aplikasi web modern berbasis Laravel 11 untuk manajemen operasional bengkel terpadu, mendukung kendaraan konvensional dan listrik (EV), multi-kendaraan per pelanggan, pencatatan waktu kerja teknisi, sistem estimasi biaya, penagihan QRIS, serta integrasi e-commerce & ERP.

## 🚀 Fitur Utama

- **Role Management**: Dashboard terpisah untuk Admin, Service Adviser (SA), Teknisi, dan Customer.
- **Workflow Servis Terstruktur**:
  - Pendaftaran & Diagnosa oleh SA (SPK / Work Order)
  - Lembar Pemeriksaan Kendaraan oleh Teknisi
  - Lembar Estimasi Biaya (Jasa, Sparepart, & Sub-Material Custom)
  - Persetujuan Pelanggan
  - Timer Pengerjaan Aktual Teknisi
  - Terbit Kwitansi / Invoice PDF otomatis
- **Tipe Servis Lengkap**:
  - Servis Reguler
  - Servis Gratis ATPM (KM 1.000, 4.000, 7.000)
  - Servis Gratis Jomoto (KM 10.000, 20.000, dst)
  - Servis Panggilan / Home Service (On-site & Off-site)
- **Multi-Kendaraan**: 1 Customer dapat memiliki banyak unit motor.
- **Reminder Servis**: Tombol instan kirim pengingat lewat WhatsApp Web.
- **Integrasi**: API-Ready untuk ERP & sinkronisasi stok sparepart dari e-commerce Jomoto.

## 📋 Dokumentasi Teknis

Silakan baca file detail arsitektur dan rancangan database di:
👉 [`PROJECT_BENGKEL_DETAIL.md`](./PROJECT_BENGKEL_DETAIL.md)

## 🌐 Deployment

- **Production Domain**: [bengkel.solusisurabaya.com](https://bengkel.solusisurabaya.com)
- **Server**: Alurelab Hosting (`emerald.hidden-server.net:31988`)
- **Web Root**: `/home/alurelab/bengkel.solusisurabaya.com/public`
