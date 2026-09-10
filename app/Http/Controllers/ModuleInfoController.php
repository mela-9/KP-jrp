<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ModuleInfoController extends Controller
{
    public function akd()
    {
        return Inertia::render('ERegist/AKD/Info', [
            'moduleName' => 'Asuransi Kecelakaan Diri (AKD)',
            'code' => 'AKD',
            'description' => 'Asuransi Kecelakaan Diri memberikan perlindungan finansial terhadap risiko kematian, cacat tetap, atau biaya pengobatan akibat kecelakaan yang dialami tertanggung selama 24 jam di seluruh dunia.',
            'coverages' => [
                'Kecelakaan lalu lintas, kerja, maupun aktivitas sehari-hari.',
                'Risiko kematian akibat kecelakaan.',
                'Cacat tetap (sebagian atau seluruhnya) akibat kecelakaan.',
                'Biaya perawatan medis / rumah sakit akibat kecelakaan.'
            ],
            'registerRoute' => 'akd.index'
        ]);
    }

    public function par()
    {
        return Inertia::render('ERegist/PAR/Info', [
            'moduleName' => 'Property All Risk (PAR)',
            'code' => 'PAR',
            'description' => 'Asuransi Property All Risk (PAR) memberikan perlindungan menyeluruh terhadap kerusakan atau kerugian fisik yang tidak terduga pada bangunan, pabrik, atau inventaris kantor dari segala risiko (all risks).',
            'coverages' => [
                'Kebakaran, sambaran petir, ledakan, dan kejatuhan pesawat.',
                'Kerusuhan, pemogokan, huru-hara, dan perbuatan jahat (SRCC).',
                'Bencana alam (banjir, gempa bumi, letusan gunung berapi).',
                'Kerusakan akibat kelalaian operasional yang tidak disengaja.'
            ],
            'registerRoute' => 'par.index'
        ]);
    }

    public function vehicle()
    {
        return Inertia::render('ERegist/Vehicle/Info', [
            'moduleName' => 'Asuransi Kendaraan Bermotor',
            'code' => 'VEH',
            'description' => 'Modul e-register untuk pengelolaan polis asuransi kendaraan bermotor, mencakup jaminan Comprehensive (Casco), Total Loss Only (TLO), serta Tanggung Jawab Hukum Pihak Ketiga (TJH).',
            'coverages' => [
                'Kerusakan fisik kendaraan akibat tabrakan atau benturan.',
                'Pencurian kendaraan oleh pengemudi/orang lain.',
                'Risiko kebakaran akibat sambaran api eksternal.',
                'Tuntutan hukum pihak ketiga atas kerusakan/cedera.'
            ],
            'registerRoute' => 'vehicle.index'
        ]);
    }

    public function varia()
    {
        return Inertia::render('ERegist/Varia/Info', [
            'moduleName' => 'Aneka / Varia',
            'code' => 'VAR',
            'description' => 'Pencatatan dan pengelolaan produk asuransi aneka (Varia) di luar lini utama, seperti uang dalam pengangkutan (Cash In Transit) dan Fidelity Guarantee.',
            'coverages' => [
                'Perlindungan uang tunai selama dalam perjalanan pengiriman.',
                'Risiko kehilangan uang di dalam brankas akibat perampokan.',
                'Penipuan atau penggelapan yang dilakukan oleh pegawai.'
            ],
            'registerRoute' => 'varia.index'
        ]);
    }

    public function publicLiability()
    {
        return Inertia::render('ERegist/PL/Info', [
            'moduleName' => 'Public Liability',
            'code' => 'PL',
            'description' => 'Asuransi Tanggung Gugat Pihak Ketiga (Public Liability) memberikan proteksi finansial terhadap klaim hukum dari pihak ketiga atas cedera badan atau kerusakan properti.',
            'coverages' => [
                'Klaim cedera fisik atau kematian pengunjung/tamu.',
                'Kerusakan properti milik pihak ketiga akibat operasional bisnis.',
                'Biaya pembelaan hukum dan pengadilan yang disetujui.'
            ],
            'registerRoute' => 'pl.index'
        ]);
    }

    public function suretyBond()
    {
        return Inertia::render('ERegist/SuretyBond/Info', [
            'moduleName' => 'Surety Bond',
            'code' => 'SB',
            'description' => 'Penerbitan dan pengawasan instrumen jaminan penawaran, pelaksanaan, dan uang muka proyek untuk mendukung proyek konstruksi maupun pengadaan.',
            'coverages' => [
                'Jaminan Penawaran (Bid Bond) bagi peserta tender.',
                'Jaminan Pelaksanaan (Performance Bond) sesuai kontrak.',
                'Jaminan Uang Muka (Advance Payment Bond) atas pencairan dana muka kerja.'
            ],
            'registerRoute' => 'surety.index'
        ]);
    }
}