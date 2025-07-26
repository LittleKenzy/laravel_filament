<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\PenjualanModel;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\PenjualanResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PenjualanResource extends Resource
{
    protected static ?string $model = PenjualanModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Laporan Penjualan';

    protected static ?string $navigationGroup = 'Faktur';

    public static ?string $label = 'Laporan Penjualan';

    public static ?string $slug = 'penjualan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Placeholder::make('kode_faktur')
                    ->label('Kode Faktur')
                    ->content(fn($record) => $record?->faktur?->kode_faktur),
                Placeholder::make('kode_customer')
                    ->label('Kode Customer')
                    ->content(fn($record) => $record?->customer?->kode_customer),
                DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required(),
                TextInput::make('jumlah')
                    ->label('Jumlah')
                    ->numeric()
                    ->required(),
                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'nama_customer')
                    ->required(),
                Select::make('faktur_id')
                    ->label('Faktur')
                    ->relationship('faktur', 'kode_faktur')
                    ->required(),
                Textarea::make('keterangan')
                    ->label('Keterangan'),
                Select::make('status')
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

                TextColumn::make('faktur.kode_faktur') // Use relationship to display kode_faktur
                    ->sortable()
                    ->searchable()
                    ->label('Kode Faktur'),
                TextColumn::make('jumlah')
                    ->sortable()
                    ->searchable()
                    ->label('Jumlah')
                    ->money('IDR'), // Format sebagai mata uang
                TextColumn::make('customer.kode_customer')
                    ->sortable()
                    ->searchable()
                    ->label('Kode Customer'),
                TextColumn::make('customer.nama_customer')
                    ->sortable()
                    ->searchable()
                    ->label('Nama Customer'),

                TextColumn::make('status')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->label('Status')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        '0' => 'Pending',
                        '1' => 'Lunas',
                        '2' => 'Belum Lunas',
                        default => 'Unknown',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        '0' => 'warning',
                        '1' => 'success',
                        '2' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->emptyStateHeading('Tidak Ada Data Laporan')
            ->emptyStateDescription('Silakan tambahkan data laporan')
            ->emptyStateIcon('heroicon-o-presentation-chart-bar')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create Post')
                    ->url(route('filament.admin.resources.Faktur.create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        '0' => 'Pending',
                        '1' => 'Lunas',
                        '2' => 'Batal',
                    ]),
                Filter::make('tanggal')
                    ->form([
                        DatePicker::make('dari_tanggal'),
                        DatePicker::make('sampai_tanggal'),
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
