<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý LVTN - STU</title>

  <!-- CSS -->
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="dashboard-body">

<!-- ========== MOBILE OVERLAY ========== -->
<div id="mobileOverlay" class="mobile-overlay"></div>

<!-- ========== MOBILE TOP BAR ========== -->
<header class="mobile-topbar">
  <button id="menuToggle" class="menu-toggle">
    <i class="fas fa-bars"></i>
  </button>

  <nav class="top-menu mobile-top-menu">
    <a href="#" data-main="trangchu" class="active">Trang chủ</a>
    <a href="#" data-main="sinhvien">Sinh viên</a>
    <a href="#" data-main="giangvien">Giảng viên</a>
    <a href="#" data-main="phanbien">Phản biện</a>
    <a href="#" data-main="baocao">Báo cáo</a>
    <a href="#" data-main="hoidong">Hội đồng</a>
  </nav>
</header>

<!-- ========== DASHBOARD CONTAINER ========== -->
<div class="dashboard-container">

  <!-- ===== SIDEBAR ===== -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header text-center">
      <img src="Logo_STU.png" alt="Logo STU" class="logo-sidebar">
    </div>

    <div class="user-info text-center px-3">
      <h6 class="text-light mb-1">
        Xin chào, <span id="userDisplay" class="text-warning">Đang tải...</span>
      </h6>
      <small id="userRole" class="text-light opacity-75 d-block mb-2">
        Chào mừng bạn
      </small>
      <button id="logoutBtn" class="btn btn-danger btn-sm w-100">
        Đăng xuất
      </button>
    </div>

    <!-- SUBMENU -->
    <div id="submenu" class="submenu px-2 mt-3"></div>
  </aside>

  <!-- ===== MAIN ===== -->
  <main class="main-area">

    <!-- ===== TOP MENU DESKTOP ===== -->
    <nav class="top-menu desktop-top-menu">
      <a href="#" data-main="trangchu" class="active">Trang chủ</a>
      <a href="#" data-main="sinhvien">Sinh viên</a>
      <a href="#" data-main="giangvien">Giảng viên</a>
      <a href="#" data-main="baocao">Báo cáo</a>
      <a href="#" data-main="hoidong">Hội đồng</a>
    </nav>

    <!-- ===== CONTENT ===== -->
    <div id="content-area" class="content-area">
      <div class="text-center py-5 text-light opacity-75">
        Chọn một chức năng để bắt đầu
      </div>
    </div>

  </main>
</div>



<!-- ========== SCRIPT ========== -->
<script src="script.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("sidebar");
  const toggle  = document.getElementById("menuToggle");
  const overlay = document.getElementById("mobileOverlay");

  if (toggle && sidebar && overlay) {
    toggle.onclick = () => {
      sidebar.classList.toggle("show");
      overlay.classList.toggle("show");
    };

    overlay.onclick = () => {
      sidebar.classList.remove("show");
      overlay.classList.remove("show");
    };
  }

  /* Đồng bộ bottom nav → top menu */
  document.querySelectorAll(".bottom-nav a").forEach(link => {
    link.onclick = e => {
      e.preventDefault();
      document.querySelectorAll(".bottom-nav a").forEach(a => a.classList.remove("active"));
      link.classList.add("active");

      const key = link.dataset.main;
      document.querySelectorAll(`.top-menu a[data-main="${key}"]`)
        .forEach(a => a.click());
    };
  });
});
</script>

</body>
</html>
