<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Contratos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
            color: #1a1a1a;
        }

        .header p {
            font-size: 11px;
            color: #666;
        }

        .report-info {
            margin-bottom: 20px;
            font-size: 10px;
        }

        .report-info p {
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table thead tr {
            background-color: #e8e8e8;
        }

        table th {
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }

        table td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 9px;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 2px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            font-size: 10px;
        }

        .summary p {
            margin-bottom: 5px;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            color: #999;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Relatório de Contratos</h1>
            <p>TeamCore HR - Sistema de Gestão de Recursos Humanos</p>
        </div>

        <div class="report-info">
            <p><strong>Data de Geração:</strong> {{ now()->format('d \d\e F \d\e Y \à\s H:i:s') }}</p>
            <p><strong>Total de Contratos:</strong> {{ count($contracts) }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Funcionário</th>
                    <th>Cargo</th>
                    <th>Tipo de Contrato</th>
                    <th>Data Início</th>
                    <th>Data Fim</th>
                    <th>Salário</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contracts as $contract)
                <tr>
                    <td>{{ $contract->employee->full_name }}</td>
                    <td>{{ $contract->designation->name ?? 'N/A' }}</td>
                    <td>{{ ucfirst(str_replace('-', ' ', $contract->type)) }}</td>
                    <td>{{ $contract->start_date->format('d/m/Y') }}</td>
                    <td>{{ $contract->end_date ? $contract->end_date->format('d/m/Y') : 'Indeterminado' }}</td>
                    <td class="text-right">{{ number_format($contract->salary, 2, ',', '.') }}€</td>
                    <td class="text-center">
                        <span class="badge {{ match($contract->status) {
                            'active' => 'badge-success',
                            'terminated' => 'badge-danger',
                            'on_hold' => 'badge-warning',
                            default => ''
                        } }}">
                            {{ match($contract->status) {
                                'active' => 'Ativo',
                                'terminated' => 'Terminado',
                                'on_hold' => 'Suspenso',
                                default => ucfirst($contract->status)
                            } }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <p><strong>Resumo:</strong></p>
            <p>Total de Contratos: {{ count($contracts) }}</p>
            <p>Contratos Ativos: {{ $contracts->where('status', 'active')->count() }}</p>
            <p>Contratos Terminados: {{ $contracts->where('status', 'terminated')->count() }}</p>
            <p>Contratos Suspensos: {{ $contracts->where('status', 'on_hold')->count() }}</p>
        </div>

        <div class="footer">
            <p>Este é um documento gerado automaticamente pelo sistema TeamCore HR.</p>
            <p>Confidencial - Para uso interno apenas</p>
        </div>
    </div>
</body>
</html>
