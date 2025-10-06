<?php

namespace App\Controllers;

use App\Models\ReservationModel;
use CodeIgniter\Controller;
use CodeIgniter\I18n\Time;

class ReservationController extends Controller
{
    protected $model;

    public function __construct()
    {
        $this->model = new ReservationModel();
        helper(['form', 'url']);
        date_default_timezone_set('Asia/Seoul');
    }

    /** 기본 페이지 */
    public function index()
    {
        return view('home', ['title' => '스터디룸 예약 시스템']);
    }

    /** 사용자 예약 페이지 */
    public function viewReserve()
    {
        return view('reservation_view', ['title' => '스터디룸 예약']);
    }

    /** 예약 조회 페이지 */
    public function viewFind()
    {
        return view('find_view', ['title' => '예약 조회']);
    }

    /** ✅ 관리자 페이지 */
    public function viewAdmin()
    {
        // 확정된 예약만 불러오기
        $builder = $this->model->builder();
        $builder->select('reservations.*, members.name AS member_name, members.phone AS member_phone, branches.name AS branch_name, rooms.name AS room_name')
            ->join('members', 'members.id = reservations.member_id', 'left')
            ->join('branches', 'branches.id = reservations.branch_id', 'left')
            ->join('rooms', 'rooms.id = reservations.room_id', 'left')
            ->where('reservations.status', 'confirmed')
            ->orderBy('reservations.date', 'DESC')
            ->orderBy('reservations.start_time', 'ASC');

        $data = [
            'title' => '관리자 페이지',
            'reservations' => $builder->get()->getResultArray(),
        ];

        return view('admin_view', $data);
    }

    /** 1️⃣ 예약 생성 */
    public function create()
    {
        $data = $this->request->getPost();

        if (empty($data['branch_id']) || empty($data['room_id']) || empty($data['name']) || empty($data['phone'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => '필수 입력값 누락']);
        }

        // 룸별 요금 설정
        $price = ($data['room_id'] == 1) ? 4000 : 8000;

        // 시간 형식 보정
        $start = $data['start_time'] . ':00';
        $end = $data['end_time'] . ':00';

        if (strtotime($start) >= strtotime($end)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => '종료 시간이 시작보다 커야 함']);
        }

        // 중복체크
        if ($this->model->hasOverlap($data['branch_id'], $data['room_id'], $data['date'], $start, $end)) {
            return $this->response->setStatusCode(409)->setJSON(['error' => '해당 시간대 예약 중복']);
        }

        $memberId = $this->model->upsertMember($data['name'], $data['phone']);

        $insert = [
            'branch_id' => $data['branch_id'],
            'room_id' => $data['room_id'],
            'member_id' => $memberId,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'date' => $data['date'],
            'start_time' => $start,
            'end_time' => $end,
            'price' => $price,
            'status' => 'pending'
        ];

        $id = $this->model->insert($insert, true);
        return $this->response->setJSON(['id' => $id, 'price' => $price, 'message' => '예약 생성 완료']);
    }

    /** 2️⃣ 인증번호 (Mock) */
    public function requestCode()
    {
        $id = $this->request->getPost('reservation_id');
        if (!$id) return $this->response->setStatusCode(400)->setJSON(['error' => 'reservation_id 필요']);

        $res = $this->model->find($id);
        if (!$res) return $this->response->setStatusCode(404)->setJSON(['error' => '예약을 찾을 수 없음']);

        $code = ReservationModel::generateCode();
        $expires = Time::now('Asia/Seoul')->addMinutes(3);

        $this->model->update($id, [
            'verify_code' => $code,
            'verify_expires' => $expires->toDateTimeString(),
        ]);

        return $this->response->setJSON([
            'mock_sms_code' => $code,
            'expires_at' => $expires->toDateTimeString(),
        ]);
    }

    /** 3️⃣ 인증 확정 */
    public function confirm()
    {
        $id = $this->request->getPost('reservation_id');
        $code = $this->request->getPost('code');

        $res = $this->model->find($id);
        if (!$res) return $this->response->setStatusCode(404)->setJSON(['error' => '예약 없음']);
        if ($res['verify_code'] != $code) {
            return $this->response->setStatusCode(400)->setJSON(['error' => '인증 실패']);
        }

        $this->model->update($id, [
            'status' => 'confirmed',
            'verify_code' => null,
            'verify_expires' => null
        ]);
        return $this->response->setJSON(['message' => '예약 확정 완료']);
    }

    /** 4️⃣ 예약 조회 */
    public function find()
    {
        $branchId = $this->request->getGet('branch_id');
        $phone = $this->request->getGet('phone');

        if (!$branchId || !$phone) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'branch_id, phone 필요']);
        }

        $builder = $this->model->builder();
        $builder->select('reservations.*, branches.name AS branch_name, rooms.name AS room_name')
                ->join('branches', 'branches.id = reservations.branch_id')
                ->join('rooms', 'rooms.id = reservations.room_id')
                ->where('reservations.branch_id', $branchId)
                ->where('reservations.phone', $phone)
                ->where('reservations.status !=', 'cancelled')
                ->orderBy('reservations.date', 'ASC')
                ->orderBy('reservations.start_time', 'ASC');

        return $this->response->setJSON($builder->get()->getResultArray());
    }

    /** 5️⃣ 관리자 - 가격 수정 (GET URL 직접 호출) */
    public function adminUpdatePriceGet($reservationId = null, $newPrice = null)
    {
        $id = (int) $reservationId;
        $price = (int) $newPrice;

        if (!$id || $price < 0) {
            return $this->response->setStatusCode(400)->setBody('잘못된 요청');
        }

        $this->model->update($id, ['price' => $price]);
        return $this->response->setBody("✅ 예약 #$id 가격이 {$price}원으로 수정되었습니다.");
    }

    /** 6️⃣ 관리자 - 회원 수정 (GET URL 직접 호출) */
    public function adminUpdateMemberGet($memberId = null, $newName = null, $newPhone = null)
    {
        $id = (int) $memberId;

        if (!$id || !$newName || !$newPhone) {
            return $this->response->setStatusCode(400)->setBody('입력값 부족');
        }

        $this->model->adminUpdateMember($id, ['name' => $newName, 'phone' => $newPhone]);
        return $this->response->setBody("✅ 회원 #$id 수정 완료 ({$newName}, {$newPhone})");
    }

    /** 7️⃣ 관리자 - 예약 삭제 */
    public function adminDeleteReservation($reservationId = null)
    {
        $id = (int) $reservationId;
        if (!$id) {
            return $this->response->setStatusCode(400)->setBody('예약 ID 필요');
        }

        $this->model->delete($id);
        return $this->response->setBody("🗑 예약 #{$id} 삭제 완료");
    }
}
