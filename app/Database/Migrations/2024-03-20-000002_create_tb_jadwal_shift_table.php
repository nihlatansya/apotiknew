<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTbJadwalShiftTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_jadwal_shift' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'dari_tanggal' => [
                'type' => 'DATE',
            ],
            'sampai_tanggal' => [
                'type' => 'DATE',
            ],
            'shift_mulai' => [
                'type' => 'TIME',
            ],
            'shift_selesai' => [
                'type' => 'TIME',
            ],
            'jenis_shift' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
        ]);

        $this->forge->addKey('id_jadwal_shift', true);
        $this->forge->createTable('tb_jadwal_shift');
    }

    public function down()
    {
        $this->forge->dropTable('tb_jadwal_shift');
    }
} 