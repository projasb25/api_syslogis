<?php

namespace App\Models\Repositories\Web;

use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderRepository
{
    public function getPurchaseOrder($id)
    {
        return DB::table('purchase_order')->where('id_purchase_order',$id)->first();
    }

    public function insertPurchaseOrder($data)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('purchase_order')->insertGetId([
                'id_corporation' => $data['id_corporation'],
                'id_organization' => $data['id_organization'],
                'id_client' => $data['id_client'],
                'id_client_store' => $data['id_client_store'],
                'id_load_template' => $data['id_load_template'],
                'id_buyer' => $data['id_buyer'],
                'number_records' => $data['count'],
                'status' => 'PENDIENTE',
                'created_by' => $data['username'],
                'purchase_order_number' => $data['purchase_order_number'],
                "id_provider" => $data["id_provider"],
                "id_vehicle" => $data["id_vehicle"],
                "document_type" => $data["document_type"],
                "document_number" => $data["document_number"],
                "driver_license" => $data["driver_license"],
            ]);

            foreach ($data['data'] as $key => $value) {
                $validate_product = DB::table('product')->where('product_code', $value['product_code'])->where('product_quantity','>',0)->first();
                if (!$validate_product) {
                    throw new CustomException(['producto invalido.', 2000], 401);
                }

                // Validar cantidades de descuento
                switch ($value['discount_from']) {
                    case 'available':
                        $aux_quantity = $validate_product->product_available_total;
                        break;
                    case 'shrinkage':
                        $aux_quantity = $validate_product->product_shrinkage_total;
                        break;
                    case 'quarantine':
                        $aux_quantity = $validate_product->product_quarantine_total;
                        break;
                    case 'scrap':
                        $aux_quantity = $validate_product->product_scrap_total;
                        break;
                    case 'demo':
                        $aux_quantity = $validate_product->product_demo_total;
                        break;
                    default:
                        break;
                }

                if ($value['product_quantity'] > $aux_quantity) {
                    throw new CustomException(["No hay stock disponible para descontar el codigo ".$value['product_code']." de ".$value['discount_from'], 2000], 400);
                }

                DB::table('purchase_order_detail')->insert([
                    'id_purchase_order' =>  $id,
                    'product_code' => $value['product_code'] ?? null,
                    'product_description' => $value['product_description'] ?? null,
                    'product_quantity' => $value['product_quantity'] ?? null,
                    'discount_from' => $value['discount_from'] ?? null,
                    'status' => 'PENDIENTE',
                    'created_by' => $data['username']
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $id;
    }

    public function processPurchaseOrder($data)
    {
        DB::beginTransaction();
        try {
            DB::table('purchase_order')->where('id_purchase_order',$data['id_purchase_order'])
            ->update([
                'modified_by' => $data['username'],
                'status' => 'PROCESADO'
            ]);
            
            DB::table('purchase_order_detail')->where('id_purchase_order',$data['id_purchase_order'])
            ->update([
                'modified_by' => $data['username'],
                'status' => 'PROCESADO'
            ]);
            
            $oc = DB::table('purchase_order')->where('id_purchase_order',$data['id_purchase_order'])->first();

            $detalle = DB::table('purchase_order_detail')->where('id_purchase_order',$data['id_purchase_order'])->get();
            foreach ($detalle as $key => $value) {
                
                $property_name = $value->discount_from; # quarantine
                $product = DB::table('product')->where('product_code',$value->product_code)->where('id_client_store', $oc->id_client_store)->first();
                Log::info('property_name ->'. $property_name);
               Log::info('product', (array) $product);
                do {
                    $inventario = DB::table('inventory')->where('id_product',$product->id_product)->where($property_name,'>',0)->first();
                    Log::info('inventario', (array) $inventario);
                    $descontar = $value->product_quantity; # 9 | 4

                    if ($descontar > $inventario->$property_name) {  # 9 > 5
                        $total_inventario = $inventario->quantity - $inventario->$property_name;  # 20 - 5 = 15
                        $aux_descontar = $inventario->$property_name; # 5
                    } else {
                        $total_inventario = $inventario->quantity - $descontar; # 12 - 4 = 8
                        $aux_descontar = $descontar;
                    }

                    // $disponible = max($inventario->available - $aux_descontar, 0);  // 780 - 1000 = 0
                    $descontar = $descontar - $aux_descontar; # 9 - 5 = 4  |  4 - 4 = 0;

                    DB::table('inventory')->where('id_inventory', $inventario->id_inventory)
                    ->update([
                        $property_name => $inventario->$property_name - $aux_descontar,
                        'quantity' => $total_inventario,
                    ]);

                    $kardex_column = ($property_name === 'available') ? 'quantity' : $property_name;
                    DB::table('kardex')->insert([
                        'id_corporation' => $oc->id_corporation,
                        'id_organization' => $oc->id_organization,
                        'id_product' => $product->id_product,
                        'id_inventory' => $inventario->id_inventory,
                        'quantity' => $aux_descontar,
                        $kardex_column => $aux_descontar,
                        'balance' => $total_inventario,
                        'balance_available' => ($property_name === 'available') ? $inventario->available - $aux_descontar : $inventario->available,
                        'doc_type' => 'ORDEN DE COMPRA',
                        'id_document' => $oc->id_purchase_order,
                        'created_by' => $data['username'],
                        'description' => 'SALIDA'
                    ]);

                } while ($descontar > 0);

                $totales = DB::table('inventory')->select(DB::raw('SUM(quantity) as qty_tot,SUM(shrinkage) as s_tot,SUM(scrap) as scrap_tot,SUM(demo) as demo_tot,SUM(quarantine) as q_tot,SUM(available) as a_tot'))->where('id_product',$product->id_product)->first();
                DB::table('product')->where('id_product', $product->id_product)
                ->update([
                    'product_quantity' => $totales->qty_tot,
                    'product_quarantine_total' => $totales->q_tot,
                    'product_shrinkage_total' => $totales->s_tot,
                    'product_scrap_total' => $totales->scrap_tot,
                    'product_demo_total' => $totales->demo_tot,
                    'product_available_total' => $totales->a_tot
                ]);



                // // Validamos que la cantidad de compra no exceda la cantidad en stock
                // if (
                //     ($data['flag'] && $value->product_quantity > $product->product_quantity) || 
                //     (!$data['flag'] && $value->product_quantity > $product->product_available_total)
                // ) {
                //     throw new CustomException(['la cantidad a descontar es mayor al stock', 2000], 400);
                // }

                // if (!$data['flag']) {
                //     // Actualizamos Inventario
                //     $descontar = $value->product_quantity; // 1000
                //     do {
                //         $inventario = DB::table('inventory')->where('id_product',$product->id_product)->where('available','>',0)->first();
                //         if ($descontar > $inventario->available) {  // 1000 > 780
                //             $total_inventario = $inventario->quantity - $inventario->available;  // 800 - 780 = 20
                //             $aux_descontar = $inventario->available;
                //         } else {
                //             $total_inventario = max($inventario->quantity - $descontar,0);  
                //             $aux_descontar = $descontar;
                //         }

                //         $disponible = max($inventario->available - $descontar, 0);  // 780 - 1000 = 0
                //         $descontar = max($descontar - $inventario->available, 0); // 1000  - 780 = 220
                        
                //         DB::table('inventory')->where('id_inventory', $inventario->id_inventory)
                //         ->update([
                //             'quantity' => $total_inventario,
                //             'available' => $disponible
                //         ]);

                //         DB::table('kardex')->insert([
                //             'id_corporation' => $oc->id_corporation,
                //             'id_organization' => $oc->id_organization,
                //             'id_product' => $product->id_product,
                //             'id_inventory' => $inventario->id_inventory,
                //             'quantity' => $aux_descontar,
                //             'shrinkage' => 0,
                //             'quarantine' => 0,
                //             'balance' => $total_inventario,
                //             'balance_available' => $disponible,
                //             'doc_type' => 'ORDEN DE COMPRA',
                //             'id_document' => $oc->id_purchase_order,
                //             'created_by' => $data['username'],
                //             'description' => 'SALIDA'
                //         ]);
                //     } while ($descontar > 0);

                // } else {
                //     // Actualizamos Inventario
                //     $descontar = $value->product_quantity;
                //     do {
                //         $inventario = DB::table('inventory')->where('id_product',$product->id_product)->where('available','>',0)->first();
                //         if ($descontar > $inventario->quantity) {
                //             $aux_descontar = $inventario->quantity;
                //         } else {
                //             $aux_descontar = $descontar;
                //         }

                //         $total_inventario = max($inventario->quantity - $descontar,0);

                //         $aux_shrink = max($inventario->shrinkage - $descontar, 0);
                //         $descontar = max($descontar - $inventario->shrinkage, 0);

                //         $aux_quarantine = max($inventario->quarantine - $descontar, 0);
                //         $descontar = max($descontar - $inventario->quarantine, 0);

                //         $disponible = max($inventario->available - $descontar, 0);
                //         $descontar = max($descontar - $inventario->available, 0);

                //         DB::table('inventory')->where('id_inventory', $inventario->id_inventory)
                //         ->update([
                //             'quantity' => $total_inventario,
                //             'shrinkage' => $aux_shrink,
                //             'quarantine' => $aux_quarantine,
                //             'available' => $disponible
                //         ]);

                //         DB::table('kardex')->insert([
                //             'id_corporation' => $oc->id_corporation,
                //             'id_organization' => $oc->id_organization,
                //             'id_product' => $product->id_product,
                //             'id_inventory' => $inventario->id_inventory,
                //             'quantity' => $aux_descontar,
                //             'shrinkage' => $aux_shrink,
                //             'quarantine' => $aux_quarantine,
                //             'balance' => $total_inventario,
                //             'balance_available' => $disponible,
                //             'doc_type' => 'ORDEN DE COMPRA',
                //             'id_document' => $oc->id_purchase_order,
                //             'created_by' => $data['username'],
                //             'description' => 'SALIDA'
                //         ]);
                //     } while ($descontar > 0);
                // }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
