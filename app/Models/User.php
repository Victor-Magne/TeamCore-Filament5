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
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

/**
 * Definição dos campos preenchíveis e ocultos utilizando Atributos PHP 8.
 */
#[Fillable([
    'name',                 // Nome do utilizador
    'email',                // Endereço de email (usado para login)
    'password',             // Senha (armazenada de forma segura)
    'employee_id',          // Ligação ao registo de Funcionário correspondente
    'must_change_password', // Flag para forçar a troca de senha no primeiro login
    'two_factor_enabled',   // Flag para forçar 2FA
])]
#[Hidden([
    'password',             // Nunca expor a senha em serializações (JSON/Arrays)
    'remember_token',       // Token para funcionalidade "lembrar-me"
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, LogsActivity, Notifiable, TwoFactorAuthenticatable;

    /**
     * Define as tipagens (casts) dos atributos para garantir o comportamento correcto do PHP.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',             // Garante que a senha é encriptada automaticamente ao guardar
            'must_change_password' => 'boolean', // Converte 0/1 da DB para true/false
            'two_factor_enabled' => 'boolean',   // Converte 0/1 da DB para true/false
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
