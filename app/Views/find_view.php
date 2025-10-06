<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<form id="findForm">
    <div class="mb-3">
        <label class="form-label">지점</label>
        <select class="form-select" name="branch_id" required>
            <option value="1">강남점</option>
            <option value="2">홍대점</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">휴대폰 번호</label>
        <input type="text" class="form-control" name="phone" required>
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="includePast" name="include_past">
        <label for="includePast" class="form-check-label">과거 예약 포함</label>
    </div>
    <button type="button" class="btn btn-primary w-100" onclick="findReservations()">예약 조회</button>
</form>

<div id="resultBox" class="result-box"></div>

<script>
async function findReservations() {
    const form = document.getElementById('findForm');
    const params = new URLSearchParams(new FormData(form)).toString();
    try {
        const res = await axios.get('/api/reservations/find?' + params);
        if (res.data.length === 0) {
            document.getElementById('resultBox').innerHTML = '<div class="alert alert-secondary">예약 내역이 없습니다.</div>';
            return;
        }
        let html = '<table class="table table-bordered mt-3"><thead><tr><th>지점</th><th>룸</th><th>날짜</th><th>시간</th><th>상태</th></tr></thead><tbody>';
        res.data.forEach(r => {
            html += `<tr>
                <td>${r.branch_name}</td>
                <td>${r.room_name}</td>
                <td>${r.date}</td>
                <td>${r.start_time} ~ ${r.end_time}</td>
                <td>${r.status}</td>
            </tr>`;
        });
        html += '</tbody></table>';
        document.getElementById('resultBox').innerHTML = html;
    } catch (err) {
        alert('조회 실패: ' + (err.response?.data?.message ?? err.message));
    }
}
</script>

<?= $this->endSection() ?>
