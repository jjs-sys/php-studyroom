<?php

namespace App\Controllers;

use App\Models\ReservationModel;
use CodeIgniter\Controller;
use CodeIgniter\I18n\Time;

class ReservationController extends Controller
{
    protected $model;
    protected $session;

    public function __construct()
    {
        $this->model = new ReservationModel();
        helper(['form', 'url']);
        $this->session = session();
        date_default_timezone_set('Asia/Seoul');
    }

    /** 홈 */
    public function index()
    {
        return view('home', ['title' => '스터디룸 예약 시스템']);
    }

    /** 예약 페이지 */
    public function viewReserve()
    {
        return view('reservation_view', ['title' => '스터디룸 예약']);
    }

    /** 관리자 페이지 */
    public function viewAdmin()
    {
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

    /** 인증번호 요청 */
    public function requestCode()
    {
        $name = $this->request->getPost('name');
        $phone = $this->request->getPost('phone');

        if (!$name || !$phone) {
            return $this->response->setStatusCode(400)->setJSON(['error' => '이름과 휴대폰 번호를 입력해주세요.']);
        }

        $code = ReservationModel::generateCode();
        $expires = Time::now('Asia/Seoul')->addMinutes(3);

        $this->session->set([
            'verify_name' => $name,
            'verify_phone' => $phone,
            'verify_code' => $code,
            'verify_expires' => $expires->toDateTimeString(),
            'verified' => false,
        ]);

        return $this->response->setJSON([
            'mock_sms_code' => $code,
            'expires_at' => $expires->toDateTimeString(),
            'message' => '📩 인증번호가 발송되었습니다 (모의).'
        ]);
    }

    /** 인증번호 확인 */
    public function verifyCode()
    {
        $phone = $this->request->getPost('phone');
        $code  = $this->request->getPost('code');

        $savedPhone = $this->session->get('verify_phone');
        $savedCode  = $this->session->get('verify_code');
        $expires    = $this->session->get('verify_expires');

        if (!$savedPhone || !$savedCode) {
            return $this->response->setStatusCode(400)->setJSON(['error' => '인증번호 요청 이력이 없습니다.']);
        }

        if ($phone !== $savedPhone) {
            return $this->response->setStatusCode(400)->setJSON(['error' => '휴대폰 번호가 일치하지 않습니다.']);
        }

        if (Time::now('Asia/Seoul')->isAfter(Time::parse($expires))) {
            return $this->response->setStatusCode(400)->setJSON(['error' => '인증번호가 만료되었습니다.']);
        }

        if ($code !== $savedCode) {
            return $this->response->setStatusCode(400)->setJSON(['error' => '잘못된 인증번호입니다.']);
        }

        $this->session->set('verified', true);
        return $this->response->setJSON(['message' => '✅ 인증 성공! 이제 예약이 가능합니다.']);
    }

    /** 예약 생성 */
    public function create()
    {
        $data = $this->request->getPost();
        $isVerified = $this->session->get('verified');
        $verifiedPhone = $this->session->get('verify_phone');

        if (!$isVerified || $verifiedPhone !== ($data['phone'] ?? '')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => '휴대폰 인증 후 예약 가능합니다.']);
        }

        if (empty($data['branch_id']) || empty($data['room_id']) || empty($data['name']) || empty($data['phone'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => '필수 입력값 누락']);
        }

        $price = ($data['room_id'] == 1) ? 4000 : 8000;
        $start = strlen($data['start_time']) === 5 ? $data['start_time'] . ':00' : $data['start_time'];
        $end   = strlen($data['end_time']) === 5 ? $data['end_time'] . ':00' : $data['end_time'];

        if (strtotime($start) >= strtotime($end)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => '종료 시간이 시작보다 커야 합니다.']);
        }

        if ($this->model->hasOverlap($data['branch_id'], $data['room_id'], $data['date'], $start, $end)) {
            return $this->response->setStatusCode(409)->setJSON(['error' => '해당 시간대에 이미 예약이 존재합니다.']);
        }

        $memberId = $this->model->upsertMember($data['name'], $data['phone']);

        $insert = [
            'branch_id'  => $data['branch_id'],
            'room_id'    => $data['room_id'],
            'member_id'  => $memberId,
            'name'       => $data['name'],
            'phone'      => $data['phone'],
            'date'       => $data['date'],
            'start_time' => $start,
            'end_time'   => $end,
            'price'      => $price,
            'status'     => 'confirmed',
        ];

        $id = $this->model->insert($insert, true);
        $this->session->remove(['verify_name', 'verify_phone', 'verify_code', 'verify_expires', 'verified']);

        return $this->response->setJSON(['message' => '🎉 예약이 확정되었습니다!', 'id' => $id]);
    }

    /**  관리자 - 회원 + 예약 정보 수정 (이름, 전화번호, 가격 포함) */
    public function adminUpdateMemberGet($memberId = null, $newName = null, $newPhone = null)
    {
        $id = (int) $memberId;

        if (!$id || !$newName || !$newPhone) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => '입력값 부족'
            ]);
        }

        $newName = urldecode($newName);
        $newPhone = urldecode($newPhone);

        // members 테이블 수정
        $memberUpdated = $this->model->adminUpdateMember($id, [
            'name'  => $newName,
            'phone' => $newPhone
        ]);

        // reservations 테이블의 동일 회원 데이터도 업데이트
        $this->model->db->table('reservations')
            ->where('member_id', $id)
            ->update([
                'name'       => $newName,
                'phone'      => $newPhone,
                'updated_at' => Time::now('Asia/Seoul')
            ]);

        if ($memberUpdated) {
            return $this->response->setJSON([
                'success' => true,
                'message' => "✅ 회원 #{$id} 및 예약정보 수정 완료 ({$newName}, {$newPhone})"
            ]);
        } else {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '❌ DB 업데이트 실패'
            ]);
        }
    }

    /** 관리자 - 가격 수정 */
    public function adminUpdatePriceGet($reservationId = null, $newPrice = null)
    {
        $id = (int) $reservationId;
        $price = (int) $newPrice;

        if (!$id || $price < 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => '잘못된 요청'
            ]);
        }

        $updated = $this->model->update($id, [
            'price' => $price,
            'updated_at' => Time::now('Asia/Seoul')
        ]);

        if ($updated) {
            return $this->response->setJSON([
                'success' => true,
                'message' => "✅ 예약 #{$id} 가격이 {$price}원으로 수정되었습니다."
            ]);
        } else {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => '❌ 가격 수정 실패'
            ]);
        }
    }

    /** 관리자 - 예약 삭제 */
    public function adminDeleteReservation($reservationId = null)
    {
        $id = (int) $reservationId;
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => '예약 ID 필요'
            ]);
        }

        $this->model->delete($id);
        return $this->response->setJSON([
            'success' => true,
            'message' => "🗑 예약 #{$id} 삭제 완료"
        ]);
    }

    /** 예약 내역 조회 페이지 */
    public function viewFind()
    {
        return view('find_view', ['title' => '예약 내역 조회']);
    }

    /** 예약 내역 조회 API */
    public function findReservations()
    {
        $branchId = $this->request->getGet('branch_id');
        $phone = $this->request->getGet('phone');
        $includePast = $this->request->getGet('include_past') === 'on';

        if (!$branchId || !$phone) {
            return $this->response->setStatusCode(400)
                ->setJSON(['message' => '지점과 휴대폰 번호를 입력하세요.']);
        }

        $builder = $this->model->builder();
        $builder->select('reservations.*, branches.name AS branch_name, rooms.name AS room_name')
                ->join('branches', 'branches.id = reservations.branch_id', 'left')
                ->join('rooms', 'rooms.id = reservations.room_id', 'left')
                ->where('reservations.branch_id', $branchId)
                ->where('reservations.phone', $phone)
                ->orderBy('reservations.date', 'DESC')
                ->orderBy('reservations.start_time', 'ASC');

        if (!$includePast) {
            $builder->where('reservations.date >=', date('Y-m-d'));
        }

        $data = $builder->get()->getResultArray();
        return $this->response->setJSON($data);
    }
}
