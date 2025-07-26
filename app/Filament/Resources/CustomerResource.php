<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Models\CustomerModel;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = CustomerModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationLabel = 'Kelola Customer';
    protected static ?string $slug = 'Kelola Customer';
    protected static ?string $navigationGroup = 'Kelola';

    public static ?string $label = 'Kelola Customer';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_customer')
                    ->required()
                    ->label('Nama Customer')
                    ->placeholder('Masukkan Nama Customer...')
                ,
                TextInput::make('alamat_customer')
                    ->required()
                    ->label('Alamat Customer')
                    ->placeholder('Alamat Customer')
                ,
                TextInput::make('telepon_customer')
                    ->required()
                    ->numeric()
                    ->label('Nomer Hp Customer')
                    ->placeholder('082********')
                ,
                TextInput::make('kode_customer')
                    ->required()
                    ->placeholder('ABC123')
                    ->label('Kode Customer')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_customer')
                    ->sortable()
                    ->searchable()
                    ->label('Nama')
                ,
                TextColumn::make('telepon_customer')
                    ->label('Nomer Hp'),

                TextColumn::make('alamat_customer')
                    ->label('Alamat'),

                TextColumn::make('kode_customer')
                    ->label('Kode')

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
