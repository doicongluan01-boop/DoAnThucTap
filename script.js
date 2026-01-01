
// ==================== script.js ====================
//Hàm login 
document.addEventListener("DOMContentLoaded", () => {
    
    // ============================================================
    // PHẦN 1: XỬ LÝ ĐĂNG NHẬP (Cập nhật logic lưu is_gvpb)
    // ============================================================
    const loginForm = document.getElementById("loginForm");
    const alertBox = document.getElementById("alert-error");

    if (loginForm && alertBox) {
        loginForm.onsubmit = async (e) => {
            e.preventDefault();
            alertBox.classList.add("d-none");

            const formData = new FormData(loginForm);
            try {
                const res = await fetch("login.php", { method: "POST", body: formData });
                const result = await res.json();

                if (result.success) {
                    // Lưu thông tin cơ bản
                    localStorage.setItem("hoten", result.hoten || "User");
                    localStorage.setItem("role", result.role);

                    // Lưu mã số riêng
                    if (result.role === "giangvien") {
                        localStorage.setItem("MaGV", result.username);
                        
                        // ★ MỚI THÊM: Lưu trạng thái Phản biện
                        if (result.is_gvpb) {
                            localStorage.setItem("is_gvpb", "true");
                        } else {
                            localStorage.removeItem("is_gvpb"); // Xóa rác nếu không phải
                        }
                    }
                    
                    if (result.role === "sinhvien") {
                        localStorage.setItem("MaSV", result.username);
                    }

                    window.location.href = "layout.html";
                } else {
                    alertBox.classList.remove("d-none");
                    alertBox.textContent = result.message || "Đăng nhập thất bại!";
                }
            } catch (err) {
                alertBox.classList.remove("d-none");
                alertBox.textContent = "Lỗi kết nối server!";
            }
        };
    }

    // ============================================================
    // PHẦN 2: PHÂN QUYỀN MENU (Chạy ở trang Layout)
    // ============================================================
    
    // Chỉ chạy nếu đang ở trang có Sidebar (tức là đã login vào trong)
    const sidebar = document.getElementById("sidebar"); 
    if (sidebar) {
        checkUserPermissions();
    }

    function checkUserPermissions() {
        const role = localStorage.getItem("role");
        const hoten = localStorage.getItem("hoten");
        const isGVPB = localStorage.getItem("is_gvpb") === "true"; // Check cờ GVPB

        // 1. Hiển thị tên user
        const userDisplay = document.getElementById("userDisplay");
        const userRoleDisplay = document.getElementById("userRole");
        if (userDisplay) userDisplay.textContent = hoten;
        
        let roleName = "Khách";
        if (role === 'admin') roleName = "Quản Trị Viên";
        if (role === 'giangvien') roleName = "Giảng Viên";
        if (role === 'sinhvien') roleName = "Sinh Viên";
        if (userRoleDisplay) userRoleDisplay.textContent = roleName;

        // 2. Cấu hình quyền menu
        // Mặc định các menu được phép thấy
        const roleConfig = {
            'admin': ['admin', 'baocao', 'hoidong'],
            'giangvien': [ 'sinhvien','giangvien', 'baocao'], 
            'sinhvien': [ 'baocao']
        };

        // Lấy danh sách quyền cơ bản
        let allowed = roleConfig[role] ? [...roleConfig[role]] : [];

        // ★ LOGIC ĐẶC BIỆT: Nếu là GV Phản Biện -> Được thêm quyền 'phanbien'
        if (role === 'giangvien' && isGVPB) {
            allowed.push('phanbien');
        }

        // 3. Ẩn/Hiện menu dựa trên data-main
        const allLinks = document.querySelectorAll("a[data-main]");
        allLinks.forEach(link => {
            const feature = link.getAttribute("data-main");
            
            // Nếu không có quyền -> Thêm class d-none (của Bootstrap) hoặc style display:none
            if (allowed.includes(feature)) {
                link.classList.remove("d-none"); // Hoặc remove class 'menu-hidden'
                link.style.display = ""; // Reset style
            } else {
                link.classList.add("d-none"); // Ẩn đi
                // Hoặc: link.style.display = "none";
            }
        });
    }


    // ============================================================
    // PHẦN 3: CÁC CHỨC NĂNG KHÁC (Giữ nguyên của bạn)
    // ============================================================

    // UPLOAD BIÊN BẢN
    const nopBB = document.getElementById("nopBienBan");
    if (nopBB) {
        nopBB.onclick = () => uploadBB();
    }

    // LOAD DANH SÁCH BIÊN BẢN
    const listBB = document.getElementById("listBB");
    if (listBB) {
        loadBB();
    }
});
//====================================================
// Hàm hiển thị submenu – nhận tham số là phần tử được click
function showSubmenu(clickedElement) {
  document.querySelectorAll(".top-menu a").forEach(a => a.classList.remove("active"));
  clickedElement.classList.add("active");

  const submenu = document.getElementById("submenu");
  let html = "";

  const main = clickedElement.id || clickedElement.getAttribute("data-main");

  if (main === "sinhvien") {
    html = `
      <a href="#" onclick="loadFile(this, 'sinhvien.php')">Danh sách sinh viên</a>
       <a href="#" onclick="loadFile(this, 'phan_nhom_sv.php')">Phân nhóm cho sinh viên</a>
      <a href="#" onclick="loadFile(this, 'giao_detai.php')">Giao đề tài cho nhóm</a>
    `;
  }
  else if (main === "giangvien") {
    html = `
      <a href="#" onclick="loadFile(this, 'bienban.php')">Nộp báo cáo hướng dẫn</a>
         <a href="#" onclick="loadFile(this, 'gapSV.php')">Gặp sinh viên</a>
            <a href="#" onclick="loadFile(this, 'phancongnhiemvu.php')">Phân công nhiệm vụ</a>
     <a href="#" onclick="loadFile(this, 'nhapdiem_gvhd.php')">Nhập điểm hướng dẫn(GVHD)</a>
     <a href="#" onclick="loadFile(this, 'nhapdiem_gvpb.php')">Nhập điểm phản biện </a>
     <a href="#" onclick="loadFile(this, 'xuatfilediempb.php')">Xuất file điểm phản biện </a>
    `;
  }
  else if (main === "admin") {
    html = `
    <a href="#" onclick="loadFile(this, 'sinhvien.php')">Danh sách sinh viên</a>
       <a href="#" onclick="loadFile(this, 'giangvien.php')">Danh sách giảng viên hướng dẫn</a>
<a href="#" onclick="loadFile(this, 'phancong_gvhd.php')">Phân công GVHD</a>
<a href="#" onclick="loadFile(this, 'phancong_gvpb.php')">Phân công GVPB</a>
 <a href="#" onclick="loadFile(this, 'baocao_sinhvien_chuagap.php')" style="color:#ff4444;font-weight:bold">
          SV chưa gặp GVHD
      </a>
    `;
  }
  else if (main === "baocao") {                     
    html = `
      <a href="#" onclick="loadFile(this, 'baocao_detai.php')">
          Đề tài đã giao
      </a>
       <a href="#" onclick="loadFile(this, 'baocao_quatrinh.php')" style="color:#ff4444;font-weight:bold">
          Báo cáo quá trình
      </a>
      <a href="#" onclick="loadFile(this, 'diem.php')">Thống kê điểm</a>
    `;
  }
  else if (main === "hoidong") {
    html = `
      <a href="#" onclick="loadFile(this, 'danhsach_hd.php')">Danh sách hội đồng</a>
            <a href="#" onclick="loadFile(this, 'phanbo_hoidong.php')">Danh sách đề tài – SV theo hội đồng</a>
            <a href="#" onclick="loadFile(this, 'nhapdiem_hoidong.php')">Nhập điểm bảo vệ (Hội đồng)</a>
    `;
  }


  submenu.innerHTML = html;
  // Tự động click mục đầu tiên (an toàn)
 if (window.innerWidth > 768) {
  setTimeout(() => {
    const firstLink = submenu.querySelector("a");
    if (firstLink && !firstLink.classList.contains("active-sub")) {
      firstLink.click();
    }
  }, 100);
}
  submenu.innerHTML = html;
}

// Load trang Phân công GVHD (có export)
function loadPhanCong(linkElement) {
  const role = localStorage.getItem("role");

  // CẤM GIẢNG VIÊN VÀ SINH VIÊN VÀO TRANG PHÂN CÔNG GVHD
  if (role !== "admin") {
    alert("Bạn không có quyền truy cập trang Phân công GVHD!");
    return;
  }

  document.querySelectorAll("#submenu a").forEach(a => a.classList.remove("active-sub"));
  if (linkElement) linkElement.classList.add("active-sub");

  document.getElementById("content-area").innerHTML = `
    <iframe src="phancong_gvhd.html?t=${Date.now()}" 
            style="width:100%; height:calc(100vh - 120px); border:none; border-radius:16px; background:white; opacity:0; transition:opacity 0.5s;"
            onload="this.style.opacity=1;">
    </iframe>
  `;
}

// Load các trang khác
function loadFile(linkElement, file) {
  document.querySelectorAll("#submenu a").forEach(a => a.classList.remove("active-sub"));
  linkElement.classList.add("active-sub");

  document.getElementById("content-area").innerHTML = `
    <iframe src="${file}?t=${Date.now()}" 
            style="width:100%; height:calc(100vh - 120px); border:none; border-radius:16px; background:white; opacity:0; transition:opacity 0.5s;"
            onload="this.style.opacity=1;">
    </iframe>
  `;
  closeSidebarMobile(); 
}

// Load script export chỉ khi cần
function loadExportScripts() {
  document.querySelectorAll('script[data-export]').forEach(s => s.remove());
  const files = ['export_excel.js', 'export_word.js'];
  files.forEach(src => {
    const script = MP.createElement('script');
    script.src = src + '?t=' + Date.now();
    script.dataset.export = 'true';
    document.body.appendChild(script);
  });
}

//  – CHỈ load bảng sinh viên
function loadTableSinhVien() {
  const container = document.getElementById('sv-table-container');
  if (!container) return;

  container.innerHTML = '<p class="text-center text-primary"><i class="fas fa-spinner fa-spin"></i> Đang tải dữ liệu...</p>';

  fetch('sinhvien.php')
    .then(r => r.text())
    .then(html => {
      container.innerHTML = html;
    })
    .catch(err => {
      container.innerHTML = '<div class="alert alert-danger text-center">Lỗi tải dữ liệu!</div>';
      console.error(err);
    });
}
/// hàm import thông minh
document.addEventListener('DOMContentLoaded', function () {
    // Tìm tất cả input có chấp nhận file excel (dù id/class gì cũng được)
    const inputs = document.querySelectorAll('input[type="file"]');
    inputs.forEach(input => {
        if (input.accept && input.accept.includes('excel')) {
            input.addEventListener('change', importSmart);
            console.log('Đã gắn import cho:', input);
        }
    });

    // Trường hợp cụ thể id = file_excel (phổ biến nhất)
    const fileInput = document.getElementById('file_excel') || 
                      document.getElementById('importExcel') || 
                      document.querySelector('input[name="file_excel"]');
    if (fileInput) {
        fileInput.addEventListener('change', importSmart);
        console.log('Import Excel đã sẵn sàng!');
    }
});

async function importSmart(e) {
    if (input.files && input.files[0]) {
        // 1. Đóng Modal hướng dẫn lại cho gọn
        var myModalEl = document.getElementById('modalImport');
        var modal = bootstrap.Modal.getInstance(myModalEl);
        if (modal) {
            modal.hide();
        }

        // 2. Hiển thị thông báo đang xử lý (Swal)
        Swal.fire({
            title: 'Đang Import...',
            text: 'Vui lòng chờ trong giây lát',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        const file = input.files[0];
        const formData = new FormData();
        formData.append('file_excel', file);

        fetch('xuly_import.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    Swal.fire({
                        title: 'Thành công!',
                        html: res.message + '<br>' + (res.detail || ''),
                        icon: 'success'
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Lỗi!', res.message, 'error');
                }
                // Reset input để chọn lại file khác nếu muốn
                input.value = ''; 
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Lỗi hệ thống', 'Không thể kết nối server', 'error');
                input.value = '';
            });
    }
}

function fallbackIframeUpload(file, status) {
    const iframe = document.createElement('iframe');
    iframe.name = 'hidden_iframe';
    iframe.style.display = 'none';
    document.body.appendChild(iframe);

    const form = document.createElement('form');
    form.target = 'hidden_iframe';
    form.method = 'POST';
    form.action = 'xuly_import.php';
    form.enctype = 'multipart/form-data';
    form.style.display = 'none';

    const input = document.createElement('input');
    input.type = 'file';
    input.name = 'file_excel';
    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    form.appendChild(input);
    document.body.appendChild(form);

    iframe.onload = () => {
        setTimeout(() => {
            const txt = iframe.contentDocument?.body?.textContent || '';
            try {
                const json = JSON.parse(txt);
                status.innerHTML = `<div class="alert alert-${json.success?'success':'danger'}">${json.message}</div>`;
                if (json.success) setTimeout(() => location.reload(), 1500);
            } catch {
                status.innerHTML = `<div class="alert alert-danger">Lỗi server:<br><small>${txt.substring(0,400)}</small></div>`;
            }
            document.body.removeChild(iframe);
            document.body.removeChild(form);
        }, 200);
    };
    form.submit();
}
//Hàm xóa
function attachDeleteEvents() {
    document.querySelectorAll('.delete-real').forEach(btn => {
        btn.addEventListener('click', function() {
            const mssv = this.dataset.mssv;
            const ten = this.closest('tr').cells[2].textContent.trim();
            const row = this.closest('tr');

            if (!confirm(`Bạn có chắc chắn muốn XÓA sinh viên:\n${mssv} - ${ten} ?`)) {
                return;
            }

            const formData = new FormData();
            formData.append('mssv', mssv);

            fetch('xoa_sinhvien.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    row.remove();
                    alert("Đã xoá thành công!");
                } else {
                    alert("Không xoá được: " + res.message);
                }
            });
        });
    });
}

///phân nhóm sinh viên
function loadPhanNhom() {
    fetch('phan_nhom_sv.php')
      .then(r => r.text())
      .then(html => {
          document.getElementById("sv-table-container").innerHTML = html;
      });
}


// ==================== KHỞI ĐỘNG KHI TRANG LOAD XONG ====================
document.addEventListener("DOMContentLoaded", () => {
  const nameEl = document.getElementById("userDisplay");
  const roleEl = document.getElementById("userRole");

  if (!nameEl || !roleEl) return;

  fetch("get_user_info.php", { credentials: "include" })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        nameEl.textContent = data.hoten;
        roleEl.textContent =
          data.role === "admin"
            ? "Quản trị viên hệ thống"
            : data.role === "giangvien"
            ? "Giảng viên hướng dẫn"
            : "Người dùng";
      } else {
        nameEl.textContent = "Khách";
        roleEl.textContent = "Chưa đăng nhập";
      }
    })
    .catch(err => {
      console.error("User info error:", err);
      nameEl.textContent = "Lỗi";
    });
});
document.addEventListener("DOMContentLoaded", () => {

  /* ================= LOGOUT ================= */
  const logoutBtn = document.getElementById("logoutBtn");

  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      if (!confirm("Bạn có chắc muốn đăng xuất?")) return;

      fetch("logout.php", {
        method: "POST",
        credentials: "include"
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          window.location.href = "index.html"; // hoặc login.php
        } else {
          alert("Đăng xuất thất bại");
        }
      })
      .catch(err => {
        console.error("Logout error:", err);
        alert("Lỗi kết nối khi đăng xuất");
      });
    });
  }

});

  // Gắn sự kiện cho menu chính
  document.querySelectorAll(".top-menu a").forEach(link => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      showSubmenu(this); // truyền chính nó vào
    });
  });

  // Mở mặc định trang Sinh viên → Phân công GVHD
  const svLink = document.querySelector(".top-menu a[data-main='sinhvien']") ||
    document.querySelector("#menu-sinhvien");
  if (svLink) svLink.click();

  // Gắn sự kiện import file (an toàn, chờ DOM load xong)
  const fileInput = document.getElementById('fileExcel');
  if (fileInput) {
    fileInput.addEventListener('change', importSmart);
  }


function closeSidebarMobile() {
  const sidebar = document.querySelector(".sidebar");
  const submenu = document.getElementById("submenu");

  if (window.innerWidth <= 768 && sidebar) {
    sidebar.classList.remove("show");
    if (submenu) submenu.innerHTML = "";
  }
}

window.addEventListener("resize", () => {
  if (window.innerWidth >= 769) {
    document.getElementById("mobileOverlay")?.classList.remove("show");
    document.getElementById("sidebar")?.classList.remove("show");
  }
});

