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
                    'id_product' => $validate_product->id_product,
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
                
                $property_name = $value->discount_from;
                $product = DB::table('product')->where('product_code',$value->product_code)->where('id_client_store', $oc->id_client_store)->first();
                $descontar = $value->product_quantity;
                do {
                    $inventario = DB::table('inventory')->where('id_product',$product->id_product)->where($property_name,'>',0)->first();

                    if ($descontar > $inventario->$property_name) { 
                        $total_inventario = $inventario->quantity - $inventario->$property_name; 
                        $aux_descontar = $inventario->$property_name;
                    } else {
                        $total_inventario = $inventario->quantity - $descontar;
                        $aux_descontar = $descontar;
                    }

                    $descontar = $descontar - $aux_descontar;

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
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function anularPurchaseOrder($data)
    {
        DB::beginTransaction();
        try {
            // DB::table('purchase_order')->where('id_purchase_order',$data['id_purchase_order'])
            //     ->update([
            //         'modified_by' => $data['username'],
            //         'status' => 'PROCESADO'
            //     ]);
                
            // DB::table('purchase_order_detail')->where('id_purchase_order',$data['id_purchase_order'])
            // ->update([
            //     'modified_by' => $data['username'],
            //     'status' => 'PROCESADO'
            // ]);

            $kardex = DB::table('kardex')->where('id_document',$data['id_purchase_order'])->where('doc_type','ORDEN DE COMPRA')->get();
            foreach ($kardex as $key => $value) {
                if($value->shrinkage > 0){
                    $origen = "shrinkage";
                } elseif($value->quarantine > 0){
                    $origen = "quarantine";
                }
                elseif($value->scrap > 0){
                    $origen = "scrap";
                }
                elseif($value->demo > 0){
                    $origen = "demo";
                }
                else {
                    $origen = "available";
                }
                Log::info('aca1');
                $inventario = DB::table('inventory')->where('id_inventory',$value->id_inventory)->first();
                Log::info($inventario->$origen);
                Log::info($value->quantity);
                Log::info($origen);
                DB::table('inventory')->where('id_inventory',$inventario->id_inventory)->update([
                    $origen => $value->quantity + $inventario->$origen,
                    'quantity' => $inventario->quantity + $value->quantity
                ]);
                Log::info('aca2');
                DB::table('kardex')->insert([
                    'id_corporation' => $value->id_corporation,
                    'id_organization' => $value->id_organization,
                    'id_product' => $value->id_product,
                    'id_inventory' => $value->id_inventory,
                    'description' => 'ANULACION',
                    'quantity' => $value->quantity,
                    'shrinkage' => $value->shrinkage,
                    'quarantine' => $value->quarantine,
                    'scrap' => $value->scrap,
                    'demo' => $value->demo,
                    'balance_available' => ($origen === 'available') ? $inventario->available + $value->quantity : $inventario->available,
                    'balance' => $inventario->quantity + $value->quantity,
                    'doc_type' => $value->doc_type,
                    'id_document' => $value->id_document,
                    'created_by' => $data['username'],
                ]);
                Log::info('aca3');
                $totales = DB::table('inventory')->select(DB::raw('SUM(quantity) as qty_tot,SUM(shrinkage) as s_tot,SUM(scrap) as scrap_tot,SUM(demo) as demo_tot,SUM(quarantine) as q_tot,SUM(available) as a_tot'))->where('id_product',$value->id_product)->first();
                DB::table('product')->where('id_product', $value->id_product)
                ->update([
                    'product_quantity' => $totales->qty_tot,
                    'product_quarantine_total' => $totales->q_tot,
                    'product_shrinkage_total' => $totales->s_tot,
                    'product_scrap_total' => $totales->scrap_tot,
                    'product_demo_total' => $totales->demo_tot,
                    'product_available_total' => $totales->a_tot
                ]);
                Log::info('aca4');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
