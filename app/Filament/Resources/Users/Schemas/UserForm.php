<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    TextInput::make('name')->required()->label('الاسم'),
                    TextInput::make('email')->required()->label('البريد الالكتروني'),
                    TextInput::make('password')
                        ->password()
                        ->same('password_confirmation')
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $context): bool => $context === 'create')
                        ->minLength('6')
                        ->maxLength('20')
                        ->label('كلمة المرور'),
                    TextInput::make('password_confirmation')
                        ->password()
                        ->dehydrated(false)
                        ->required(fn (string $context): bool => $context === 'create')
                        ->label('إعادة كلمة المرور'),
                    CheckboxList::make('roles')
                        ->label('الصلاحية')
                        ->relationship(
                            'roles',
                            'name',
                            fn (Builder $query) => $query
                                ->whereNotIn('name', [
                                    Utils::getPanelUserRoleName(),
                                    ...(! auth()->user()?->hasRole(Utils::getSuperAdminName())
                                        ? [Utils::getSuperAdminName()]
                                        : []),
                                ]),
                        ),
                    Toggle::make('is_active')
                        ->label('تفعيل الحساب')
                        ->default(true)
                        ->disabled(fn (?User $record) => $record?->hasRole('super_admin'))
                        ->helperText('عند إلغاء التفعيل لن يتمكن المستخدم من تسجيل الدخول')
                        ->required(),
                ])->columns(1)->columnSpanFull(),
            ]);
    }
}
