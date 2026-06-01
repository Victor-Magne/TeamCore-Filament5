<?php

/**
 * Ficheiro do Modelo Unit.
 *
 * Este modelo define as Unidades Organizacionais da empresa (ex: Direcção,
 * Departamentos, Secções). Suporta uma estrutura hierárquica (parent/child)
 * e associa gestores responsáveis a cada unidade.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Unit extends Model
{
    use HasFactory, LogsActivity, NodeTrait, SoftDeletes;

    protected $table = 'organizational_units';

    protected $fillable = [
        'name',
        'type',
        'description',
        'parent_id',
        'manager_id',
        'is_main_direction',
    ];

    /**
     * Relacionamento: Gestor Principal.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * Relacionamento: Funcionários da Unidade.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'unit_id');
    }

    /**
     * Relacionamento: Gestores (BelongsToMany).
     */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'unit_manager', 'unit_id', 'employee_id');
    }

    /**
     * Obtém todos os IDs das unidades descendentes via nested set (query única).
     *
     * @return array<int>
     */
    public function getAllDescendantIds(): array
    {
        return $this->descendants()->pluck('id')->all();
    }

    /**
     * Verifica se esta unidade é a Direcção Geral.
     */
    public function isGeneralDirection(): bool
    {
        return (bool) $this->is_main_direction;
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
