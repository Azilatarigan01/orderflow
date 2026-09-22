<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Vendor;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = [
            [
                'code' => 'VND-001',
                'name' => 'PT Sinar Mega Solusindo',
                'category' => 'Hardware & IT Equipment',
                'contact_person' => 'Bambang Pamungkas',
                'email' => 'sales@sinarmega.co.id',
                'phone' => '021-5551234',
                'address' => 'Jl. Kebon Jeruk No. 45, Jakarta Barat',
                'tax_number' => '01.234.567.8-012.000',
                'bank_name' => 'Bank Mandiri',
                'bank_account_no' => '123-00-9876543-2',
                'bank_account_name' => 'PT Sinar Mega Solusindo',
                'rating' => 4.80,
                'is_active' => true,
            ],
            [
                'code' => 'VND-002',
                'name' => 'CV Bintang Alat Kantor',
                'category' => 'Office Supplies & Stationery',
                'contact_person' => 'Siti Aminah',
                'email' => 'order@bintangatk.com',
                'phone' => '021-8899221',
                'address' => 'Jl. Pintu Air No. 12, Jakarta Pusat',
                'tax_number' => '02.456.789.1-034.000',
                'bank_name' => 'BCA',
                'bank_account_no' => '8820-192-334',
                'bank_account_name' => 'CV Bintang Alat Kantor',
                'rating' => 4.50,
                'is_active' => true,
            ],
            [
                'code' => 'VND-003',
                'name' => 'PT Mitra Solusi Cloud',
                'category' => 'Software & Cloud Services',
                'contact_person' => 'David Lee',
                'email' => 'enterprise@mitracloud.id',
                'phone' => '021-3344556',
                'address' => 'Cyber 2 Tower Lt. 18, HR Rasuna Said, Jakarta Selatan',
                'tax_number' => '03.789.123.4-056.000',
                'bank_name' => 'Bank Danamon',
                'bank_account_no' => '003-889-11234',
                'bank_account_name' => 'PT Mitra Solusi Cloud',
                'rating' => 4.90,
                'is_active' => true,
            ],
            [
                'code' => 'VND-004',
                'name' => 'PT Abadi Sukses Makmur',
                'category' => 'Furniture & Interior',
                'contact_person' => 'Agus Pratama',
                'email' => 'sales@abadi-furniture.co.id',
                'phone' => '021-7788990',
                'address' => 'Jl. Raya Fatmawati No. 88, Jakarta Selatan',
                'tax_number' => '04.112.334.5-078.000',
                'bank_name' => 'BCA',
                'bank_account_no' => '5420-991-002',
                'bank_account_name' => 'PT Abadi Sukses Makmur',
                'rating' => 4.30,
                'is_active' => true,
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::updateOrCreate(['code' => $vendor['code']], $vendor);
        }
    }
}
