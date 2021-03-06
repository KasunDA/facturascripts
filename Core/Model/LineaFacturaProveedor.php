<?php
/**
 * This file is part of facturacion_base
 * Copyright (C) 2013-2017  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Core\Model;

/**
 * Línea de una factura de proveedor.
 *
 * @author Carlos García Gómez <neorazorx@gmail.com>
 */
class LineaFacturaProveedor
{

    use Base\LineaDocumento;
    use Base\ModelTrait;

    /**
     * TODO
     * @var array
     */
    private static $facturas;

    /**
     * TODO
     * @var array
     */
    private static $albaranes;

    /**
     * ID de la linea del albarán relacionado, si lo hay.
     * @var int
     */
    public $idlineaalbaran;

    /**
     * ID de la factura de esta línea.
     * @var int
     */
    public $idfactura;

    /**
     * ID del albarán relacionado con la factura, si lo hay.
     * @var int
     */
    public $idalbaran;

    /**
     * TODO
     * @var string
     */
    private $codigo;

    /**
     * TODO
     * @var string
     */
    private $fecha;

    /**
     * TODO
     * @var string
     */
    private $albaran_codigo;

    /**
     * TODO
     * @var int
     */
    private $albaran_numero;
    
    public function tableName()
    {
        return 'lineasfacturasprov';
    }
    
    public function primaryColumn()
    {
        return 'idlinea';
    }

    /**
     * Resetea los valores de todas las propiedades modelo.
     */
    public function clear()
    {
        $this->idlinea = null;
        $this->idlineaalbaran = null;
        $this->idfactura = null;
        $this->idalbaran = null;
        $this->referencia = null;
        $this->codcombinacion = null;
        $this->descripcion = '';
        $this->cantidad = 0;
        $this->pvpunitario = 0;
        $this->pvpsindto = 0;
        $this->dtopor = 0;
        $this->pvptotal = 0;
        $this->codimpuesto = null;
        $this->iva = 0;
        $this->recargo = 0;
        $this->irpf = 0;
    }

    /**
     * TODO
     * @return null|string
     */
    public function showCodigo()
    {
        if ($this->codigo === null) {
            $this->fill();
        }
        return $this->codigo;
    }

    /**
     * TODO
     * @return string
     */
    public function showFecha()
    {
        if ($this->fecha === null) {
            $this->fill();
        }
        return $this->fecha;
    }

    /**
     * TODO
     * @return string
     */
    public function showNombre()
    {
        $nombre = 'desconocido';

        foreach (self::$facturas as $a) {
            if ($a->idfactura === $this->idfactura) {
                $nombre = $a->nombre;
                break;
            }
        }

        return $nombre;
    }

    /**
     * Devuelve la url donde ver/modificar estos datos
     * @return string
     */
    public function url()
    {
        return 'index.php?page=ComprasFactura&id=' . $this->idfactura;
    }

    /**
     * TODO
     * @return null|string
     */
    public function albaranCodigo()
    {
        if ($this->albaran_codigo === null) {
            $this->fill();
        }
        return $this->albaran_codigo;
    }

    /**
     * TODO
     * @return string
     */
    public function albaranUrl()
    {
        if ($this->idalbaran === null) {
            return 'index.php?page=ComprasAlbaranes';
        }
        return 'index.php?page=ComprasAlbaran&id=' . $this->idalbaran;
    }

    /**
     * TODO
     * @return int|null
     */
    public function albaranNumero()
    {
        if ($this->albaran_numero === null) {
            $this->fill();
        }
        return $this->albaran_numero;
    }

    /**
     * TODO
     * @return bool
     */
    public function test()
    {
        $this->descripcion = static::noHtml($this->descripcion);
        $total = $this->pvpunitario * $this->cantidad * (100 - $this->dtopor) / 100;
        $totalsindto = $this->pvpunitario * $this->cantidad;

        if (!$this->floatcmp($this->pvptotal, $total, FS_NF0, true)) {
            $this->miniLog->alert('Error en el valor de pvptotal de la línea ' . $this->referencia
                . ' de la factura. Valor correcto: ' . $total);
            return false;
        }
        if (!$this->floatcmp($this->pvpsindto, $totalsindto, FS_NF0, true)) {
            $this->miniLog->alert('Error en el valor de pvpsindto de la línea ' . $this->referencia
                . ' de la factura. Valor correcto: ' . $totalsindto);
            return false;
        }
        return true;
    }

    /**
     * TODO
     *
     * @param string $query
     * @param int $offset
     *
     * @return array
     */
    public function search($query = '', $offset = 0)
    {
        $linealist = [];
        $query = mb_strtolower(static::noHtml($query), 'UTF8');

        $sql = 'SELECT * FROM ' . $this->tableName() . ' WHERE ';
        if (is_numeric($query)) {
            $sql .= "referencia LIKE '%" . $query . "%' OR descripcion LIKE '%" . $query . "%'";
        } else {
            $buscar = str_replace(' ', '%', $query);
            $sql .= "lower(referencia) LIKE '%" . $buscar . "%' OR lower(descripcion) LIKE '%" . $buscar . "%'";
        }
        $sql .= ' ORDER BY idfactura DESC, idlinea ASC';

        $data = $this->dataBase->selectLimit($sql, FS_ITEM_LIMIT, $offset);
        if (!empty($data)) {
            foreach ($data as $l) {
                $linealist[] = new LineaFacturaProveedor($l);
            }
        }

        return $linealist;
    }

    /**
     * TODO
     *
     * @param int $idalb
     *
     * @return array
     */
    public function facturasFromAlbaran($idalb)
    {
        $facturalist = [];
        $sql = 'SELECT DISTINCT idfactura FROM ' . $this->tableName()
            . ' WHERE idalbaran = ' . $this->var2str($idalb) . ';';

        $data = $this->dataBase->select($sql);
        if (!empty($data)) {
            $factura = new FacturaProveedor();
            foreach ($data as $lfac) {
                $fac = $factura->get($lfac['idfactura']);
                if ($fac) {
                    $facturalist[] = $fac;
                }
            }
        }

        return $facturalist;
    }

    /**
     * Completa con los datos de la factura.
     */
    private function fill()
    {
        $encontrado = false;
        foreach (self::$facturas as $f) {
            if ($f->idfactura === $this->idfactura) {
                $this->codigo = $f->codigo;
                $this->fecha = $f->fecha;
                $encontrado = true;
                break;
            }
        }
        if (!$encontrado) {
            $fac = new FacturaProveedor();
            $fac = $fac->get($this->idfactura);
            if ($fac) {
                $this->codigo = $fac->codigo;
                $this->fecha = $fac->fecha;
                self::$facturas[] = $fac;
            }
        }

        if (!$this->idalbaran === null) {
            $encontrado = false;
            foreach (self::$albaranes as $a) {
                if ($a->idalbaran === $this->idalbaran) {
                    $this->albaran_codigo = $a->codigo;
                    if ($a->numproveedor === null || $a->numproveedor === '') {
                        $this->albaran_numero = $a->numero;
                    } else {
                        $this->albaran_numero = $a->numproveedor;
                    }
                    $encontrado = true;
                    break;
                }
            }
            if (!$encontrado) {
                $alb = new AlbaranProveedor();
                $alb = $alb->get($this->idalbaran);
                if ($alb) {
                    $this->albaran_codigo = $alb->codigo;
                    if ($alb->numproveedor === null || $alb->numproveedor === '') {
                        $this->albaran_numero = $alb->numero;
                    } else {
                        $this->albaran_numero = $alb->numproveedor;
                    }
                    self::$albaranes[] = $alb;
                }
            }
        }
    }
}
