<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string|\UnitEnum|null $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Site Settings';
    protected static ?int $navigationSort     = 10;
    protected string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin');
    }

    public function mount(): void
    {
        $this->form->fill(SiteSetting::instance()->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Branding')
                    ->description('Logo and clinic identity shown across the system.')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Clinic Logo')
                            ->image()
                            ->directory('logos')
                            ->disk('public')
                            ->visibility('public')
                            ->imagePreviewHeight('80')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'])
                            ->helperText('PNG with transparent background recommended. Max 2MB.')
                            ->columnSpanFull(),
                        TextInput::make('clinic_name')
                            ->label('Clinic Name')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('tagline')
                            ->label('Tagline')
                            ->placeholder('Your Trusted Dental Care Partner')
                            ->maxLength(150),
                    ])->columns(2),

                Section::make('Contact Information')
                    ->description('Displayed in the patient portal, print templates, and footer.')
                    ->schema([
                        TextInput::make('address')->maxLength(200),
                        TextInput::make('city')->maxLength(100),
                        TextInput::make('phone')->tel()->maxLength(50),
                        TextInput::make('email')->email()->maxLength(100),
                        TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->placeholder('https://facebook.com/yourclinic')
                            ->maxLength(255),
                        TextInput::make('footer_text')
                            ->label('Footer Text')
                            ->placeholder('© 2026 DentCare. All rights reserved.')
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        SiteSetting::instance()->update($data);

        Notification::make()
            ->title('Settings saved successfully!')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
