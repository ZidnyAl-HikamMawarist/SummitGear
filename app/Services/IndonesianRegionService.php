<?php

namespace App\Services;

class IndonesianRegionService
{
    /**
     * Data Wilayah Indonesia (Hierarkis Provinsi -> Kab/Kota -> Kecamatan -> Desa/Kelurahan + Kode Pos)
     */
    protected static array $regions = [
        'Jawa Barat' => [
            'Kab. Bandung' => [
                'Soreang' => [
                    ['name' => 'Soreang', 'postal_code' => '40911'],
                    ['name' => 'Sekarwangi', 'postal_code' => '40911'],
                    ['name' => 'Panyirapan', 'postal_code' => '40915'],
                    ['name' => 'Sadu', 'postal_code' => '40913'],
                ],
                'Ciwidey' => [
                    ['name' => 'Ciwidey', 'postal_code' => '40973'],
                    ['name' => 'Panundaan', 'postal_code' => '40973'],
                    ['name' => 'Rawabogo', 'postal_code' => '40973'],
                    ['name' => 'Lebakmuncang', 'postal_code' => '40973'],
                ],
                'Banjaran' => [
                    ['name' => 'Banjaran', 'postal_code' => '40377'],
                    ['name' => 'Kiangroke', 'postal_code' => '40377'],
                    ['name' => 'Tarajusari', 'postal_code' => '40377'],
                ],
                'Pangalengan' => [
                    ['name' => 'Pangalengan', 'postal_code' => '40378'],
                    ['name' => 'Margamulya', 'postal_code' => '40378'],
                    ['name' => 'Warnasari', 'postal_code' => '40378'],
                    ['name' => 'Sukamanah', 'postal_code' => '40378'],
                ],
                'Cileunyi' => [
                    ['name' => 'Cileunyi Kulon', 'postal_code' => '40622'],
                    ['name' => 'Cileunyi Wetan', 'postal_code' => '40622'],
                    ['name' => 'Cimekar', 'postal_code' => '40623'],
                ],
            ],
            'Kab. Bandung Barat' => [
                'Lembang' => [
                    ['name' => 'Lembang', 'postal_code' => '40391'],
                    ['name' => 'Cikole', 'postal_code' => '40391'],
                    ['name' => 'Jayagiri', 'postal_code' => '40391'],
                    ['name' => 'Kayuambon', 'postal_code' => '40391'],
                    ['name' => 'Gudangkahuripan', 'postal_code' => '40391'],
                ],
                'Ngamprah' => [
                    ['name' => 'Ngamprah', 'postal_code' => '40552'],
                    ['name' => 'Mekarsari', 'postal_code' => '40552'],
                    ['name' => 'Gadobangkong', 'postal_code' => '40552'],
                    ['name' => 'Pakuhaji', 'postal_code' => '40552'],
                ],
                'Padalarang' => [
                    ['name' => 'Kertajaya', 'postal_code' => '40553'],
                    ['name' => 'Kertamulya', 'postal_code' => '40553'],
                    ['name' => 'Ciburuy', 'postal_code' => '40553'],
                    ['name' => 'Tagogapu', 'postal_code' => '40553'],
                ],
                'Parongpong' => [
                    ['name' => 'Ciwaruga', 'postal_code' => '40559'],
                    ['name' => 'Cihanjuang', 'postal_code' => '40559'],
                    ['name' => 'Cigugur Girang', 'postal_code' => '40559'],
                    ['name' => 'Karyawangi', 'postal_code' => '40559'],
                ],
            ],
            'Kota Bandung' => [
                'Coblong' => [
                    ['name' => 'Dago', 'postal_code' => '40135'],
                    ['name' => 'Lebak Siliwangi', 'postal_code' => '40132'],
                    ['name' => 'Sadang Serang', 'postal_code' => '40133'],
                    ['name' => 'Sekeloa', 'postal_code' => '40134'],
                ],
                'Cicendo' => [
                    ['name' => 'Pasirkaliki', 'postal_code' => '40171'],
                    ['name' => 'Arjuna', 'postal_code' => '40172'],
                    ['name' => 'Pajajaran', 'postal_code' => '40173'],
                    ['name' => 'Pamoyanan', 'postal_code' => '40173'],
                ],
                'Sukasari' => [
                    ['name' => 'Geger Kalong', 'postal_code' => '40153'],
                    ['name' => 'Isola', 'postal_code' => '40154'],
                    ['name' => 'Sarijadi', 'postal_code' => '40151'],
                    ['name' => 'Sukarasa', 'postal_code' => '40152'],
                ],
                'Sumur Bandung' => [
                    ['name' => 'Braga', 'postal_code' => '40111'],
                    ['name' => 'Kebon Pisang', 'postal_code' => '40112'],
                    ['name' => 'Merdeka', 'postal_code' => '40113'],
                    ['name' => 'Babakan Ciamis', 'postal_code' => '40117'],
                ],
                'Buahbatu' => [
                    ['name' => 'Margasari', 'postal_code' => '40286'],
                    ['name' => 'Sekejati', 'postal_code' => '40286'],
                    ['name' => 'Cijawura', 'postal_code' => '40287'],
                    ['name' => 'Jatisari', 'postal_code' => '40286'],
                ],
            ],
            'Kota Cimahi' => [
                'Cimahi Utara' => [
                    ['name' => 'Cibabat', 'postal_code' => '40513'],
                    ['name' => 'Citeureup', 'postal_code' => '40512'],
                    ['name' => 'Pasirkaliki', 'postal_code' => '40514'],
                    ['name' => 'Cipageran', 'postal_code' => '40511'],
                ],
                'Cimahi Tengah' => [
                    ['name' => 'Baros', 'postal_code' => '40521'],
                    ['name' => 'Cigugur Tengah', 'postal_code' => '40522'],
                    ['name' => 'Karangmekar', 'postal_code' => '40523'],
                    ['name' => 'Setiamanah', 'postal_code' => '40524'],
                    ['name' => 'Padasuka', 'postal_code' => '40526'],
                ],
                'Cimahi Selatan' => [
                    ['name' => 'Cibeber', 'postal_code' => '40531'],
                    ['name' => 'Leuwigajah', 'postal_code' => '40532'],
                    ['name' => 'Utama', 'postal_code' => '40533'],
                    ['name' => 'Melong', 'postal_code' => '40534'],
                    ['name' => 'Cibeureum', 'postal_code' => '40535'],
                ],
            ],
            'Kab. Bogor' => [
                'Cibinong' => [
                    ['name' => 'Cibinong', 'postal_code' => '16911'],
                    ['name' => 'Cirimekar', 'postal_code' => '16915'],
                    ['name' => 'Pakansari', 'postal_code' => '16915'],
                    ['name' => 'Nanggewer', 'postal_code' => '16912'],
                ],
                'Cisarua (Puncak)' => [
                    ['name' => 'Cisarua', 'postal_code' => '16750'],
                    ['name' => 'Tugu Selatan', 'postal_code' => '16750'],
                    ['name' => 'Tugu Utara', 'postal_code' => '16750'],
                    ['name' => 'Batu Layang', 'postal_code' => '16750'],
                ],
                'Babakan Madang' => [
                    ['name' => 'Babakan Madang', 'postal_code' => '16810'],
                    ['name' => 'Citaringgul', 'postal_code' => '16810'],
                    ['name' => 'Sumur Batu', 'postal_code' => '16810'],
                    ['name' => 'Bojong Koneng', 'postal_code' => '16810'],
                ],
            ],
            'Kota Bogor' => [
                'Bogor Tengah' => [
                    ['name' => 'Babakan', 'postal_code' => '16128'],
                    ['name' => 'Pabaton', 'postal_code' => '16121'],
                    ['name' => 'Paledang', 'postal_code' => '16122'],
                    ['name' => 'Sempur', 'postal_code' => '16129'],
                ],
                'Bogor Selatan' => [
                    ['name' => 'Batutulis', 'postal_code' => '16133'],
                    ['name' => 'Bondongan', 'postal_code' => '16131'],
                    ['name' => 'Empang', 'postal_code' => '16132'],
                    ['name' => 'Cikaret', 'postal_code' => '16132'],
                ],
            ],
            'Kota Depok' => [
                'Pancoran Mas' => [
                    ['name' => 'Depok', 'postal_code' => '16431'],
                    ['name' => 'Depok Jaya', 'postal_code' => '16432'],
                    ['name' => 'Pancoran Mas', 'postal_code' => '16436'],
                    ['name' => 'Rangkapan Jaya', 'postal_code' => '16435'],
                ],
                'Beji' => [
                    ['name' => 'Beji', 'postal_code' => '16421'],
                    ['name' => 'Beji Timur', 'postal_code' => '16422'],
                    ['name' => 'Kemiri Muka', 'postal_code' => '16423'],
                    ['name' => 'Kukusan', 'postal_code' => '16425'],
                    ['name' => 'Pondok Cina', 'postal_code' => '16424'],
                ],
            ],
            'Kota Bekasi' => [
                'Bekasi Barat' => [
                    ['name' => 'Bintara', 'postal_code' => '17134'],
                    ['name' => 'Kranji', 'postal_code' => '17135'],
                    ['name' => 'Kota Baru', 'postal_code' => '17133'],
                    ['name' => 'Bintara Jaya', 'postal_code' => '17136'],
                ],
                'Bekasi Selatan' => [
                    ['name' => 'Pekayon Jaya', 'postal_code' => '17148'],
                    ['name' => 'Jakasetia', 'postal_code' => '17147'],
                    ['name' => 'Kayuringin Jaya', 'postal_code' => '17144'],
                    ['name' => 'Jaka Mulya', 'postal_code' => '17146'],
                ],
            ],
            'Kab. Garut' => [
                'Tarogong Kaler' => [
                    ['name' => 'Rancabango', 'postal_code' => '44151'],
                    ['name' => 'Panembong', 'postal_code' => '44151'],
                    ['name' => 'Sirnajaya', 'postal_code' => '44151'],
                    ['name' => 'Cimurah', 'postal_code' => '44151'],
                ],
                'Cisurupan (Papandayan)' => [
                    ['name' => 'Balewangi', 'postal_code' => '44163'],
                    ['name' => 'Cidatar', 'postal_code' => '44163'],
                    ['name' => 'Cisurupan', 'postal_code' => '44163'],
                    ['name' => 'Tambakbaya', 'postal_code' => '44163'],
                ],
                'Garut Kota' => [
                    ['name' => 'Kota Kulon', 'postal_code' => '44112'],
                    ['name' => 'Kota Wetan', 'postal_code' => '44111'],
                    ['name' => 'Pakuwon', 'postal_code' => '44117'],
                    ['name' => 'Regol', 'postal_code' => '44114'],
                ],
            ],
            'Kab. Cianjur' => [
                'Cipanas (Gede Pangrango)' => [
                    ['name' => 'Cipanas', 'postal_code' => '43253'],
                    ['name' => 'Cimacan', 'postal_code' => '43253'],
                    ['name' => 'Sindangjaya', 'postal_code' => '43253'],
                    ['name' => 'Batulawang', 'postal_code' => '43253'],
                ],
                'Cianjur' => [
                    ['name' => 'Pamoyanan', 'postal_code' => '43211'],
                    ['name' => 'Sayang', 'postal_code' => '43213'],
                    ['name' => 'Bojongherang', 'postal_code' => '43216'],
                ],
            ],
            'Kab. Sukabumi' => [
                'Cikole' => [
                    ['name' => 'Cikole', 'postal_code' => '43113'],
                    ['name' => 'Selabatu', 'postal_code' => '43114'],
                    ['name' => 'Gunungparang', 'postal_code' => '43111'],
                ],
                'Cisaat' => [
                    ['name' => 'Cisaat', 'postal_code' => '43152'],
                    ['name' => 'Nagrak', 'postal_code' => '43152'],
                    ['name' => 'Sukamanah', 'postal_code' => '43152'],
                ],
            ],
            'Kab. Kuningan' => [
                'Cilimus (Ciremai)' => [
                    ['name' => 'Linggajati', 'postal_code' => '45556'],
                    ['name' => 'Linggasana', 'postal_code' => '45556'],
                    ['name' => 'Bandorasa Kulon', 'postal_code' => '45556'],
                    ['name' => 'Cilimus', 'postal_code' => '45556'],
                ],
                'Kuningan' => [
                    ['name' => 'Kuningan', 'postal_code' => '45511'],
                    ['name' => 'Purwawinangun', 'postal_code' => '45512'],
                    ['name' => 'Winduhaji', 'postal_code' => '45516'],
                ],
            ],
        ],
        'DKI Jakarta' => [
            'Jakarta Selatan' => [
                'Kebayoran Baru' => [
                    ['name' => 'Senayan', 'postal_code' => '12190'],
                    ['name' => 'Gandaria Utara', 'postal_code' => '12140'],
                    ['name' => 'Cipete Utara', 'postal_code' => '12150'],
                    ['name' => 'Melawai', 'postal_code' => '12160'],
                ],
                'Cilandak' => [
                    ['name' => 'Cilandak Barat', 'postal_code' => '12430'],
                    ['name' => 'Lebak Bulus', 'postal_code' => '12440'],
                    ['name' => 'Pondok Labu', 'postal_code' => '12450'],
                ],
                'Tebet' => [
                    ['name' => 'Tebet Barat', 'postal_code' => '12810'],
                    ['name' => 'Tebet Timur', 'postal_code' => '12820'],
                    ['name' => 'Menteng Dalam', 'postal_code' => '12870'],
                ],
            ],
            'Jakarta Pusat' => [
                'Menteng' => [
                    ['name' => 'Menteng', 'postal_code' => '10310'],
                    ['name' => 'Gondangdia', 'postal_code' => '10350'],
                    ['name' => 'Cikini', 'postal_code' => '10330'],
                ],
                'Tanah Abang' => [
                    ['name' => 'Bendungan Hilir', 'postal_code' => '10210'],
                    ['name' => 'Karet Tengsin', 'postal_code' => '10220'],
                    ['name' => 'Kebon Kacang', 'postal_code' => '10240'],
                ],
                'Gambir' => [
                    ['name' => 'Gambir', 'postal_code' => '10110'],
                    ['name' => 'Kebon Kelapa', 'postal_code' => '10120'],
                    ['name' => 'Petojo Selatan', 'postal_code' => '10160'],
                ],
            ],
            'Jakarta Barat' => [
                'Kebon Jeruk' => [
                    ['name' => 'Kebon Jeruk', 'postal_code' => '11530'],
                    ['name' => 'Duri Kepa', 'postal_code' => '11510'],
                    ['name' => 'Kedoya Selatan', 'postal_code' => '11520'],
                ],
                'Kembangan' => [
                    ['name' => 'Kembangan Utara', 'postal_code' => '11610'],
                    ['name' => 'Kembangan Selatan', 'postal_code' => '11610'],
                    ['name' => 'Meruya Utara', 'postal_code' => '11620'],
                ],
            ],
            'Jakarta Timur' => [
                'Jatinegara' => [
                    ['name' => 'Kampung Melayu', 'postal_code' => '13320'],
                    ['name' => 'Bidara Cina', 'postal_code' => '13330'],
                    ['name' => 'Cipinang Cempedak', 'postal_code' => '13340'],
                ],
                'Duren Sawit' => [
                    ['name' => 'Pondok Kelapa', 'postal_code' => '13450'],
                    ['name' => 'Duren Sawit', 'postal_code' => '13440'],
                    ['name' => 'Klender', 'postal_code' => '13470'],
                ],
            ],
            'Jakarta Utara' => [
                'Kelapa Gading' => [
                    ['name' => 'Kelapa Gading Barat', 'postal_code' => '14240'],
                    ['name' => 'Kelapa Gading Timur', 'postal_code' => '14240'],
                    ['name' => 'Pegangsaan Dua', 'postal_code' => '14250'],
                ],
                'Tanjung Priok' => [
                    ['name' => 'Tanjung Priok', 'postal_code' => '14310'],
                    ['name' => 'Sunter Agung', 'postal_code' => '14350'],
                    ['name' => 'Sunter Jaya', 'postal_code' => '14360'],
                ],
            ],
        ],
        'Banten' => [
            'Kota Tangerang Selatan' => [
                'Serpong' => [
                    ['name' => 'Lengkong Gudang', 'postal_code' => '15321'],
                    ['name' => 'Rawabuntu', 'postal_code' => '15318'],
                    ['name' => 'Serpong', 'postal_code' => '15311'],
                    ['name' => 'Cilenggang', 'postal_code' => '15310'],
                ],
                'Pamulang' => [
                    ['name' => 'Pamulang Barat', 'postal_code' => '15417'],
                    ['name' => 'Pamulang Timur', 'postal_code' => '15417'],
                    ['name' => 'Benda Baru', 'postal_code' => '15418'],
                ],
                'Ciputat' => [
                    ['name' => 'Ciputat', 'postal_code' => '15411'],
                    ['name' => 'Cipayung', 'postal_code' => '15411'],
                    ['name' => 'Sawah Baru', 'postal_code' => '15413'],
                ],
            ],
            'Kota Tangerang' => [
                'Tangerang' => [
                    ['name' => 'Sukasari', 'postal_code' => '15118'],
                    ['name' => 'Babakan', 'postal_code' => '15118'],
                    ['name' => 'Cikokol', 'postal_code' => '15117'],
                ],
                'Cipondoh' => [
                    ['name' => 'Cipondoh', 'postal_code' => '15148'],
                    ['name' => 'Poris Plawad', 'postal_code' => '15141'],
                    ['name' => 'Gondrong', 'postal_code' => '15146'],
                ],
            ],
            'Kota Serang' => [
                'Serang' => [
                    ['name' => 'Serang', 'postal_code' => '42116'],
                    ['name' => 'Kotabaru', 'postal_code' => '42112'],
                    ['name' => 'Cipare', 'postal_code' => '42117'],
                ],
            ],
        ],
        'Jawa Tengah' => [
            'Kota Semarang' => [
                'Semarang Tengah' => [
                    ['name' => 'Pandansari', 'postal_code' => '50139'],
                    ['name' => 'Pekunden', 'postal_code' => '50134'],
                    ['name' => 'Sekayu', 'postal_code' => '50132'],
                ],
                'Banyumanik' => [
                    ['name' => 'Banyumanik', 'postal_code' => '50264'],
                    ['name' => 'Srondol Wetan', 'postal_code' => '50263'],
                    ['name' => 'Padangsari', 'postal_code' => '50267'],
                ],
            ],
            'Kota Surakarta (Solo)' => [
                'Banjarsari' => [
                    ['name' => 'Manahan', 'postal_code' => '57139'],
                    ['name' => 'Kestalan', 'postal_code' => '57133'],
                    ['name' => 'Gilingan', 'postal_code' => '57134'],
                ],
                'Laweyan' => [
                    ['name' => 'Laweyan', 'postal_code' => '57148'],
                    ['name' => 'Purwosari', 'postal_code' => '57142'],
                    ['name' => 'Kerten', 'postal_code' => '57143'],
                ],
            ],
            'Kab. Magelang' => [
                'Mertoyudan' => [
                    ['name' => 'Mertoyudan', 'postal_code' => '56172'],
                    ['name' => 'Banyurojo', 'postal_code' => '56172'],
                    ['name' => 'Donorojo', 'postal_code' => '56172'],
                ],
                'Borobudur' => [
                    ['name' => 'Borobudur', 'postal_code' => '56553'],
                    ['name' => 'Wanurejo', 'postal_code' => '56553'],
                    ['name' => 'Tanjungsari', 'postal_code' => '56553'],
                ],
                'Sawangan (Merbabu)' => [
                    ['name' => 'Sawangan', 'postal_code' => '56481'],
                    ['name' => 'Kapuhan', 'postal_code' => '56481'],
                    ['name' => 'Krogowanan', 'postal_code' => '56481'],
                ],
            ],
            'Kab. Wonosobo (Dieng)' => [
                'Kejajar (Prau / Dieng)' => [
                    ['name' => 'Dieng', 'postal_code' => '56354'],
                    ['name' => 'Parikesit', 'postal_code' => '56354'],
                    ['name' => 'Patakbanteng', 'postal_code' => '56354'],
                    ['name' => 'Sikunang', 'postal_code' => '56354'],
                    ['name' => 'Sembungan', 'postal_code' => '56354'],
                ],
                'Garung' => [
                    ['name' => 'Garung', 'postal_code' => '56353'],
                    ['name' => 'Tlogo', 'postal_code' => '56353'],
                    ['name' => 'Marongsari', 'postal_code' => '56353'],
                ],
                'Wonosobo' => [
                    ['name' => 'Wonosobo Barat', 'postal_code' => '56311'],
                    ['name' => 'Wonosobo Timur', 'postal_code' => '56311'],
                    ['name' => 'Jaraksari', 'postal_code' => '56314'],
                ],
            ],
            'Kab. Boyolali (Merapi/Merbabu)' => [
                'Selo' => [
                    ['name' => 'Selo', 'postal_code' => '57363'],
                    ['name' => 'Samiran', 'postal_code' => '57363'],
                    ['name' => 'Lencoh', 'postal_code' => '57363'],
                    ['name' => 'Jrakah', 'postal_code' => '57363'],
                ],
            ],
        ],
        'DI Yogyakarta' => [
            'Kota Yogyakarta' => [
                'Danurejan' => [
                    ['name' => 'Suryatmajan', 'postal_code' => '55213'],
                    ['name' => 'Bausasran', 'postal_code' => '55211'],
                    ['name' => 'Tegal Panggung', 'postal_code' => '55212'],
                ],
                'Gondokusuman' => [
                    ['name' => 'Kotabaru', 'postal_code' => '55224'],
                    ['name' => 'Terban', 'postal_code' => '55223'],
                    ['name' => 'Klitren', 'postal_code' => '55222'],
                ],
            ],
            'Kab. Sleman' => [
                'Depok' => [
                    ['name' => 'Caturtunggal', 'postal_code' => '55281'],
                    ['name' => 'Maguwoharjo', 'postal_code' => '55282'],
                    ['name' => 'Condongcatur', 'postal_code' => '55283'],
                ],
                'Mlati' => [
                    ['name' => 'Sinduadi', 'postal_code' => '55284'],
                    ['name' => 'Sendangadi', 'postal_code' => '55285'],
                    ['name' => 'Tirtoadi', 'postal_code' => '55287'],
                ],
                'Pakem (Kaliurang / Merapi)' => [
                    ['name' => 'Hargobinangun', 'postal_code' => '55582'],
                    ['name' => 'Pakembinangun', 'postal_code' => '55582'],
                    ['name' => 'Purwobinangun', 'postal_code' => '55582'],
                ],
            ],
            'Kab. Bantul' => [
                'Banguntapan' => [
                    ['name' => 'Banguntapan', 'postal_code' => '55198'],
                    ['name' => 'Baturetno', 'postal_code' => '55197'],
                    ['name' => 'Singosaren', 'postal_code' => '55193'],
                ],
                'Kasihan' => [
                    ['name' => 'Tirtonirmolo', 'postal_code' => '55181'],
                    ['name' => 'Ngestiharjo', 'postal_code' => '55182'],
                    ['name' => 'Tamantirto', 'postal_code' => '55183'],
                ],
            ],
        ],
        'Jawa Timur' => [
            'Kota Surabaya' => [
                'Gubeng' => [
                    ['name' => 'Gubeng', 'postal_code' => '60281'],
                    ['name' => 'Airlangga', 'postal_code' => '60286'],
                    ['name' => 'Mojo', 'postal_code' => '60285'],
                ],
                'Wonokromo' => [
                    ['name' => 'Wonokromo', 'postal_code' => '60241'],
                    ['name' => 'Darmo', 'postal_code' => '60241'],
                    ['name' => 'Sawunggaling', 'postal_code' => '60242'],
                ],
            ],
            'Kota Malang' => [
                'Klojen' => [
                    ['name' => 'Klojen', 'postal_code' => '65111'],
                    ['name' => 'Rampal Celaket', 'postal_code' => '65111'],
                    ['name' => 'Oro-oro Dowo', 'postal_code' => '65112'],
                ],
                'Lowokwaru' => [
                    ['name' => 'Lowokwaru', 'postal_code' => '65141'],
                    ['name' => 'Jatimulyo', 'postal_code' => '65141'],
                    ['name' => 'Ketawanggede', 'postal_code' => '65145'],
                ],
            ],
            'Kota Batu' => [
                'Batu' => [
                    ['name' => 'Sisir', 'postal_code' => '65314'],
                    ['name' => 'Temas', 'postal_code' => '65315'],
                    ['name' => 'Pesanggrahan', 'postal_code' => '65313'],
                ],
                'Bumiaji (Arjuno-Welirang)' => [
                    ['name' => 'Bumiaji', 'postal_code' => '65331'],
                    ['name' => 'Tulungrejo', 'postal_code' => '65336'],
                    ['name' => 'Sumbergondo', 'postal_code' => '65335'],
                ],
            ],
            'Kab. Malang' => [
                'Poncokusumo (Bromo/Semeru)' => [
                    ['name' => 'Poncokusumo', 'postal_code' => '65157'],
                    ['name' => 'Gubugklakah', 'postal_code' => '65157'],
                    ['name' => 'Ngadas', 'postal_code' => '65157'],
                    ['name' => 'Wringinanom', 'postal_code' => '65157'],
                ],
                'Singosari' => [
                    ['name' => 'Pagentan', 'postal_code' => '65153'],
                    ['name' => 'Candirenggo', 'postal_code' => '65153'],
                    ['name' => 'Klampok', 'postal_code' => '65153'],
                ],
            ],
            'Kab. Lumajang' => [
                'Senduro (Semeru)' => [
                    ['name' => 'Senduro', 'postal_code' => '67361'],
                    ['name' => 'Burno', 'postal_code' => '67361'],
                    ['name' => 'Ranupani', 'postal_code' => '67361'],
                ],
                'Pasrujambe' => [
                    ['name' => 'Pasrujambe', 'postal_code' => '67361'],
                    ['name' => 'Sukorejo', 'postal_code' => '67361'],
                ],
            ],
            'Kab. Banyuwangi (Ijen)' => [
                'Licin (Kawah Ijen)' => [
                    ['name' => 'Licin', 'postal_code' => '68454'],
                    ['name' => 'Tamansari', 'postal_code' => '68454'],
                    ['name' => 'Jelagah', 'postal_code' => '68454'],
                ],
                'Banyuwangi' => [
                    ['name' => 'Kepatihan', 'postal_code' => '68411'],
                    ['name' => 'Taman Baru', 'postal_code' => '68416'],
                    ['name' => 'Penganjuran', 'postal_code' => '68416'],
                ],
            ],
        ],
        'Bali' => [
            'Kota Denpasar' => [
                'Denpasar Selatan' => [
                    ['name' => 'Sanur', 'postal_code' => '80228'],
                    ['name' => 'Sidakarya', 'postal_code' => '80224'],
                    ['name' => 'Renon', 'postal_code' => '80226'],
                ],
                'Denpasar Barat' => [
                    ['name' => 'Pemecutan', 'postal_code' => '80112'],
                    ['name' => 'Padangsambian', 'postal_code' => '80117'],
                ],
            ],
            'Kab. Badung' => [
                'Kuta' => [
                    ['name' => 'Kuta', 'postal_code' => '80361'],
                    ['name' => 'Legian', 'postal_code' => '80361'],
                    ['name' => 'Seminyak', 'postal_code' => '80361'],
                ],
                'Kuta Utara' => [
                    ['name' => 'Canggu', 'postal_code' => '80351'],
                    ['name' => 'Tibubeneng', 'postal_code' => '80351'],
                    ['name' => 'Kerobokan', 'postal_code' => '80361'],
                ],
            ],
            'Kab. Bangli (Batur)' => [
                'Kintamani (Gunung Batur)' => [
                    ['name' => 'Batur Selatan', 'postal_code' => '80652'],
                    ['name' => 'Batur Tengah', 'postal_code' => '80652'],
                    ['name' => 'Batur Utara', 'postal_code' => '80652'],
                    ['name' => 'Kintamani', 'postal_code' => '80652'],
                    ['name' => 'Toyabungkah', 'postal_code' => '80652'],
                ],
            ],
            'Kab. Karangasem (Agung)' => [
                'Rendang (Besakih / G. Agung)' => [
                    ['name' => 'Besakih', 'postal_code' => '80863'],
                    ['name' => 'Menanga', 'postal_code' => '80863'],
                    ['name' => 'Rendang', 'postal_code' => '80863'],
                ],
            ],
        ],
        'Nusa Tenggara Barat' => [
            'Kab. Lombok Timur (Rinjani)' => [
                'Sembalun (Rinjani Basecamp)' => [
                    ['name' => 'Sembalun Lawang', 'postal_code' => '83656'],
                    ['name' => 'Sembalun Bumbung', 'postal_code' => '83656'],
                    ['name' => 'Sembalun Timba Gading', 'postal_code' => '83656'],
                    ['name' => 'Sajang', 'postal_code' => '83656'],
                ],
            ],
            'Kab. Lombok Utara' => [
                'Bayan (Senaru / Rinjani)' => [
                    ['name' => 'Senaru', 'postal_code' => '83354'],
                    ['name' => 'Bayan', 'postal_code' => '83354'],
                    ['name' => 'Anyar', 'postal_code' => '83354'],
                ],
            ],
            'Kota Mataram' => [
                'Mataram' => [
                    ['name' => 'Mataram Timur', 'postal_code' => '83121'],
                    ['name' => 'Pajang', 'postal_code' => '83122'],
                    ['name' => 'Pejanggik', 'postal_code' => '83127'],
                ],
            ],
        ],
        'Sumatera Utara' => [
            'Kota Medan' => [
                'Medan Kota' => [
                    ['name' => 'Pasar Merah Barat', 'postal_code' => '20217'],
                    ['name' => 'Mesjid', 'postal_code' => '20212'],
                    ['name' => 'Kotamatsum III', 'postal_code' => '20215'],
                ],
                'Medan Baru' => [
                    ['name' => 'Padang Bulan', 'postal_code' => '20155'],
                    ['name' => 'Babura', 'postal_code' => '20154'],
                ],
            ],
            'Kab. Karo (Sibayak / Sinabung)' => [
                'Berastagi' => [
                    ['name' => 'Gundaling I', 'postal_code' => '22152'],
                    ['name' => 'Gundaling II', 'postal_code' => '22152'],
                    ['name' => 'Tambak Lau Mulgap I', 'postal_code' => '22152'],
                ],
            ],
        ],
        'Sumatera Barat' => [
            'Kota Padang' => [
                'Padang Barat' => [
                    ['name' => 'Purus', 'postal_code' => '25115'],
                    ['name' => 'Rimbo Kaluang', 'postal_code' => '25111'],
                ],
            ],
            'Kota Bukittinggi (Marapi / Singgalang)' => [
                'Guguk Panjang' => [
                    ['name' => 'Benteng Pasar Atas', 'postal_code' => '26113'],
                    ['name' => 'Bukit Cangang Kayu Ramang', 'postal_code' => '26115'],
                ],
            ],
        ],
        'Sulawesi Selatan' => [
            'Kota Makassar' => [
                'Panakkukang' => [
                    ['name' => 'Panaikang', 'postal_code' => '90231'],
                    ['name' => 'Masale', 'postal_code' => '90231'],
                ],
                'Ujung Pandang' => [
                    ['name' => 'Losari', 'postal_code' => '90112'],
                    ['name' => 'Maloku', 'postal_code' => '90111'],
                ],
            ],
            'Kab. Gowa (Bawakaraeng)' => [
                'Tinggimoncong (Malino / Bawakaraeng)' => [
                    ['name' => 'Malino', 'postal_code' => '92174'],
                    ['name' => 'Bulutana', 'postal_code' => '92174'],
                    ['name' => 'Pattapang', 'postal_code' => '92174'],
                ],
            ],
        ],
    ];

    public static function getProvinces(): array
    {
        return array_keys(self::$regions);
    }

    public static function getRegencies(string $province): array
    {
        if (!isset(self::$regions[$province])) {
            return [];
        }
        return array_keys(self::$regions[$province]);
    }

    public static function getDistricts(string $province, string $regency): array
    {
        if (!isset(self::$regions[$province][$regency])) {
            return [];
        }
        return array_keys(self::$regions[$province][$regency]);
    }

    public static function getVillages(string $province, string $regency, string $district): array
    {
        if (!isset(self::$regions[$province][$regency][$district])) {
            return [];
        }
        return self::$regions[$province][$regency][$district];
    }

    public static function getAllRegions(): array
    {
        return self::$regions;
    }
}
