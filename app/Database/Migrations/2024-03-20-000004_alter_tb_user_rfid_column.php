<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterTbUserRfidColumn extends Migration
{
    public function up()
    {
        // Ubah tipe data kolom id_kartu_rfid menjadi VARCHAR(50) dengan atribut khusus
        $this->forge->modifyColumn('tb_user', [
            'id_kartu_rfid' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'iduser',
                'attributes' => ['UNSIGNED ZEROFILL']
            ]
        ]);
    }

    public function down()
    {
        // Kembalikan ke tipe data sebelumnya
        $this->forge->modifyColumn('tb_user', [
            'id_kartu_rfid' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'iduser'
            ]
        ]);
    }
} 