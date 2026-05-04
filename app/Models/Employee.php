<?php

/**
 * Ficheiro do Modelo Employee.
 *
 * Este é o modelo central da aplicação, representando um funcionário.
 * Contém dados pessoais, contactos, informações fiscais e serve de pivot para
 * quase todas as outras funcionalidades (Contratos, Assiduidade, Férias, Salários, etc.).
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Support\Carbon;

class Employee extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * Campos preenchíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'city_id',          // Cidade de residência
        'unit_id',          // Unidade organizacional / Departamento
        'designation_id',   // Cargo actual
        'first_name',       // Primeiro nome
        'last_name',        // Apelido
        'email',            // Email profissional/pessoal
        'phone_number',     // Contacto telefónico
        'date_of_birth',    // Data de nascimento
        'gender',           // Género
        'nif',              // Número de Identificação Fiscal (Portugal)
        'nss',              // Número de Segurança Social
        'address',          // Morada completa
        'zip_code',         // Código Postal
        'date_hired',       // Data de contratação
        'date_dismissed',   // Data de demissão/saída
        'vacation_balance', // Saldo actual de dias de férias disponíveis
    ];

    /**
     * Conversões de tipos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'date_hired' => 'date',
        'date_dismissed' => 'datetime',
        'vacation_balance' => 'integer',
    ];

    /**
     * Relacionamento: Cidade.
     *
     * Liga o funcionário à sua cidade de residência.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Acessor para o nome completo.
     *
     * Combina o primeiro nome e o apelido para exibição simplificada na UI.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Relacionamento: Unidade Organizacional.
     *
     * Liga o funcionário ao departamento ou secção onde trabalha.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Relacionamento: Designação (Cargo).
     *
     * Indica o cargo actual do funcionário.
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Relacionamento: Utilizador (User).
     *
     * Liga o registo de funcionário a uma conta de utilizador para acesso ao sistema.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * Relacionamento: Contratos.
     *
     * Um funcionário pode ter tido vários contratos ao longo do tempo (ex: renovações).
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Relacionamento: Férias.
     *
     * Histórico de pedidos e períodos de férias.
     */
    public function vacations(): HasMany
    {
        return $this->hasMany(Vacation::class);
    }

    /**
     * Relacionamento: Licenças e Ausências (Justificadas).
     *
     * Registo de baixas médicas, licenças parentais, etc.
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(LeaveAndAbsence::class);
    }

    /**
     * Relacionamento: Registos de Presença.
     *
     * Dados diários de picagem de ponto (Entradas/Saídas).
     */
    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    /**
     * Relacionamento: Banco de Horas.
     *
     * Registo acumulado do saldo de horas.
     */
    public function hourBank(): HasOne
    {
        return $this->hasOne(HourBank::class);
    }

    /**
     * Relacionamento: Ausências (Auditadas).
     *
     * Registos de faltas ou atrasos que resultaram em descontos no banco de horas.
     */
    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }

    /**
     * Relacionamento: Processamento Salarial (Payroll).
     *
     * Histórico de recibos de vencimento processados.
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Relacionamento: Unidades que este funcionário gere.
     */
    public function managedUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'manager_id');
    }

    /**
     * Relacionamento: Unidades que este funcionário gere (via pivot).
     */
    public function managedUnitsViaPivot(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'unit_manager', 'employee_id', 'unit_id');
    }

    /**
     * Obtém todas as unidades geridas pelo funcionário (diretas e via pivot).
     */
    public function getAllManagedUnits(): \Illuminate\Support\Collection
    {
        return $this->managedUnits->merge($this->managedUnitsViaPivot)->unique('id');
    }

    /**
     * Obtém o saldo acumulado total do funcionário.
     *
     * @return int Saldo em minutos (pode ser negativo).
     */
    public function getTotalHourBankBalance(): int
    {
        return $this->hourBank?->balance ?? 0;
    }

    /**
     * Obtém os ganhos e perdas de um mês específico.
     *
     * @param string $monthYear Formato 'Y-m'
     */
    public function getMonthlyHourBankStats(string $monthYear): array
    {
        $month = Carbon::createFromFormat('Y-m', $monthYear);
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $movements = HourBankMovement::where('employee_id', $this->id)
            ->whereBetween('date', [$start, $end])
            ->get();

        return [
            'added' => $movements->where('type', 'addition')->sum('amount'),
            'used' => abs($movements->where('type', 'deduction')->sum('amount')),
        ];
    }

    /**
     * Configuração do log de actividades.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(class_basename($this));
    }
}
