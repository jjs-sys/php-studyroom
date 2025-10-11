<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<h3>📖 스터디룸 예약</h3>

<form id="reservationForm">
    <label>이름</label>
    <input type="text" name="name" required><br><br>

    <label>휴대폰 번호</label>
    <input type="text" name="phone" id="phone" required placeholder="01012345678">
    <button type="button" onclick="requestCode()">인증번호 요청</button><br><br>

    <div id="verifyBox" style="display:none;">
        <label>인증번호 입력</label>
        <input type="text" id="codeInput" placeholder="6자리 숫자">
        <button type="button" onclick="verifyCode()">인증 확인</button>
    </div>

    <hr>

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

    <label>예약 날짜</label>
    <input type="date" name="date" required><br><br>

    <label>시작 시간</label>
    <input type="time" name="start_time" step="1800" required><br><br>

    <label>종료 시간</label>
    <input type="time" name="end_time" step="1800" required><br><br>

    <p>요금: ₩<span id="priceDisplay">4000</span></p>
    <input type="hidden" name="price" id="priceField" value="4000">

    <button type="button" onclick="createReservation()">예약하기</button>
</form>

<hr>
<div id="resultBox"></div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
// ✅ 세션 쿠키 유지 설정 (가장 중요)
axios.defaults.withCredentials = true;

const BASE_URL = 'http://localhost:8080';
const priceMap = {1: 4000, 2: 8000};

// 룸 가격 표시
document.getElementById('roomSelect').addEventListener('change', e => {
    const price = priceMap[e.target.value];
    document.getElementById('priceDisplay').innerText = price;
    document.getElementById('priceField').value = price;
});

// ✅ 인증번호 요청
async function requestCode() {
    const name = document.querySelector('input[name="name"]').value.trim();
    const phone = document.querySelector('input[name="phone"]').value.trim();
    if (!name || !phone) return alert('이름과 휴대폰 번호를 입력하세요.');

    try {
        const formData = new FormData();
        formData.append('name', name);
        formData.append('phone', phone);

        const res = await axios.post(`${BASE_URL}/api/reservations/request-code`, formData);
        alert(`📩 인증번호 (모의): ${res.data.mock_sms_code}`);
        document.getElementById('verifyBox').style.display = 'block';
    } catch (err) {
        alert('❌ 인증번호 요청 실패: ' + (err.response?.data?.error || err.message));
    }
}

// ✅ 인증번호 확인
async function verifyCode() {
    const phone = document.getElementById('phone').value.trim();
    const code  = document.getElementById('codeInput').value.trim();
    if (!code) return alert('인증번호를 입력하세요.');

    try {
        const formData = new FormData();
        formData.append('phone', phone);
        formData.append('code', code);

        const res = await axios.post(`${BASE_URL}/api/reservations/verify-code`, formData);
        alert(res.data.message);
        document.getElementById('verifyBox').innerHTML = '<p>✅ 인증 완료!</p>';
    } catch (err) {
        alert('❌ 인증 실패: ' + (err.response?.data?.error || err.message));
    }
}

// ✅ 예약 생성
async function createReservation() {
    const form = document.getElementById('reservationForm');
    const formData = new FormData(form);

    try {
        const res = await axios.post(`${BASE_URL}/api/reservations/create`, formData);
        document.getElementById('resultBox').innerHTML = `<p>${res.data.message}</p>`;
        form.reset();
    } catch (err) {
        alert('❌ 예약 실패: ' + (err.response?.data?.error || err.message));
    }
}
</script>

<?= $this->endSection() ?>
