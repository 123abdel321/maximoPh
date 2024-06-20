<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EntornosTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('entornos')->delete();
        
        \DB::table('entornos')->insert(array (
            0 => 
            array (
                'id' => 1,
                'nombre' => 'id_comprobante_ventas',
                'valor' => '10',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-02-21 02:09:32',
            ),
            1 => 
            array (
                'id' => 2,
                'nombre' => 'id_comprobante_gastos',
                'valor' => '5',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-02-21 02:09:32',
            ),
            2 => 
            array (
                'id' => 3,
                'nombre' => 'id_comprobante_recibos_caja',
                'valor' => '1',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-02-21 02:09:32',
            ),
            3 => 
            array (
                'id' => 4,
                'nombre' => 'id_comprobante_pagos',
                'valor' => '2',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-02-21 02:09:32',
            ),
            4 => 
            array (
                'id' => 5,
                'nombre' => 'id_cuenta_intereses',
                'valor' => '2810',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-02-21 02:09:32',
            ),
            5 => 
            array (
                'id' => 6,
                'nombre' => 'id_cuenta_descuento',
                'valor' => '2111',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-02-21 02:09:32',
            ),
            6 => 
            array (
                'id' => 7,
                'nombre' => 'id_cuenta_ingreso_recibos_caja',
                'valor' => '2792',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-02-21 02:09:32',
            ),
            7 => 
            array (
                'id' => 8,
                'nombre' => 'id_cuenta_egreso_pagos',
                'valor' => '2792',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-02-21 02:09:32',
            ),
            8 => 
            array (
                'id' => 9,
                'nombre' => 'id_cuenta_ingreso_pasarela',
                'valor' => '2792',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-02-21 02:09:32',
            ),
            9 => 
            array (
                'id' => 10,
                'nombre' => 'area_total_m2',
                'valor' => '0',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-04-02 22:21:46',
            ),
            10 => 
            array (
                'id' => 11,
                'nombre' => 'numero_total_unidades',
                'valor' => '0',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-04-02 22:21:46',
            ),
            11 => 
            array (
                'id' => 12,
                'nombre' => 'valor_total_presupuesto_year_actual',
                'valor' => '0',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-04-02 22:21:46',
            ),
            12 => 
            array (
                'id' => 13,
                'nombre' => 'validacion_estricta_area',
                'valor' => '0',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-02-21 02:09:32',
            ),
            13 => 
            array (
                'id' => 14,
                'nombre' => 'dia_limite_pago_sin_interes',
                'valor' => '0',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-02-21 02:09:32',
            ),
            14 => 
            array (
                'id' => 15,
                'nombre' => 'dia_limite_descuento_pronto_pago',
                'valor' => '0',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-02-21 02:09:32',
            ),
            15 => 
            array (
                'id' => 16,
                'nombre' => 'porcentaje_descuento_pronto_pago',
                'valor' => '0',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-02-21 02:09:32',
            ),
            16 => 
            array (
                'id' => 17,
                'nombre' => 'porcentaje_intereses_mora',
                'valor' => '2',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-04-02 22:21:46',
            ),
            17 => 
            array (
                'id' => 18,
                'nombre' => 'editar_valor_admon_inmueble',
                'valor' => '1',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-04-02 22:21:46',
            ),
            18 => 
            array (
                'id' => 19,
                'nombre' => 'periodo_facturacion',
                'valor' => '2024-01-01',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => '2024-04-02 22:21:46',
            ),
            19 => 
            array (
                'id' => 20,
                'nombre' => 'id_comprobante_notas',
                'valor' => '4',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            20 => 
            array (
                'id' => 22,
                'nombre' => 'id_cuenta_ingreso',
                'valor' => '2805',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            21 => 
            array (
                'id' => 24,
                'nombre' => 'id_cuenta_anticipos',
                'valor' => '1081',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            22 => 
            array (
                'id' => 25,
                'nombre' => 'id_cuenta_ingreso_intereses',
                'valor' => '2812',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            23 => 
            array (
                'id' => 26,
                'nombre' => 'editar_coheficiente_admon_inmueble',
                'valor' => '0',
                'created_by' => NULL,
                'updated_by' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
        
        
    }
}