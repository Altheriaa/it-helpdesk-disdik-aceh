<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Tiket Support</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #333; }

        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px double #333; padding-bottom: 15px; }
        .header h1 { font-size: 16px; font-weight: bold; margin-bottom: 2px; }
        .header h2 { font-size: 13px; font-weight: bold; margin-bottom: 5px; }
        .header p { font-size: 10px; color: #666; }

        .meta { margin-bottom: 15px; font-size: 10px; }
        .meta table { width: 100%; }
        .meta td { padding: 2px 5px; }
        .meta .label { font-weight: bold; width: 120px; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data th, table.data td { border: 1px solid #999; padding: 5px 6px; text-align: left; font-size: 10px; }
        table.data th { background-color: #2563eb; color: #fff; font-weight: bold; text-align: center; }
        table.data tr:nth-child(even) { background-color: #f3f4f6; }
        table.data td.center { text-align: center; }

        .badge { padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; color: #fff; }
        .badge-open { background-color: #6b7280; }
        .badge-in_progress { background-color: #f59e0b; }
        .badge-resolved { background-color: #10b981; }
        .badge-closed { background-color: #ef4444; }
        .badge-low { background-color: #9ca3af; }
        .badge-medium { background-color: #f59e0b; }
        .badge-high { background-color: #ef4444; }
        .badge-critical { background-color: #2563eb; }

        .footer { margin-top: 30px; font-size: 10px; text-align: center; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        .summary { margin-bottom: 15px; }
        .summary td { padding: 3px 8px; font-size: 11px; }
        .summary .count { font-weight: bold; font-size: 13px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DINAS PENDIDIKAN PROVINSI ACEH</h1>
        <h2>LAPORAN TIKET SUPPORT</h2>
        <p>E-Helpdesk Support System</p>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td class="label">Periode</td>
                <td>: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d M Y') : 'Semua' }} — {{ $dateUntil ? \Carbon\Carbon::parse($dateUntil)->format('d M Y') : 'Sekarang' }}</td>
            </tr>
            <tr>
                <td class="label">Dicetak pada</td>
                <td>: {{ $generatedAt }}</td>
            </tr>
            <tr>
                <td class="label">Total Tiket</td>
                <td>: {{ $tickets->count() }} tiket</td>
            </tr>
        </table>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td><span class="badge badge-open">Open</span></td>
                <td class="count">{{ $tickets->where('status', 'open')->count() }}</td>
                <td><span class="badge badge-in_progress">In Progress</span></td>
                <td class="count">{{ $tickets->where('status', 'in_progress')->count() }}</td>
                <td><span class="badge badge-resolved">Resolved</span></td>
                <td class="count">{{ $tickets->where('status', 'resolved')->count() }}</td>
                <td><span class="badge badge-closed">Closed</span></td>
                <td class="count">{{ $tickets->where('status', 'closed')->count() }}</td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Pegawai</th>
                <th>Bidang</th>
                <th>Subjek</th>
                <th style="width: 60px;">Prioritas</th>
                <th style="width: 70px;">Status</th>
                <th>IT Support</th>
                <th style="width: 80px;">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tickets as $index => $ticket)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $ticket->client->user->name ?? '-' }}</td>
                    <td>{{ $ticket->client->division->name ?? '-' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($ticket->subject, 35) }}</td>
                    <td class="center">
                        <span class="badge badge-{{ $ticket->priority }}">
                            {{ match($ticket->priority) { 'low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'critical' => 'Kritis', default => $ticket->priority } }}
                        </span>
                    </td>
                    <td class="center">
                        <span class="badge badge-{{ $ticket->status }}">
                            {{ match($ticket->status) { 'open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed', default => $ticket->status } }}
                        </span>
                    </td>
                    <td>{{ $ticket->support->user->name ?? '-' }}</td>
                    <td class="center">{{ $ticket->created_at->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center" style="padding: 20px;">Tidak ada data tiket.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini digenerate otomatis oleh E-Helpdesk Support System — Dinas Pendidikan Provinsi Aceh</p>
    </div>
</body>
</html>
