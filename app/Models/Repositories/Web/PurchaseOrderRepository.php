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
                'purchase_order_number' => $data['purchase_order_number']
            ]);

            foreach ($data['data'] as $key => $value) {
                $validate_product = DB::table('product')->where('product_code', $value['product_code'])->where('product_quantity','>',0)->first();
                if (!$validate_product) {
                    throw new CustomException(['producto invalido.', 2000], 401);
                }

                DB::table('purchase_order_detail')->insert([
                    'id_purchase_order' =>  $id,
                    'product_code' => $value['product_code'] ?? null,
                    'product_description' => $value['product_description'] ?? null,
                    'product_quantity' => $value['product_quantity'] ?? null,
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
                $product = DB::table('product')->where('product_code',$value->product_code)->where('id_client_store', $oc->id_client_store)->first();

                // Validamos que la cantidad de compra no exceda la cantidad en stock
                if (
                    ($data['flag'] && $value->product_quantity > $product->product_quantity) || 
                    (!$data['flag'] && $value->product_quantity > $product->product_available_total)
                ) {
                    throw new CustomException(['la cantidad a descontar es mayor al stock', 2000], 400);
                }

                if (!$data['flag']) {
                    // Actualizamos Inventario
                    $descontar = $value->product_quantity;
                    do {
                        $inventario = DB::table('inventory')->where('id_product',$product->id_product)->where('available','>',0)->first();
                        if ($descontar > $inventario->available) {
                            $total_inventario = $inventario->quantity - $inventario->available;
                        } else {
                            $total_inventario = max($inventario->quantity - $descontar,0);
                        }

                        $disponible = max($inventario->available - $descontar, 0);
                        $descontar = max($descontar - $inventario->available, 0);
                        
                        DB::table('inventory')->where('id_inventory', $inventario->id_inventory)
                        ->update([
                            'quantity' => $total_inventario,
                            'available' => $disponible
                        ]);

                        DB::table('kardex')->insert([
                            'id_corporation' => $oc->id_corporation,
                            'id_organization' => $oc->id_organization,
                            'id_product' => $product->id_product,
                            'id_inventory' => $inventario->id_inventory,
                            'quantity' => $inventario->quantity,
                            'balance' => $total_inventario,
                            'doc_type' => 'ORDEN DE COMPRA',
                            'id_document' => $oc->id_purchase_order,
                            'created_by' => $data['username'],
                            'description' => 'SALIDA'
                        ]);
                    } while ($descontar > 0);

                } else {
                    // Actualizamos Inventario
                    $descontar = $value->product_quantity;
                    do {
                        $inventario = DB::table('inventory')->where('id_product',$product->id_product)->where('available','>',0)->first();
                        $total_inventario = max($inventario->quantity - $descontar,0);

                        $aux_shrink = max($inventario->shrinkage - $descontar, 0);
                        $descontar = max($descontar - $inventario->shrinkage, 0);

                        $aux_quarantine = max($inventario->quarantine - $descontar, 0);
                        $descontar = max($descontar - $inventario->quarantine, 0);

                        $disponible = max($inventario->available - $descontar, 0);
                        $descontar = max($descontar - $inventario->available, 0);

                        DB::table('inventory')->where('id_inventory', $inventario->id_inventory)
                        ->update([
                            'quantity' => $total_inventario,
                            'shrinkage' => $aux_shrink,
                            'quarantine' => $aux_quarantine,
                            'available' => $disponible
                        ]);

                        DB::table('kardex')->insert([
                            'id_corporation' => $oc->id_corporation,
                            'id_organization' => $oc->id_organization,
                            'id_product' => $product->id_product,
                            'id_inventory' => $inventario->id_inventory,
                            'quantity' => $inventario->quantity,
                            'balance' => $total_inventario,
                            'doc_type' => 'ORDEN DE COMPRA',
                            'id_document' => $oc->id_purchase_order,
                            'created_by' => $data['username'],
                            'description' => 'SALIDA'
                        ]);
                    } while ($descontar > 0);
                }

                $totales = DB::table('inventory')->select(DB::raw('SUM(quantity) as qty_tot,SUM(shrinkage) as s_tot,SUM(quarantine) as q_tot,SUM(available) as a_tot'))->where('id_product',$product->id_product)->first();
                DB::table('product')->where('id_product', $product->id_product)
                ->update([
                    'product_quarantine_total' => $totales->q_tot,
                    'product_shrinkage_total' => $totales->s_tot,
                    'product_quantity' => $totales->qty_tot,
                    'product_available_total' => $totales->a_tot
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
