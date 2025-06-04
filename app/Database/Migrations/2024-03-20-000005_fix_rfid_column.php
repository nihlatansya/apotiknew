<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixRfidColumn extends Migration
{
    public function up()
    {
        // Hapus kolom yang ada
        $this->forge->dropColumn('tb_user', 'id_kartu_rfid');
        
        // Tambahkan kolom baru dengan tipe CHAR untuk memastikan leading zeros tetap ada
        $this->forge->addColumn('tb_user', [
            'id_kartu_rfid' => [
                'type' => 'CHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'iduser'
            ]
        ]);
    }

    public function down()
    {
        // Kembalikan ke tipe data sebelumnya
        $this->forge->dropColumn('tb_user', 'id_kartu_rfid');
        
        $this->forge->addColumn('tb_user', [
            'id_kartu_rfid' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'iduser'
            ]
        ]);
    }
} 