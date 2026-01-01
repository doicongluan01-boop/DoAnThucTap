function exportExcel() {
    // 1. Tải thư viện xlsx-js-style (Phiên bản hỗ trợ Style màu sắc, đóng khung)
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js';
    
    // Hiện thông báo đang xử lý
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Đang xuất báo cáo...',
            html: 'Đang định dạng theo mẫu <b>STU Standard</b>',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
    }

    script.onload = function () {
        // Lấy lại HTML của trang hiện tại để đảm bảo dữ liệu mới nhất
        fetch(window.location.href)
            .then(res => res.text())
            .then(html => {
                // 2. Lấy bảng dữ liệu từ HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const tableElement = doc.getElementById('mainTable');

                if (!tableElement) {
                    alert('Không tìm thấy dữ liệu!');
                    return;
                }

                // Clone bảng để xóa cột "Tác vụ" (cột cuối) cho đẹp
                const tableClone = tableElement.cloneNode(true);
                const rows = tableClone.querySelectorAll('tr');
                rows.forEach(row => {
                    if (row.cells.length > 0) row.deleteCell(-1); 
                });

                // 3. TẠO WORKBOOK & WORKSHEET
                const wb = XLSX.utils.book_new();
                
                // --- QUAN TRỌNG: origin: "A8" nghĩa là đẩy bảng dữ liệu xuống dòng thứ 8 ---
                // Để dành dòng 1->7 viết Tiêu đề trường
                const ws = XLSX.utils.table_to_sheet(tableClone, { raw: true, origin: "A8" });

                // 4. CHÈN HEADER "CHUẨN STU" VÀO 7 DÒNG ĐẦU
                const headerData = [
                    ["TRƯỜNG ĐẠI HỌC CÔNG NGHỆ SÀI GÒN", "", "", "", "CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM"], // Dòng 1
                    ["KHOA CÔNG NGHỆ THÔNG TIN", "", "", "", "Độc lập - Tự do - Hạnh phúc"],               // Dòng 2
                    [""],                                                                                   // Dòng 3 (Trống)
                    ["DANH SÁCH SINH VIÊN THỰC HIỆN KHÓA LUẬN TỐT NGHIỆP"],                                 // Dòng 4 (Tiêu đề đỏ)
                    ["Năm học: 2024 - 2025"],                                                               // Dòng 5
                    [""],                                                                                   // Dòng 6
                    [""]                                                                                    // Dòng 7
                ];
                XLSX.utils.sheet_add_aoa(ws, headerData, { origin: "A1" });

                // 5. TRỘN Ô (MERGE CELLS) CHO HEADER CÂN ĐỐI
                // (Cấu hình này dựa trên việc bảng của bạn có khoảng 9 cột từ A->I)
                if(!ws['!merges']) ws['!merges'] = [];
                ws['!merges'].push(
                    { s: { r: 0, c: 0 }, e: { r: 0, c: 3 } }, // Tên trường (A1:D1)
                    { s: { r: 1, c: 0 }, e: { r: 1, c: 3 } }, // Tên khoa (A2:D2)
                    { s: { r: 0, c: 4 }, e: { r: 0, c: 8 } }, // Quốc hiệu (E1:I1)
                    { s: { r: 1, c: 4 }, e: { r: 1, c: 8 } }, // Tiêu ngữ (E2:I2)
                    { s: { r: 3, c: 0 }, e: { r: 3, c: 8 } }, // Tiêu đề lớn (A4:I4)
                    { s: { r: 4, c: 0 }, e: { r: 4, c: 8 } }  // Năm học (A5:I5)
                );

                // 6. ĐỊNH DẠNG STYLE (FONTS, BORDERS)
                // Auto-fit độ rộng cột
                ws['!cols'] = [
                    { wch: 5 },  { wch: 15 }, { wch: 25 }, { wch: 10 }, 
                    { wch: 25 }, { wch: 15 }, { wch: 30 }, { wch: 25 }, { wch: 20 }
                ];

                const range = XLSX.utils.decode_range(ws['!ref']);
                
                for (let R = range.s.r; R <= range.e.r; ++R) {
                    for (let C = range.s.c; C <= range.e.c; ++C) {
                        const cellRef = XLSX.utils.encode_cell({ r: R, c: C });
                        if (!ws[cellRef]) continue;

                        // Style Mặc định: Font Times New Roman
                        let style = {
                            font: { name: "Times New Roman", sz: 12 },
                            alignment: { vertical: "center", wrapText: true }
                        };

                        // --- HEADER TRƯỜNG (Dòng 1-2) ---
                        if (R < 2) {
                            style.font.bold = true;
                            style.alignment.horizontal = "center";
                            if(C >= 4) style.font.sz = 11; // Bên Quốc hiệu chữ nhỏ hơn xíu
                        }
                        
                        // --- TIÊU ĐỀ LỚN (Dòng 4) ---
                        else if (R === 3) {
                            style.font.sz = 16;
                            style.font.bold = true;
                            style.font.color = { rgb: "C00000" }; // Màu Đỏ
                            style.alignment.horizontal = "center";
                        }
                        
                        // --- NĂM HỌC (Dòng 5) ---
                        else if (R === 4) {
                            style.font.italic = true;
                            style.alignment.horizontal = "center";
                        }

                        // --- TIÊU ĐỀ BẢNG (Dòng 8 - Index 7) ---
                        else if (R === 7) {
                            style.font.bold = true;
                            style.font.color = { rgb: "FFFFFF" };
                            style.fill = { fgColor: { rgb: "0050aa" } }; // Xanh STU
                            style.alignment.horizontal = "center";
                            style.border = {
                                top: { style: "thin" }, bottom: { style: "thin" },
                                left: { style: "thin" }, right: { style: "thin" }
                            };
                        }

                        // --- DỮ LIỆU BẢNG (Dòng 9 trở đi) ---
                        else if (R > 7) {
                            // Kẻ khung toàn bộ
                            style.border = {
                                top: { style: "thin" }, bottom: { style: "thin" },
                                left: { style: "thin" }, right: { style: "thin" }
                            };
                            // Căn giữa STT, MSSV...
                            if ([0, 1, 3, 5].includes(C)) style.alignment.horizontal = "center";
                            
                            // Tô màu cột Nhóm (Cột F - Index 5)
                            if (C === 5) {
                                const val = ws[cellRef].v ? ws[cellRef].v.toString() : "";
                                if (val && val !== "Chưa có") {
                                    style.font.bold = true;
                                    style.font.color = { rgb: "006400" }; // Xanh lá
                                }
                            }
                        }
                        ws[cellRef].s = style;
                    }
                }

                // Xuất file
                XLSX.utils.book_append_sheet(wb, ws, "DanhSachLVTN");
                XLSX.writeFile(wb, `DanhSach_LVTN_STU_${new Date().toISOString().slice(0,10)}.xlsx`);

                if (typeof Swal !== 'undefined') Swal.close();
            })
            .catch(err => {
                console.error(err);
                if (typeof Swal !== 'undefined') Swal.fire('Lỗi', 'Lỗi xuất file (Xem console)', 'error');
                else alert('Lỗi xuất file');
            });
    };
    document.head.appendChild(script);
}