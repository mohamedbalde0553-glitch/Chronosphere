<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; background: #fff; }
    .header { background: #059669; color: #fff; padding: 14px 20px; margin-bottom: 16px; border-radius: 4px; }
    .header h1 { font-size: 16px; font-weight: bold; }
    .header p { font-size: 10px; opacity: .85; margin-top: 2px; }
    .kpis { display: flex; gap: 10px; margin-bottom: 14px; }
    .kpi { flex: 1; border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px 10px; }
    .kpi-label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: .03em; }
    .kpi-value { font-size: 18px; font-weight: bold; color: #111827; margin-top: 2px; }
    .section-title { font-size: 11px; font-weight: bold; color: #374151; border-bottom: 1px solid #e5e7eb;
                     padding-bottom: 4px; margin-bottom: 8px; margin-top: 14px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f9fafb; font-size: 9px; font-weight: 600; color: #6b7280;
         text-transform: uppercase; letter-spacing: .03em; padding: 5px 8px; text-align: left;
         border-bottom: 1px solid #e5e7eb; }
    td { padding: 4px 8px; border-bottom: 1px solid #f3f4f6; font-size: 9px; }
    tr:nth-child(even) td { background: #f9fafb; }
    .text-right { text-align: right; }
    .badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8px; font-weight: 600; }
    .badge-planned    { background: #dbeafe; color: #1d4ed8; }
    .badge-completed  { background: #dcfce7; color: #15803d; }
    .badge-cancelled  { background: #fee2e2; color: #b91c1c; }
    .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 8px;
              color: #9ca3af; padding: 6px; border-top: 1px solid #e5e7eb; }
    .two-col { display: flex; gap: 14px; }
    .two-col > div { flex: 1; }
</style>
</head>
<body>

<div class="header">
    <h1>Rapport RH — ChronoSphere</h1>
    <p>Période : {{ $start->format('d/m/Y') }} au {{ $end->format('d/m/Y') }} &nbsp;|&nbsp; Généré le {{ now()->format('d/m/Y H:i') }}</p>
</div>

@php
    $totalH   = intdiv($stats['totalWorked'], 60);
    $totalMin = $stats['totalWorked'] % 60;
    $otH      = intdiv($stats['totalOvertime'], 60);
    $otMin    = $stats['totalOvertime'] % 60;
@endphp

<div class="kpis">
    <div class="kpi">
        <div class="kpi-label">Heures travaillées</div>
        <div class="kpi-value">{{ $totalH }}h{{ sprintf('%02d', $totalMin) }}</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Heures sup</div>
        <div class="kpi-value" style="color:#d97706;">{{ $otH }}h{{ sprintf('%02d', $otMin) }}</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Employés actifs</div>
        <div class="kpi-value">{{ $stats['totalEmployees'] }}</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Absentéisme</div>
        <div class="kpi-value" style="color:{{ $stats['absenteeism'] > 10 ? '#dc2626' : '#059669' }};">{{ $stats['absenteeism'] }} %</div>
    </div>
</div>

<div class="two-col">
    <div>
        <div class="section-title">Top 5 employés</div>
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Employé</th><th class="text-right">Heures</th><th class="text-right">Heures sup</th><th class="text-right">Shifts</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['top5'] as $i => $emp)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $emp['name'] }}</td>
                    <td class="text-right">{{ $emp['hours'] }}h</td>
                    <td class="text-right">{{ $emp['overtime'] }}h</td>
                    <td class="text-right">{{ $emp['shifts'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div>
        <div class="section-title">Par département</div>
        <table>
            <thead>
                <tr><th>Département</th><th class="text-right">Shifts</th><th class="text-right">Heures</th></tr>
            </thead>
            <tbody>
                @foreach($stats['byDept'] as $dept => $d)
                <tr>
                    <td>{{ $dept }}</td>
                    <td class="text-right">{{ $d['shifts'] }}</td>
                    <td class="text-right">{{ $d['hours'] }}h</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="section-title">Détail des shifts</div>
<table>
    <thead>
        <tr>
            <th>Employé</th><th>Code</th><th>Département</th><th>Type</th>
            <th>Début</th><th>Fin</th><th class="text-right">Heures</th><th class="text-right">HS</th><th>Statut</th>
        </tr>
    </thead>
    <tbody>
        @foreach($shifts as $s)
        <tr>
            <td>{{ $s['employee'] }}</td>
            <td>{{ $s['code'] }}</td>
            <td>{{ $s['department'] }}</td>
            <td>{{ $s['shift_type'] }}</td>
            <td>{{ $s['start'] }}</td>
            <td>{{ $s['end'] }}</td>
            <td class="text-right">{{ $s['hours'] }}h</td>
            <td class="text-right">{{ $s['overtime'] > 0 ? $s['overtime'].'h' : '—' }}</td>
            <td><span class="badge badge-{{ $s['status'] }}">{{ $s['status'] }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">ChronoSphere &mdash; Rapport généré automatiquement</div>
</body>
</html>
