@extends('layouts.app')

@section('title', 'API Client')

@push('styles')
    <style>
        /* ──────────────────────────────────────────────
           FILTER / HEADER BAR
        ────────────────────────────────────────────── */
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .toolbar .search-group {
            position: relative;
        }

        .toolbar .search-group i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: .8rem;
            color: #adb5bd;
            pointer-events: none;
        }

        .toolbar .search-input {
            width: 240px;
            border-radius: 9px;
            border-color: rgba(0, 0, 0, .12);
            font-size: .82rem;
            padding-left: 34px;
            transition: border-color .15s, box-shadow .15s;
        }

        .toolbar .search-input:focus {
            border-color: #495057;
            box-shadow: 0 0 0 3px rgba(73, 80, 87, .1);
        }

        .btn-add {
            border-radius: 9px;
            font-size: .8rem;
            font-weight: 600;
            padding: 6px 14px;
            background: #1a1a1a;
            border-color: #1a1a1a;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            transition: background .15s, transform .1s;
        }

        .btn-add:hover {
            background: #333;
            transform: translateY(-1px);
        }

        /* ──────────────────────────────────────────────
           TABLE CARD
        ────────────────────────────────────────────── */
        .table-card {
            border: 1px solid rgba(0, 0, 0, .07);
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
            overflow: hidden;
        }

        .table-card .table {
            margin-bottom: 0;
            font-size: .83rem;
        }

        .table-card thead th {
            background: #f8f9fa;
            border-bottom: 1.5px solid rgba(0, 0, 0, .08);
            color: #6c757d;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            padding: 11px 14px;
            white-space: nowrap;
        }

        .table-card tbody td {
            padding: 13px 14px;
            vertical-align: middle;
            border-color: rgba(0, 0, 0, .05);
        }

        .table-card tbody tr {
            transition: background .1s ease;
        }

        .table-card tbody tr:hover td {
            background: rgba(0, 0, 0, .018);
        }

        .table-card tbody tr:last-child td {
            border-bottom: 0;
        }

        /* ──────────────────────────────────────────────
           CLIENT NAME CELL
        ────────────────────────────────────────────── */
        .client-name-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .client-avatar {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: #f1f3f5;
            color: #495057;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .78rem;
            flex-shrink: 0;
            text-transform: uppercase;
        }

        .client-name {
            font-weight: 600;
            color: #212529;
            font-size: .85rem;
            line-height: 1.3;
        }

        .client-desc {
            font-size: .72rem;
            color: #adb5bd;
            margin-top: 1px;
        }

        /* ──────────────────────────────────────────────
           API KEY CELL
        ────────────────────────────────────────────── */
        .api-key-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f8f9fa;
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 7px;
            padding: 4px 10px;
            font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
            font-size: .74rem;
            color: #495057;
            letter-spacing: .02em;
        }

        .api-key-pill i {
            font-size: .7rem;
            color: #adb5bd;
        }

        /* ──────────────────────────────────────────────
           BADGES (consistent with dashboard theme)
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

        .badge-status-unik {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-status-tidak_terbaca {
            background: #f1f5f9;
            color: #64748b;
        }

        /* ──────────────────────────────────────────────
           FONT MONO
        ────────────────────────────────────────────── */
        .font-mono {
            font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
        }

        /* ──────────────────────────────────────────────
           ACTION BUTTONS
        ────────────────────────────────────────────── */
        .action-group {
            display: inline-flex;
            gap: 5px;
        }

        .action-group .btn {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border-radius: 8px !important;
            font-size: .8rem;
            border: 1px solid rgba(0, 0, 0, .1);
            transition: background .12s, border-color .12s, transform .1s;
        }

        .action-group .btn:hover {
            transform: translateY(-1px);
        }

        .action-group .btn-outline-secondary:hover {
            background: #f1f3f5;
            border-color: rgba(0, 0, 0, .15);
        }

        .action-group .btn-outline-danger:hover {
            background: #fee2e2;
            border-color: #fecaca;
            color: #b91c1c;
        }

        .action-group form {
            display: inline-flex;
        }

        /* ──────────────────────────────────────────────
           EMPTY STATE
        ────────────────────────────────────────────── */
        .empty-state {
            padding: 56px 24px;
            text-align: center;
            color: #adb5bd;
        }

        .empty-state i {
            font-size: 2.4rem;
            display: block;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: .85rem;
            margin: 0;
        }

        /* ──────────────────────────────────────────────
           PAGINATION (consistent with dashboard theme)
        ────────────────────────────────────────────── */
        .pagination-wrapper {
            padding: 14px 18px;
            border-top: 1px solid rgba(0, 0, 0, .06);
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
            border: 1px solid rgba(0, 0, 0, .09);
            text-decoration: none;
            transition: background .12s, border-color .12s, color .12s;
            cursor: pointer;
            line-height: 1;
        }

        .pg-btn:hover:not(:disabled):not(.pg-active) {
            background: #f3f4f6;
            border-color: rgba(0, 0, 0, .15);
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
    </style>
@endpush

@section('content')

    {{-- ── TOOLBAR ───────────────────────────────────────────────────── --}}
    <div class="toolbar">
        <form method="GET" action="{{ route('clients.index') }}" class="search-group">
            <i class="bi bi-search"></i>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                class="form-control form-control-sm search-input" placeholder="Cari nama client...">
        </form>

        <a href="{{ route('clients.create') }}" class="btn btn-add text-white">
            <i class="bi bi-plus-lg"></i> Tambah API Client
        </a>
    </div>

    {{-- ── TABLE ─────────────────────────────────────────────────────── --}}
    <div class="card table-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>API Key</th>
                        <th>Status</th>
                        <th>Terakhir Digunakan</th>
                        <th>Terdaftar</th>
                        <th class="text-end" style="width:130px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            {{-- Nama --}}
                            <td>
                                <div class="client-name-wrap">
                                    <div class="client-avatar">
                                        {{ strtoupper(substr($client->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="client-name">{{ $client->nama }}</div>
                                        @if ($client->keterangan)
                                            <div class="client-desc">{{ $client->keterangan }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- API Key --}}
                            <td>
                                <span class="api-key-pill">
                                    <i class="bi bi-key-fill"></i>
                                    {{ substr($client->api_key, 0, 8) }}••••••••••••
                                </span>
                            </td>

                            {{-- Status --}}
                            <td>
                                @if ($client->is_active)
                                    <span class="badge-soft badge-status-unik">
                                        <i class="bi bi-check-circle-fill" style="font-size:.65rem"></i> Aktif
                                    </span>
                                @else
                                    <span class="badge-soft badge-status-tidak_terbaca">
                                        <i class="bi bi-dash-circle-fill" style="font-size:.65rem"></i> Nonaktif
                                    </span>
                                @endif
                            </td>

                            {{-- Terakhir Digunakan --}}
                            <td class="small text-muted">
                                {{ $client->last_used_at ? $client->last_used_at->diffForHumans() : 'Belum pernah' }}
                            </td>

                            {{-- Terdaftar --}}
                            <td class="small text-muted">
                                {{ $client->created_at->format('d M Y') }}
                            </td>

                            {{-- Aksi --}}
                            <td class="text-end">
                                <div class="action-group">
                                    <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-outline-secondary"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form action="{{ route('clients.regenerate', $client->id) }}" method="POST"
                                        onsubmit="return confirm('Regenerasi API key untuk \'{{ $client->nama }}\'? Key lama akan langsung tidak berlaku.')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary" title="Regenerasi API Key">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('clients.destroy', $client->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus API client \'{{ $client->nama }}\'? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-key"></i>
                                    <p>Belum ada API client terdaftar.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── PAGINATION ───────────────────────────────────────────── --}}
        @if ($clients->hasPages())
            <div class="pagination-wrapper">

                <span class="pagination-info">
                    <span class="pagination-range">{{ $clients->firstItem() }}–{{ $clients->lastItem() }}</span>
                    dari <strong>{{ $clients->total() }}</strong> data
                </span>

                <div class="pagination-nav">

                    @if ($clients->onFirstPage())
                        <button class="pg-btn" disabled><i class="bi bi-chevron-left"></i></button>
                    @else
                        <a href="{{ $clients->previousPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-left"></i></a>
                    @endif

                    @foreach ($clients->getUrlRange(1, $clients->lastPage()) as $page => $url)
                        @if ($page == $clients->currentPage())
                            <span class="pg-btn pg-active">{{ $page }}</span>
                        @elseif (abs($page - $clients->currentPage()) <= 2 || $page == 1 || $page == $clients->lastPage())
                            <a href="{{ $url }}" class="pg-btn">{{ $page }}</a>
                        @elseif (abs($page - $clients->currentPage()) == 3)
                            <span class="pg-ellipsis">···</span>
                        @endif
                    @endforeach

                    @if ($clients->hasMorePages())
                        <a href="{{ $clients->nextPageUrl() }}" class="pg-btn"><i class="bi bi-chevron-right"></i></a>
                    @else
                        <button class="pg-btn" disabled><i class="bi bi-chevron-right"></i></button>
                    @endif

                </div>
            </div>
        @endif
    </div>

@endsection