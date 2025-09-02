<?php

namespace App\Imports;

use App\Models\Part;
use App\Models\Brand;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PartsImport implements ToModel, WithHeadingRow, WithValidation
{
    private $brands;
    private $categories;

    public function __construct()
    {
        // Cache brand and category data to avoid repeated database queries in the loop
        $this->brands = Brand::pluck('id', 'nama_brand');
        $this->categories = Category::pluck('id', 'nama_kategori');
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Part([
            'kode_part'     => $row['kode_part'],
            'nama_part'     => $row['nama_part'],
            'brand_id'      => $this->brands->get($row['brand']),
            'category_id'   => $this->categories->get($row['kategori']),
            'satuan'        => $row['satuan'],
            'stok_minimum'  => $row['stok_minimum'],
            'harga_beli_default' => $row['harga_beli_default'],
            'harga_jual_default' => $row['harga_jual_default'],
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'kode_part' => 'required|unique:parts,kode_part',
            'nama_part' => 'required',
            'brand' => 'required|exists:brands,nama_brand',
            'kategori' => 'required|exists:categories,nama_kategori',
            'satuan' => 'required',
            'harga_beli_default' => 'required|numeric',
            'harga_jual_default' => 'required|numeric',
        ];
    }
}
