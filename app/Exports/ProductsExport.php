<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    protected $title;

    public function __construct($title)
    {
        $this->title = $title;
    }

    public function collection(): Collection
    {
        $products = Product::select('nama_produk', 'kategori_produk', 'harga_beli', 'harga_jual', 'stok_produk')->get();

        $indexedProducts = $products->map(function ($product, $index) {
            $indexedProduct = $product->toArray();
            $indexedProduct = array_merge(['No' => $index + 1], $indexedProduct);
            return $indexedProduct;
        });

        return $indexedProducts;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Produk',
            'Kategori Produk',
            'Harga Beli',
            'Harga Jual',
            'Stok Produk',
        ];
    }

    public function title(): string
    {
        return $this->title;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFA500']]],
        ];
    }
    
}
