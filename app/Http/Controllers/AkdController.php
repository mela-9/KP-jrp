<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AkdRegister;
use Inertia\Inertia;

class AkdController extends Controller
{
    public function index()
    {
        // Mengambil semua data AKD dari database, diurutkan dari yang terbaru
        $akdData = AkdRegister::orderBy('created_at', 'desc')->get();
        
        // Mengirim data tersebut ke file React (Index.jsx)
        return Inertia::render('ERegist/AKD/Index', [
            'akdData' => $akdData
        ]);
    }
}