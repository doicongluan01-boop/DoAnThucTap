function exportWord() {
    // Hiện thông báo loading
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Đang xuất Word...',
            html: 'Đang định dạng theo mẫu <b>STU Standard</b>',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
    }

    // Lấy dữ liệu từ trang hiện tại
    fetch(window.location.href)
        .then(r => r.text())
        .then(html => {
            // 1. Phân tích HTML để lấy bảng dữ liệu sạch
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const tableElement = doc.getElementById('mainTable');

            if (!tableElement) {
                if (typeof Swal !== 'undefined') Swal.fire('Lỗi', 'Không tìm thấy bảng dữ liệu!', 'error');
                else alert('Lỗi: Không có dữ liệu');
                return;
            }

            // Clone bảng để xử lý (Xóa cột tác vụ cuối cùng)
            const tableClone = tableElement.cloneNode(true);
            const rows = tableClone.querySelectorAll('tr');
            rows.forEach(row => {
                if (row.cells.length > 0) row.deleteCell(-1); // Xóa cột cuối
            });

            // 2. Thiết lập Header "Chuẩn STU" (Dùng bảng ẩn viền để chia cột)
            const headerContent = `
                <table style="width: 100%; border: none; margin-bottom: 20px;">
                    <tr style="border: none;">
                        <td style="width: 40%; text-align: center; vertical-align: top; border: none;">
                            <span style="font-size: 11pt;">TRƯỜNG ĐẠI HỌC CÔNG NGHỆ SÀI GÒN</span><br>
                            <b style="font-size: 12pt;">KHOA CÔNG NGHỆ THÔNG TIN</b>
                            <hr style="width: 30%; border-top: 1px solid black;">
                        </td>
                        <td style="width: 60%; text-align: center; vertical-align: top; border: none;">
                            <span style="font-size: 11pt;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</span><br>
                            <b style="font-size: 12pt;">Độc lập - Tự do - Hạnh phúc</b>
                            <hr style="width: 30%; border-top: 1px solid black;">
                        </td>
                    </tr>
                </table>
                <div style="text-align: center; margin-bottom: 20px;">
                    <h2 style="font-size: 16pt; color: #C00000; margin: 0;">DANH SÁCH SINH VIÊN THỰC HIỆN KHÓA LUẬN TỐT NGHIỆP</h2>
                    <p style="font-size: 12pt; font-style: italic; margin: 5px 0;">Năm học: 2024 - 2025</p>
                </div>
            `;

            // 3. Cấu trúc HTML hoàn chỉnh cho file Word
            // Lưu ý: @page section để chỉnh khổ giấy ngang (Landscape) vì bảng nhiều cột
            const wordTemplate = `
                <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
                <head>
                    <meta charset="utf-8">
                    <title>Danh sách sinh viên</title>
                    <style>
                        /* Cấu hình trang in ngang */
                        @page Section1 { size: 841.9pt 595.3pt; margin: 1.0cm 1.0cm 1.0cm 1.0cm; mso-page-orientation: landscape; }
                        div.Section1 { page: Section1; }
                        
                        body { font-family: "Times New Roman", serif; font-size: 12pt; }
                        
                        /* Style cho bảng dữ liệu */
                        table { border-collapse: collapse; width: 100%; }
                        table, th, td { border: 1px solid black; }
                        th, td { padding: 5px; vertical-align: middle; }
                        
                        /* Header bảng màu xanh STU */
                        th { background-color: #0050aa; color: white; text-align: center; font-weight: bold; }
                        
                        /* Căn giữa các cột ngắn */
                        td:nth-child(1), td:nth-child(2), td:nth-child(4), td:nth-child(6) { text-align: center; }
                    </style>
                </head>
                <body>
                    <div class="Section1">
                        ${headerContent}
                        ${tableClone.outerHTML}
                        <br>
                        <table style="width: 100%; border: none;">
                            <tr style="border: none;">
                                <td style="width: 50%; border: none;"></td>
                                <td style="width: 50%; text-align: center; border: none; font-style: italic;">
                                    Tp. Hồ Chí Minh, ngày ${new Date().getDate()} tháng ${new Date().getMonth() + 1} năm ${new Date().getFullYear()}
                                </td>
                            </tr>
                        </table>
                    </div>
                </body>
                </html>
            `;

            // 4. Tạo file và tải xuống
            const blob = new Blob(['\ufeff', wordTemplate], {
                type: 'application/msword'
            });

            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `DS_LVTN_STU_${new Date().toISOString().slice(0, 10)}.doc`; // Đuôi .doc để mở tốt nhất
            document.body.appendChild(link);
            link.click();
            
            // Dọn dẹp
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            
            if (typeof Swal !== 'undefined') Swal.close();
        })
        .catch(err => {
            console.error(err);
            if (typeof Swal !== 'undefined') Swal.fire('Lỗi', 'Không thể xuất Word', 'error');
            else alert('Lỗi xuất Word');
        });
}