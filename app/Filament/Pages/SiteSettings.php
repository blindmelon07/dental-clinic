<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

                // ── Branding ──────────────────────────────────────────────
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
                        Select::make('site_theme')
                            ->label('Website Theme')
                            ->helperText('Controls the color scheme of the public-facing website (not the admin panel).')
                            ->options([
                                'light'   => 'Light',
                                'dark'    => 'Dark (Black & Dark Blue)',
                                'luxury'  => 'Luxury (Black & Gold)',
                            ])
                            ->default('light')
                            ->required()
                            ->native(false),
                    ])->columns(2),

                // ── Contact Information ───────────────────────────────────
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

                // ── Clinic Hours ──────────────────────────────────────────
                Section::make('Clinic Hours')
                    ->description('Shown in the footer and contact page.')
                    ->schema([
                        TextInput::make('hours_weekday')
                            ->label('Mon – Fri Hours')
                            ->placeholder('9:00 AM – 6:00 PM')
                            ->maxLength(50),
                        TextInput::make('hours_saturday')
                            ->label('Saturday Hours')
                            ->placeholder('9:00 AM – 2:00 PM')
                            ->maxLength(50),
                        TextInput::make('hours_sunday')
                            ->label('Sunday Hours')
                            ->placeholder('Closed')
                            ->maxLength(50),
                    ])->columns(3),

                // ── Homepage – Hero ───────────────────────────────────────
                Section::make('Homepage – Hero Section')
                    ->description('Main headline and description shown on the home page.')
                    ->schema([
                        TextInput::make('hero_heading')
                            ->label('Heading Line 1')
                            ->placeholder('Healthy Smiles')
                            ->maxLength(80),
                        TextInput::make('hero_subheading')
                            ->label('Heading Line 2 (teal accent)')
                            ->placeholder('Start Here')
                            ->maxLength(80),
                        Textarea::make('hero_description')
                            ->label('Hero Description')
                            ->placeholder('From routine cleanings to advanced cosmetic procedures...')
                            ->rows(3)
                            ->maxLength(300)
                            ->columnSpanFull(),
                    ])->columns(2),

                // ── Homepage – Hero Gallery ───────────────────────────────
                Section::make('Homepage – Hero Gallery')
                    ->description('Up to 4 images that rotate in the hero slideshow. Leave empty to use default placeholder images.')
                    ->schema([
                        FileUpload::make('hero_image_1')
                            ->label('Slide 1')
                            ->image()
                            ->directory('hero-gallery')
                            ->disk('public')
                            ->visibility('public')
                            ->imagePreviewHeight('120')
                            ->maxSize(3072)
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                            ->helperText('Recommended: portrait ratio (4:5), at least 800×1000px.'),
                        FileUpload::make('hero_image_2')
                            ->label('Slide 2')
                            ->image()
                            ->directory('hero-gallery')
                            ->disk('public')
                            ->visibility('public')
                            ->imagePreviewHeight('120')
                            ->maxSize(3072)
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp']),
                        FileUpload::make('hero_image_3')
                            ->label('Slide 3')
                            ->image()
                            ->directory('hero-gallery')
                            ->disk('public')
                            ->visibility('public')
                            ->imagePreviewHeight('120')
                            ->maxSize(3072)
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp']),
                        FileUpload::make('hero_image_4')
                            ->label('Slide 4')
                            ->image()
                            ->directory('hero-gallery')
                            ->disk('public')
                            ->visibility('public')
                            ->imagePreviewHeight('120')
                            ->maxSize(3072)
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp']),
                    ])->columns(4),

                // ── Homepage – Stats ──────────────────────────────────────
                Section::make('Homepage – Trust Stats')
                    ->description('The four statistics shown below the hero buttons and in the services page.')
                    ->schema([
                        TextInput::make('stat_years')
                            ->label('Years Experience')
                            ->placeholder('15+')
                            ->maxLength(20),
                        TextInput::make('stat_patients')
                            ->label('Happy Patients')
                            ->placeholder('10k+')
                            ->maxLength(20),
                        TextInput::make('stat_satisfaction')
                            ->label('Satisfaction Rate')
                            ->placeholder('98%')
                            ->maxLength(20),
                        TextInput::make('stat_emergency')
                            ->label('Emergency Care')
                            ->placeholder('24/7')
                            ->maxLength(20),
                    ])->columns(4),

                // ── Homepage – Testimonial ────────────────────────────────
                Section::make('Homepage – Testimonial')
                    ->description('The patient quote shown in the dark banner on the home page.')
                    ->schema([
                        Textarea::make('testimonial_quote')
                            ->label('Quote')
                            ->placeholder('"The entire team made me feel completely at ease..."')
                            ->rows(3)
                            ->maxLength(400)
                            ->columnSpanFull(),
                        TextInput::make('testimonial_author')
                            ->label('Patient Name')
                            ->placeholder('Maria Santos')
                            ->maxLength(100),
                        TextInput::make('testimonial_since')
                            ->label('Patient Since')
                            ->placeholder('Patient since 2021')
                            ->maxLength(50),
                    ])->columns(2),

                // ── About – Story ─────────────────────────────────────────
                Section::make('About Page – Our Story')
                    ->description('The story section on the About Us page.')
                    ->schema([
                        TextInput::make('about_story_heading')
                            ->label('Story Heading')
                            ->placeholder('Care With Compassion')
                            ->maxLength(100)
                            ->columnSpanFull(),
                        Textarea::make('about_story_body')
                            ->label('Story Body')
                            ->rows(4)
                            ->maxLength(800)
                            ->columnSpanFull(),
                    ]),

                // ── About – Milestones ────────────────────────────────────
                Section::make('About Page – Milestones')
                    ->description('The four timeline milestones on the About Us page.')
                    ->schema([
                        TextInput::make('milestone_1_title')->label('Milestone 1 Title')->maxLength(100),
                        Textarea::make('milestone_1_body')->label('Milestone 1 Description')->rows(2)->maxLength(300),
                        TextInput::make('milestone_2_title')->label('Milestone 2 Title')->maxLength(100),
                        Textarea::make('milestone_2_body')->label('Milestone 2 Description')->rows(2)->maxLength(300),
                        TextInput::make('milestone_3_title')->label('Milestone 3 Title')->maxLength(100),
                        Textarea::make('milestone_3_body')->label('Milestone 3 Description')->rows(2)->maxLength(300),
                        TextInput::make('milestone_4_title')->label('Milestone 4 Title')->maxLength(100),
                        Textarea::make('milestone_4_body')->label('Milestone 4 Description')->rows(2)->maxLength(300),
                    ])->columns(2),

                // ── About – Testimonial ───────────────────────────────────
                Section::make('About Page – Testimonial')
                    ->description('The patient quote shown in the dark banner on the About Us page.')
                    ->schema([
                        Textarea::make('about_testimonial_quote')
                            ->label('Quote')
                            ->placeholder('"From the moment you walk in, you can tell this clinic genuinely cares..."')
                            ->rows(3)
                            ->maxLength(400)
                            ->columnSpanFull(),
                        TextInput::make('about_testimonial_author')
                            ->label('Patient Name')
                            ->placeholder('James Rivera')
                            ->maxLength(100),
                        TextInput::make('about_testimonial_since')
                            ->label('Patient Since')
                            ->placeholder('Patient since 2019')
                            ->maxLength(50),
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
