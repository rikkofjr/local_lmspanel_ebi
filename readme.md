# LMSPanel - Ekosistem Control Panel & Analytics LMS

## Definisi Gambaran Umum

**LMSPanel (`local_lmspanel_ebi`)** adalah pusat kendali dan dasbor analitik utama untuk LMS. 

Plugin ini  berfokus pada manajemen analitik internal mengenai penggunaan LMS secara luas.


## Panduan Instalasi Singkat

1. **Struktur Folder:** Letakkan folder pada direktori Moodle `local/lmspanel_ebi/`.
2. **Penyelesaian Instalasi:** Akses *Site Administration > Notifications* pada Moodle sebagai Administrator untuk pendaftaran plugin.

## 4. Hal yang Harus Diperhatikan

* **Integrasi Modul:** pastikan plugin yang dibutuhkan sudah terinstal (lihat pada file `public\local\lmspanel_ebi\classes\plugin_checker.php` ) dan jika ada tambahan lakukan update disana .
* **Hak Akses:** Untuk memantau dashboard ini berikan accses `local/lmspanel_ebi:view` dan `local/lmspanel_ebi:manage`.
* **Skalabilitas:** Panel dirancang modular untuk memuat penambahan modul analitik LMS baru di masa mendatang.