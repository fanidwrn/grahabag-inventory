document.addEventListener("DOMContentLoaded", function () {
  // 1. LIVE FILTER SEARCH & PAGINATION UNTUK SEMUA TABEL
  const searchInputs = document.querySelectorAll("#tableSearch, #bahanSearch");
  let currentPage = 1;
  const itemsPerPage = 20;

  function renderPagination() {
    const table = document.querySelector(".data-table");
    if (!table) return;

    const tbody = table.querySelector("tbody");
    const allRows = Array.from(tbody.querySelectorAll("tr"));

    // Exclude empty state rows (like "Belum ada data")
    const dataRows = allRows.filter((row) => !row.querySelector("td[colspan]"));

    // Apply search filter if any
    let activeRows = dataRows;
    const filterInput = document.querySelector("#tableSearch, #bahanSearch");
    if (filterInput) {
      const filter = filterInput.value.toLowerCase();
      if (filter) {
        activeRows = dataRows.filter((row) => {
          return row.innerText.toLowerCase().includes(filter);
        });
      }
    }

    const totalItems = activeRows.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;

    // Hide all rows first
    dataRows.forEach((row) => (row.style.display = "none"));

    // Show active rows for current page
    activeRows
      .slice(startIndex, endIndex)
      .forEach((row) => (row.style.display = ""));

    // Show empty state if needed
    const emptyRow = allRows.find((row) => row.querySelector("td[colspan]"));
    if (totalItems === 0 && dataRows.length > 0) {
      // All filtered out
      if (emptyRow) emptyRow.style.display = "";
    } else if (totalItems === 0 && dataRows.length === 0) {
      if (emptyRow) emptyRow.style.display = "";
    } else {
      if (emptyRow) emptyRow.style.display = "none";
    }

    // Update Footer Info
    const infoEl = document.querySelector(".pagination-info");
    if (infoEl) {
      const startDisplay = totalItems === 0 ? 0 : startIndex + 1;
      const endDisplay = Math.min(endIndex, totalItems);
      infoEl.innerHTML = `Menampilkan ${startDisplay}-${endDisplay} dari ${totalItems} data`;
    }

    // Update Footer Controls
    const controlsEl = document.querySelector(".pagination-controls");
    if (controlsEl) {
      let html = "";

      // Prev btn
      if (currentPage > 1) {
        html += `<button type="button" class="btn-page" onclick="goToPage(${currentPage - 1})">&lt;</button>`;
      } else {
        html += `<button type="button" class="btn-page" disabled>&lt;</button>`;
      }

      // Page numbers
      let startPage = Math.max(1, currentPage - 2);
      let endPage = Math.min(totalPages, currentPage + 2);

      if (startPage > 1) {
        html += `<button type="button" class="btn-page" onclick="goToPage(1)">1</button>`;
        if (startPage > 2) html += `<span class="page-dots">...</span>`;
      }

      for (let i = startPage; i <= endPage; i++) {
        if (i === currentPage) {
          html += `<button type="button" class="btn-page page-active">${i}</button>`;
        } else {
          html += `<button type="button" class="btn-page" onclick="goToPage(${i})">${i}</button>`;
        }
      }

      if (endPage < totalPages) {
        if (endPage < totalPages - 1)
          html += `<span class="page-dots">...</span>`;
        html += `<button type="button" class="btn-page" onclick="goToPage(${totalPages})">${totalPages}</button>`;
      }

      // Next btn
      if (currentPage < totalPages) {
        html += `<button type="button" class="btn-page" onclick="goToPage(${currentPage + 1})">&gt;</button>`;
      } else {
        html += `<button type="button" class="btn-page" disabled>&gt;</button>`;
      }

      controlsEl.innerHTML = html;
    }
  }

  // Expose to window so onclick works
  window.goToPage = function (page) {
    currentPage = page;
    renderPagination();
  };

  if (searchInputs.length > 0) {
    searchInputs.forEach((input) => {
      input.addEventListener("keyup", function () {
        currentPage = 1;
        renderPagination();
      });
    });
  }

  // Initial render
  renderPagination();

  // 2. TOGGLE SHOW/HIDE PASSWORD
  const togglePassword = document.getElementById("togglePassword");
  const passwordInput = document.getElementById("passwordInput");
  const eyeIcon = document.getElementById("eyeIcon");

  if (togglePassword && passwordInput && eyeIcon) {
    togglePassword.addEventListener("click", function () {
      const type =
        passwordInput.getAttribute("type") === "password" ? "text" : "password";
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

  // 3. GENERIC MODAL HANDLER (OPEN/CLOSE)
  const openModalBtns = document.querySelectorAll(".btn-open-modal");
  const closeModalBtns = document.querySelectorAll(
    ".close-modal-btn, .btn-modal-cancel",
  );
  const modals = document.querySelectorAll(".modal-backdrop");

  openModalBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const targetId = this.getAttribute("data-target");
      const targetModal = document.getElementById(targetId);
      if (targetModal) targetModal.style.display = "flex";
    });
  });

  closeModalBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const modal = this.closest(".modal-backdrop");
      if (modal) {
        modal.style.display = "none";
        if (modal.id === "addStockOutModal") resetBulkForm();
      }
    });
  });

  // Menutup modal otomatis jika area luar box diklik
  window.addEventListener("click", function (event) {
    modals.forEach((modal) => {
      if (event.target === modal) {
        modal.style.display = "none";
        if (modal.id === "addStockOutModal") resetBulkForm();
      }
    });
  });

  // 4. DATA PARSING UNTUK EDIT MODAL
  // Bahan Baku Edit
  const editMaterialTriggers = document.querySelectorAll(".btnEditTrigger");
  editMaterialTriggers.forEach((btn) => {
    btn.addEventListener("click", function () {
      document.getElementById("edit_material_id").value = this.dataset.id;
      document.getElementById("edit_material_name").value = this.dataset.name;
      document.getElementById("edit_category_id").value = this.dataset.category;
      document.getElementById("edit_stock").value = this.dataset.stock;
      document.getElementById("edit_unit").value = this.dataset.unit;
      document.getElementById("edit_minimum_stock").value = this.dataset.min;
      document.getElementById("edit_price").value = this.dataset.price;
      document.getElementById("editMaterialModal").style.display = "flex";
    });
  });
  // Supplier Edit
  const editSupplierTriggers = document.querySelectorAll(
    ".btnEditSupplierTrigger",
  );
  editSupplierTriggers.forEach((btn) => {
    btn.addEventListener("click", function () {
      document.getElementById("edit_supplier_id").value = this.dataset.id;
      document.getElementById("edit_supplier_name").value = this.dataset.name;
      document.getElementById("edit_supplier_email").value = this.dataset.email;
      document.getElementById("edit_supplier_phone").value = this.dataset.phone;
      document.getElementById("edit_supplier_address").value =
        this.dataset.address;
      document.getElementById("editSupplierModal").style.display = "flex";
    });
  });

  // Stok Masuk Edit
  const editStockTriggers = document.querySelectorAll(".btnEditStockTrigger");
  editStockTriggers.forEach((btn) => {
    btn.addEventListener("click", function () {
      document.getElementById("edit_stock_in_id").value = this.dataset.id;
      document.getElementById("edit_stock_material_id").value =
        this.dataset.material;
      document.getElementById("edit_stock_supplier_id").value =
        this.dataset.supplier || "";
      document.getElementById("edit_stock_date").value = this.dataset.date;
      document.getElementById("edit_stock_total").value = this.dataset.total;
      document.getElementById("edit_stock_desc").value = this.dataset.desc;
      document.getElementById("editStockModal").style.display = "flex";
    });
  });

  // Stok Keluar Edit
  const editStockOutTriggers = document.querySelectorAll(
    ".btnEditStockOutTrigger",
  );
  editStockOutTriggers.forEach((btn) => {
    btn.addEventListener("click", function () {
      document.getElementById("edit_stock_out_id").value = this.dataset.id;
      document.getElementById("edit_stock_out_material_id").value =
        this.dataset.material;
      document.getElementById("edit_stock_out_date").value = this.dataset.date;
      document.getElementById("edit_stock_out_total").value =
        this.dataset.total;
      document.getElementById("edit_stock_out_catatan").value =
        this.dataset.catatan;
      document.getElementById("editStockOutModal").style.display = "flex";
    });
  });

  // 5. GENERIC DELETE CONFIRMATION DIALOG
  const deleteTriggers = document.querySelectorAll(".btn-delete-confirm");
  deleteTriggers.forEach((link) => {
    link.addEventListener("click", function (e) {
      const message =
        this.getAttribute("data-confirm-message") ||
        "Apakah Anda yakin ingin menghapus data ini?";
      if (!confirm(message)) {
        e.preventDefault();
      }
    });
  });

  // 6. TOAST NOTIFICATION AUTO FADE-OUT
  const toast = document.getElementById("toastNotification");
  if (toast) {
    setTimeout(() => {
      toast.style.opacity = "0";
      toast.style.transform = "translateY(-20px)";
      setTimeout(() => toast.remove(), 500);
    }, 3500);
  }

  // 7. DINAMIS MULTI-ITEM ROW CONTROLLER (STOK KELUAR)
  const btnAddNewRow = document.getElementById("btnAddNewRow");
  const dynamicItemContainer = document.getElementById("dynamicItemContainer");

  if (btnAddNewRow && dynamicItemContainer) {
    btnAddNewRow.addEventListener("click", function () {
      const newRow = document.createElement("div");
      newRow.className = "bulk-item-row";

      let optionsHTML = '<option value="">-- Pilih Bahan Baku --</option>';
      // masterMaterials is assumed to be defined in the global scope via PHP
      if (typeof masterMaterials !== "undefined") {
        masterMaterials.forEach((mat) => {
          optionsHTML += `<option value="${mat.material_id}">${mat.material_name} (Stok Tersedia: ${mat.stock} ${mat.unit})</option>`;
        });
      }

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
    triggers.forEach((btn) => {
      btn.onclick = function () {
        const row = this.closest(".bulk-item-row");
        if (row) row.remove();
      };
    });
  }

  function resetBulkForm() {
    if (dynamicItemContainer) {
      const rows = dynamicItemContainer.querySelectorAll(".bulk-item-row");
      for (let i = 1; i < rows.length; i++) {
        rows[i].remove();
      }
    }
  }

  // 8. PENGAJUAN BAHAN MODAL & AJAX
  const viewPengajuanTriggers = document.querySelectorAll(
    ".btnViewPengajuanTrigger",
  );
  if (viewPengajuanTriggers.length > 0) {
    viewPengajuanTriggers.forEach((btn) => {
      btn.addEventListener("click", function () {
        document.getElementById("viewMaterial").value = this.dataset.material;
        document.getElementById("viewSupplier").value = this.dataset.supplier;
        document.getElementById("viewTotal").value = this.dataset.total;
        document.getElementById("viewContactMethod").value =
          this.dataset.method || "whatsapp";
        document.getElementById("viewDesc").value = this.dataset.desc;
        document.getElementById("viewPengajuanModal").style.display = "flex";
      });
    });
  }

  const editPengajuanTriggers = document.querySelectorAll(
    ".btnEditPengajuanTrigger",
  );
  if (editPengajuanTriggers.length > 0) {
    editPengajuanTriggers.forEach((btn) => {
      btn.addEventListener("click", function () {
        document.getElementById("editPurchaseId").value = this.dataset.id;
        document.getElementById("editMaterial").value = this.dataset.materialid;
        document.getElementById("editSupplier").value = this.dataset.supplierid;
        document.getElementById("editTotal").value = this.dataset.total;
        document.getElementById("editContactMethod").value =
          this.dataset.method || "whatsapp";
        document.getElementById("editDesc").value = this.dataset.desc;
        document.getElementById("editPengajuanModal").style.display = "flex";
      });
    });
  }

  const btnUpdateStatusTriggers = document.querySelectorAll(
    ".btnUpdateStatusTrigger",
  );
  if (btnUpdateStatusTriggers.length > 0) {
    btnUpdateStatusTriggers.forEach((btn) => {
      btn.addEventListener("click", function () {
        if (confirm(this.dataset.confirm)) {
          const purchaseId = this.dataset.id;
          const newStatus = this.dataset.status;
          const method = this.dataset.method || "whatsapp";
          const phone = this.dataset.phone || "";
          const email = this.dataset.email || "";
          const message = this.dataset.message || "";

          const formData = new FormData();
          formData.append("purchase_id", purchaseId);
          formData.append("status", newStatus);

          fetch("../api/update_status_pengajuan.php", {
            method: "POST",
            body: formData,
          })
            .then((res) => res.json())
            .then((data) => {
              if (data.status === "success") {
                if (newStatus === "approved") {
                  if (method === "whatsapp" && phone) {
                    let formattedPhone = phone.replace(/\D/g, "");
                    if (formattedPhone.startsWith("0")) {
                      formattedPhone = "62" + formattedPhone.substring(1);
                    }
                    const waLink = `https://wa.me/${formattedPhone}?text=${encodeURIComponent(message)}`;
                    window.open(waLink, "_blank");
                  } else if (method === "email" && email) {
                    const mailtoLink = `mailto:${email}?subject=GRAHABAG%20-%20Permintaan%20Bahan%20Baku&body=${encodeURIComponent(message)}`;
                    window.open(mailtoLink, "_blank");
                  }
                }
                location.reload();
              } else {
                location.reload(); // To show error toast
              }
            })
            .catch((err) => {
              console.error(err);
              location.reload();
            });
        }
      });
    });
  }
});
