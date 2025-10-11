<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class ReservationModel extends Model
{
    protected $table            = 'reservations';
    protected $primaryKey       = 'id';
    protected $useTimestamps    = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'branch_id','room_id','member_id','name','phone','date','start_time','end_time','price',
        'status','verify_code','verify_expires'
    ];

    // ---- 공통 시간 범위 ----
    public const OPEN_TIME  = '08:00:00';
    public const CLOSE_TIME = '23:00:00';
    public const SLOT_MIN   = 30; // 30분 단위
    public const MAX_DAYS   = 14; // 오늘~+14일

    // SMS Mock: 6자리 랜덤
    public static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    // 중복/겹침 체크
    public function hasOverlap(int $branchId, int $roomId, string $date, string $start, string $end, ?int $ignoreId = null): bool
    {
        $builder = $this->db->table($this->table);
        $builder->where('branch_id', $branchId)
                ->where('room_id', $roomId)
                ->where('date', $date)
                ->where('status !=', 'cancelled')
                ->where('start_time <', $end)
                ->where('end_time >', $start);

        if ($ignoreId) {
            $builder->where('id !=', $ignoreId);
        }

        return (bool) $builder->countAllResults();
    }

    // 회원 정보 삽입/업데이트
    public function upsertMember(string $name, string $phone): int
    {
        $builder = $this->db->table('members');
        $row = $builder->where('phone', $phone)->get()->getRowArray();

        if ($row) {
            if ($row['name'] !== $name) {
                $builder->where('id', $row['id'])->update([
                    'name'       => $name,
                    'updated_at' => Time::now('Asia/Seoul')
                ]);
            }
            return (int) $row['id'];
        }

        $builder->insert([
            'name'       => $name,
            'phone'      => $phone,
            'created_at' => Time::now('Asia/Seoul'),
            'updated_at' => Time::now('Asia/Seoul')
        ]);
        return (int) $this->db->insertID();
    }

    // 관리자 - 회원 수정
    public function adminUpdateMember(int $memberId, array $data): bool
    {
        $allowed = array_intersect_key($data, array_flip(['name','phone']));
        if (empty($allowed)) return false;
        $allowed['updated_at'] = Time::now('Asia/Seoul');
        return $this->db->table('members')->where('id', $memberId)->update($allowed);
    }

    // 관리자 - 가격 수정
    public function adminUpdatePrice(int $reservationId, int $price): bool
    {
        return $this->update($reservationId, [
            'price'      => $price,
            'updated_at' => Time::now('Asia/Seoul'),
        ]);
    }
}
