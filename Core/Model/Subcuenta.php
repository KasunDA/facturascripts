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
 * El cuarto nivel de un plan contable. Está relacionada con una única cuenta.
 *
 * @author Carlos García Gómez <neorazorx@gmail.com>
 */
class Subcuenta
{

    use Base\ModelTrait;

    /**
     * Clave primaria.
     * @var int
     */
    public $idsubcuenta;

    /**
     * TODO
     * @var float
     */
    public $codsubcuenta;

    /**
     * ID de la cuenta a la que pertenece.
     * @var int
     */
    public $idcuenta;

    /**
     * TODO
     * @var string
     */
    public $codcuenta;

    /**
     * TODO
     * @var string
     */
    public $codejercicio;

    /**
     * TODO
     * @var string
     */
    public $coddivisa;

    /**
     * TODO
     * @var string
     */
    public $codimpuesto;

    /**
     * TODO
     * @var string
     */
    public $descripcion;

    /**
     * TODO
     * @var float
     */
    public $haber;

    /**
     * TODO
     * @var float
     */
    public $debe;

    /**
     * TODO
     * @var float
     */
    public $saldo;

    /**
     * TODO
     * @var float
     */
    public $recargo;

    /**
     * TODO
     * @var float
     */
    public $iva;
    
    public function tableName()
    {
        return 'co_subcuentas';
    }
    
    public function primaryColumn()
    {
        return 'idsubcuenta';
    }

    /**
     * Resetea los valores de todas las propiedades modelo.
     */
    public function clear()
    {
        $this->idsubcuenta = null;
        $this->codsubcuenta = null;
        $this->idcuenta = null;
        $this->codcuenta = null;
        $this->codejercicio = null;
        $this->coddivisa = $this->defaultItems->codDivisa();
        $this->codimpuesto = null;
        $this->descripcion = '';
        $this->debe = 0;
        $this->haber = 0;
        $this->saldo = 0;
        $this->recargo = 0;
        $this->iva = 0;
    }

    /**
     * Devuelve la descripción en base64.
     * @return string
     */
    public function getDescripcion64()
    {
        return base64_encode($this->descripcion);
    }

    /**
     * TODO
     * @return int
     */
    public function tasaconv()
    {
        if ($this->coddivisa !== null) {
            $divisa = new Divisa();
            $div0 = $divisa->get($this->coddivisa);
            if ($div0) {
                return $div0->tasaconv;
            }
        }
        return 1;
    }

    /**
     * Devuelve la url donde ver/modificar estos datos
     * @return string
     */
    public function url()
    {
        if ($this->idsubcuenta === null) {
            return 'index.php?page=ContabilidadCuentas';
        }
        return 'index.php?page=ContabilidadSubcuenta&id=' . $this->idsubcuenta;
    }

    /**
     * TODO
     * @return bool|mixed
     */
    public function getCuenta()
    {
        $cuenta = new Cuenta();
        return $cuenta->get($this->idcuenta);
    }

    /**
     * TODO
     * @return bool|mixed
     */
    public function getEjercicio()
    {
        $eje = new Ejercicio();
        return $eje->get($this->codejercicio);
    }

    /**
     * TODO
     *
     * @param int $offset
     *
     * @return array
     */
    public function getPartidas($offset = 0)
    {
        $part = new Partida();
        return $part->allFromSubcuenta($this->idsubcuenta, $offset);
    }

    /**
     * TODO
     * @return array
     */
    public function getPartidasFull()
    {
        $part = new Partida();
        return $part->fullFromSubcuenta($this->idsubcuenta);
    }

    /**
     * TODO
     * @return int
     */
    public function countPartidas()
    {
        $part = new Partida();
        return $part->countFromSubcuenta($this->idsubcuenta);
    }

    /**
     * TODO
     * @return array
     */
    public function getTotales()
    {
        $part = new Partida();
        return $part->totalesFromSubcuenta($this->idsubcuenta);
    }

    /**
     * TODO
     *
     * @param string $cod
     * @param string $codejercicio
     * @param bool $crear
     *
     * @return bool|Subcuenta
     */
    public function getByCodigo($cod, $codejercicio, $crear = false)
    {
        $sql = 'SELECT * FROM ' . $this->tableName() . ' WHERE codsubcuenta = ' . $this->var2str($cod)
            . ' AND codejercicio = ' . $this->var2str($codejercicio) . ';';

        $subc = $this->dataBase->select($sql);
        if (!empty($subc)) {
            return new Subcuenta($subc[0]);
        }
        if ($crear) {
            /// buscamos la subcuenta equivalente en otro ejercicio
            $sql = 'SELECT * FROM ' . $this->tableName() . ' WHERE codsubcuenta = ' . $this->var2str($cod)
                . ' ORDER BY idsubcuenta DESC;';
            $subc = $this->dataBase->select($sql);
            if (!empty($subc)) {
                $oldSc = new Subcuenta($subc[0]);

                /// buscamos la cuenta equivalente es ESTE ejercicio
                $cuenta = new Cuenta();
                $newC = $cuenta->getByCodigo($oldSc->codcuenta, $codejercicio);
                if ($newC) {
                    $newSc = new Subcuenta();
                    $newSc->codcuenta = $newC->codcuenta;
                    $newSc->coddivisa = $oldSc->coddivisa;
                    $newSc->codejercicio = $codejercicio;
                    $newSc->codimpuesto = $oldSc->codimpuesto;
                    $newSc->codsubcuenta = $oldSc->codsubcuenta;
                    $newSc->descripcion = $oldSc->descripcion;
                    $newSc->idcuenta = $newC->idcuenta;
                    $newSc->iva = $oldSc->iva;
                    $newSc->recargo = $oldSc->recargo;
                    if ($newSc->save()) {
                        return $newSc;
                    }
                    return false;
                }
                $this->miniLog->alert('No se ha encontrado la cuenta equivalente a ' . $oldSc->codcuenta
                    . ' en el ejercicio ' . $codejercicio
                    . ' <a href="index.php?page=ContabilidadEjercicio&cod=' . $codejercicio
                    . '">¿Has importado el plan contable?</a>');
                return false;
            }
            $this->miniLog->alert('No se ha encontrado ninguna subcuenta equivalente a ' . $cod . ' para copiar.');
            return false;
        }
        return false;
    }

    /**
     * Devuelve la primera subcuenta del ejercicio $codeje cuya cuenta madre
     * está marcada como cuenta especial $id.
     *
     * @param int $idcuesp
     * @param string $codeje
     *
     * @return Subcuenta|bool
     */
    public function getCuentaesp($idcuesp, $codeje)
    {
        $sql = 'SELECT * FROM co_subcuentas WHERE idcuenta IN '
            . '(SELECT idcuenta FROM co_cuentas WHERE idcuentaesp = ' . $this->var2str($idcuesp)
            . ' AND codejercicio = ' . $this->var2str($codeje) . ') ORDER BY codsubcuenta ASC;';

        $data = $this->dataBase->select($sql);
        if (!empty($data)) {
            return new Subcuenta($data[0]);
        }
        return false;
    }

    /**
     * TODO
     * @return bool
     */
    public function tieneSaldo()
    {
        return !$this->floatcmp($this->debe, $this->haber, FS_NF0, true);
    }

    /**
     * TODO
     * @return bool
     */
    public function test()
    {
        $this->descripcion = static::noHtml($this->descripcion);

        $limpiarCache = false;
        $totales = $this->getTotales();

        if (abs($this->debe - $totales['debe']) > .001) {
            $this->debe = $totales['debe'];
            $limpiarCache = true;
        }

        if (abs($this->haber - $totales['haber']) > .001) {
            $this->haber = $totales['haber'];
            $limpiarCache = true;
        }

        if (abs($this->saldo - $totales['saldo']) > .001) {
            $this->saldo = $totales['saldo'];
            $limpiarCache = true;
        }

        if ($limpiarCache) {
            $this->cleanCache();
        }

        if (strlen($this->codsubcuenta) > 0 && strlen($this->descripcion) > 0) {
            return true;
        }
        $this->miniLog->alert('Faltan datos en la subcuenta.');
        return false;
    }

    /**
     * TODO
     */
    public function cleanCache()
    {
        /*
          if (file_exists('tmp/' . FS_TMP_NAME . 'libro_mayor/' . $this->idsubcuenta . '.pdf')) {
          if (!@unlink('tmp/' . FS_TMP_NAME . 'libro_mayor/' . $this->idsubcuenta . '.pdf')) {
          $this->miniLog->alert('Error al eliminar tmp/' . FS_TMP_NAME . 'libro_mayor/' . $this->idsubcuenta . '.pdf');
          }
          }

          if (file_exists('tmp/' . FS_TMP_NAME . 'libro_diario/' . $this->codejercicio . '.pdf')) {
          if (!@unlink('tmp/' . FS_TMP_NAME . 'libro_diario/' . $this->codejercicio . '.pdf')) {
          $this->miniLog->alert('Error al eliminar tmp/' . FS_TMP_NAME . 'libro_diario/' . $this->codejercicio . '.pdf');
          }
          }

          if (file_exists('tmp/' . FS_TMP_NAME . 'inventarios_balances/' . $this->codejercicio . '.pdf')) {
          if (!@unlink('tmp/' . FS_TMP_NAME . 'inventarios_balances/' . $this->codejercicio . '.pdf')) {
          $this->miniLog->alert('Error al eliminar tmp/' . FS_TMP_NAME . 'inventarios_balances/' . $this->codejercicio . '.pdf');
          }
          }
         */
    }

    /**
     * TODO
     *
     * @param int $idcuenta
     *
     * @return array
     */
    public function allFromCuenta($idcuenta)
    {
        $sublist = [];
        $sql = 'SELECT * FROM ' . $this->tableName() . ' WHERE idcuenta = ' . $this->var2str($idcuenta)
            . ' ORDER BY codsubcuenta ASC;';

        $subcuentas = $this->dataBase->select($sql);
        if (!empty($subcuentas)) {
            foreach ($subcuentas as $s) {
                $sublist[] = new Subcuenta($s);
            }
        }

        return $sublist;
    }

    /**
     * Devuelve las subcuentas del ejercicio $codeje cuya cuenta madre
     * está marcada como cuenta especial $id.
     *
     * @param int $idcuesp
     * @param string $codeje
     *
     * @return array
     */
    public function allFromCuentaesp($idcuesp, $codeje)
    {
        $cuentas = [];
        $sql = 'SELECT * FROM co_subcuentas WHERE idcuenta IN '
            . '(SELECT idcuenta FROM co_cuentas WHERE idcuentaesp = ' . $this->var2str($idcuesp)
            . ' AND codejercicio = ' . $this->var2str($codeje) . ') ORDER BY codsubcuenta ASC;';

        $data = $this->dataBase->select($sql);
        if (!empty($data)) {
            foreach ($data as $d) {
                $cuentas[] = new Subcuenta($d);
            }
        }

        return $cuentas;
    }

    /**
     * Devuelve las subcuentas de un ejercicio:
     * - Todas si $random = false.
     * - $limit si $random = true.
     *
     * @param string $codejercicio
     * @param bool $random
     * @param bool $limit
     *
     * @return array
     */
    public function allFromEjercicio($codejercicio, $random = false, $limit = false)
    {
        $sublist = [];

        if ($random && $limit) {
            $sql = 'SELECT * FROM ' . $this->tableName() . ' WHERE codejercicio = '
                . $this->var2str($codejercicio) . ' ORDER BY random()';
            if (strtolower(FS_DB_TYPE) === 'mysql') {
                $sql = 'SELECT * FROM ' . $this->tableName() . ' WHERE codejercicio = '
                    . $this->var2str($codejercicio) . ' ORDER BY RAND()';
            }
            $subcuentas = $this->dataBase->selectLimit($sql, $limit);
        } else {
            $sql = 'SELECT * FROM ' . $this->tableName() . ' WHERE codejercicio = '
                . $this->var2str($codejercicio) . ' ORDER BY codsubcuenta ASC;';
            $subcuentas = $this->dataBase->select($sql);
        }

        if (!empty($subcuentas)) {
            foreach ($subcuentas as $s) {
                $sublist[] = new Subcuenta($s);
            }
        }

        return $sublist;
    }

    /**
     * TODO
     *
     * @param string $query
     *
     * @return array
     */
    public function search($query)
    {
        $sublist = [];
        $query = mb_strtolower(static::noHtml($query), 'UTF8');
        $sql = 'SELECT * FROM ' . $this->tableName() . " WHERE codsubcuenta LIKE '" . $query . "%'"
            . " OR codsubcuenta LIKE '%" . $query . "'"
            . " OR lower(descripcion) LIKE '%" . $query . "%'"
            . ' ORDER BY codejercicio DESC, codcuenta ASC;';

        $data = $this->dataBase->select($sql);
        if (!empty($data)) {
            foreach ($data as $s) {
                $sublist[] = new Subcuenta($s);
            }
        }

        return $sublist;
    }

    /**
     * Devuelve los resultados de la búsuqeda $query sobre las subcuentas del
     * ejercicio $codejercicio
     *
     * @param string $codejercicio
     * @param string $query
     *
     * @return Subcuenta
     */
    public function searchByEjercicio($codejercicio, $query)
    {
        $query = $this->escapeString(mb_strtolower(trim($query), 'UTF8'));

        $sublist = $this->cache->get('search_subcuenta_ejercicio_' . $codejercicio . '_' . $query);
        if (count($sublist) < 1) {
            $sql = 'SELECT * FROM ' . $this->tableName() . ' WHERE codejercicio = ' . $this->var2str($codejercicio)
                . " AND (codsubcuenta LIKE '" . $query . "%' OR codsubcuenta LIKE '%" . $query . "'"
                . " OR lower(descripcion) LIKE '%" . $query . "%') ORDER BY codcuenta ASC;";

            $data = $this->dataBase->select($sql);
            if (!empty($data)) {
                foreach ($data as $s) {
                    $sublist[] = new Subcuenta($s);
                }
            }

            $this->cache->set('search_subcuenta_ejercicio_' . $codejercicio . '_' . $query, $sublist);
        }

        return $sublist;
    }

    /**
     * Esta función es llamada al crear la tabla del modelo. Devuelve el SQL
     * que se ejecutará tras la creación de la tabla. útil para insertar valores
     * por defecto.
     * @return string
     */
    public function install()
    {
        $this->cleanCache();
        /*

          /// eliminamos todos los PDFs relacionados
          if (file_exists('tmp/' . FS_TMP_NAME . 'libro_mayor')) {
          foreach (glob('tmp/' . FS_TMP_NAME . 'libro_mayor/*') as $file) {
          if (is_file($file)) {
          unlink($file);
          }
          }
          }
          if (file_exists('tmp/' . FS_TMP_NAME . 'libro_diario')) {
          foreach (glob('tmp/' . FS_TMP_NAME . 'libro_diario/*') as $file) {
          if (is_file($file)) {
          unlink($file);
          }
          }
          }
          if (file_exists('tmp/' . FS_TMP_NAME . 'inventarios_balances')) {
          foreach (glob('tmp/' . FS_TMP_NAME . 'inventarios_balances/*') as $file) {
          if (is_file($file)) {
          unlink($file);
          }
          }
          }

          /// forzamos la creación de la tabla de cuentas
          $cuenta = new Cuenta();
         */
        return '';
    }
}
