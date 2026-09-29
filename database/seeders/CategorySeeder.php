<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama' => 'A. Pertanian, Kehutanan, dan Perikanan',
                'children' => [
                    [
                        'nama' => '1. Pertanian, Peternakan, Perburuan dan Jasa Pertanian',
                        'children' => [
                            ['nama' => '1a. Tanaman Pangan'],
                            ['nama' => '1b. Tanaman Hortikultura Semusim'],
                            ['nama' => '1c. Perkebunan Semusim'],
                            ['nama' => '1d. Tanaman Hortikultura Tahunan dan Lainnya'],
                            ['nama' => '1e. Perkebunan Tahunan'],
                            ['nama' => '1f. Peternakan'],
                            ['nama' => '1g. Jasa Pertanian dan Perburuan'],
                        ],
                    ],
                    ['nama' => '2. Kehutanan dan Penebangan Kayu'],
                    ['nama' => '3. Perikanan'],
                ],
            ],
            [
                'nama' => 'B. Pertambangan dan Penggalian',
                'children' => [
                    ['nama' => '1. Pertambangan Minyak, Gas dan Panas Bumi'],
                    ['nama' => '2. Pertambangan Batubara dan Lignit'],
                    ['nama' => '3. Pertambangan Bijih Logam'],
                    ['nama' => '4. Pertambangan dan Penggalian Lainnya'],
                ],
            ],
            [
                'nama' => 'C. Industri Pengolahan',
                'children' => [
                    [
                        'nama' => '1. Industri Batubara dan Pengilangan Migas',
                        'children' => [
                            ['nama' => '1a. Industri Batu Bara'],
                            ['nama' => '1b. Pengilangan Migas'],
                        ],
                    ],
                    ['nama' => '2. Industri Makanan dan Minuman'],
                    ['nama' => '3. Pengolahan Tembakau'],
                    ['nama' => '4. Industri Tekstil dan Pakaian Jadi'],
                    ['nama' => '5. Industri Kulit, Barang dari Kulit dan Alas Kaki'],
                    ['nama' => '6. Industri Kayu, Barang dari Kayu dan Gabus dan Barang Anyaman dari Bambu, Rotan dan Sejenisnya'],
                    ['nama' => '7. Industri Kertas dan Barang dari Kertas, Percetakan dan Reproduksi Media Rekaman'],
                    ['nama' => '8. Industri Kimia, Farmasi dan Obat Tradisional'],
                    ['nama' => '9. Industri Karet, Barang dari Karet dan Plastik'],
                    ['nama' => '10. Industri Barang Galian bukan Logam'],
                    ['nama' => '11. Industri Logam Dasar'],
                    ['nama' => '12. Industri Barang dari Logam, Komputer, Barang Elektronik, Optik dan Peralatan Listrik'],
                    ['nama' => '13. Industri Mesin dan Perlengkapan YTDL (Yang Tidak Diklasifikasikan Di Tempat Lain)'],
                    ['nama' => '14. Industri Alat Angkutan'],
                    ['nama' => '15. Industri Furnitur'],
                    ['nama' => '16. Industri pengolahan lainnya, jasa reparasi dan pemasangan mesin dan peralatan'],
                ],
            ],
            [
                'nama' => 'D. Pengadaan Listrik dan Gas',
                'children' => [
                    ['nama' => '1. Ketenagalistrikan'],
                    ['nama' => '2. Pengadaan Gas dan Produksi Es'],
                ],
            ],
            ['nama' => 'E. Pengadaan Air, Pengelolaan Sampah, Limbah dan Daur Ulang'],
            ['nama' => 'F. Konstruksi'],
            [
                'nama' => 'G. Perdagangan Besar dan Eceran; Reparasi Mobil dan Sepeda Motor',
                'children' => [
                    ['nama' => '1. Perdagangan Mobil, Sepeda Motor dan Reparasinya'],
                    ['nama' => '2. Perdagangan Besar dan Eceran, Bukan Mobil dan Sepeda Motor'],
                ],
            ],
            [
                'nama' => 'H. Transportasi dan Pergudangan',
                'children' => [
                    ['nama' => '1. Angkutan Rel'],
                    ['nama' => '2. Angkutan Darat'],
                    ['nama' => '3. Angkutan Laut'],
                    ['nama' => '4. Angkutan Sungai Danau dan Penyeberangan'],
                    ['nama' => '5. Angkutan Udara'],
                    ['nama' => '6. Pergudangan dan Jasa Penunjang Angkutan, Pos dan Kurir'],
                ],
            ],
            [
                'nama' => 'I. Penyediaan Akomodasi dan Makan Minum',
                'children' => [
                    ['nama' => '1. Penyediaan Akomodasi'],
                    ['nama' => '2. Penyediaan Makan Minum'],
                ],
            ],
            ['nama' => 'J. Informasi dan Komunikasi'],
            [
                'nama' => 'K. Jasa Keuangan dan Asuransi',
                'children' => [
                    ['nama' => '1. Jasa Perantara Keuangan'],
                    ['nama' => '2. Asuransi dan Dana Pensiun'],
                    ['nama' => '3. Jasa Keuangan Lainnya'],
                    ['nama' => '4. Jasa Penunjang Keuangan'],
                ],
            ],
            ['nama' => 'L. Real Estate'],
            ['nama' => 'M, N. Jasa Perusahaan'],
            ['nama' => 'O. Administrasi Pemerintahan, Pertahanan dan Jaminan Sosial Wajib'],
            ['nama' => 'P. Jasa Pendidikan'],
            ['nama' => 'Q. Jasa Kesehatan dan Kegiatan Sosial'],
            ['nama' => 'R, S, T, U. Jasa lainnya'],
        ];

        foreach ($data as $item) {
            $this->insertCategory($item, null, 1);
        }
    }

    private function insertCategory(array $item, ?int $parentId, int $level): void
    {
        $category = Category::create([
            'parent_id' => $parentId,
            'nama_kategori' => $item['nama'],
            'level' => $level,
        ]);

        if (!empty($item['children'])) {
            foreach ($item['children'] as $child) {
                $this->insertCategory($child, $category->id, $level + 1);
            }
        }
    }
}