<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2017  Carlos Garcia Gomez  carlos@facturascripts.com
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
namespace FacturaScripts\Core\Controller;

use FacturaScripts\Core\Base;
use FacturaScripts\Core\Model;

/**
 * Controlador para la lista de ejercicios contables
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
class ListEjercicio extends Base\ListController
{

    public function __construct(&$cache, &$i18n, &$miniLog, $className)
    {
        parent::__construct($cache, $i18n, $miniLog, $className);

        $this->addOrderBy('codejercicio', 'Código');
        $this->addOrderBy('nombre');

        $this->addFilterSelect('estado', 'ejercicios');
    }

    public function privateCore(&$response, $user)
    {
        parent::privateCore($response, $user);

        // Load data with estructure data
        $where = $this->getWhere();
        $order = $this->getOrderBy($this->selectedOrderBy);
        $model = new Model\Ejercicio();
        $this->count = $model->count($where);
        if ($this->count > 0) {
            $this->cursor = $model->all($where, $order);
        }
    }

    protected function getWhere()
    {
        $result = parent::getWhere();

        if ($this->query != '') {
            $fields = "nombre|codejercicio";
            $result[] = new Base\DataBase\DataBaseWhere($fields, $this->query, "LIKE");
        }
        return $result;
    }

    public function getPageData()
    {
        $pagedata = parent::getPageData();
        $pagedata['title'] = 'Ejercicios';
        $pagedata['icon'] = 'fa-calendar';
        $pagedata['menu'] = 'contabilidad';
        return $pagedata;
    }

    protected function getColumns()
    {
        return [
            ['label' => 'Codigo', 'field' => 'codejercicio', 'display' => 'left'],
            ['label' => 'Nombre', 'field' => 'nombre', 'display' => 'left'],
            ['label' => 'Inicio', 'field' => 'fechainicio', 'display' => 'center'],
            ['label' => 'Fin', 'field' => 'fechafin', 'display' => 'center'],
            ['label' => 'Estado', 'field' => 'estado', 'display' => 'center'],
            ['label' => 'Plan', 'field' => 'plancontable', 'display' => 'center'],
            ['label' => 'Long.Cta', 'field' => 'longsubcuenta', 'display' => 'center']
        ];
    }
}
