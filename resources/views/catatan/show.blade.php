@extends('layouts.app')

@section('title', $catatan->judul)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-start">
                <div class="card-header">
                    <h4 class="mb-1">{{ $catatan->judul }}</h4>
                    <p class="text-muted mb-0">Dibuat pada: {{ $catatan->created_at->format('d F Y \p\u\k\u\l H.i') }}</p>
                    @if($catatan->lampiran)
                    <span class="badge bg-info mt-2">Ada lampiran</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <p class="card-text">{!! nl2br(e($catatan->isi)) !!}</p>
            </div>
            @if($catatan->lampiran)
            <hr>
            <p class="mb-0"><strong>Lampiran:</strong></p>
            @php
            $ext = pathinfo($catatan->lampiran, PATHINFO_EXTENSION);
            @endphp

            <div class="mb-2 mt-2">
                <a href="{{ Storage::url($catatan->lampiran) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                    📎 Buka Lampiran ({{ strtoupper($ext) }})
                </a>
            </div>

            @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
            <a href="{{ Storage::url($catatan->lampiran) }}" target="_blank">
                <img src="{{ Storage::url($catatan->lampiran) }}" alt="Lampiran" class="img-fluid mt-2" style="max-height:300px;">
            </a>
            @endif
            @endif

            <div class="card-footer text-end">
                <a href="/catatan" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>

                @if($catatan->is_archived)
                <form action="/catatan/{{ $catatan->id }}/unarchive" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="fas fa-undo"></i> Pulihkan</button>
                </form>
                @else
                <form action="/catatan/{{ $catatan->id }}/archive" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary" onclick="return confirm('Arsipkan catatan ini?')">
                        <i class="fas fa-archive"></i> Arsipkan
                    </button>
                </form>
                @endif

                <a href="/catatan/{{ $catatan->id }}/edit" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>

                <form action="/catatan/{{ $catatan->id }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-danger"
                        onclick="bukaModalHapus('/catatan/{{ $catatan->id }}', '{{ addslashes($catatan->judul) }}')">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection