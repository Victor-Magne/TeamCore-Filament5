<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { color: #333; }
    </style>
</head>
<body>
    <h2>Relatório de Contratos - TeamCore HR</h2>
    <p>Data de emissão: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Funcionário</th>
                <th>Tipo</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Salário</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td>{{ $record->employee->first_name }} {{ $record->employee->last_name }}</td>
                <td>{{ $record->type }}</td>
                <td>{{ $record->start_date->format('d/m/Y') }}</td>
                <td>{{ $record->end_date ? $record->end_date->format('d/m/Y') : 'Indeterminado' }}</td>
                <td>{{ number_format($salary, 2, ',', '.') }}€</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>