<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FakturResource\Pages;
use App\Models\Barang;
use App\Models\CustomerModel;
use App\Models\FakturModel;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FakturResource extends Resource
{
    protected static ?string $model = FakturModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $connectionName = null;

    public static function setConnection(string $connection): void
    {
        static::$connectionName = $connection;
    }

    public static function getConnectionName(): ?string
    {
        return static::$connectionName ?? (new static::$model)->getConnectionName();
    }

    public static function newModel(): \Illuminate\Database\Eloquent\Model
    {
        $modelClass = static::$model;
        $model = new $modelClass();

        if (static::$connectionName) {
            $model->setConnection(static::$connectionName);
        }

        return $model;
    }

    public static function newQuery(): Builder
    {
        return static::newModel()->newQuery();
    }

    public static function qualifyColumn(string $column): string
    {
        return (new static::$model)->qualifyColumn($column);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('kode_faktur')
                    ->columnSpan(2),
                DatePicker::make('tanggal_faktur')
                    ->columnSpan([
                        'default' => 2,
                        'md' => 1,
                        'lg' => 1,
                        'xl' => 1,
                    ]),
                Select::make('customer_id')
                    ->reactive()
                    ->relationship('customer', 'nama_customer')
                    ->columnSpan([
                        'default' => 2,
                        'md' => 1,
                        'lg' => 1,
                        'xl' => 1,
                    ])
                    ->afterStateUpdated(function ($state, callable $set) {
                        $customer = CustomerModel::find($state);

                        if ($customer) {
                            $set('kode_customer', $customer->kode_customer);
                        }
                    })
                    ->afterStateHydrated(function ($state, callable $set) {
                        $customer = CustomerModel::find($state);

                        if ($customer) {
                            $set('kode_customer', $customer->kode_customer);
                        }
                    }),
                TextInput::make('kode_customer')
                    ->reactive()
                    ->columnSpan(2),
                Repeater::make('detail')
                    ->relationship()
                    ->schema([
                        Select::make('barang_id')
                            ->columnSpan([
                                'default' => 2,
                                'md' => 1,
                                'lg' => 1,
                                'xl' => 1,
                            ])
                            ->reactive()
                            ->relationship('barang', 'nama_barang')
                            ->afterStateUpdated(function ($state, callable $set) {
                                $barang = Barang::find($state);

                                if ($barang) {
                                    $set('harga', $barang->harga_barang);
                                    $set('nama_barang', $barang->nama_barang);
                                }
                            }),
                        TextInput::make('nama_barang')->columnSpan([
                            'default' => 2,
                            'md' => 1,
                            'lg' => 1,
                            'xl' => 1,
                        ]),
                        TextInput::make('harga')
                            ->numeric()
                            ->prefix('Rp')
                            ->columnSpan([
                                'default' => 2,
                                'md' => 1,
                                'lg' => 1,
                                'xl' => 1,
                            ]),
                        TextInput::make('qty')
                            ->columnSpan([
                                'default' => 2,
                                'md' => 1,
                                'lg' => 1,
                                'xl' => 1,
                            ])
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                $tampungHarga = $get('harga');
                                $set('hasil_qty', intval($state * $tampungHarga));
                            })
                            ->numeric(),
                        TextInput::make('hasil_qty')->numeric()->columnSpan([
                            'default' => 2,
                            'md' => 1,
                            'lg' => 1,
                            'xl' => 1,
                        ]),
                        TextInput::make('diskon')->numeric()->columnSpan([
                            'default' => 2,
                            'md' => 1,
                            'lg' => 1,
                            'xl' => 1,
                        ])
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                $hasil_qty = $get('hasil_qty');
                                $diskon = $hasil_qty * ($state / 100);
                                $hasil = $hasil_qty - $diskon;

                                $set('subtotal', intval($hasil));
                            })
                        ,
                        TextInput::make('subtotal')->numeric()->columnSpan([
                            'default' => 2,
                            'md' => 1,
                            'lg' => 1,
                            'xl' => 1,
                        ]),
                    ])
                    ->live()
                    ->columnSpan(2),
                Textarea::make('ket_faktur')->columnSpan(2),
                TextInput::make('total')
                    ->placeholder(function (Set $set, Get $get) {
                        $detail = collect($get('detail'))->pluck('subtotal')->sum();

                        if ($detail == null) {
                            $set('total', 0);
                        } else {
                            $set('total', $detail);
                        }
                    })
                    ->columnSpan([
                        'default' => 2,
                        'md' => 1,
                        'lg' => 1,
                        'xl' => 1,
                    ]),
                TextInput::make('nominal_charge')->columnSpan([
                    'default' => 2,
                    'md' => 1,
                    'lg' => 1,
                    'xl' => 1,
                ])
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, $state, Get $get) {
                        $total = $get('total');
                        $charge = $total * ($state / 100);
                        $hasil = $total + $charge;

                        $set('total_final', $hasil);
                        $set('charge', $charge);
                    })
                ,
                TextInput::make('charge')->columnSpan(2),
                TextInput::make('total_final')->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_faktur'),
                TextColumn::make('tanggal_faktur'),
                TextColumn::make('kode_customer'),
                TextColumn::make('customer.nama_customer'),
                TextColumn::make('ket_faktur'),
                TextColumn::make('total'),
                TextColumn::make('nominal_charge'),
                TextColumn::make('charge'),
                TextColumn::make('total_final'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListFakturs::route('/'),
            'create' => Pages\CreateFaktur::route('/create'),
            'edit' => Pages\EditFaktur::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
