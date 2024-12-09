<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Filament\Resources\PropertyResource\RelationManagers;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('old_id')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('slug')
                    ->maxLength(100),
                Forms\Components\TextInput::make('property_ref_field')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('market_status_field')
                    ->maxLength(20),
                Forms\Components\DateTimePicker::make('expiry_date_time'),
                Forms\Components\TextInput::make('market_type_field')
                    ->maxLength(20),
                Forms\Components\Toggle::make('commercial_field')
                    ->required(),
                Forms\Components\TextInput::make('locality_id_field')
                    ->numeric(),
                Forms\Components\TextInput::make('property_type_id_field')
                    ->numeric(),
                Forms\Components\TextInput::make('property_status_id_field')
                    ->numeric(),
                Forms\Components\TextInput::make('price_field')
                    ->numeric(),
                Forms\Components\TextInput::make('old_price_field')
                    ->numeric(),
                Forms\Components\TextInput::make('premium_field')
                    ->numeric(),
                Forms\Components\TextInput::make('rent_period_field')
                    ->maxLength(30),
                Forms\Components\DateTimePicker::make('date_available_field'),
                Forms\Components\Toggle::make('por_field'),
                Forms\Components\Textarea::make('description_field')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('title_field')
                    ->maxLength(200),
                Forms\Components\Textarea::make('long_description_field')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('specifications_field')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('items_included_in_price_field')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('sole_agents_field'),
                Forms\Components\TextInput::make('property_block_id_field')
                    ->numeric(),
                Forms\Components\TextInput::make('bedrooms_field')
                    ->numeric(),
                Forms\Components\TextInput::make('bathrooms_field')
                    ->numeric(),
                Forms\Components\TextInput::make('contact_details_field')
                    ->maxLength(100),
                Forms\Components\Toggle::make('is_property_of_the_month_field')
                    ->required(),
                Forms\Components\Toggle::make('is_featured_field')
                    ->required(),
                Forms\Components\Toggle::make('is_hot_property_field'),
                Forms\Components\DateTimePicker::make('date_on_market_field'),
                Forms\Components\DateTimePicker::make('date_price_reduced_field'),
                Forms\Components\TextInput::make('virtual_tour_url_field')
                    ->maxLength(200),
                Forms\Components\Toggle::make('show_on_3rd_party_sites_field'),
                Forms\Components\Toggle::make('prices_starting_from_field'),
                Forms\Components\TextInput::make('hot_property_title_field')
                    ->maxLength(200),
                Forms\Components\TextInput::make('area_field')
                    ->numeric(),
                Forms\Components\TextInput::make('weight_field')
                    ->numeric(),
                Forms\Components\TextInput::make('consultant_id')
                    ->maxLength(10),
                Forms\Components\TextInput::make('latitude_field')
                    ->numeric(),
                Forms\Components\TextInput::make('longitude_field')
                    ->numeric(),
                Forms\Components\Toggle::make('show_in_searches'),
                Forms\Components\Toggle::make('is_managed_property'),
                Forms\Components\TextInput::make('three_d_walk_through')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('date_off_market_field'),
                Forms\Components\TextInput::make('user_id')
                    ->numeric(),
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name'),
                Forms\Components\TextInput::make('status')
                    ->maxLength(50),
                Forms\Components\TextInput::make('region_field')
                    ->numeric(),
                Forms\Components\DateTimePicker::make('orig_created_at'),
                Forms\Components\TextInput::make('external_area_field')
                    ->numeric(),
                Forms\Components\TextInput::make('internal_area_field')
                    ->numeric(),
                Forms\Components\TextInput::make('plot_area_field')
                    ->numeric(),
                Forms\Components\Toggle::make('to_synch')
                    ->required(),
                Forms\Components\Textarea::make('data')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('meta_data'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('old_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('property_ref_field')
                    ->searchable(),
                Tables\Columns\TextColumn::make('market_status_field')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expiry_date_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('market_type_field')
                    ->searchable(),
                Tables\Columns\IconColumn::make('commercial_field')
                    ->boolean(),
                Tables\Columns\TextColumn::make('locality_id_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('property_type_id_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('property_status_id_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('old_price_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('premium_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rent_period_field')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_available_field')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('por_field')
                    ->boolean(),
                Tables\Columns\TextColumn::make('title_field')
                    ->searchable(),
                Tables\Columns\IconColumn::make('sole_agents_field')
                    ->boolean(),
                Tables\Columns\TextColumn::make('property_block_id_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bedrooms_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bathrooms_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_details_field')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_property_of_the_month_field')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured_field')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_hot_property_field')
                    ->boolean(),
                Tables\Columns\TextColumn::make('date_on_market_field')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_price_reduced_field')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('virtual_tour_url_field')
                    ->searchable(),
                Tables\Columns\IconColumn::make('show_on_3rd_party_sites_field')
                    ->boolean(),
                Tables\Columns\IconColumn::make('prices_starting_from_field')
                    ->boolean(),
                Tables\Columns\TextColumn::make('hot_property_title_field')
                    ->searchable(),
                Tables\Columns\TextColumn::make('area_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('consultant_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latitude_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('longitude_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('show_in_searches')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_managed_property')
                    ->boolean(),
                Tables\Columns\TextColumn::make('three_d_walk_through')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_off_market_field')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('region_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('orig_created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('external_area_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('internal_area_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('plot_area_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('to_synch')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
