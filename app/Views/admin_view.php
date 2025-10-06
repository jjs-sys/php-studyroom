<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<style>
    .admin-container {
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    h3 { text-align: center; margin-bottom: 24px; }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 15px;
    }
    th, td {
        border-bottom: 1px solid #ddd;
        padding: 8px;
        text-align: center;
    }
    th { background: #f3f3f3; font-weight: bold; }
    input {
        width: 100%;
        padding: 4px 6px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    button {
        padding: 4px 8px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-update { background: #007bff; color: white; }
    .btn-delete { background: #dc3545; color: white; }
    .btn-update:hover { background: #0056b3; }
    .btn-delete:hover { background: #a71d2a; }
</style>

<div class="admin-container">
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
            <th>가격</th>
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
            <td><?= esc($r['branch_id']) ?>번</td>
            <td><input type="text" value="<?= esc($r['member_name']) ?>" id="name-<?= esc($r['member_id']) ?>"></td>
            <td><input type="text" value="<?= esc($r['member_phone']) ?>" id="phone-<?= esc($r['member_id']) ?>"></td>
            <td><input type="number" value="<?= esc($r['price']) ?>" id="price-<?= esc($r['id']) ?>" min="0"></td>
            <td><?= esc($r['date']) ?></td>
            <td><?= esc($r['start_time']) ?>~<?= esc($r['end_time']) ?></td>
            <td><button class="btn-update" onclick="updateAll(<?= esc($r['id']) ?>, <?= esc($r['member_id']) ?>)">수정</button></td>
            <td><button class="btn-delete" onclick="deleteReservation(<?= esc($r['id']) ?>)">삭제</button></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
async function updateAll(reservationId, memberId) {
    const name = document.getElementById(`name-${memberId}`).value;
    const phone = document.getElementById(`phone-${memberId}`).value;
    const price = document.getElementById(`price-${reservationId}`).value;

    try {
        await axios.get(`/admin/update-member/${memberId}/${name}/${phone}`);
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

<?= $this->endSection() ?>
