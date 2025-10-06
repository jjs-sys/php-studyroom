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

    // 중복/겹침 체크 (동일 지점/룸/날짜에서 시간이 겹치면 불가)
    public function hasOverlap(int $branchId, int $roomId, string $date, string $start, string $end, ?int $ignoreId = null): bool
    {
        $builder = $this->db->table($this->table);
        $builder->where('branch_id', $branchId)
                ->where('room_id', $roomId)
                ->where('date', $date)
                ->where('status !=', 'cancelled')
                // (A.start < B.end) AND (A.end > B.start)  -> 겹침
                ->where('start_time <', $end)
                ->where('end_time >', $start);
        if ($ignoreId) {
            $builder->where('id !=', $ignoreId);
        }
        return (bool) $builder->countAllResults();
    }

    // 08:00~23:00 & 30분 단위 & 오늘~+14일
    public function validateBusinessRules(string $date, string $start, string $end): bool
    {
        if ($start >= $end) return false;
        if ($start < self::OPEN_TIME || $end > self::CLOSE_TIME) return false;

        // 30분 단위
        $sM = (int) substr($start, 3, 2);
        $eM = (int) substr($end, 3, 2);
        if ($sM % self::SLOT_MIN !== 0 || $eM % self::SLOT_MIN !== 0) return false;

        // 오늘~+14일
        $today = Time::today('Asia/Seoul')->toDateString();
        $limit = Time::today('Asia/Seoul')->addDays(self::MAX_DAYS)->toDateString();
        if ($date < $today || $date > $limit) return false;

        return true;
    }

    // ----- members 테이블 간단 유틸 -----
    public function upsertMember(string $name, string $phone): int
    {
        $builder = $this->db->table('members');
        $row = $builder->where('phone', $phone)->get()->getRowArray();
        if ($row) {
            // 이름 최신화
            if ($row['name'] !== $name) {
                $builder->where('id', $row['id'])->update(['name' => $name, 'updated_at' => Time::now('Asia/Seoul')]);
            }
            return (int) $row['id'];
        }
        $builder->insert([
            'name'       => $name,
            'phone'      => $phone,
            'created_at' => Time::now('Asia/Seoul'),
            'updated_at' => Time::now('Asia/Seoul'),
        ]);
        return (int) $this->db->insertID();
    }

    public function adminUpdateMember(int $memberId, array $data): bool
    {
        $allowed = array_intersect_key($data, array_flip(['name','phone']));
        if (empty($allowed)) return false;
        $allowed['updated_at'] = Time::now('Asia/Seoul');
        return $this->db->table('members')->where('id', $memberId)->update($allowed);
    }

    public function adminUpdatePrice(int $reservationId, int $price): bool
    {
        return $this->update($reservationId, [
            'price'      => $price,
            'updated_at' => Time::now('Asia/Seoul'),
        ]);
    }
}
