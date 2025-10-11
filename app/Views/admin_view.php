<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>관리자 페이지 | CodeIgniter 4</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="/favicon.ico">

    <!-- ✅ 기본 CodeIgniter 스타일 (수정 금지) -->
    <style {csp-style-nonce}>
        html, body {
            color: #212529;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            font-size: 16px;
            margin: 0;
            padding: 0;
            background: #f9fafc;
        }
        header {
            background-color: #f7f8f9;
            padding: .4rem 2rem;
        }
        header ul {
            border-bottom: 1px solid #f2f2f2;
            list-style-type: none;
            margin: 0;
            padding: 0;
            text-align: right;
        }
        header li {
            display: inline-block;
        }
        header li a {
            border-radius: 5px;
            color: rgba(0,0,0,0.5);
            text-decoration: none;
            padding: .4rem .65rem;
        }
        header li a:hover {
            background-color: rgba(221,72,20,0.2);
            color: rgba(221,72,20,1);
        }
        footer {
            background-color: rgba(221,72,20,0.8);
            text-align: center;
            color: white;
            padding: 20px;
        }
    </style>

    <!-- ✅ 관리자 페이지 전용 스타일 (독립 범위) -->
    <style>
        .admin-page {
            width: 100%;
            min-height: 100vh;
            background: #f9fafc;
            padding: 60px 0 80px;
            box-sizing: border-box;
        }

        .admin-page h3 {
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 40px;
        }

        .admin-page table {
            width: 90%;
            max-width: 1400px;
            margin: 0 auto;
            border-collapse: collapse;
            font-size: 16px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .admin-page th {
            background: #f0f3f9;
            padding: 14px 10px;
            border-bottom: 2px solid #ddd;
            font-weight: 600;
            color: #333;
        }

        .admin-page td {
            border-bottom: 1px solid #e5e5e5;
            text-align: center;
            padding: 12px 10px;
        }

        .admin-page input {
            width: 95%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            text-align: center;
            background-color: #fafafa;
            transition: 0.2s;
        }
        .admin-page input:focus {
            border-color: #007bff;
            background: #fff;
            outline: none;
        }

        .admin-page button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            transition: 0.2s;
        }
        .admin-page .btn-update {
            background: #007bff;
            color: white;
        }
        .admin-page .btn-update:hover {
            background: #0056b3;
        }
        .admin-page .btn-delete {
            background: #dc3545;
            color: white;
        }
        .admin-page .btn-delete:hover {
            background: #a71d2a;
        }

        .admin-page tbody tr:hover {
            background-color: #f8fbff;
        }

        /* 컬럼 폭 고정 */
        .admin-page th:nth-child(1), .admin-page td:nth-child(1) { width: 5%; }
        .admin-page th:nth-child(2), .admin-page td:nth-child(2) { width: 10%; }
        .admin-page th:nth-child(3), .admin-page td:nth-child(3) { width: 15%; }
        .admin-page th:nth-child(4), .admin-page td:nth-child(4) { width: 20%; }
        .admin-page th:nth-child(5), .admin-page td:nth-child(5) { width: 10%; }
        .admin-page th:nth-child(6), .admin-page td:nth-child(6) { width: 15%; }
        .admin-page th:nth-child(7), .admin-page td:nth-child(7) { width: 15%; }
        .admin-page th:nth-child(8), .admin-page td:nth-child(8) { width: 5%; }
        .admin-page th:nth-child(9), .admin-page td:nth-child(9) { width: 5%; }
    </style>
</head>

<body>

<header>
    <ul>
        <li><a href="/">🏠 홈</a></li>
        <li><a href="/view/admin" style="color:#dd4814;font-weight:bold;">관리자</a></li>
    </ul>
</header>

<!-- ✅ 관리자 페이지 영역 -->
<div class="admin-page">

    <h3>📋 예약 관리 페이지</h3>

    <?php if (empty($reservations)): ?>
        <p style="text-align:center;">확정된 예약이 없습니다.</p>
    <?php else: ?>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>지점</th>
            <th>이름</th>
            <th>전화번호</th>
            <th>가격(₩)</th>
            <th>날짜</th>
            <th>시간</th>
            <th>수정</th>
            <th>삭제</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($reservations as $r): ?>
        <tr id="row-<?= esc($r['id']) ?>">
            <td><?= esc($r['id']) ?></td>
            <td><?= esc($r['branch_name'] ?? $r['branch_id'].'번') ?></td>
            <td><input type="text" id="name-<?= esc($r['id']) ?>" value="<?= esc($r['member_name'] ?? $r['name']) ?>"></td>
            <td><input type="text" id="phone-<?= esc($r['id']) ?>" value="<?= esc($r['member_phone'] ?? $r['phone']) ?>"></td>
            <td><input type="number" id="price-<?= esc($r['id']) ?>" value="<?= esc($r['price']) ?>" min="0"></td>
            <td><?= esc($r['date']) ?></td>
            <td><?= esc($r['start_time']) ?>~<?= esc($r['end_time']) ?></td>
            <td><button class="btn-update" onclick="updateReservation(<?= esc($r['id']) ?>, <?= esc($r['member_id']) ?>)">수정</button></td>
            <td><button class="btn-delete" onclick="deleteReservation(<?= esc($r['id']) ?>)">삭제</button></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<footer>
    <div>관리자 페이지 | © <?= date('Y') ?> StudyRoom System</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
async function updateReservation(reservationId, memberId) {
    const name = document.getElementById(`name-${reservationId}`).value.trim();
    const phone = document.getElementById(`phone-${reservationId}`).value.trim();
    const price = document.getElementById(`price-${reservationId}`).value.trim();
    if (!name || !phone) return alert('이름과 전화번호를 입력하세요.');
    try {
        await axios.get(`/admin/update-member/${memberId}/${encodeURIComponent(name)}/${encodeURIComponent(phone)}`);
        await axios.get(`/admin/update-price/${reservationId}/${price}`);
        alert(`✅ 예약 #${reservationId} 수정 완료`);
    } catch (err) {
        alert('❌ 수정 실패: ' + (err.response?.data ?? err.message));
    }
}
async function deleteReservation(id) {
    if (!confirm('정말 삭제하시겠습니까?')) return;
    try {
        await axios.get(`/admin/delete/${id}`);
        document.getElementById(`row-${id}`).remove();
        alert(`🗑 예약 #${id} 삭제 완료`);
    } catch (err) {
        alert('삭제 실패: ' + (err.response?.data ?? err.message));
    }
}
</script>

</body>
</html>
