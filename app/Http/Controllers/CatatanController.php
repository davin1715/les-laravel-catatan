<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Catatan;
use Illuminate\Support\Facades\Storage;

class CatatanController extends Controller
{
    public function index(Request $request)
    {
        $query = Catatan::where('user_id', auth()->id())
            ->where('is_archived', false);

        if ($request->filled('cari')) {
            $keyword = '%' . $request->cari . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', $keyword)
                    ->orWhere('isi', 'like', $keyword);
            });
        }

        $urut = $request->get('urut', 'terbaru');
        $query->orderBy('created_at', $urut == 'terlama' ? 'asc' : 'desc');

        $catatan = $query->paginate(5);
        $jumlah_aktif = Catatan::where('user_id', auth()->id())->where('is_archived', false)->count();

        return view('catatan.index', compact('catatan', 'jumlah_aktif'));
    }

    public function create()
    {
        return view('catatan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|max:255',
            'isi' => 'required',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf,txt|max:2048'
        ]);

        $data = $request->only(['judul', 'isi']);
        $data['user_id'] = auth()->id();

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $filename = time() . '_' . $file->hashName();
            $file->storeAs('lampiran', $filename, 'public');
            $data['lampiran'] = 'lampiran/' . $filename;
        }

        Catatan::create($data);
        return redirect('/catatan')->with('sukses', 'Catatan berhasil disimpan!');
    }

    public function export()
    {
        $catatan = Catatan::where('user_id', auth()->id())
            ->where('is_archived', false)
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'catatan_aktif_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($catatan) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM agar karakter/simbol terbaca rapi di Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Judul', 'Isi', 'Tanggal Dibuat'], ';');

            foreach ($catatan as $item) {
                $judul = preg_replace('/[\r\n\t]+/', ' ', (string) $item->judul);
                $isi   = preg_replace('/[\r\n\t]+/', ' ', (string) $item->isi);

                $tanggal = $item->created_at
                    ? '="' . $item->created_at->format('d F Y H:i') . '"'
                    : '-';

                fputcsv($handle, [
                    trim($judul),
                    trim($isi),
                    $tanggal,
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function show($id)
    {
        $catatan = Catatan::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        return view('catatan.show', compact('catatan'));
    }

    public function edit($id)
    {
        $catatan = Catatan::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        return view('catatan.edit', compact('catatan'));
    }

    public function update(Request $request, $id)
    {

        $catatan = Catatan::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'judul' => 'required|max:255',
            'isi' => 'required',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf,txt|max:2048'
        ]);

        $data = $request->only(['judul', 'isi']);

        if ($request->hasFile('lampiran')) {
            if ($catatan->lampiran && Storage::disk('public')->exists($catatan->lampiran)) {
                Storage::disk('public')->delete($catatan->lampiran);
            }
            $file = $request->file('lampiran');
            $filename = time() . '_' . $file->hashName();
            $file->storeAs('lampiran', $filename, 'public');
            $data['lampiran'] = 'lampiran/' . $filename;
        }

        $catatan->update($data);
        return redirect("/catatan/{$catatan->id}")->with('sukses', 'Catatan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $catatan = Catatan::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        if ($catatan->lampiran && Storage::disk('public')->exists($catatan->lampiran)) {
            Storage::disk('public')->delete($catatan->lampiran);
        }
        $catatan->delete();
        return redirect('/catatan')->with('sukses', 'Catatan berhasil dihapus.');
    }

    public function arsip(Request $request)
    {
        $query = Catatan::where('user_id', auth()->id())
            ->where('is_archived', true);

        if ($request->filled('cari')) {
            $keyword = '%' . $request->cari . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', $keyword)
                    ->orWhere('isi', 'like', $keyword);
            });
        }

        $urut = $request->get('urut', 'terbaru');
        $query->orderBy('created_at', $urut == 'terlama' ? 'asc' : 'desc');

        $catatan = $query->paginate(5);
        $jumlah_arsip = Catatan::where('user_id', auth()->id())->where('is_archived', true)->count();

        return view('catatan.arsip', compact('catatan', 'jumlah_arsip'));
    }

    public function archive($id)
    {
        $catatan = Catatan::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $catatan->is_archived = true;
        $catatan->save();
        return redirect('/catatan')->with('sukses', 'Catatan telah diarsipkan.');
    }

    public function unarchive($id)
    {
        $catatan = Catatan::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $catatan->is_archived = false;
        $catatan->save();
        return redirect('/catatan')->with('sukses', 'Catatan dipulihkan dari arsip.');
    }
    public function importForm()
    {
        return view('catatan.import');
    }
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');

        if ($handle === false) {
            return redirect('/catatan')->with('error', 'Gagal membuka file.');
        }

        // Lewati header dengan delimiter ';'
        fgetcsv($handle, 0, ';');

        $jumlah = 0;
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            // Lewati baris kosong
            if (empty(array_filter($row, fn($val) => trim((string) $val) !== ''))) {
                continue;
            }

            if (count($row) >= 2) {
                $judul = trim((string) ($row[0] ?? ''));
                $isi   = trim((string) ($row[1] ?? ''));

                // Lewati jika judul dan isi kosong
                if ($judul === '' && $isi === '') {
                    continue;
                }

                // Bersihkan format ="..." jika file berasal dari export()
                $rawTanggal = isset($row[2]) ? trim((string) $row[2]) : '';
                $rawTanggal = preg_replace('/^="(.*)"$/', '$1', $rawTanggal);

                $createdAt = now();
                if ($rawTanggal !== '' && $rawTanggal !== '-') {
                    try {
                        // Jika nama bulan Bahasa Indonesia, mapping ke Inggris agar Carbon/strtotime lebih mudah membaca
                        $bulanId = [
                            'Januari' => 'January',
                            'Februari' => 'February',
                            'Maret' => 'March',
                            'April' => 'April',
                            'Mei' => 'May',
                            'Juni' => 'June',
                            'Juli' => 'July',
                            'Agustus' => 'August',
                            'September' => 'September',
                            'Oktober' => 'October',
                            'November' => 'November',
                            'Desember' => 'December',
                        ];
                        $tanggalNormalized = str_ireplace(array_keys($bulanId), array_values($bulanId), $rawTanggal);
                        $createdAt = Carbon::parse($tanggalNormalized);
                    } catch (\Throwable $e) {
                        $createdAt = now();
                    }
                }

                $catatan = new Catatan();
                $catatan->user_id = auth()->id();
                $catatan->judul = $judul;
                $catatan->isi = $isi;
                $catatan->is_archived = false;
                $catatan->created_at = $createdAt;
                $catatan->updated_at = now();
                $catatan->save();

                $jumlah++;
            }
        }

        fclose($handle);

        return redirect('/catatan')->with('sukses', "Berhasil mengimpor {$jumlah} catatan.");
    }
}
