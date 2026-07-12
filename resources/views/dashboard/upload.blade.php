@extends('layouts.app')

@section('title', 'Uji Coba Deteksi')

@section('content')

    <p class="text-muted small mb-4" style="max-width: 640px;">
        Halaman ini memungkinkan Admin menguji pipeline deteksi materai secara langsung:
        unggah dokumen PDF/gambar, sistem akan melakukan konversi, preprocessing, OCR,
        dan pengecekan duplikasi — sama seperti yang dilakukan endpoint
        <code>POST /api/materai/upload</code> pada REST API.
    </p>

    @if (!$result)

        {{-- ── Form Upload (belum ada hasil) ── --}}
        <div class="card p-4" style="max-width: 520px;">
            <form action="{{ route('upload.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label class="form-label fw-semibold small">Pilih Dokumen (PDF/JPG/PNG, maks. 10MB)</label>
                <input type="file" name="file" class="form-control mb-3" accept=".pdf,.jpg,.jpeg,.png" required>

                <button type="submit" class="btn btn-dark w-100">
                    <i class="bi bi-cloud-upload"></i> Analisis Dokumen
                </button>
            </form>
        </div>

    @else

        {{-- ── Hasil Pratinjau ── --}}
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card p-4 mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="text-muted small mb-1">Hasil Ekstraksi Nomor Seri</div>
                            <div class="font-mono fs-5 fw-bold">
                                {{ $result['nomor_seri'] ?? '— Tidak Terbaca —' }}
                            </div>
                        </div>
                        <span class="badge-soft badge-status-{{ $result['status'] }} fs-6">
                            @switch($result['status'])
                                @case('unik') Unik @break
                                @case('duplikat') Duplikat @break
                                @default Tidak Terbaca
                            @endswitch
                        </span>
                    </div>

                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 160px;">Jenis Materai</td>
                                <td>
                                    @if ($result['jenis'])
                                        <span class="badge-soft badge-jenis-{{ $result['jenis'] }}">{{ ucfirst($result['jenis']) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Nama File</td>
                                <td>{{ $result['nama_file'] }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Confidence OCR</td>
                                <td>
                                    {{ $result['confidence'] }}%
                                    <div class="progress mt-1" style="height: 6px; max-width: 200px;">
                                        <div class="progress-bar bg-dark" style="width: {{ $result['confidence'] }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Peringatan duplikat --}}
                @if ($result['status'] === 'duplikat' && $result['duplikat_info'])
                    <div class="card p-4 mb-3 border-warning-subtle">
                        <div class="fw-semibold small mb-2 text-warning-emphasis">
                            <i class="bi bi-exclamation-triangle"></i> Nomor seri ini sudah pernah terdaftar!
                        </div>
                        <div class="p-2 bg-light rounded small">
                            <div><strong>Nomor seri:</strong> <span class="font-mono">{{ $result['duplikat_info']['nomor_seri'] }}</span></div>
                            <div><strong>File asli:</strong> {{ $result['duplikat_info']['nama_file'] }}</div>
                            <div><strong>Diunggah oleh:</strong> {{ $result['duplikat_info']['uploaded_by'] }}</div>
                            <div><strong>Tanggal:</strong> {{ $result['duplikat_info']['tanggal'] }}</div>
                        </div>
                        <div class="text-muted small mt-2">
                            Anda tetap dapat menyimpan data ini — sistem akan menandainya sebagai
                            duplikat dan menautkannya ke data asli di atas untuk keperluan audit.
                        </div>
                    </div>
                @endif

                {{-- Raw OCR text --}}
                @if ($result['raw_text'])
                    <div class="card p-4 mb-3">
                        <div class="fw-semibold small mb-2">
                            <i class="bi bi-file-text"></i> Teks Mentah Hasil OCR
                        </div>
                        <pre class="font-mono small bg-light p-3 rounded mb-0" style="max-height: 200px; overflow:auto; white-space: pre-wrap;">{{ $result['raw_text'] }}</pre>
                    </div>
                @endif

                {{-- Form konfirmasi simpan --}}
                <div class="card p-4">
                    <div class="fw-semibold small mb-2">
                        <i class="bi bi-pencil-square"></i> Konfirmasi sebelum menyimpan
                    </div>
                    <form action="{{ route('upload.store') }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <label class="form-label small text-muted mb-1">
                                Nomor Seri (dapat dikoreksi manual jika hasil OCR kurang tepat)
                            </label>
                            <input type="text" name="nomor_seri" class="form-control font-mono"
                                   value="{{ $result['nomor_seri'] }}"
                                   placeholder="Kosongkan jika tidak terbaca">
                        </div>
                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-dark flex-grow-1">
                                <i class="bi bi-save"></i> Simpan ke Basis Data
                            </button>
                        </div>
                    </form>

                    <form action="{{ route('upload.cancel') }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i> Batalkan & Upload Ulang
                        </button>
                    </form>
                </div>
            </div>

            {{-- Preview gambar --}}
            <div class="col-lg-5">
                <div class="card p-3">
                    <div class="fw-semibold small mb-2">
                        <i class="bi bi-image"></i> Pratinjau Halaman Dokumen
                    </div>
                    <img src="{{ $result['image_url'] }}" class="img-fluid rounded border" alt="Preview dokumen">

                    @if ($result['qr_image_url'])
                        <div class="fw-semibold small mt-3 mb-2">
                            <i class="bi bi-qr-code"></i> Area E-Materai Terdeteksi
                        </div>
                        <img src="{{ $result['qr_image_url'] }}" class="img-fluid rounded border" alt="Area e-materai">
                    @endif
                </div>
            </div>
        </div>

    @endif

@endsection
