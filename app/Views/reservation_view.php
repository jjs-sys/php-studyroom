<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<h3>스터디룸 예약</h3>
<form id="reservationForm">
    <label>지점</label>
    <select name="branch_id" required>
        <option value="1">강남점</option>
        <option value="2">홍대점</option>
    </select><br><br>

    <label>룸</label>
    <select name="room_id" id="roomSelect" required>
        <option value="1">A룸 (₩4,000)</option>
        <option value="2">B룸 (₩8,000)</option>
    </select><br><br>

    <label>이름</label>
    <input type="text" name="name" required><br><br>

    <label>휴대폰 번호</label>
    <input type="text" name="phone" required placeholder="01012345678"><br><br>

    <label>예약 날짜</label>
    <input type="date" name="date" required><br><br>

    <label>시작 시간</label>
    <input type="time" name="start_time" step="1800" required><br><br>

    <label>종료 시간</label>
    <input type="time" name="end_time" step="1800" required><br><br>

    <p>요금: ₩<span id="priceDisplay">4000</span></p>
    <input type="hidden" name="price" id="priceField" value="4000">

    <button type="button" onclick="createReservation()">예약 요청</button>
</form>

<div id="resultBox"></div>

<script>
const priceMap = {1: 4000, 2: 8000};
document.getElementById('roomSelect').addEventListener('change', e => {
    const price = priceMap[e.target.value];
    document.getElementById('priceDisplay').innerText = price;
    document.getElementById('priceField').value = price;
});

async function createReservation() {
    const form = document.getElementById('reservationForm');
    const formData = new FormData(form);
    try {
        const res = await axios.post('/api/reservations/create', formData);
        document.getElementById('resultBox').innerHTML =
            `<p>✅ 예약 완료 (요금: ₩${res.data.price})</p>`;
    } catch (err) {
        alert('오류: ' + (err.response?.data?.error || err.message));
    }
}
</script>

<?= $this->endSection() ?>
