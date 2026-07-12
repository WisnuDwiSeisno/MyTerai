@extends('layouts.app')

@section('title', $client->exists ? 'Edit API Client' : 'Tambah API Client')

@section('content')

    <div class="mb-3">
        <a href="{{ route('clients.index') }}" class="text-decoration-none small text-muted">
            <i class="bi bi-arrow-left"></i> Kembali ke API Client
        </a>
    </div>

    <div class="card p-4" style="max-width: 520px;">
        <form action="{{ $client->exists ? route('clients.update', $client->id) : route('clients.store') }}"
              method="POST">
            @csrf
            @if ($client->exists)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label class="form-label small fw-semibold">Nama Client / Platform</label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama', $client->nama) }}"
                       placeholder="Contoh: Web Fakultas Teknik" required>
                @error('nama') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Keterangan (opsional)</label>
                <textarea name="keterangan" class="form-control" rows="3"
                          placeholder="Deskripsi singkat penggunaan API ini">{{ old('keterangan', $client->keterangan) }}</textarea>
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                       value="1" {{ old('is_active', $client->exists ? $client->is_active : true) ? 'checked' : '' }}>
                <label class="form-check-label small" for="is_active">
                    Aktif (jika nonaktif, API key tidak dapat digunakan)
                </label>
            </div>

            @if (!$client->exists)
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle"></i>
                    API key akan dibuat otomatis setelah client disimpan, dan hanya
                    ditampilkan satu kali pada halaman berikutnya.
                </div>
            @endif

            <button type="submit" class="btn btn-dark">
                <i class="bi bi-save"></i> Simpan
            </button>
        </form>
    </div>

@endsection
