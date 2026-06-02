<?php

namespace App\Providers;

use Filament\Forms\Components\Field;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureUrlForProduction();

        Model::unguard();
        $this->app->setLocale('ar');

        $this->makeFilamentUseTranslatedValidationMessages();
    }

    /**
     * Behind HTTPS reverse proxies, request()->root() may be http:// while links use https://.
     * Filament SPA only adds wire:navigate when URLs match request()->root() (is_app_url).
     */
    private function configureUrlForProduction(): void
    {
        if (! $this->app->environment('production') && ! str_starts_with((string) config('app.url'), 'https://')) {
            return;
        }

        $appUrl = rtrim((string) config('app.url'), '/');

        if ($appUrl !== '') {
            URL::forceRootUrl($appUrl);
        }

        URL::forceScheme('https');
    }

    private function makeFilamentUseTranslatedValidationMessages(): void
    {
        // Make Filament use translated validation messages
        FilamentView::registerRenderHook(
            'panels::body.start',
            fn () => '<script>window.filamentData = window.filamentData || {}; window.filamentData.locale = "ar";</script>'
        );

        // Configure fields to translate labels and use custom validation messages
        Field::configureUsing(function (Field $field): void {
            $field
                ->translateLabel()
                ->validationMessages([
                    'required' => 'حقل :attribute مطلوب',
                    'unique' => ':attribute مستخدم بالفعل',
                    'exists' => 'القيمة المحددة في حقل :attribute غير صالحة',
                    'string' => 'يجب أن يكون حقل :attribute نصًا',
                    'numeric' => 'يجب أن يكون حقل :attribute رقمًا',
                    'date' => 'حقل :attribute ليس تاريخًا صالحًا',
                    'email' => 'يجب أن يكون حقل :attribute عنوان بريد إلكتروني صالح',
                    'min' => 'يجب أن يكون حقل :attribute على الأقل :min',
                    'max' => 'يجب ألا يكون حقل :attribute أكبر من :max',
                ]);
        });
    }
}
