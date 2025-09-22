<?php

namespace App\Http\Controllers;

use App\Models\Leaf;
use App\Models\Type;
use App\Models\Unit;
use App\Models\Medicine;
use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MedicineImport implements ToModel, WithHeadingRow {
    /**
     * @param array $row
     *
     * @return Medicine|null
     */

// public function model( array $row ) {

//     return new Medicine( [

//         'qr_code'      => $row['qr_code'],

//         'product_type' => $row['product_type'],

//         'strength'     => $row['strength'],

//         'leaf_id'      => $row['leaf_id'],

//         'shelf'        => $row['shelf'],

//         // 'category_id'  => $row['category_id'],

//         'type_id'      => $row['type_id'],

//         'supplier_id'  => $row['supplier_id'],

//         'vat'          => $row['vat'],

//         'status'       => $row['status'],

//         'name'         => $row['name'],

//         'generic_name' => $row['generic_name'],

//         'unit_id'      => $row['unit_id'],

//         'des'          => $row['des'],

//         'price'        => $row['price'],

//         'buy_price'    => $row['buy_price'],

//         'mfg_date'     => ( !empty( $row['mfg_date'] ) && $row['mfg_date'] !== '0000-00-00' ) ? $row['mfg_date'] : null,

//         'exp_date'     => ( !empty( $row['exp_date'] ) && $row['exp_date'] !== '0000-00-00' ) ? $row['exp_date'] : null,

//         // 'igta'         => $row['igta'],

//         // 'hot'          => $row['hot'],

//         // 'global'       => $row['global'],

    //     ] );
    // }

    public function model( array $row ) {
        // Convert date format to YYYY-MM-DD (if not already in correct format)
        $mfg_date = ( !empty( $row['mfg_date'] ) && strtotime( $row['mfg_date'] ) ) ? date( 'Y-m-d', strtotime( $row['mfg_date'] ) ) : null;
        $exp_date = ( !empty( $row['exp_date'] ) && strtotime( $row['exp_date'] ) ) ? date( 'Y-m-d', strtotime( $row['exp_date'] ) ) : null;

        return new Medicine( [
            'qr_code'      => $row['qr_code'],
            'product_type' => $row['product_type'],
            'strength'     => $row['strength'],
            'leaf_id'      => Leaf::where( 'name', $row['leaf_name'] )->value( 'id' ),
            'shelf'        => $row['shelf'],
            'type_id'      => Type::where( 'name', $row['type_name'] )->value( 'id' ),
            'supplier_id'  => Supplier::where( 'name', $row['supplier_name'] )->value( 'id' ),
            'vat'          => $row['vat'],
            'status'       => $row['status'],
            'name'         => $row['name'],
            'generic_name' => $row['generic_name'],
            'unit_id'      => Unit::where( 'name', $row['unit_name'] )->value( 'id' ),
            'des'          => $row['des'],
            'price'        => $row['price'],
            'buy_price'    => $row['buy_price'],
            'mfg_date'     => $mfg_date,
            'exp_date'     => $exp_date,
        ] );
    }

}
