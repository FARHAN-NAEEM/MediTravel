<?php

namespace App\Filament\Resources\AdminResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'adminActivityLogs';

    protected static ?string $title = 'Activity log';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('action')
            ->columns([
                Tables\Columns\TextColumn::make('action')->badge()->searchable(),
                Tables\Columns\TextColumn::make('description')->wrap()->searchable(),
                Tables\Columns\TextColumn::make('actor.name')->label('Performed by')->placeholder('System'),
                Tables\Columns\TextColumn::make('ip_address')->label('IP')->placeholder('Unavailable'),
                Tables\Columns\TextColumn::make('created_at')->label('Time')->dateTime('M j, Y g:i A')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
