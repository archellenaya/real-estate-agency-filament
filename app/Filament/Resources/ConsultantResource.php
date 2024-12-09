<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsultantResource\Pages;
use App\Filament\Resources\ConsultantResource\RelationManagers;
use App\Models\Consultant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConsultantResource extends Resource
{
    protected static ?string $model = Consultant::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('agent_code')
                    ->maxLength(255),
                Forms\Components\TextInput::make('full_name_field')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image_file_name_field')
                    ->image(),
                Forms\Components\FileUpload::make('image_name_field')
                    ->image(),
                Forms\Components\TextInput::make('branch_id_field')
                    ->numeric(),
                Forms\Components\Textarea::make('description_field')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('designation_field')
                    ->maxLength(50),
                Forms\Components\TextInput::make('contact_number_field')
                    ->maxLength(50),
                Forms\Components\TextInput::make('whatsapp_number_field')
                    ->maxLength(50),
                Forms\Components\TextInput::make('email_field')
                    ->email()
                    ->maxLength(300),
                Forms\Components\Toggle::make('is_available'),
                Forms\Components\TextInput::make('old_id')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('office_phone_field')
                    ->tel()
                    ->maxLength(50),
                Forms\Components\FileUpload::make('orig_consultant_image_src')
                    ->image(),
                Forms\Components\TextInput::make('external_id')
                    ->maxLength(255),
                Forms\Components\Toggle::make('to_synch')
                    ->required(),
                Forms\Components\Textarea::make('data')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image_status_field')
                    ->image()
                    ->required(),
                Forms\Components\TextInput::make('url_field')
                    ->maxLength(1024),
                Forms\Components\TextInput::make('optimization_retries')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('agent_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name_field')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image_file_name_field'),
                Tables\Columns\ImageColumn::make('image_name_field'),
                Tables\Columns\TextColumn::make('branch_id_field')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('designation_field')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_number_field')
                    ->searchable(),
                Tables\Columns\TextColumn::make('whatsapp_number_field')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_field')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_available')
                    ->boolean(),
                Tables\Columns\TextColumn::make('old_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('office_phone_field')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('orig_consultant_image_src'),
                Tables\Columns\TextColumn::make('external_id')
                    ->searchable(),
                Tables\Columns\IconColumn::make('to_synch')
                    ->boolean(),
                Tables\Columns\ImageColumn::make('image_status_field'),
                Tables\Columns\TextColumn::make('url_field')
                    ->searchable(),
                Tables\Columns\TextColumn::make('optimization_retries')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListConsultants::route('/'),
            'create' => Pages\CreateConsultant::route('/create'),
            'edit' => Pages\EditConsultant::route('/{record}/edit'),
        ];
    }
}
