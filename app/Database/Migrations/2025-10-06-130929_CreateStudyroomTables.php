<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStudyroomTables extends Migration
{
    public function up()
    {
        // branches
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'open_time'  => ['type' => 'TIME', 'null' => true, 'default' => '08:00:00'],
            'close_time' => ['type' => 'TIME', 'null' => true, 'default' => '23:00:00'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('branches', true);

        // rooms
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'branch_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 50],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('branch_id');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rooms', true);

        // members (관리페이지에서 수정)
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 50],
            'phone'      => ['type' => 'VARCHAR', 'constraint' => 20, 'unique' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('members', true);

        // reservations
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'branch_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'room_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'member_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 50],  // 비회원 예약 대비
            'phone'           => ['type' => 'VARCHAR', 'constraint' => 20],
            'date'            => ['type' => 'DATE'],
            'start_time'      => ['type' => 'TIME'],
            'end_time'        => ['type' => 'TIME'],
            'price'           => ['type' => 'INT', 'null' => true, 'default' => 0],
            'status'          => ["type" => "ENUM('pending','confirmed','cancelled')", 'default' => 'pending'],
            'verify_code'     => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'verify_expires'  => ['type' => 'DATETIME', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['branch_id', 'room_id', 'date']);
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('room_id', 'rooms', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('member_id', 'members', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('reservations', true);

        // 샘플 브랜치 & 룸 (선택사항)
        $this->db->table('branches')->insertBatch([
            ['name' => '강남점', 'open_time' => '08:00:00', 'close_time' => '23:00:00'],
            ['name' => '홍대점', 'open_time' => '08:00:00', 'close_time' => '23:00:00'],
        ]);
        $this->db->table('rooms')->insertBatch([
            ['branch_id' => 1, 'name' => 'A'],
            ['branch_id' => 1, 'name' => 'B'],
            ['branch_id' => 2, 'name' => 'A'],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('reservations', true);
        $this->forge->dropTable('members', true);
        $this->forge->dropTable('rooms', true);
        $this->forge->dropTable('branches', true);
    }
}
