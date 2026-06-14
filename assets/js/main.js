// assets/js/main.js

// 1. Format Rupiah Otomatis
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
}

// 2. Fungsi Toggle Sidebar (Jika ada tombol menu)
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('aside');
    const toggleBtn = document.getElementById('sidebar-toggle');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });
    }
});