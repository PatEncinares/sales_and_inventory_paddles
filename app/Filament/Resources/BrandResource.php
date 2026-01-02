<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    // ✅ Always set a navigation group + better icon for new resources
    protected static ?string $navigationGroup = 'Catalog';
    protected static ?string $navigationIcon  = 'heroicon-o-tag';
    protected static ?int $navigationSort     = 3;

    public static function getNavigationLabel(): string
    {
        return 'Brands';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Brand Information')
                ->description('Create or update brand details used across products.')
                ->icon('heroicon-o-tag')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Brand Name')
                        ->placeholder('e.g., Michelin, 3M, Meguiar’s')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2)
                        ->live(onBlur: true)
                        ->unique(ignoreRecord: true),

                    Forms\Components\Placeholder::make('created_at')
                        ->label('Created')
                        ->content(fn (?Brand $record) => $record?->created_at?->timezone(config('app.timezone'))->format('M d, Y h:i A') ?? '—')
                        ->visible(fn (?Brand $record) => filled($record)),

                    Forms\Components\Placeholder::make('updated_at')
                        ->label('Last Updated')
                        ->content(fn (?Brand $record) => $record?->updated_at?->timezone(config('app.timezone'))->format('M d, Y h:i A') ?? '—')
                        ->visible(fn (?Brand $record) => filled($record)),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable()
                    ->weight('semi-bold')
                    ->copyable()
                    ->copyMessage('Brand name copied')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'view'   => Pages\ViewBrand::route('/{record}'),
            'edit'   => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
