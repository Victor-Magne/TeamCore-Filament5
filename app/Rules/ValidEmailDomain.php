<?php

/**
 * Ficheiro da Regra de Validação ValidEmailDomain.
 *
 * Esta regra personalizada garante que o endereço de e-mail fornecido não só
 * tem um formato básico de e-mail, como também inclui um domínio com TLD
 * válido (ex: .pt, .com, .org), prevenindo erros comuns de introdução de dados.
 */

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidEmailDomain implements ValidationRule
{
    /**
     * Executa a regra de validação.
     *
     * @param  string  $attribute Nome do campo a ser validado
     * @param  mixed   $value Valor introduzido pelo utilizador
     * @param  Closure(string): PotentiallyTranslatedString  $fail Função de retorno em caso de falha
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Expressão regular que verifica:
        // 1. Caracteres permitidos antes do @
        // 2. Presença do símbolo @
        // 3. Nome do domínio
        // 4. Extensão (TLD) com pelo menos 2 caracteres (ex: .pt, .com)
        if (! preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $value)) {
            $fail('O campo :attribute deve ser um e-mail com um domínio válido (ex: .com, .pt).');
        }
    }
}
