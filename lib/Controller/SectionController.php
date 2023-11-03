<?php
/**
 * @author Andreas Goetz <cpuidle@gmx.de>
 * @copyright Copyright (c) 2011-2019, The volkszaehler.org project
 * @license http://www.opensource.org/licenses/gpl-license.php GNU Public License
 */
/*
 * This file is part of volkzaehler.org
 *
 * volkzaehler.org is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * volkzaehler.org is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with volkszaehler.org. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Volkszaehler\Controller;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;

use Volkszaehler\Model;
use Volkszaehler\Util;
use Volkszaehler\Interpreter\Interpreter;
use Volkszaehler\View\View;
use Volkszaehler\Interpreter\SQL;
//use Doctrine\ORM;

/**
 * Section controller
 */
class SectionController extends Controller {

        protected $options;     // optional request parameters
        protected $conn;        // PDO connection handle


        public function __construct(Request $request, EntityManager $em, View $view) {
                parent::__construct($request, $em, $view);
                $this->options = array_map('strtolower', (array) $this->getParameters()->get('options'));

                // get dbal connection from EntityManager
                $this->conn = $em->getConnection();
        }

	/**
	 * Query for data by given channel or group or multiple channels
	 *
	 * @param string|array $uuid
	 * @return array
	 */
	public function get($uuid) {
                //SectionController::debug_to_console("get");
                if (is_string($uuid)) {
		//$period  = $this->getParameters()->get('period');
		//$groupBy = $this->getParameters()->get('group');
		//$now = $this->getParameters()->get('now');
                $entity = $this->ef->get($uuid, true);
                $channel_id = $entity->getId();

                $sql = 'SELECT * FROM volkszaehler.pattern WHERE channel_id=' . $channel_id;
                //print_r($entity->getUuid());
                //print_r($entity->getId());
                $sqlParameters = array();

                // run query
                $stmt   = $this->conn->executeQuery($sql, $sqlParameters);
                $tuple  = false;
                $result = array();
                do {
                        //$firstTimestamp = $this->lastTimestamp; // SensorInterpreter

                        for ($i = 0; $tuple = $stmt->fetch(); $i++) {
                                $package = array($tuple['timestamp1'], $tuple['timestamp2']); // times
                                array_push($result, $package);  // add row
                                //$this->lastTimestamp = $tuple[0];
                        }
                } while ($tuple !== false);

                $stmt->closeCursor();
                //print_r($result);

		// result
		return  $result;
                }

                // multiple UUIDs
                return array_map(function($uuid) {
                        return $this->get($uuid);
                }, (array) $uuid);
	}

        /*
         * Override inherited visibility
         */

        public function add($uuid) {
                throw new \Exception('Invalid context operation: \'add\'');
        }

        public function delete($uuids) {
                throw new \Exception('Invalid context operation: \'delete\'');
        }

}

?>
