<?php

namespace App\Models;

use App\Models\Supplier;
use App\Models\Medicine;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;
use Auth;
use Storage;
use Brian2694\Toastr\Facades\Toastr;

//class ProductsImport implements ToModel, WithHeadingRow, WithValidation
class SupplierImport implements ToCollection, WithHeadingRow, WithValidation, ToModel
{
    private $rows = 0;
    
    public function collection(Collection $rows) {
        $canImport = true;
        
        
        if($canImport) {
            foreach ($rows as $row) {
                $unit = new Unit();
               if(!empty($row['unit'])){
                  $unit->name = $row['unit'];  
               } else {
                   $unit->name = 'PC';
               }
               $unit->save();
				$medicine = new Medicine();
				$medicine->name = $row['medicine_name'];
				$medicine->category_id = 12;
                $medicine->leaf_id = 2;
                $medicine->unit_id = $unit->id;
                $medicine->global = 1;
                $medicine->strength = $row['strength'];
                $medicine->generic_name = $row['generic_name'];
                $medicine->supplier_id = $row['manufacturer_company_name'];
                $medicine->image = 'demo.jpg';
                $medicine->save();
            }
            
          Toastr::success('Seller Imported Succesfully', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
        }
        
        
    }
    
    public function model(array $row)
    {
        ++$this->rows;
    }
    
    public function getRowCount(): int
    {
        return $this->rows;
    }

    public function rules(): array
    {
        return [
             //Can also use callback validation rules
             'unit_price' => function($attribute, $value, $onFailure) {
                  if (!is_numeric($value)) {
                      $onFailure('Unit price is not numeric');
                  }
              }
       ];
    }

  

}
