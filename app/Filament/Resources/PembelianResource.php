<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianResource\Pages;
use App\Filament\Resources\PembelianResource\RelationManagers;
use App\Models\PembelianBarang;
use App\Models\Pembelian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// tambahan
use Filament\Forms\Components\Wizard; //untuk menggunakan wizard
use Filament\Forms\Components\TextInput; //untuk penggunaan text input
use Filament\Forms\Components\DateTimePicker; //untuk penggunaan date time picker
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select; //untuk penggunaan select
use Filament\Forms\Components\Repeater; //untuk penggunaan repeater
use Filament\Tables\Columns\TextColumn; //untuk tampilan tabel
use Filament\Forms\Components\Placeholder; //untuk menggunakan text holder
use Filament\Forms\Get; //menggunakan get 
use Filament\Forms\Set; //menggunakan set 
use Filament\Forms\Components\Hidden; //menggunakan hidden field
use Filament\Tables\Filters\SelectFilter; //untuk menambahkan filter
use Filament\Tables\Filters\Filter;

// model
use App\Models\Pembeli;
use App\Models\Barang;
use App\Models\Pembayaran;
use App\Models\PenjualanBarang;
use App\Models\Supplier;


// DB
use Illuminate\Support\Facades\DB;
// untuk dapat menggunakan action
use Filament\Forms\Components\Actions\Action;

class PembelianResource extends Resource
{
    protected static ?string $model = Pembelian::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Wizard
                Wizard::make([

                    // ===========================
                    // STEP 1 - DATA PEMBELIAN
                    // ===========================
                    Wizard\Step::make('Pemesanan')
                        ->schema([
                            Forms\Components\Section::make('Faktur')
                                ->icon('heroicon-m-document-duplicate')
                                ->schema([
                                    TextInput::make('no_faktur')
                                        ->default(fn () => Pembelian::getKodeFaktur())
                                        ->label('Nomor Faktur')
                                        ->required()
                                        ->readonly()
                                    ,
                                    DateTimePicker::make('tgl')
                                        ->default(now())
                                        ->label('Tanggal Pembelian')
                                    ,
                                    Select::make('supplier_id')
                                        ->label('Supplier')
                                        ->options(Supplier::pluck('nama_supplier', 'id')->toArray())
                                        ->required()
                                        ->placeholder('Pilih Supplier')
                                        ->searchable()
                                    ,
                                    TextInput::make('total_tagihan')
                                        ->default(0)
                                        ->hidden()
                                    ,
                                    TextInput::make('status')
                                        ->default('pesan') // pesan/proses/selesai/batal
                                        ->hidden()
                                    ,
                                ])
                                ->collapsible()
                                ->columns(3)
                            ,
                        ]),

                    // ===========================
                    // STEP 2 - PILIH BARANG
                    // ===========================
                    Wizard\Step::make('Pilih Barang')
                        ->schema([
                            Repeater::make('items')
                                ->relationship('pembelianBarang')
                                ->schema([
                                    Select::make('barang_id')
                                        ->label('Barang')
                                        ->options(Barang::pluck('nama_barang', 'id')->toArray())
                                        ->required()
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                        ->reactive()
                                        ->placeholder('Pilih Barang')
                                        ->afterStateUpdated(function ($state, $set) {
                                            $barang = Barang::find($state);
                                            $set('harga_beli', $barang ? $barang->harga_barang : 0);
                                            $set('harga_jual', $barang ? $barang->harga_barang * 1.2 : 0);
                                        })
                                        ->searchable()
                                    ,
                                    TextInput::make('harga_beli')
                                        ->label('Harga Beli')
                                        ->numeric()
                                        ->default(0)
                                        ->readonly()
                                        ->dehydrated()
                                    ,
                                    TextInput::make('harga_jual')
                                        ->label('Harga Jual')
                                        ->numeric()
                                        ->default(0)
                                        ->readonly()
                                        ->dehydrated()
                                    ,
                                    TextInput::make('jml')
                                        ->label('Jumlah')
                                        ->default(1)
                                        ->reactive()
                                        ->live()
                                        ->required()
                                        ->afterStateUpdated(function ($state, $set, $get) {
                                            $hargaBeli = (int) ($get('harga_beli') ?? 0);
                                            $jml       = (int) ($state ?? 0);
                                            $subtotal  = $hargaBeli * $jml;
                                            $set('subtotal', $subtotal);

                                            // Hitung total tagihan dari semua items
                                            $items = $get('../../items') ?? [];
                                            $totalTagihan = collect($items)
                                                ->sum(fn ($items) => (int) ($items['harga_beli'] ?? 0) * (int) ($items['jml'] ?? 0));
                                            $set('../../total_tagihan', $totalTagihan);
                                        })
                                    ,
                                    TextInput::make('subtotal')
                                        ->label('Subtotal')
                                        ->numeric()
                                        ->default(0)
                                        ->readonly()
                                        ->dehydrated()
                                    ,
                                    DatePicker::make('tgl')
                                        ->default(today())
                                        ->label('Tanggal')
                                        ->required()
                                    ,
                                ])
                                ->columns([
                                    'md' => 3,
                                ])
                                ->addable()
                                ->deletable()
                                ->reorderable()
                                ->createItemButtonLabel('Tambah Barang')
                                ->minItems(1)
                                ->required()
                            ,

                            // Tombol Proses Pembelian
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('Proses Pembelian')
                                    ->action(function ($get) {

                                        // Simpan data pembelian
                                        $pembelian = Pembelian::updateOrCreate(
                                            ['no_faktur' => $get('no_faktur')],
                                            [
                                                'tgl'           => $get('tgl'),
                                                'supplier_id'   => $get('supplier_id'),
                                                'status'        => 'pesan',
                                                'total_tagihan' => 0,
                                            ]
                                        );

                                        // Simpan data barang
                                        foreach ($get('items') as $item) {
                                            PembelianBarang::updateOrCreate(
                                                [
                                                    'pembelian_id' => $pembelian->id,
                                                    'barang_id'    => $item['barang_id'],
                                                ],
                                                [
                                                    'harga_beli' => $item['harga_beli'],
                                                    'harga_jual' => $item['harga_jual'],
                                                    'jml'        => $item['jml'],
                                                    'subtotal'   => $item['harga_beli'] * $item['jml'],
                                                    'tgl'        => $item['tgl'],
                                                ]
                                            );

                                            // Tambah stok barang di tabel barang
                                            $barang = Barang::find($item['barang_id']);
                                            if ($barang) {
                                                $barang->increment('stok', $item['jml']); // ✅ increment karena pembelian
                                            }
                                        }

                                        // Hitung total tagihan
                                        $totalTagihan = PembelianBarang::where('pembelian_id', $pembelian->id)
                                            ->sum(DB::raw('harga_beli * jml'));

                                        // Update total tagihan
                                        $pembelian->update(['total_tagihan' => $totalTagihan]);
                                    })
                                    ->label('Proses')
                                    ->color('primary')
                            ]),
                        ]),

                    // ===========================
                    // STEP 3 - PEMBAYARAN
                    // ===========================
                    Wizard\Step::make('Pembayaran')
                        ->schema([
                            Placeholder::make('Tabel Pembayaran')
                                    ->content(fn (Get $get) => view('filament.components.pembelian-table', [
                                        'pembelians' => Pembelian::where('no_faktur', $get('no_faktur'))->get()
                                ])), 
                        ]),

                ])->columnSpan(3)
                // Akhir Wizard
                            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_faktur')
                ->label('No Faktur')
                ->searchable()
            ,
            TextColumn::make('supplier.nama_supplier') // Relasi ke nama supplier
                ->label('Nama Supplier')
                ->sortable()
                ->searchable()
            ,
            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'selesai' => 'success',
                    'bayar'   => 'primary',
                    'pesan'   => 'warning',
                    'proses'  => 'info',
                    'batal'   => 'danger',
                    default   => 'secondary',
                })
            ,
            TextColumn::make('total_tagihan')
                ->label('Total Tagihan')
                ->formatStateUsing(fn (string|int|null $state): string => rupiah($state))
                ->sortable()
                ->alignment('end') // Rata kanan
            ,
            TextColumn::make('tgl')
                ->label('Tanggal Pembelian')
                ->date()
                ->sortable()
            ,
            TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true) // Sembunyikan by default
            ,
            
            ])
            ->filters([
                SelectFilter::make('status')
                ->label('Filter Status')
                ->options([
                    'pesan'   => 'Pemesanan',
                    'bayar'   => 'Pembayaran',
                    'proses'  => 'Diproses',
                    'selesai' => 'Selesai',
                    'batal'   => 'Dibatalkan',
                ])
                ->searchable()
                ->preload()
            ,
            // Filter berdasarkan supplier
            SelectFilter::make('supplier_id')
                ->label('Filter Supplier')
                ->options(Supplier::pluck('nama_supplier', 'id')->toArray())
                ->searchable()
                ->preload()
            ,
            // Filter berdasarkan tanggal
            Filter::make('tgl')
                ->form([
                    Forms\Components\DatePicker::make('dari_tanggal')
                        ->label('Dari Tanggal')
                    ,
                    Forms\Components\DatePicker::make('sampai_tanggal')
                        ->label('Sampai Tanggal')
                    ,
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['dari_tanggal'],
                            fn (Builder $query, $date): Builder =>
                                $query->whereDate('tgl', '>=', $date),
                        )
                        ->when(
                            $data['sampai_tanggal'],
                            fn (Builder $query, $date): Builder =>
                                $query->whereDate('tgl', '<=', $date),
                        );
                })
            ,
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            'edit' => Pages\EditPembelian::route('/{record}/edit'),
        ];
    }
}
