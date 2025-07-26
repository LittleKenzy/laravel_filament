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

    protected static ?string $navigationGroup = 'Faktur';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Faktur';

    public static ?string $slug = 'Faktur';

    public static ?string $label = 'Faktur';

    protected static ?string $connectionName = null;

    public static function setConnection(string $connection): void
    {
        static::$connectionName = $connection;
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
                    ->disabled()
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
                    ->prefix('Rp')
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state, Get $get) {
                        // Auto calculate total_final when total changes
                        $total = (float) ($state ?? 0);
                        $nominalCharge = (float) ($get('nominal_charge') ?? 0);

                        if ($total > 0) {
                            $charge = $total * ($nominalCharge / 100);
                            $hasil = $total + $charge;

                            $set('charge', (int) $charge);
                            $set('total_final', (int) $hasil);
                        } else {
                            $set('charge', 0);
                            $set('total_final', 0);
                        }
                    })
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
                TextInput::make('nominal_charge')
                    ->columnSpan([
                        'default' => 2,
                        'md' => 1,
                        'lg' => 1,
                        'xl' => 1,
                    ])
                    ->default(0)
                    ->numeric()
                    ->dehydrateStateUsing(fn($state) => $state ?? 0)
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state, Get $get) {
                        $total = (float) ($get('total') ?? 0);
                        $nominalCharge = (float) ($state ?? 0);

                        // Debug - hapus setelah berhasil
                        \Log::info('Debug Calculation:', [
                            'total' => $total,
                            'nominal_charge' => $nominalCharge,
                            'raw_total' => $get('total'),
                            'raw_nominal' => $state
                        ]);

                        if ($total > 0) {
                            $charge = $total * ($nominalCharge / 100);
                            $hasil = $total + $charge;

                            $set('charge', (int) $charge);
                            $set('total_final', (int) $hasil);

                            \Log::info('Set Values:', [
                                'charge' => (int) $charge,
                                'total_final' => (int) $hasil
                            ]);
                        }
                    }),
                TextInput::make('charge')
                    ->prefix('Rp')
                    ->columnSpan(2)
                    ->label('Hasil Charge')
                    ->default(function (Get $get) {
                        // Calculate default charge based on existing data
                        $total = (float) ($get('total') ?? 0);
                        $nominalCharge = (float) ($get('nominal_charge') ?? 0);

                        if ($total > 0) {
                            return (int) ($total * ($nominalCharge / 100));
                        }

                        return 0;
                    })
                    ->numeric()
                    ->dehydrateStateUsing(fn($state) => $state ?? 0),
                TextInput::make('total_final')
                    ->prefix('Rp')
                    ->columnSpan(2)
                    ->default(function (Get $get) {
                        // Calculate default value based on existing data
                        $total = (float) ($get('total') ?? 0);
                        $nominalCharge = (float) ($get('nominal_charge') ?? 0);

                        if ($total > 0) {
                            $charge = $total * ($nominalCharge / 100);
                            return (int) ($total + $charge);
                        }

                        return 0;
                    })
                    ->numeric()
                    ->dehydrateStateUsing(fn($state) => $state ?? 0)
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
                TextColumn::make('total')
                    // ->formatStateUsing(fn (FakturModel $record): string => 'Rp ' . number_format($record->totallyGuarded, 0, '.', '.')),
                    ->formatStateUsing(fn($state): string => 'Rp ' . number_format($state, 0, '.', '.')),
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