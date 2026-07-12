@extends('layouts.app')

@section('title', 'Data Materai')

@push('styles')
<style>
    /* ──────────────────────────────────────────────
       STAT CARDS
    ────────────────────────────────────────────── */
    .stat-card {
        border: 1px solid rgba(0,0,0,.07);
        border-radius: 14px;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
        transition: transform .15s ease, box-shadow .15s ease;
        position: relative;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,.08);
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: 14px 14px 0 0;
        background: currentColor;
        opacity: .25;
    }
    .stat-card .stat-icon {
        font-size: 1.4rem;
        opacity: .15;
        position: absolute;
        bottom: 10px; right: 14px;
    }
    .stat-card .stat-number {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        letter-spacing: -.03em;
    }
    .stat-card .stat-label {
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #6c757d;
        margin-top: 4px;
    }

    /* ──────────────────────────────────────────────
       FILTER CARD
    ────────────────────────────────────────────── */
    .filter-card {
        border: 1px solid rgba(0,0,0,.07);
        border-radius: 14px;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }
    .filter-card .form-control,
    .filter-card .form-select {
        border-radius: 8px;
        border-color: rgba(0,0,0,.12);
        font-size: .82rem;
        transition: border-color .15s, box-shadow .15s;
    }
    .filter-card .form-control:focus,
    .filter-card .form-select:focus {
        border-color: #495057;
        box-shadow: 0 0 0 3px rgba(73,80,87,.1);
    }
    .filter-card .form-label {
        color: #495057;
        font-size: .7rem;
        letter-spacing: .05em;
        text-transform: uppercase;
        font-weight: 700;
    }

    /* ──────────────────────────────────────────────
       TABLE CARD
    ────────────────────────────────────────────── */
    .table-card {
        border: 1px solid rgba(0,0,0,.07);
        border-radius: 14px;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        overflow: hidden;
    }
    .table-card .table {
        margin-bottom: 0;
        font-size: .83rem;
    }
    .table-card thead th {
        background: #f8f9fa;
        border-bottom: 1.5px solid rgba(0,0,0,.08);
        color: #6c757d;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        padding: 11px 14px;
        white-space: nowrap;
    }
    .table-card tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        border-color: rgba(0,0,0,.05);
    }
    .table-card tbody tr {
        transition: background .1s ease;
    }
    .table-card tbody tr:hover td {
        background: rgba(0,0,0,.018);
    }
    .table-card tbody tr:last-child td {
        border-bottom: 0;
    }

    /* ──────────────────────────────────────────────
       QR THUMBNAIL
    ────────────────────────────────────────────── */
    .qr-thumb {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid rgba(0,0,0,.1);
        cursor: zoom-in;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .qr-thumb:hover {
        transform: scale(1.12);
        box-shadow: 0 4px 12px rgba(0,0,0,.12);
    }
    .qr-preview {
        max-width: 220px;
        border-radius: 10px;
        border: 1px solid rgba(0,0,0,.08);
    }

    /* ──────────────────────────────────────────────
       BADGES
    ────────────────────────────────────────────── */
    .badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .04em;
        white-space: nowrap;
    }
    .badge-status-unik    { background: #d1fae5; color: #065f46; }
    .badge-status-duplikat { background: #fef3c7; color: #92400e; }
    .badge-status-tidak_terbaca { background: #f1f5f9; color: #64748b; }
    .badge-jenis-fisik      { background: #dbeafe; color: #1e40af; }
    .badge-jenis-elektronik { background: #ede9fe; color: #5b21b6; }

    /* ──────────────────────────────────────────────
       CONFIDENCE BADGE
    ────────────────────────────────────────────── */
    .confidence-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: .7rem;
        font-weight: 700;
        border: 1px solid rgba(0,0,0,.1);
        background: #f8f9fa;
        color: #495057;
        min-width: 46px;
        text-align: center;
    }

    /* ──────────────────────────────────────────────
       FONT MONO
    ────────────────────────────────────────────── */
    .font-mono {
        font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
        font-size: .78rem;
        letter-spacing: .01em;
    }

    /* ──────────────────────────────────────────────
       SECTION HEADER
    ────────────────────────────────────────────── */
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .75rem;
    }
    .section-title {
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: #6c757d;
    }

    /* ──────────────────────────────────────────────
       EMPTY STATE
    ────────────────────────────────────────────── */
    .empty-state {
        padding: 56px 24px;
        text-align: center;
        color: #adb5bd;
    }
    .empty-state i { font-size: 2.4rem; display: block; margin-bottom: 10px; }
    .empty-state p { font-size: .85rem; margin: 0; }

    /* ──────────────────────────────────────────────
       MODAL
    ────────────────────────────────────────────── */
    .modal-content {
        border: none;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,.15);
    }
    .modal-header {
        border-bottom: 1px solid rgba(0,0,0,.06);
        padding: 16px 20px 14px;
    }
    .modal-footer {
        border-top: 1px solid rgba(0,0,0,.06);
        padding: 12px 20px;
    }
    .modal-title { font-size: .92rem; font-weight: 700; }

    /* ──────────────────────────────────────────────
       PAGINATION
    ────────────────────────────────────────────── */
    .pagination-wrapper {
        padding: 14px 18px;
        border-top: 1px solid rgba(0,0,0,.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .pagination-info {
        font-size: .75rem;
        color: #9ca3af;
    }
    .pagination-range {
        font-weight: 700;
        color: #374151;
    }
    .pagination-nav {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .pg-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        font-size: .78rem;
        font-weight: 600;
        color: #374151;
        background: transparent;
        border: 1px solid rgba(0,0,0,.09);
        text-decoration: none;
        transition: background .12s, border-color .12s, color .12s;
        cursor: pointer;
        line-height: 1;
    }
    .pg-btn:hover:not(:disabled):not(.pg-active) {
        background: #f3f4f6;
        border-color: rgba(0,0,0,.15);
        color: #111827;
    }
    .pg-btn.pg-active {
        background: #1a1a1a;
        border-color: #1a1a1a;
        color: #fff;
        cursor: default;
        pointer-events: none;
    }
    .pg-btn:disabled {
        opacity: .3;
        cursor: not-allowed;
    }
    .pg-ellipsis {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        font-size: .78rem;
        color: #9ca3af;
        letter-spacing: .05em;
    }

    /* ──────────────────────────────────────────────
       BUTTONS
    ────────────────────────────────────────────── */
    .btn-sm { border-radius: 8px; font-size: .78rem; font-weight: 600; }
    .btn-dark { background: #1a1a1a; border-color: #1a1a1a; }
    .btn-dark:hover { background: #333; border-color: #333; }
    .btn-icon {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 8px;
    }
</style>
@endpush

@section('content')

    {{-- ── STAT CARDS ─────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">

        <div class="col-6 col-md-3">
            <div class="card stat-card p-3" style="color:#212529">
                <div class="stat-number">{{ $summary['total'] }}</div>
                <div class="stat-label">Total Data</div>
                <i class="bi bi-archive stat-icon"></i>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card stat-card p-3" style="color:#065f46">
                <div class="stat-number text-success">{{ $summary['unik'] }}</div>
                <div class="stat-label">Status Unik</div>
                <i class="bi bi-patch-check stat-icon" style="color:#065f46"></i>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card stat-card p-3" style="color:#92400e">
                <div class="stat-number text-warning">{{ $summary['duplikat'] }}</div>
                <div class="stat-label">Terindikasi Duplikat</div>
                <i class="bi bi-copy stat-icon" style="color:#92400e"></i>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card stat-card p-3" style="color:#64748b">
                <div class="stat-number text-secondary">{{ $summary['tidak_terbaca'] }}</div>
                <div class="stat-label">Tidak Terbaca</div>
                <i class="bi bi-eye-slash stat-icon" style="color:#64748b"></i>
            </div>
        </div>

    </div>

    {{-- ── FILTER ───────────────────────────────────────────────────── --}}
    <div class="card filter-card p-3 mb-3">

        <div class="section-header mb-2">
            <span class="section-title"><i class="bi bi-funnel me-1"></i>Filter Data</span>
            @if (collect($filters)->filter()->isNotEmpty())
                <a href="{{ route('dashboard.index') }}"
                   class="btn btn-sm btn-outline-secondary btn-icon"
                   title="Reset filter">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>

        <form method="GET" action="{{ route('dashboard.index') }}">
            <div class="row g-2 align-items-end">

                {{-- Cari Nomor Seri --}}
                <div class="col-12 col-md-4">
                    <label class="form-label">Cari Nomor Seri</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 pe-1"
                              style="border-radius:8px 0 0 8px; border-color:rgba(0,0,0,.12)">
                            <i class="bi bi-search text-muted" style="font-size:.8rem"></i>
                        </span>
                        <input type="text" name="search"
                               value="{{ $filters['search'] ?? '' }}"
                               class="form-control form-control-sm font-mono border-start-0 ps-1"
                               style="border-radius:0 8px 8px 0"
                               placeholder="Contoh: AB1234567890123">
                    </div>
                </div>

                {{-- Status --}}
                <div class="col-6 col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        @foreach (['unik' => 'Unik', 'duplikat' => 'Duplikat', 'tidak_terbaca' => 'Tidak Terbaca'] as $val => $label)
                            <option value="{{ $val }}" @selected(($filters['status'] ?? '') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Jenis --}}
                <div class="col-6 col-md-2">
                    <label class="form-label">Jenis</label>
                    <select name="jenis" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        @foreach (['fisik' => 'Fisik', 'elektronik' => 'Elektronik'] as $val => $label)
                            <option value="{{ $val }}" @selected(($filters['jenis'] ?? '') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Sumber Upload --}}
                <div class="col-12 col-md-3">
                    <label class="form-label">Sumber Upload</label>
                    <select name="client" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="dashboard" @selected(($filters['client'] ?? '') === 'dashboard')>
                            Dashboard Admin
                        </option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected(($filters['client'] ?? '') == $client->id)>
                                {{ $client->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Submit --}}
                <div class="col-12 col-md-1">
                    <button type="submit" class="btn btn-sm btn-dark w-100">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                </div>

            </div>
        </form>
    </div>

    {{-- ── TABLE ────────────────────────────────────────────────────── --}}
    <div class="card table-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nomor Seri</th>
                        <th>Jenis</th>
                        <th class="text-center" style="width:68px">Materai</th>
                        <th>Status</th>
                        <th>Nama File</th>
                        <th>Diunggah Oleh</th>
                        <th class="text-center" style="width:90px">Confidence</th>
                        <th>Tanggal</th>
                        <th style="width:46px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($materais as $materai)
                        <tr>

                            {{-- Nomor Seri --}}
                            <td>
                                <span class="font-mono text-dark">
                                    {{ $materai->nomor_seri ?? '—' }}
                                </span>
                            </td>

                            {{-- Jenis --}}
                            <td>
                                @if ($materai->jenis)
                                    <span class="badge-soft badge-jenis-{{ $materai->jenis }}">
                                        {{ ucfirst($materai->jenis) }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- QR --}}
                            <td class="text-center">
                                @if (!empty($materai->qr_image_url))
                                    <img src="{{ $materai->qr_image_url }}"
                                         class="qr-thumb"
                                         alt="Materai"
                                         data-bs-toggle="modal"
                                         data-bs-target="#modalQr"
                                         data-src="{{ $materai->qr_image_url }}"
                                         title="Klik untuk perbesar">
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="badge-soft badge-status-{{ $materai->status }}">
                                    @switch($materai->status)
                                        @case('unik')
                                            <i class="bi bi-patch-check-fill" style="font-size:.65rem"></i> Unik
                                            @break
                                        @case('duplikat')
                                            <i class="bi bi-exclamation-triangle-fill" style="font-size:.65rem"></i> Duplikat
                                            @break
                                        @default
                                            <i class="bi bi-eye-slash-fill" style="font-size:.65rem"></i> Tidak Terbaca
                                    @endswitch
                                </span>
                                @if ($materai->status === 'duplikat' && $materai->duplikatDari)
                                    <div class="small text-muted mt-1" style="font-size:.7rem">
                                        dari #{{ $materai->duplikatDari->id }}
                                        &middot; {{ $materai->duplikatDari->nama_file }}
                                    </div>
                                @endif
                                @if ($materai->duplikat_count > 0)
                                    <div class="small text-muted mt-1" style="font-size:.7rem">
                                        diduplikasi {{ $materai->duplikat_count }}&times;
                                    </div>
                                @endif
                            </td>

                            {{-- Nama File --}}
                            <td class="small text-dark" style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap">
                                <span title="{{ $materai->nama_file }}">{{ $materai->nama_file }}</span>
                            </td>

                            {{-- Diunggah Oleh --}}
                            <td class="small text-muted">
                                {{ $materai->apiClient->nama ?? 'Dashboard Admin' }}
                            </td>

                            {{-- Confidence --}}
                            <td class="text-center">
                                @if (!is_null($materai->confidence))
                                    <span class="confidence-badge">{{ $materai->confidence }}%</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Tanggal --}}
                            <td class="small text-muted" style="white-space:nowrap">
                                {{ $materai->created_at->format('d M Y') }}
                                <div style="font-size:.68rem; color:#adb5bd">
                                    {{ $materai->created_at->format('H:i') }}
                                </div>
                            </td>

                            {{-- Aksi --}}
                            <td class="text-end">
                                <a href="{{ route('dashboard.show', $materai->id) }}"
                                   class="btn btn-sm btn-outline-secondary btn-icon"
                                   title="Lihat detail">
                                    <i class="bi bi-eye" style="font-size:.8rem"></i>
                                </a>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Belum ada data materai ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($materais->hasPages())
            <div class="pagination-wrapper">

                {{-- Info kiri --}}
                <span class="pagination-info">
                    <span class="pagination-range">{{ $materais->firstItem() }}–{{ $materais->lastItem() }}</span>
                    dari <strong>{{ $materais->total() }}</strong> data
                </span>

                {{-- Navigasi --}}
                <div class="pagination-nav">

                    @if ($materais->onFirstPage())
                        <button class="pg-btn" disabled><i class="bi bi-chevron-left"></i></button>
                    @else
                        <a href="{{ $materais->previousPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-left"></i></a>
                    @endif

                    @foreach ($materais->getUrlRange(1, $materais->lastPage()) as $page => $url)
                        @if ($page == $materais->currentPage())
                            <span class="pg-btn pg-active">{{ $page }}</span>
                        @elseif (abs($page - $materais->currentPage()) <= 2 || $page == 1 || $page == $materais->lastPage())
                            <a href="{{ $url }}" class="pg-btn">{{ $page }}</a>
                        @elseif (abs($page - $materais->currentPage()) == 3)
                            <span class="pg-ellipsis">···</span>
                        @endif
                    @endforeach

                    @if ($materais->hasMorePages())
                        <a href="{{ $materais->nextPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-right"></i></a>
                    @else
                        <button class="pg-btn" disabled><i class="bi bi-chevron-right"></i></button>
                    @endif

                </div>
            </div>
        @endif
    </div>

    {{-- ── MODAL QR ─────────────────────────────────────────────────── --}}
    <div class="modal fade" id="modalQr" tabindex="-1" aria-label="QR E-Materai">
        <div class="modal-dialog modal-dialog-centered" style="max-width:340px">
            <div class="modal-content">

                <div class="modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-qr-code" style="font-size:1.1rem"></i>
                        <h5 class="modal-title mb-0">QR E-Materai</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center py-4">
                    <img id="modalQrImg" src="" class="qr-preview" alt="Materai">
                    <p class="text-muted mt-3 mb-0" style="font-size:.78rem; line-height:1.5">
                        Scan menggunakan <strong>Peruri Scanner</strong><br>
                        untuk mendapatkan nomor seri materai.
                    </p>
                </div>

                <div class="modal-footer">
                    <a id="modalQrDownload" href="#" download
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-download me-1"></i>Download
                    </a>
                    <button type="button" class="btn btn-sm btn-dark" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    document.getElementById('modalQr').addEventListener('show.bs.modal', function (e) {
        const src = e.relatedTarget.dataset.src;
        document.getElementById('modalQrImg').src = src;
        document.getElementById('modalQrDownload').href = src;
    });
</script>
@endpush