<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTbPresensiTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_presensi' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tanggal' => [
                'type' => 'DATE',
            ],
            'jam_masuk' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'jam_pulang' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'persentase' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
                'default' => 0.00,
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'dibuat_pada' => [
                'type' => 'DATETIME',
            ],
            'diupdate_pada' => [
                'type' => 'DATETIME',
            ],
            'tb_user_iduser' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tb_jadwal_shift_id_jadwal_shift' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
        ]);

        $this->forge->addKey('id_presensi', true);

        // Add foreign key constraints
        $this->forge->addForeignKey('tb_user_iduser', 'tb_user', 'iduser', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tb_jadwal_shift_id_jadwal_shift', 'tb_jadwal_shift', 'id_jadwal_shift', 'CASCADE', 'CASCADE');

        $this->forge->createTable('tb_presensi');
    }

    public function down()
    {
        $this->forge->dropTable('tb_presensi');
    }
}
