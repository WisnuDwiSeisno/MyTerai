@extends('layouts.app')

@section('title', 'Detail Materai')

@section('content')

    <div class="mb-3">
        <a href="{{ route('dashboard.index') }}" class="text-decoration-none small text-muted">
            <i class="bi bi-arrow-left"></i> Kembali ke Data Materai
        </a>
    </div>

    <div class="row g-3">
        {{-- Kiri: info utama --}}
        <div class="col-lg-7">
            <div class="card p-4 mb-3">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted small mb-1">Nomor Seri Materai</div>
                        <div class="font-mono fs-5 fw-bold">
                            {{ $materai->nomor_seri ?? '— Tidak Terbaca —' }}
                        </div>
                    </div>
                    <span class="badge-soft badge-status-{{ $materai->status }} fs-6">
                        @switch($materai->status)
                            @case('unik') Unik @break
                            @case('duplikat') Duplikat @break
                            @default Tidak Terbaca
                        @endswitch
                    </span>
                </div>

                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 180px;">Jenis Materai</td>
                            <td>
                                @if ($materai->jenis)
                                    <span class="badge-soft badge-jenis-{{ $materai->jenis }}">{{ ucfirst($materai->jenis) }}</span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nama File</td>
                            <td>{{ $materai->nama_file }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Diunggah Oleh</td>
                            <td>{{ $materai->apiClient->nama ?? 'Dashboard Admin' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Confidence OCR</td>
                            <td>
                                @if (!is_null($materai->confidence))
                                    {{ $materai->confidence }}%
                                    <div class="progress mt-1" style="height: 6px; max-width: 200px;">
                                        <div class="progress-bar bg-dark" style="width: {{ $materai->confidence }}%"></div>
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Diproses</td>
                            <td>{{ $materai->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jumlah Diduplikasi</td>
                            <td>{{ $materai->duplikat_count }} kali</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Raw OCR Text --}}
            @if ($materai->raw_text)
                <div class="card p-4 mb-3">
                    <div class="fw-semibold small mb-2">
                        <i class="bi bi-file-text"></i> Teks Mentah Hasil OCR
                    </div>
                    <pre class="font-mono small bg-light p-3 rounded mb-0" style="max-height: 220px; overflow:auto; white-space: pre-wrap;">{{ $materai->raw_text }}</pre>
                </div>
            @endif

            {{-- Duplikasi: data asli --}}
            @if ($materai->duplikatDari)
                <div class="card p-4 mb-3 border-warning-subtle">
                    <div class="fw-semibold small mb-2 text-warning-emphasis">
                        <i class="bi bi-exclamation-triangle"></i> Data ini terindikasi duplikat dari:
                    </div>
                    <a href="{{ route('dashboard.show', $materai->duplikatDari->id) }}" class="text-decoration-none">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                            <div>
                                <div class="font-mono small fw-semibold">{{ $materai->duplikatDari->nomor_seri }}</div>
                                <div class="text-muted small">{{ $materai->duplikatDari->nama_file }}</div>
                            </div>
                            <i class="bi bi-arrow-up-right"></i>
                        </div>
                    </a>
                </div>
            @endif

            {{-- Duplikasi: daftar yang menduplikasi data ini --}}
            @if ($materai->duplikatList->isNotEmpty())
                <div class="card p-4 mb-3 border-warning-subtle">
                    <div class="fw-semibold small mb-2 text-warning-emphasis">
                        <i class="bi bi-files"></i> {{ $materai->duplikatList->count() }} data lain menduplikasi nomor seri ini:
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach ($materai->duplikatList as $dup)
                            <a href="{{ route('dashboard.show', $dup->id) }}"
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2">
                                <div>
                                    <div class="small fw-semibold">{{ $dup->nama_file }}</div>
                                    <div class="text-muted small">
                                        oleh {{ $dup->apiClient->nama ?? 'Dashboard Admin' }} &middot;
                                        {{ $dup->created_at->format('d M Y H:i') }}
                                    </div>
                                </div>
                                <i class="bi bi-arrow-up-right"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Hapus --}}
            <form action="{{ route('dashboard.destroy', $materai->id) }}" method="POST"
                  onsubmit="return confirm('Hapus data materai ini secara permanen?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i> Hapus Data Ini
                </button>
            </form>
        </div>

        {{-- Kanan: preview gambar --}}
        <div class="col-lg-5">
            <div class="card p-3">
                <div class="fw-semibold small mb-2">
                    <i class="bi bi-image"></i> Pratinjau Halaman Dokumen
                </div>
                @if ($materai->path_file)
                    <iframe
                        src="{{ route('dashboard.file', $materai->id) }}"
                        width="100%"
                        height="700"
                        class="border rounded">
                    </iframe>

                    <div class="mt-2">
                        <a href="{{ route('dashboard.file', $materai->id) }}"
                        target="_blank"
                        class="btn btn-sm btn-primary">
                            <i class="bi bi-box-arrow-up-right"></i>
                            Buka File
                        </a>
                    </div>
                @endif

                @if ($materai->qr_image_url)
                    <div class="fw-semibold small mt-3 mb-2">
                        <i class="bi bi-qr-code"></i> Area E-Materai Terdeteksi
                    </div>
                    <img src="{{ $materai->qr_image_url }}" class="img-fluid rounded border" alt="Area e-materai">
                @endif
            </div>
        </div>
    </div>

@endsection
