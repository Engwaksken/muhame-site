<?php

namespace App\Filament\Resources\Subscribers\Pages;

use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Models\Subscriber;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManageSubscribers extends ManageRecords
{
    protected static string $resource = SubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')->label('Download CSV')->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn (): StreamedResponse => response()->streamDownload(function (): void {
                    $out = fopen('php://output', 'w');
                    fputcsv($out, ['email', 'name', 'subscribed_at']);

                    Subscriber::query()->orderBy('created_at')->chunk(500, function ($subscribers) use ($out): void {
                        foreach ($subscribers as $subscriber) {
                            fputcsv($out, [
                                self::csvSafe($subscriber->email),
                                self::csvSafe($subscriber->name),
                                $subscriber->created_at->toDateTimeString(),
                            ]);
                        }
                    });

                    fclose($out);
                }, 'subscribers-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv'])),
        ];
    }

    /** Stops spreadsheet apps from treating a value as a formula. */
    private static function csvSafe(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
