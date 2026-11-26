<?php

namespace App\Http\Controllers;

use App\Models\Kontak;
use Illuminate\Http\Request;
use PharIo\Manifest\Email;

class KontakController extends Controller
{
    public function index()
    {
        $kontak = Kontak::all();
        return view('kontak.index', compact('kontak'));
    }

    public function create()
    {
        return view('kontak.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|max:100',
            'email' => 'required|email',
            'pesan' => 'required'
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'pesan.required' => 'Pesan tidak boleh kosong.'
        ]);

        Kontak::create($request->only(['nama', 'email', 'pesan']));

        return redirect('/kontak')->with('sukses', 'Kontak berhasil disimpan!');
    }

    public function show($id)
    {
        $kontak = Kontak::findOrFail($id);
        return view('kontak.show', compact('kontak'));
    }

    public function edit($id)
    {
        $kontak = Kontak::findOrFail($id);
        return view('kontak.edit', compact('kontak'));
    }

    public function update(Request $request, $id)
    {
         $request->validate([
            'nama' => 'required|max:100',
            'email' => 'required|email|unique:kontak,email,'.$id,
            'pesan' => 'required'
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => ' email sudah terdaftar.',
            'pesan.required' => 'Pesan tidak boleh kosong.'
        ]);

        $kontak = Kontak::findOrFail($id);
        $kontak->update($request->only(['nama', 'email', 'pesan']));

        return redirect("/kontak/{$id}")->with('sukses', 'Kontak berhasil diperbarui!');
    }

    public function destroy($id)
{
    $kontak = Kontak::findOrFail($id);
    $kontak->delete();
    return redirect('/kontak')->with('sukses', 'Kontak berhasil dihapus.');
}
}
