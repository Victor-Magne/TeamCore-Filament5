<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ForceChangePassword extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.force-change-password';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        if (!auth()->user()->must_change_password) {
            redirect()->to(filament()->getHomeUrl());
        }

        $this->form->fill();
    }

    public function getTitle(): string
    {
        return __('Alteração de Password Obrigatória');
    }

    public static function getNavigationLabel(): string
    {
        return __('Alterar Password');
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('new_password')
                    ->label(__('Nova Password'))
                    ->password()
                    ->required()
                    ->rule(Password::default())
                    ->same('new_password_confirmation')
                    ->validationAttribute(__('Nova Password')),
                TextInput::make('new_password_confirmation')
                    ->label(__('Confirmar Nova Password'))
                    ->password()
                    ->required()
                    ->validationAttribute(__('Confirmação da Nova Password')),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('changePassword')
                ->label(__('Alterar Password'))
                ->submit('changePassword'),
        ];
    }

    public function changePassword(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        $user->update([
            'password' => Hash::make($data['new_password']),
            'must_change_password' => false,
        ]);

        Notification::make()
            ->title(__('Password alterada com sucesso!'))
            ->success()
            ->send();

        $this->redirect(filament()->getHomeUrl());
    }
}
