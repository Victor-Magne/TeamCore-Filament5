<?php

/**
 * Ficheiro do Modelo User.
 *
 * Este modelo representa os utilizadores do sistema com acesso autenticado.
 * Integra com o sistema de autenticação do Laravel, gestão de permissões (Spatie Shield),
 * autenticação em dois factores (Filament Breezy) e auditoria de actividade.
 */

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

/**
 * Definição dos campos preenchíveis e ocultos utilizando Atributos PHP 8.
 */
#[Fillable([
    'name',
    'email',
    'password',
    'employee_id',
    'is_active',
    'must_change_password',
    'two_factor_enabled',
])]
#[Hidden([
    'password',             // Nunca expor a senha em serializações (JSON/Arrays)
    'remember_token',       // Token para funcionalidade "lembrar-me"
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPushSubscriptions, HasRoles, LogsActivity, Notifiable, TwoFactorAuthenticatable;

    /**
     * Define as tipagens (casts) dos atributos para garantir o comportamento correcto do PHP.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'two_factor_enabled' => 'boolean',
        ];
    }

    /**
     * Relacionamento: Funcionário.
     *
     * Liga a conta de utilizador aos dados profissionais e pessoais do funcionário.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Configuração do log de actividades.
     *
     * Regista logins, alterações de perfil e outras acções realizadas pelo utilizador.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName(class_basename($this));
    }
}
