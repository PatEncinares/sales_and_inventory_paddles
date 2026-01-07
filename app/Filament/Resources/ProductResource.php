<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Brand;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    // ✅ Rule: always group + always change icon for new resource
    protected static ?string $navigationGroup = 'Catalog';
    protected static ?string $navigationIcon  = 'heroicon-o-cube';
    protected static ?int $navigationSort     = 2;

    public static function getNavigationLabel(): string
    {
        return 'Products';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Product Details')
                ->description('Basic information used in listings and orders.')
                ->icon('heroicon-o-cube')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Product Name')
                        ->placeholder('e.g. Volley V1 Pickleball Paddle')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('sku')
                        ->label('SKU')
                        ->placeholder('e.g. VOL-V1-BLK')
                        // ->required()
                        ->maxLength(100)
                        ->unique(ignoreRecord: true),

                    Forms\Components\Select::make('brand_id')
                        ->label('Brand')
                        ->relationship('brand', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('color')
                        ->label('Color')
                        ->placeholder('e.g. Black / Red / Blue')
                        ->maxLength(100),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->inline(false),
                ]),

            Forms\Components\Section::make('Pricing & Inventory')
                ->description('Keep pricing and stock accurate to avoid overselling.')
                ->icon('heroicon-o-banknotes')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('price')
                        ->label('Price')
                        ->numeric()
                        // ->required()
                        ->prefix('₱')
                        ->minValue(0)
                        ->rule('regex:/^\d+(\.\d{1,2})?$/'),

                    Forms\Components\TextInput::make('cost')
                        ->label('Cost (Optional)')
                        ->numeric()
                        ->prefix('₱')
                        ->minValue(0)
                        ->rule('regex:/^\d+(\.\d{1,2})?$/'),

                    Forms\Components\TextInput::make('stock_qty')
                        ->label('Stock Qty')
                        ->numeric()
                        ->required()
                        ->default(0)
                        ->minValue(0),
                ]),

            Forms\Components\Section::make('Description')
                ->description('Short specs / notes for staff and customers.')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Forms\Components\Textarea::make('description')
                        ->rows(5)
                        ->maxLength(5000)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('System Info')
                ->collapsed()
                ->columns(2)
                ->schema([
                    Forms\Components\Placeholder::make('created_at')
                        ->label('Created')
                        ->content(fn (?Product $record) => $record?->created_at?->timezone(config('app.timezone'))->format('M d, Y h:i A') ?? '—')
                        ->visible(fn (?Product $record) => filled($record)),

                    Forms\Components\Placeholder::make('updated_at')
                        ->label('Last Updated')
                        ->content(fn (?Product $record) => $record?->updated_at?->timezone(config('app.timezone'))->format('M d, Y h:i A') ?? '—')
                        ->visible(fn (?Product $record) => filled($record)),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('semi-bold')
                    ->description(fn (Product $record) => $record->sku, position: 'below'),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('color')
                    ->label('Color')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('PHP', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_qty')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->color(fn (Product $record) => $record->stock_qty <= 0 ? 'danger' : ($record->stock_qty <= 5 ? 'warning' : 'success')),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock (≤ 5)')
                    ->query(fn (Builder $query) => $query->where('stock_qty', '<=', 5)),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Out of Stock')
                    ->query(fn (Builder $query) => $query->where('stock_qty', '<=', 0)),
            ])
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
            // Later: ProductImagesRelationManager, StockMovementsRelationManager, etc.
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view'   => Pages\ViewProduct::route('/{record}'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
