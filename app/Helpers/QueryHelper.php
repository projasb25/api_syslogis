<?php

namespace App\Helpers;

use App\Models\Functions\Diccionario;

class QueryHelper # implements ResponseInterface
{
    public static function generarFiltro($filtros, $origin, $daterange)
    {
        $where = '';
        $diccionario = new \App\Models\Functions\Diccionario();
        $dic = $diccionario->getDiccionario();

        foreach ($filtros as $key => $filtro) {

            $column = $dic[$origin][$key]['column'];
            $value = str_replace("'", "\'", $filtro['value']);

            if ($value !== '') {
                switch ($filtro['operator']) {
                    case 'contains':
                        $where .= " and $column like '%$value%'";
                        break;
                    case 'nocontains':
                        $where .= " and $column not like '%$value%'";
                        break;
                    case 'equals':
                        $where .= " and $column = '$value'";
                        break;
                    case 'noequals':
                        $where .= " and $column <> '$value'";
                        break;
                    case 'empty':
                        $where .= " and ($column = '' or $column is null)";
                        break;
                    case 'noempty':
                        $where .= " and $column <> '' and $column is not null";
                        break;
                    case 'greater':
                        $where .= " and $column > $value";
                        break;
                    case 'greaterequal':
                        $where .= " and $column >= $value";
                        break;
                    case 'smaller':
                        $where .= " and $column < $value";
                        break;
                    case 'smallerequal':
                        $where .= " and $column <= $value";
                        break;
                    case 'in':
                        $where .= " and $column in ($value)";
                        break;
                    default:
                        break;
                }
            }
        }

        if ($daterange && $daterange['startDate']) {
            $startdate = substr($daterange['startDate'], 0, 10);
            $enddate = substr($daterange['endDate'], 0, 10);

            $columndate = $dic[$origin]['fechafilter']['column'];
            $where .= " and $columndate BETWEEN DATE_FORMAT('$startdate', '%Y-%m-%d') AND DATE_FORMAT('$enddate', '%Y-%m-%d')";
        }
        return $where;
    }

    public static function success($data)
    {
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
