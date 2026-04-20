<?php

namespace App\Filament\Resources\AttendanceLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get; // <-- Namespace corrigido
use Filament\Schemas\Components\Utilities\Set; // <-- Namespace corrigido
use Filament\Schemas\Schema;
use Carbon\Carbon;

class AttendanceLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Funcionário')
                ->schema([
                    Select::make('employee_id')
                        ->label('Funcionário')
                        ->relationship('employee', 'first_name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),
                ]),

            Section::make('Horários do Ponto')
                ->description('Registar os 4 momentos do dia: entrada, saída para almoço, volta do almoço e fim do expediente.')
                ->schema([
                    DateTimePicker::make('time_in')
                        ->label('Entrada')
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateTotal($set, $get)),

                    DateTimePicker::make('lunch_break_start')
                        ->label('Saída para Almoço')
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateTotal($set, $get)),

                    DateTimePicker::make('lunch_break_end')
                        ->label('Volta do Almoço')
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateTotal($set, $get)),

                    DateTimePicker::make('time_out')
                        ->label('Fim do Expediente')
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateTotal($set, $get)),
                ])->columns(2),

            Section::make('Tempo Total')
                ->schema([
                    TextInput::make('total_minutes_display')
                        ->label('Tempo Total Calculado')
                        ->readOnly() // <-- Substitui o disabled()/Placeholder
                        ->dehydrated(false) // Não tenta salvar esta string no banco de dados
                        ->default('Preencha entrada e saída')
                        ->extraInputAttributes(['class' => 'font-bold'])
                        ->columnSpanFull(),
                ]),

            Section::make('Observações')
                ->schema([
                    Textarea::make('notes')
                        ->label('Notas')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * Função privada para calcular e atualizar o tempo na tela
     */
    private static function calculateTotal(Set $set, Get $get): void
    {
        $timeIn = $get('time_in');
        $timeOut = $get('time_out');
        $lunchStart = $get('lunch_break_start');
        $lunchEnd = $get('lunch_break_end');

        // Se faltar algum dos campos principais, reseta o valor visual
        if (!$timeIn || !$timeOut) {
            $set('total_minutes_display', 'Preencha entrada e saída');
            return;
        }

        try {
            $timeInObj = Carbon::parse($timeIn);
            $timeOutObj = Carbon::parse($timeOut);

            $totalMinutes = $timeInObj->diffInMinutes($timeOutObj);

            // Subtrair tempo de almoço se preenchido
            if ($lunchStart && $lunchEnd) {
                $lunchStartObj = Carbon::parse($lunchStart);
                $lunchEndObj = Carbon::parse($lunchEnd);
                $lunchMinutes = $lunchStartObj->diffInMinutes($lunchEndObj);
                $totalMinutes -= $lunchMinutes;
            }

            // Calculando horas e minutos
            $hours = intdiv((int) abs($totalMinutes), 60);
            $minutes = abs($totalMinutes) % 60;

            // Injetando o valor formatado de volta no campo
            $set('total_minutes_display', "{$hours}h {$minutes}m ({$totalMinutes} min)");
        } catch (\Exception $e) {
            // Prevenção contra parse de datas incompletas
            $set('total_minutes_display', 'Aguardando formato válido...');
        }
    }
}
