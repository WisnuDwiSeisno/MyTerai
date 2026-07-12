@extends('layouts.app')

@section('title', 'API Key')

@section('content')

    <div class="card p-4" style="max-width: 520px;">
        <div class="d-flex align-items-center gap-2 mb-3 text-success">
            <i class="bi bi-check-circle-fill fs-4"></i>
            <div class="fw-semibold">API key untuk "{{ $client->nama }}" siap digunakan</div>
        </div>

        <label class="form-label small fw-semibold">API Key</label>
        <div class="input-group mb-2">
            <input type="text" id="apiKeyField" class="form-control font-mono" value="{{ $client->api_key }}" readonly>
            <button class="btn btn-outline-secondary" type="button" onclick="copyKey()">
                <i class="bi bi-clipboard"></i> Salin
            </button>
        </div>

        <div class="alert alert-warning small mb-3">
            <i class="bi bi-exclamation-triangle"></i>
            Simpan API key ini sekarang. Setelah meninggalkan halaman ini, key akan
            ditampilkan dalam bentuk tersamar pada daftar API client.
        </div>

        <div class="small text-muted mb-3">
            Sertakan key ini pada setiap request ke API dengan header:
            <pre class="font-mono small bg-light p-2 rounded mt-1 mb-0">X-API-Key: {{ $client->api_key }}</pre>
        </div>

        <a href="{{ route('clients.index') }}" class="btn btn-dark">
            Selesai, kembali ke daftar API Client
        </a>
    </div>

    @push('scripts')
    <script>
        function copyKey() {
            const field = document.getElementById('apiKeyField');
            field.select();
            navigator.clipboard.writeText(field.value);

            const btn = field.nextElementSibling;
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i> Tersalin';
            setTimeout(() => btn.innerHTML = original, 1500);
        }
    </script>
    @endpush

@endsection
