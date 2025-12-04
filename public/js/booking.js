function hitungTotal() {
    const hargaPerMalam = parseInt(
        document.getElementById("hargaPerMalam").value
    );
    const checkInVal = document.querySelector('[name="check_in_date"]').value;
    const checkOutVal = document.querySelector('[name="check_out_date"]').value;

    let total = 0;

    // Hitung lama menginap (dalam hari)
    if (checkInVal && checkOutVal) {
        const masuk = new Date(checkInVal);
        const keluar = new Date(checkOutVal);

        const selisih = (keluar - masuk) / (1000 * 60 * 60 * 24);

        if (selisih > 0) {
            total += selisih * hargaPerMalam;
        }
    }

    // Tambah fasilitas tambahan
    document
        .querySelectorAll('input[name="fasilitas_ids[]"]:checked')
        .forEach((cb) => {
            const biaya = document.querySelector(
                `.hargaFasilitas[data-id="${cb.value}"]`
            ).value;

            total += parseInt(biaya);
        });

    // Format rupiah
    document.getElementById("totalHarga").innerText =
        "Rp " + total.toLocaleString("id-ID");
}

// Trigger perhitungan saat input berubah
document.querySelectorAll("input").forEach((el) => {
    el.addEventListener("change", hitungTotal);
});
