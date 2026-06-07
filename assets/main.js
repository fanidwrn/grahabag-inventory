document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("tableSearch");
    if (searchInput) {
        searchInput.addEventListener("keyup", function() {
            const filter = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll(".data-table tbody tr");
            
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    }

    // Toggle Show/Hide Password 
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("passwordInput");
    const eyeIcon = document.getElementById("eyeIcon");

    if (togglePassword && passwordInput && eyeIcon) {
        togglePassword.addEventListener("click", function() {
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);
            
            if (type === "password") {
                eyeIcon.src = "../assets/icons/eye-show.png";
                eyeIcon.alt = "Show Password";
            } else {
                eyeIcon.src = "../assets/icons/eye-hide.png";
                eyeIcon.alt = "Hide Password";
            }
        });
    }

    // LIVE FILTER SEARCH UNTUK HALAMAN BAHAN BAKU ===
    const bahanSearch = document.getElementById("bahanSearch");
    if (bahanSearch) {
        bahanSearch.addEventListener("keyup", function() {
            const filter = bahanSearch.value.toLowerCase();
            const rows = document.querySelectorAll(".data-table tbody tr");
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? "" : "none";
            });
        });
    }

    // MODAL INTERACTION: TAMBAH BAHAN ===
    const addModal = document.getElementById("addMaterialModal");
    const openAddBtn = document.getElementById("openAddModal");
    const closeAddBtn = document.getElementById("closeAddModal");
    const cancelAddBtn = document.getElementById("cancelAddModal");

    if (openAddBtn && addModal) {
        openAddBtn.onclick = () => addModal.style.display = "flex";
        closeAddBtn.onclick = () => addModal.style.display = "none";
        cancelAddBtn.onclick = () => addModal.style.display = "none";
    }

    // MODAL INTERACTION: EDIT BAHAN ===
    const editModal = document.getElementById("editMaterialModal");
    const closeEditBtn = document.getElementById("closeEditModal");
    const cancelEditBtn = document.getElementById("cancelEditModal");
    const editTriggers = document.querySelectorAll(".btnEditTrigger");

    editTriggers.forEach(btn => {
        btn.addEventListener("click", function() {
            // Isi field modal edit berdasarkan atribut data- dari baris tabel yang di-klik
            document.getElementById("edit_material_id").value = this.dataset.id;
            document.getElementById("edit_material_name").value = this.dataset.name;
            document.getElementById("edit_category_id").value = this.dataset.category;
            document.getElementById("edit_stock").value = this.dataset.stock;
            document.getElementById("edit_unit").value = this.dataset.unit;
            document.getElementById("edit_minimum_stock").value = this.dataset.min;
            document.getElementById("edit_price").value = this.dataset.price;

            // Tampilkan modal edit
            editModal.style.display = "flex";
        });
    });

    if (editModal) {
        closeEditBtn.onclick = () => editModal.style.display = "none";
        cancelEditBtn.onclick = () => editModal.style.display = "none";
    }

    // Menutup modal otomatis jika area luar box diklik
    window.onclick = function(event) {
        if (event.target == addModal) addModal.style.display = "none";
        if (event.target == editModal) editModal.style.display = "none";
    }

    // KONFIRMASI AKSI DELETE ===
    const deleteTriggers = document.querySelectorAll(".btnDeleteTrigger");
    deleteTriggers.forEach(link => {
        link.addEventListener("click", function(e) {
            if (!confirm("Apakah Anda yakin ingin menghapus data bahan baku ini secara permanen?")) {
                e.preventDefault(); // Batalkan pengalihan link ke API jika memilih 'Cancel'
            }
        });
    });

    // TOAST NOTIFICATION AUTO FADE-OUT ===
    const toast = document.getElementById("toastNotification");
    if (toast) {
        setTimeout(() => {
            toast.style.opacity = "0";
            toast.style.transform = "translateY(-20px)";
            setTimeout(() => toast.remove(), 500); // Hapus elemen penuh setelah transisi selesai
        }, 3500); // Muncul selama 3.5 detik
    }

    // MODAL INTERACTION: TAMBAH STOK MASUK ===
    const addStockModal = document.getElementById("addStockModal");
    const openAddStockBtn = document.getElementById("openAddStockModal");
    const closeAddStockBtn = document.getElementById("closeAddStock");
    const cancelAddStockBtn = document.getElementById("cancelAddStock");

    if (openAddStockBtn && addStockModal) {
        openAddStockBtn.onclick = () => addStockModal.style.display = "flex";
        closeAddStockBtn.onclick = () => addStockModal.style.display = "none";
        cancelAddStockBtn.onclick = () => addStockModal.style.display = "none";
    }

    // MODAL INTERACTION: EDIT STOK MASUK ===
    const editStockModal = document.getElementById("editStockModal");
    const closeEditStockBtn = document.getElementById("closeEditStock");
    const cancelEditStockBtn = document.getElementById("cancelEditStock");
    const editStockTriggers = document.querySelectorAll(".btnEditStockTrigger");

    editStockTriggers.forEach(btn => {
        btn.addEventListener("click", function() {
            document.getElementById("edit_stock_in_id").value = this.dataset.id;
            document.getElementById("edit_stock_material_id").value = this.dataset.material;
            document.getElementById("edit_stock_supplier_id").value = this.dataset.supplier || "";
            document.getElementById("edit_stock_date").value = this.dataset.date;
            document.getElementById("edit_stock_total").value = this.dataset.total;
            document.getElementById("edit_stock_desc").value = this.dataset.desc;

            editStockModal.style.display = "flex";
        });
    });

    if (editStockModal) {
        closeEditStockBtn.onclick = () => editStockModal.style.display = "none";
        cancelEditStockBtn.onclick = () => editStockModal.style.display = "none";
    }

    // Konfirmasi Hapus Stok Masuk
    const deleteStockTriggers = document.querySelectorAll(".btnDeleteStockTrigger");
    deleteStockTriggers.forEach(link => {
        link.addEventListener("click", function(e) {
            if (!confirm("Peringatan! Menghapus data ini akan otomatis mengurangi kembali jumlah stok pada master data Bahan Baku. Lanjutkan?")) {
                e.preventDefault();
            }
        });
    });

    // PENANGANAN MODAL UTAMA STOK KELUAR ===
    const addStockOutModal = document.getElementById("addStockOutModal");
    const openAddStockOutBtn = document.getElementById("openAddStockOutModal");
    const closeAddStockOutBtn = document.getElementById("closeAddStockOut");
    const cancelAddStockOutBtn = document.getElementById("cancelAddStockOut");

    if (openAddStockOutBtn && addStockOutModal) {
        openAddStockOutBtn.onclick = () => addStockOutModal.style.display = "flex";
        closeAddStockOutBtn.onclick = () => {
            addStockOutModal.style.display = "none";
            resetBulkForm();
        };
        cancelAddStockOutBtn.onclick = () => {
            addStockOutModal.style.display = "none";
            resetBulkForm();
        };
    }

    // MODAL INTERACTION: EDIT STOK KELUAR ===
    const editStockOutModal = document.getElementById("editStockOutModal");
    const closeEditStockOutBtn = document.getElementById("closeEditStockOut");
    const cancelEditStockOutBtn = document.getElementById("cancelEditStockOut");
    const editStockOutTriggers = document.querySelectorAll(".btnEditStockOutTrigger");

    editStockOutTriggers.forEach(btn => {
        btn.addEventListener("click", function() {
            document.getElementById("edit_stock_out_id").value = this.dataset.id;
            document.getElementById("edit_stock_out_material_id").value = this.dataset.material;
            document.getElementById("edit_stock_out_date").value = this.dataset.date;
            document.getElementById("edit_stock_out_total").value = this.dataset.total;
            document.getElementById("edit_stock_out_catatan").value = this.dataset.catatan;

            if(editStockOutModal) editStockOutModal.style.display = "flex";
        });
    });

    if (editStockOutModal) {
        closeEditStockOutBtn.onclick = () => editStockOutModal.style.display = "none";
        cancelEditStockOutBtn.onclick = () => editStockOutModal.style.display = "none";
    }

    // DINAMIS MULTI-ITEM ROW CONTROLLER ===
    const btnAddNewRow = document.getElementById("btnAddNewRow");
    const dynamicItemContainer = document.getElementById("dynamicItemContainer");

    if (btnAddNewRow && dynamicItemContainer) {
        btnAddNewRow.addEventListener("click", function() {
            const newRow = document.createElement("div");
            newRow.className = "bulk-item-row";
            
            // Buat opsi dropdown berdasarkan data JSON masterMaterials yang dikirim dari PHP
            let optionsHTML = '<option value="">-- Pilih Bahan Baku --</option>';
            masterMaterials.forEach(mat => {
                optionsHTML += `<option value="${mat.material_id}">${mat.material_name} (Stok Tersedia: ${mat.stock} ${mat.unit})</option>`;
            });

            newRow.style.display = "flex";
            newRow.style.gap = "15px";
            newRow.style.background = "#f8fafc";
            newRow.style.padding = "15px";
            newRow.style.borderRadius = "8px";
            newRow.style.border = "1px solid #e2e8f0";
            newRow.style.marginBottom = "10px";
            newRow.style.alignItems = "center";

            newRow.innerHTML = `
                <div style="flex-grow: 1;">
                    <label class="sub-label" style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Nama Bahan Baku</label>
                    <select name="material_ids[]" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1;">
                        ${optionsHTML}
                    </select>
                </div>
                <div style="width: 130px;">
                    <label class="sub-label" style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Jumlah Keluar</label>
                    <input type="number" name="total_outs[]" placeholder="0" min="1" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1;">
                </div>
                <div style="align-self: center; margin-top: 20px;">
                    <button type="button" class="btn-delete-row-item remove-row-trigger" style="width: 28px; height: 28px; font-size: 12px; display: flex; align-items: center; justify-content: center; background: #fee2e2; color: #ef4444; border: none; border-radius: 6px; cursor: pointer;">✕</button>
                </div>
            `;
            
            dynamicItemContainer.appendChild(newRow);
            attachRemoveEvent();
        });
    }

    function attachRemoveEvent() {
        const triggers = document.querySelectorAll(".remove-row-trigger");
        triggers.forEach(btn => {
            btn.onclick = function() {
                const row = this.closest(".bulk-item-row");
                row.remove();
            };
        });
    }

    function resetBulkForm() {
        if(dynamicItemContainer) {
            // Kembalikan isi kontainer hanya menyisakan baris pertama saja saat modal ditutup
            const rows = dynamicItemContainer.querySelectorAll(".bulk-item-row");
            for(let i = 1; i < rows.length; i++) {
                rows[i].remove();
            }
        }
    }

    // Dialog Konfirmasi Pembatalan Record Stok Keluar
    const deleteStockOutTriggers = document.querySelectorAll(".btnDeleteStockOutTrigger");
    deleteStockOutTriggers.forEach(link => {
        link.addEventListener("click", function(e) {
            if (!confirm("Menghapus data pengeluaran ini akan otomatis mengembalikan jumlah kuantitas stok ke master data Bahan Baku. Lanjutkan?")) {
                e.preventDefault();
            }
        });
    });
});