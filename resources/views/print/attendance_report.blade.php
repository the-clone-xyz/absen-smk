<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .status-hadir { color: green; font-weight: bold; }
        .status-sakit { color: blue; }
        .status-izin { color: orange; }
        .status-alpha { color: red; }
        
        @media print {
            @page { size: A4; margin: 2cm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h1>LAPORAN KEHADIRAN SISWA</h1>
        <p>SMK TAMANSISWA</p>
        <p>Periode: {{ $start_date }} s/d {{ $end_date }}</p>
        <p>Kelas: {{ $kelas_nama }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 15%">Tanggal</th>
                <th style="width: 25%">Nama Siswa</th>
                <th style="width: 15%">Kelas</th>
                <th style="width: 10%">Jam</th>
                <th style="width: 10%">Status</th>
                <th style="width: 20%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->date }}</td>
                <td>{{ $item->user->name }}</td>
                <td>{{ $item->user->student->kelas->name ?? '-' }}</td>
                <td>{{ $item->time_in }}</td>
                <td>
                    <span class="status-{{ strtolower($item->status) }}">
                        {{ $item->status }}
                    </span>
                </td>
                <td>{{ $item->description }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center;">Tidak ada data absensi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right;">
        <p>Dicetak pada: {{ now()->format('d-m-Y H:i') }}</p>
        <br><br><br>
        <p>( .................................... )</p>
        <p>Administrator</p>
    </div>

</body>
</html>