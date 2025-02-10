<?php

namespace App\Http\Controllers\Admin;

use App\BarcodeGenerator;
use App\Http\Controllers\Controller;
use App\Models\Barcode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BarcodeController extends Controller
{
    protected $rules = [
        'name' => ['required', 'string', 'max:255'],
        'value' => ['required', 'string', 'max:255', 'unique:barcodes'],
        'lat' => ['required', 'numeric', 'between:-90,90'], // Validasi latitude
        'lng' => ['required', 'numeric', 'between:-180,180'], // Validasi longitude
        'radius' => ['required', 'numeric', 'min:1', 'max:1000'], // Batasi radius antara 1 dan 1000 meter
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barcodes = Barcode::all(); // Mengambil semua barcode
        return view('admin.barcodes.index', compact('barcodes')); // Kirim data ke view
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Placeholder untuk method show, bisa ditambahkan logic lainnya jika diperlukan.
    }

    /**
     * Show the form for creating a new barcode.
     */
    public function create()
    {
        return view('admin.barcodes.create'); // Tampilkan form untuk membuat barcode baru
    }

    /**
     * Store a newly created barcode.
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate($this->rules);

        try {
            Barcode::create([
                'name' => $request->name,
                'value' => $request->value,
                'latitude' => doubleval($request->lat),
                'longitude' => doubleval($request->lng),
                'radius' => $request->radius,
            ]);
            return redirect()->route('admin.barcodes')->with('flash.banner', __('Created successfully.'));
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('flash.banner', $th->getMessage())
                ->with('flash.bannerStyle', 'danger');
        }
    }

    /**
     * Show the form for editing the specified barcode.
     */
    public function edit(Barcode $barcode)
    {
        return view('admin.barcodes.edit', ['barcode' => $barcode]);
    }

    /**
     * Update the specified barcode.
     */
    public function update(Request $request, Barcode $barcode)
    {
        // Validasi input dengan pengecualian untuk value yang tidak boleh sama
        $request->validate(array_merge($this->rules, [
            'value' => ['required', 'string', 'max:255', Rule::unique('barcodes')->ignore($barcode->id)],
        ]));

        try {
            $barcode->update([
                'name' => $request->name,
                'value' => $request->value,
                'latitude' => doubleval($request->lat),
                'longitude' => doubleval($request->lng),
                'radius' => $request->radius,
            ]);
            return redirect()->route('admin.barcodes')->with('flash.banner', __('Updated successfully.'));
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with('flash.banner', $th->getMessage())
                ->with('flash.bannerStyle', 'danger');
        }
    }

    /**
     * Download the barcode as a PNG file.
     */
    public function download($barcodeId)
    {
        $barcode = Barcode::find($barcodeId);
        
        // Validasi jika barcode tidak ditemukan
        if (!$barcode) {
            return redirect()->back()->with('flash.banner', 'Barcode not found.')->with('flash.bannerStyle', 'danger');
        }

        // Membuat QR code berdasarkan value barcode
        $barcodeFile = (new BarcodeGenerator(width: 1280, height: 1280))->generateQrCode($barcode->value);
        return response($barcodeFile)->withHeaders([
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename=' . ($barcode->name ?? $barcode->value) . '.png',
        ]);
    }

    /**
     * Download all barcodes as a ZIP file.
     */
    public function downloadAll()
    {
        $barcodes = Barcode::all();
        if ($barcodes->isEmpty()) {
            return redirect()->back()
                ->with('flash.banner', 'Barcode ' . __('Not Found'))
                ->with('flash.bannerStyle', 'danger');
        }

        // Membuat file ZIP untuk semua barcode
        $zipFile = (new BarcodeGenerator(width: 1280, height: 1280))->generateQrCodesZip(
            $barcodes->mapWithKeys(fn ($barcode) => [$barcode->name => $barcode->value])->toArray()
        );

        return response(file_get_contents($zipFile))->withHeaders([
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename=barcodes.zip',
        ]);
    }
}
