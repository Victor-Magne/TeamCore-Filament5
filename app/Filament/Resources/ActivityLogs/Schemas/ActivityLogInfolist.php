<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Carbon\Carbon;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalhes do Evento')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('event')
                            ->label('Evento')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'created' => 'success',
                                'updated' => 'info',
                                'deleted' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'created' => 'Criado',
                                'updated' => 'Atualizado',
                                'deleted' => 'Eliminado',
                                default => $state ?? 'Sistema',
                            }),

                        TextEntry::make('log_name')
                            ->label('Módulo')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('subject_type')
                            ->label('Entidade Afetada')
                            ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—'),

                        TextEntry::make('subject_id')
                            ->label('ID do Registo'),

                        TextEntry::make('causer_id')
                            ->label('Utilizador')
                            ->state(fn ($record): string => optional($record->causer)->name ?? "ID #{$record->causer_id}"),

                        TextEntry::make('created_at')
                            ->label('Data e Hora')
                            ->dateTime('d/m/Y H:i:s'),
                    ]),

                Section::make('Alterações')
                    ->icon('heroicon-o-arrows-right-left')
                    ->schema([
                        TextEntry::make('attribute_changes')
                            ->label('')
                            ->html()
                            ->columnSpanFull()
                            ->formatStateUsing(function ($state): string {
                                if (empty($state) || ! is_array($state)) {
                                    return '<p class="text-sm text-gray-400 italic">Nenhuma alteração registada.</p>';
                                }

                                $old = $state['old'] ?? [];
                                $new = $state['attributes'] ?? [];

                                if (empty($old) && empty($new)) {
                                    return '<p class="text-sm text-gray-400 italic">Nenhuma alteração registada.</p>';
                                }

                                $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));
                                $skipKeys = ['updated_at', 'created_at'];

                                $rows = '';
                                foreach ($allKeys as $key) {
                                    if (in_array($key, $skipKeys)) {
                                        continue;
                                    }

                                    $oldVal = array_key_exists($key, $old) ? self::formatValue($old[$key]) : '—';
                                    $newVal = array_key_exists($key, $new) ? self::formatValue($new[$key]) : '—';

                                    if ($oldVal === $newVal) {
                                        continue;
                                    }

                                    $fieldLabel = self::formatFieldName($key);
                                    $rows .= "<tr class='border-b border-gray-700'>
                                        <td class='py-2 pr-6 text-sm font-medium text-gray-300 whitespace-nowrap'>{$fieldLabel}</td>
                                        <td class='py-2 pr-6 text-sm text-red-400'>{$oldVal}</td>
                                        <td class='py-2 text-sm text-green-400'>{$newVal}</td>
                                    </tr>";
                                }

                                if (! $rows) {
                                    return '<p class="text-sm text-gray-400 italic">Nenhuma alteração registada.</p>';
                                }

                                return "<table class='w-full'>
                                    <thead>
                                        <tr class='border-b border-gray-600'>
                                            <th class='text-left text-xs font-semibold text-gray-500 pb-2 pr-6'>Campo</th>
                                            <th class='text-left text-xs font-semibold text-gray-500 pb-2 pr-6'>Antes</th>
                                            <th class='text-left text-xs font-semibold text-gray-500 pb-2'>Depois</th>
                                        </tr>
                                    </thead>
                                    <tbody>{$rows}</tbody>
                                </table>";
                            }),
                    ]),
            ]);
    }

    private static function formatValue(mixed $value): string
    {
        if (is_null($value)) {
            return '<span class="text-gray-500 italic">nulo</span>';
        }

        if (is_bool($value)) {
            return $value ? 'Sim' : 'Não';
        }

        if (is_array($value)) {
            return '<pre class="text-xs whitespace-pre-wrap">'.e(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)).'</pre>';
        }

        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $value)) {
            try {
                return Carbon::parse($value)->format('d/m/Y H:i');
            } catch (\Exception) {
                return e($value);
            }
        }

        return e((string) $value);
    }

    private static function formatFieldName(string $key): string
    {
        $translations = [
            'balance' => 'Saldo',
            'extra_hours_added' => 'Horas Extra Adicionadas',
            'salary' => 'Salário',
            'status' => 'Estado',
            'name' => 'Nome',
            'email' => 'Email',
            'first_name' => 'Primeiro Nome',
            'last_name' => 'Apelido',
            'phone' => 'Telefone',
            'start_date' => 'Data de Início',
            'end_date' => 'Data de Fim',
            'check_in' => 'Entrada',
            'check_out' => 'Saída',
            'lunch_break_start' => 'Início do Almoço',
            'lunch_break_end' => 'Fim do Almoço',
            'is_active' => 'Conta Ativa',
            'amount' => 'Valor',
            'type' => 'Tipo',
            'description' => 'Descrição',
            'date' => 'Data',
            'notes' => 'Notas',
            'daily_work_minutes' => 'Minutos de Trabalho Diário',
            'contract_type' => 'Tipo de Contrato',
            'role' => 'Função',
            'department' => 'Departamento',
            'base_salary' => 'Salário Base',
            'total_net' => 'Salário Líquido',
            'deductions' => 'Deduções',
            'month_year' => 'Mês de Referência',
        ];

        return $translations[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }
}
