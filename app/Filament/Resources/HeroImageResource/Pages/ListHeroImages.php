<?php

namespace App\Filament\Resources\HeroImageResource\Pages;

use App\Filament\Resources\HeroImageResource;
use App\Models\HeroImage;
use App\Models\Setting;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListHeroImages extends ListRecords
{
    protected static string $resource = HeroImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sliderSettings')
                ->label('Slider timing')
                ->icon('heroicon-o-clock')
                ->fillForm(fn () => ['interval' => HeroImage::slideInterval()])
                ->form([
                    Select::make('interval')->label('Change slide every')
                        ->options([3 => '3 seconds', 5 => '5 seconds'])
                        ->required()->in([3, 5]),
                ])
                ->action(function (array $data): void {
                    abort_unless(HeroImageResource::canCreate(), 403);
                    Setting::updateOrCreate(['key' => 'hero_slide_interval'], ['value' => (string) $data['interval']]);
                    cache()->forget('site_settings');
                    Notification::make()->title('Slider timing saved')->success()->send();
                }),
            Actions\CreateAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        $count = HeroImage::where('is_active', true)->count();

        return $count < 2
            ? 'Add at least two active slides to start automatic playback. A single slide stays visible as a still image.'
            : $count.' active slides. Automatic playback every '.HeroImage::slideInterval().' seconds.';
    }
}
