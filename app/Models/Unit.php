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
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Unit extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * Nome da tabela associada.
     *
     * @var string
     */
    protected $table = 'organizational_units';

    /**
     * Campos preenchíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',              // Nome da unidade (ex: Departamento Financeiro)
        'type',              // Tipo de unidade (direction, department, section)
        'description',       // Descrição das responsabilidades da unidade
        'parent_id',         // ID da unidade pai (para hierarquia)
        'manager_id',        // ID do funcionário que é o gestor directo
        'is_main_direction', // Flag para indicar se é a direcção principal da empresa
    ];

    /**
     * Relacionamento: Unidade Pai.
     *
     * Permite subir na hierarquia organizacional.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'parent_id');
    }

    /**
     * Relacionamento: Unidades Filhas (Sub-unidades).
     *
     * Permite obter todas as divisões que pertencem a esta unidade.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Unit::class, 'parent_id');
    }

    /**
     * Relacionamento: Gestor Principal.
     *
     * Liga ao funcionário que detém a responsabilidade máxima sobre a unidade.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * Relacionamento: Funcionários da Unidade.
     *
     * Obtém todos os funcionários alocados a esta unidade específica.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'unit_id');
    }

    /**
     * Relacionamento: Gestores (BelongsToMany).
     *
     * Caso a unidade tenha múltiplos gestores ou para manter histórico
     * de gestão via tabela pivot 'unit_manager'.
     */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'unit_manager', 'unit_id', 'employee_id');
    }

    /**
     * Obtém todos os IDs das unidades descendentes de forma recursiva.
     *
     * Útil para filtros de visibilidade hierárquica.
     *
     * @return array<int>
     */
    public function getAllDescendantIds(): array
    {
        $ids = [];

        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }

        return $ids;
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
