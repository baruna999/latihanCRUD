<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// tambahan
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Columns\BadgeColumn;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Textarea;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Masterdata';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Supplier')
                ->icon('heroicon-m-truck')
                ->schema([
                    TextInput::make('kode_supplier')
                        ->default(fn () => Supplier::getKodeSupplier())
                        ->label('Kode Supplier')
                        ->required()
                        ->readonly()
                    ,
                    TextInput::make('nama_supplier')
                        ->label('Nama Supplier')
                        ->required()
                        ->maxLength(255)
                    ,
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->nullable()
                        ->maxLength(255)
                    ,
                    TextInput::make('no_telp')
                        ->label('No. Telepon')
                        ->tel()
                        ->nullable()
                        ->maxLength(15)
                    ,
                    Textarea::make('alamat')
                        ->label('Alamat')
                        ->nullable()
                        ->columnSpanFull() // Alamat memenuhi lebar penuh
                        ->rows(3)
                    ,
                ])
                ->collapsible()
                ->columns(2)
            ,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_supplier')
                ->label('Kode Supplier')
                ->searchable()
                ->sortable()
            ,
            TextColumn::make('nama_supplier')
                ->label('Nama Supplier')
                ->searchable()
                ->sortable()
            ,
            TextColumn::make('email')
                ->label('Email')
                ->searchable()
                ->toggleable()
            ,
            TextColumn::make('no_telp')
                ->label('No. Telepon')
                ->searchable()
                ->toggleable()
            ,
            TextColumn::make('alamat')
                ->label('Alamat')
                ->limit(50) // Batasi tampilan karakter
                ->toggleable(isToggledHiddenByDefault: true)
            ,
            TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true)
            ,
            ])
            ->filters([
                Filter::make('nama_supplier')
                ->form([
                    Forms\Components\TextInput::make('nama')
                        ->label('Cari Nama Supplier')
                        ->placeholder('Ketik nama supplier...')
                    ,
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query->when(
                        $data['nama'],
                        fn (Builder $query, $nama): Builder =>
                            $query->where('nama_supplier', 'like', "%{$nama}%"),
                    );
                })
            ,
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
