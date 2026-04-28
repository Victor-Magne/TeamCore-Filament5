<?php

/**
 * Ficheiro do Modelo Designation.
 *
 * Este modelo define os cargos ou funções existentes na organização.
 * Estabelece a nomenclatura do cargo, o nível hierárquico/experiência e o
 * salário base de referência para essa posição.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Designation extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * Campos preenchíveis.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',        // Nome do cargo (ex: Engenheiro de Software, Gestor de RH)
        'level',       // Nível (ex: Junior, Pleno, Sénior)
        'base_salary'  // Salário base de referência para esta designação
    ];

    /**
     * Conversões de tipos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'base_salary' => 'decimal:2', // Precisão decimal para valores monetários
    ];

    /**
     * Relacionamento: Funcionários.
     *
     * Lista todos os funcionários que possuem esta designação/cargo.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
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
