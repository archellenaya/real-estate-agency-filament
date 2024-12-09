<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileResource\Pages;
use App\Filament\Resources\FileResource\RelationManagers;
use App\Models\File;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FileResource extends Resource
{
    protected static ?string $model = File::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('property_id')
                    ->relationship('property', 'id')
                    ->required(),
                Forms\Components\TextInput::make('original_file_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('file_name_field')
                    ->maxLength(255),
                Forms\Components\TextInput::make('file_type_field'),
                Forms\Components\TextInput::make('sequence_no_field')
                    ->numeric(),
                Forms\Components\TextInput::make('mime')
                    ->maxLength(255),
                Forms\Components\TextInput::make('seo_url_field')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('orig_image_src')
                    ->image(),
                Forms\Components\TextInput::make('url_field')
                    ->maxLength(255),
                Forms\Components\TextInput::make('optimization_retries')
                    ->numeric(),
                Forms\Components\FileUpload::make('image_status_field')
                    ->image()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('property.id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('original_file_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('file_name_field')
                    ->searchable(),
                Tables\Columns\TextColumn::make('file_type_field'),
                Tables\Columns\TextColumn::make('sequence_no_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mime')
                    ->searchable(),
                Tables\Columns\TextColumn::make('seo_url_field')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('orig_image_src'),
                Tables\Columns\TextColumn::make('url_field')
                    ->searchable(),
                Tables\Columns\TextColumn::make('optimization_retries')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image_status_field'),
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
            'index' => Pages\ListFiles::route('/'),
            'create' => Pages\CreateFile::route('/create'),
            'edit' => Pages\EditFile::route('/{record}/edit'),
        ];
    }
}
