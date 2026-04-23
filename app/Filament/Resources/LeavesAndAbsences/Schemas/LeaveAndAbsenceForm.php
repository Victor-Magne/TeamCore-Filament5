<?php

namespace App\Filament\Resources\LeavesAndAbsences\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeaveAndAbsenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Funcionário e Tipo')
                ->schema([
                    Select::make('employee_id')
                        ->label('Funcionário')
                        ->relationship('employee', 'first_name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live(),

                    Select::make('type')
                        ->label('Tipo de Ausência')
                        ->options([
                            'sick_leave' => 'Baixa Médica (SNS)',
                            'parental' => 'Licença Parental',
                            'marriage' => 'Licença de Casamento',
                            'bereavement' => 'Nojo (Falecimento)',
                            'justified_absence' => 'Falta Justificada',
                            'unjustified' => 'Falta Injustificada',
                        ])
                        ->required()
                        ->native(false),
                ])->columns(2),

            Section::make('Datas')
                ->schema([
                    DatePicker::make('start_date')
                        ->label('Data de Início')
                        ->required()
                        ->native(false),

                    DatePicker::make('end_date')
                        ->label('Data de Fim')
                        ->required()
                        ->native(false),
                ])->columns(2),

            Section::make('Detalhes')
                ->schema([
                    Textarea::make('reason')
                        ->label('Motivo')
                        ->columnSpanFull(),

                    Toggle::make('is_paid')
                        ->label('É Remunerado')
                        ->default(true),

                    FileUpload::make('justification_doc')
                        ->label('Documento de Justificação')
                        ->directory('leaves')
                        ->columnSpanFull(),
                ]),

            Section::make('Aprovação')
                ->schema([
                    Select::make('status')
                        ->label('Estado')
                        ->options([
                            'pending' => 'Pendente',
                            'approved' => 'Aprovado',
                            'rejected' => 'Rejeitado',
                        ])
                        ->required()
                        ->native(false)
                        ->disabled(fn ($record, callable $get): bool =>
                            (($record && $record->employee_id === auth()->user()?->employee_id) ||
                             ((int) $get('employee_id') === auth()->user()?->employee_id)) &&
                            ! auth()->user()?->can('Approve:OwnLeaveAndAbsence')
                        )
                        ->dehydrated(),

                    Textarea::make('rejection_reason')
                        ->label('Razão da Rejeição')
                        ->visible(fn (callable $get) => $get('status') === 'rejected')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
