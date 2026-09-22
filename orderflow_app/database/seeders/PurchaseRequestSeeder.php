<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\StatusHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class PurchaseRequestSeeder extends Seeder
{
    public function run(): void
    {
        $requester = User::where('email', 'requester@orderflow.com')
            ->orWhere('email', 'requester.it@orderflow.com')
            ->first();

        $manager = User::where('email', 'manager@orderflow.com')
            ->orWhere('email', 'manager.it@orderflow.com')
            ->first();

        $itDept = Department::where('code', 'IT')->first();

        if (!$requester || !$itDept) {
            return;
        }

        // PR 1: Submitted (Menunggu Approval)
        $pr1 = PurchaseRequest::updateOrCreate(
            ['pr_number' => 'PR-202609-0001'],
            [
                'user_id' => $requester->id,
                'department_id' => $itDept->id,
                'title' => 'Pengadaan 3 Unit Laptop Developer & Monitor Eksternal',
                'description' => 'Penambahan kapasitas workstation tim backend dan frontend seiring onboarding 3 engineer baru untuk proyek OrderFlow Enterprise.',
                'required_date' => now()->addDays(14)->toDateString(),
                'estimated_total' => 74100000,
                'status' => 'submitted',
            ]
        );

        $pr1->items()->delete();
        $pr1->items()->createMany([
            [
                'item_name' => 'Laptop Lenovo ThinkPad T14 Gen 4',
                'specification' => 'AMD Ryzen 7 PRO, RAM 32GB DDR5, SSD 1TB NVMe, Garansi Premier 3 Tahun',
                'quantity' => 3,
                'unit' => 'Unit',
                'estimated_unit_price' => 18500000,
                'subtotal' => 55500000,
            ],
            [
                'item_name' => 'Monitor Dell UltraSharp 27 Inch 4K U2723QE',
                'specification' => 'IPS Black, USB-C Hub 90W Power Delivery, Height Adjustable',
                'quantity' => 3,
                'unit' => 'Unit',
                'estimated_unit_price' => 6200000,
                'subtotal' => 18600000,
            ],
        ]);

        $pr1->histories()->delete();
        StatusHistory::create([
            'purchase_request_id' => $pr1->id,
            'from_status' => null,
            'to_status' => 'draft',
            'user_id' => $requester->id,
            'notes' => 'Draf pengajuan disiapkan oleh pemohon.',
            'created_at' => now()->subDays(2),
        ]);
        StatusHistory::create([
            'purchase_request_id' => $pr1->id,
            'from_status' => 'draft',
            'to_status' => 'submitted',
            'user_id' => $requester->id,
            'notes' => 'Pengajuan diajukan ke Manager IT untuk review anggaran.',
            'created_at' => now()->subDay(),
        ]);

        // PR 2: Approved
        $pr2 = PurchaseRequest::updateOrCreate(
            ['pr_number' => 'PR-202609-0002'],
            [
                'user_id' => $requester->id,
                'department_id' => $itDept->id,
                'title' => 'Perpanjangan Kapasitas Cloud Server AWS Kuartal IV',
                'description' => 'Upgrade kapasitas database Amazon RDS Aurora PostgreSQL dan Load Balancer untuk menjamin SLA 99.99%.',
                'required_date' => now()->addDays(7)->toDateString(),
                'estimated_total' => 12000000,
                'status' => 'approved',
            ]
        );
        $pr2->items()->delete();
        $pr2->items()->create([
            'item_name' => 'Paket Kapasitas AWS RDS Multi-AZ & Compute Savings',
            'specification' => 'Dedicated Compute db.r6g.xlarge, 500GB Provisioned IOPS Storage',
            'quantity' => 1,
            'unit' => 'Paket',
            'estimated_unit_price' => 12000000,
            'subtotal' => 12000000,
        ]);

        $pr2->histories()->delete();
        StatusHistory::create([
            'purchase_request_id' => $pr2->id,
            'from_status' => null,
            'to_status' => 'draft',
            'user_id' => $requester->id,
            'notes' => 'Draf perpanjangan server cloud dibuat.',
            'created_at' => now()->subDays(5),
        ]);
        StatusHistory::create([
            'purchase_request_id' => $pr2->id,
            'from_status' => 'draft',
            'to_status' => 'submitted',
            'user_id' => $requester->id,
            'notes' => 'Pengajuan diserahkan ke Manager.',
            'created_at' => now()->subDays(4),
        ]);
        StatusHistory::create([
            'purchase_request_id' => $pr2->id,
            'from_status' => 'submitted',
            'to_status' => 'approved',
            'user_id' => $manager ? $manager->id : $requester->id,
            'notes' => 'Persetujuan disetujui: Alokasi anggaran IT sesuai pagu OPEX Kuartal IV.',
            'created_at' => now()->subDays(3),
        ]);

        // PR 3: Revision Required (Perlu Revisi)
        $pr3 = PurchaseRequest::updateOrCreate(
            ['pr_number' => 'PR-202609-0003'],
            [
                'user_id' => $requester->id,
                'department_id' => $itDept->id,
                'title' => 'Pengadaan Switch Cisco Catalyst & Kabel Patch Cord Server Room',
                'description' => 'Penggantian switch distribusi lantai 3 yang mengalami degradasi port.',
                'required_date' => now()->addDays(10)->toDateString(),
                'estimated_total' => 17000000,
                'status' => 'revision_required',
            ]
        );
        $pr3->items()->delete();
        $pr3->items()->create([
            'item_name' => 'Managed Switch Cisco Catalyst 24 Port Gigabit PoE+',
            'specification' => 'Layer 3 Managed Switch, 370W PoE Budget, 4x SFP+ 10G Uplink',
            'quantity' => 2,
            'unit' => 'Unit',
            'estimated_unit_price' => 8500000,
            'subtotal' => 17000000,
        ]);

        $pr3->histories()->delete();
        StatusHistory::create([
            'purchase_request_id' => $pr3->id,
            'from_status' => null,
            'to_status' => 'draft',
            'user_id' => $requester->id,
            'notes' => 'Draf pengadaan switch dibuat.',
            'created_at' => now()->subDays(3),
        ]);
        StatusHistory::create([
            'purchase_request_id' => $pr3->id,
            'from_status' => 'draft',
            'to_status' => 'submitted',
            'user_id' => $requester->id,
            'notes' => 'Diajukan untuk otorisasi.',
            'created_at' => now()->subDays(2),
        ]);
        StatusHistory::create([
            'purchase_request_id' => $pr3->id,
            'from_status' => 'submitted',
            'to_status' => 'revision_required',
            'user_id' => $manager ? $manager->id : $requester->id,
            'notes' => 'Mohon lampirkan diagram topologi rak server dan konfirmasi apakah modul SFP+ sudah termasuk dalam paket.',
            'created_at' => now()->subDay(),
        ]);

        // PR 4: Draft (Draf Pengajuan Baru)
        $pr4 = PurchaseRequest::updateOrCreate(
            ['pr_number' => 'PR-202609-0004'],
            [
                'user_id' => $requester->id,
                'department_id' => $itDept->id,
                'title' => 'Pengadaan Printer Laser Multifungsi Auto Duplex',
                'description' => 'Kebutuhan cetak laporan audit berkas fisik dan scan invoice.',
                'required_date' => now()->addDays(20)->toDateString(),
                'estimated_total' => 4500000,
                'status' => 'draft',
            ]
        );
        $pr4->items()->delete();
        $pr4->items()->create([
            'item_name' => 'Printer HP LaserJet Pro MFP 4103fdw',
            'specification' => 'Print, Scan, Copy, Fax, Wireless, Duplex Printing, 40 ppm',
            'quantity' => 1,
            'unit' => 'Unit',
            'estimated_unit_price' => 4500000,
            'subtotal' => 4500000,
        ]);

        $pr4->histories()->delete();
        StatusHistory::create([
            'purchase_request_id' => $pr4->id,
            'from_status' => null,
            'to_status' => 'draft',
            'user_id' => $requester->id,
            'notes' => 'Draf pengajuan pembelian berhasil dibuat.',
            'created_at' => now(),
        ]);
    }
}
