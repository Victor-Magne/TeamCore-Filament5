<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Contrato - {{ $contract->employee->full_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #1a1a1a;
        }

        .header p {
            font-size: 12px;
            color: #666;
        }

        .contract-info {
            margin: 30px 0;
        }

        .info-section {
            margin-bottom: 30px;
        }

        .info-section h3 {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1a1a1a;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
        }

        .info-row {
            display: flex;
            margin-bottom: 10px;
        }

        .info-label {
            width: 35%;
            font-weight: bold;
            color: #555;
        }

        .info-value {
            width: 65%;
            color: #333;
        }

        .table-section {
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table thead tr {
            background-color: #f5f5f5;
        }

        table th {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }

        table td {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 11px;
        }

        table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }

        .signature-block {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 11px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #999;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Contrato de Trabalho</h1>
            <p>Documento gerado em {{ now()->format('d \d\e F \d\e Y \à\s H:i') }}</p>
        </div>

        <div class="contract-info">
            <div class="info-section">
                <h3>Informações do Funcionário</h3>
                <div class="info-row">
                    <span class="info-label">Nome Completo:</span>
                    <span class="info-value">{{ $contract->employee->full_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $contract->employee->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Telemóvel:</span>
                    <span class="info-value">{{ $contract->employee->phone_number ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">NIF:</span>
                    <span class="info-value">{{ $contract->employee->nif ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nº Seg. Social:</span>
                    <span class="info-value">{{ $contract->employee->nss ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="info-section">
                <h3>Detalhes do Contrato</h3>
                <div class="info-row">
                    <span class="info-label">Tipo de Contrato:</span>
                    <span class="info-value">{{ ucfirst(str_replace('-', ' ', $contract->type)) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Designação/Cargo:</span>
                    <span class="info-value">{{ $contract->designation->name ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Data de Início:</span>
                    <span class="info-value">{{ $contract->start_date->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Data de Fim:</span>
                    <span class="info-value">{{ $contract->end_date ? $contract->end_date->format('d/m/Y') : 'Indeterminado' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Salário Base:</span>
                    <span class="info-value">{{ number_format($contract->salary, 2, ',', '.') }}€</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Minutos de Trabalho Diários:</span>
                    <span class="info-value">{{ $contract->daily_work_minutes }} min</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Estado:</span>
                    <span class="info-value">
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
                    </span>
                </div>
            </div>

            <div class="info-section">
                <h3>Departamento</h3>
                <div class="info-row">
                    <span class="info-label">Unidade:</span>
                    <span class="info-value">{{ $contract->employee->unit->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-block">
                <p>Funcionário</p>
                <div class="signature-line">
                    {{ $contract->employee->full_name }}
                </div>
            </div>
            <div class="signature-block">
                <p>Departamento RH</p>
                <div class="signature-line">
                    Data: {{ now()->format('d/m/Y') }}
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Este é um documento gerado automaticamente pelo sistema TeamCore HR.</p>
            <p>Gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
