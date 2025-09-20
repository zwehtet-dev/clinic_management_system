<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\DrugBatch;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\Drugs\DrugResource;
use Filament\Widgets\TableWidget as BaseWidget;

class DrugBatchExpiryWidget extends BaseWidget
{
    protected static ?string $heading = 'Drug Batches Expiring Soon';

    protected static ?int $sort = 8;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DrugBatch::with('drug')
                    ->where('expiry_date', '<=', Carbon::now()->addDays(60))
                    ->where('quantity_available', '>', 0)
                    ->orderBy('expiry_date')
            )
            ->columns([
                TextColumn::make('drug.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('batch_number')
                    ->searchable(),

                TextColumn::make('quantity_received')
                    ->label('Quantity Received')
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('quantity_available')
                    ->label('Remaing Quantity')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->color(function ($record): string {
                        $daysUntilExpiry = Carbon::now()->diffInDays($record->expiry_date, false);

                        if ($daysUntilExpiry < 0) return 'danger'; // Expired
                        if ($daysUntilExpiry <= 7) return 'danger'; // Expires within 7 days
                        if ($daysUntilExpiry <= 30) return 'warning'; // Expires within 30 days
                        return 'success'; // Good
                    }),

                TextColumn::make('days_until_expiry')
                    ->label('Days Until Expiry')
                    ->getStateUsing(function ($record): string {
                        $days = Carbon::now()->diffInDays($record->expiry_date, false);

                        if ($days < 0) return 'EXPIRED';
                        if ($days == 0) return 'Expires today';
                        return $days . ' days';
                    })
                    ->badge()
                    ->color(function ($record): string {
                        $days = Carbon::now()->diffInDays($record->expiry_date, false);

                        if ($days < 0) return 'danger';
                        if ($days <= 7) return 'danger';
                        if ($days <= 30) return 'warning';
                        return 'success';
                    }),
            ])
            ->actions([
                Action::make('view_drug')
                    ->label('View Drug')
                    ->icon('heroicon-o-eye')
                    ->url(fn (DrugBatch $record): string =>
                        DrugResource::getUrl('view', ['record' => $record->drug])
                    ),
            ])
            ->emptyStateHeading('No Batches Expiring Soon')
            ->emptyStateDescription('All drug batches are within safe expiry dates.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->striped()
            ->paginated([5, 10, 15]);
    }
}
