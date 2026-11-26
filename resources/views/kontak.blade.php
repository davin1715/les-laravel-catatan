@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Daftar Kontak</h2>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Pesan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kontaks as $index => $kontak)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $kontak->nama }}</td>
                <td>{{ $kontak->email }}</td>
                {{-- Menampilkan potongan pesan (misal dibatasi 50 karakter) --}}
                <td>{{ Str::limit($kontak->pesan, 50) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection