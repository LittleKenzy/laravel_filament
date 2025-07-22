<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenjualanResource\Pages;
use App\Models\PenjualanModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PenjualanResource extends Resource
{
    protected static ?string $model = PenjualanModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Laporan Penjualan';

    protected static ?string $navigationGroup = 'Faktur';

    public static ?string $label = 'Laporan Penjualan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_faktur')
                    ->label('Kode Faktur')
                    ->required(),
                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required(),
                Forms\Components\TextInput::make('jumlah')
                    ->label('Jumlah')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'nama_customer')
                    ->required(),
                Forms\Components\Select::make('faktur_id')
                    ->label('Faktur')
                    ->relationship('faktur', 'kode_faktur')
                    ->required(),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan'),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        0 => 'Pending',
                        1 => 'Lunas',
                        2 => 'Batal',
                    ])
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->sortable()
                    ->searchable()
                    ->date('d F Y'),

                TextColumn::make('kode_faktur') // Perbaiki dari 'kode' ke 'kode_faktur'
                    ->sortable()
                    ->searchable()
                    ->label('Kode Faktur'),

                TextColumn::make('jumlah')
                    ->sortable()
                    ->searchable()
                    ->label('Jumlah')
                    ->money('IDR'), // Format sebagai mata uang

                TextColumn::make('customer.nama_customer')
                    ->sortable()
                    ->searchable()
                    ->label('Nama Customer'),

                TextColumn::make('status')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '0' => 'Pending',
                        '1' => 'Lunas', 
                        '2' => 'Batal',
                        default => 'Unknown',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        '0' => 'warning',
                        '1' => 'success',
                        '2' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('faktur.kode_faktur')
                    ->sortable()
                    ->searchable()
                    ->label('Ref Faktur'),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        '0' => 'Pending',
                        '1' => 'Lunas',
                        '2' => 'Batal',
                    ]),
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'], fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date))
                            ->when($data['sampai_tanggal'], fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal', 'desc');
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
            'index' => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
            'view' => Pages\ViewPenjualan::route('/{record}'),
            'edit' => Pages\EditPenjualan::route('/{record}/edit'),
        ];
    }
}