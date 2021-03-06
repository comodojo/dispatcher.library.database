<?php namespace Comodojo\Database\QueryBuilder;

use \Comodojo\Exception\DatabaseException;
use \Exception;

/**
 * STORE query builder
 * 
 * @package     Comodojo Spare Parts
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @license     MIT
 *
 * LICENSE:
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class QueryStore {

    private $model = null;

    private $table = null;

    private $values = null;

    private $keys = null;

    private $values_array = array();

    private $keys_array = array();

    /**
     * @param string $model
     */
    public function __construct($model) {

        $this->model = $model;

    }

    /**
     * @param string $data
     */
    final public function table($data) {

        $this->table = $data;

        return $this;

    }

    /**
     * @param string $data
     */
    final public function values($data) {

        $this->values = $data;

        return $this;

    }

    /**
     * @param string $data
     */
    final public function keys($data) {

        $this->keys = $data;

        return $this;

    }

    final public function valuesArray($data) {

        $this->values_array = $data;

        return $this;

    }

    final public function keysArray($data) {

        $this->keys_array = $data;

        return $this;

    }

    public function getQuery() {

        if ( is_null($this->table) || empty($this->values) ) throw new DatabaseException('Invalid parameters for database->store', 1002);

        if ( sizeof($this->values_array) == 1 ) {

            $query_pattern = "INSERT INTO %s%s VALUES %s";

            $keys = ($this->keys == "*" || is_null($this->keys)) ? null : "(".$this->keys.")";

            $query = sprintf($query_pattern, $this->table, " ".$keys, $this->values);

        } else {

            switch ( $this->model ) {

                case ("MYSQLI"):
                case ("MYSQL_PDO"):
                case ("DB2"):
                case ("DBLIB_PDO"):
                case ("POSTGRESQL"):

                    $query_pattern = "INSERT INTO %s%s VALUES%s";

                    $keys = ($this->keys == "*" || is_null($this->keys)) ? null : "(".$this->keys.")";

                    $query = sprintf($query_pattern, $this->table, " ".$keys, " ".$this->values);

                    break;

                // case ("POSTGRESQL"):

                //     $query_pattern = "INSERT INTO %s%s VALUES%s RETURNING id";

                //     $keys = ( $this->keys == "*" OR is_null($this->keys) ) ? null : "(".$this->keys.")";

                //     $query = sprintf($query_pattern, $this->table, " ".$keys, " ".$this->values);

                //     break;
                
                case ("SQLITE_PDO"):

                    $query_pattern = "INSERT INTO %s %s SELECT %s UNION SELECT %s";

                    if ( $this->keys == "*" || is_null($this->keys) ) throw new DatabaseException('SQLite require expllicit keys definition in multiple insert statement');

                    $keys = "(".$this->keys.")";

                    $select = array();

                    foreach ( $this->keys_array as $position => $key ) array_push($select, $this->values_array[0][$position]." AS ".$key);

                    $union_select = array();

                    foreach ( array_slice($this->values_array, 1) as $values ) array_push($union_select, implode(", ", $values));

                    $query = sprintf($query_pattern, $this->table, $keys, implode(", ", $select), implode(" UNION SELECT ", $union_select));

                    break;
                    
                case ("ORACLE_PDO"):

                    $query_pattern = "INSERT INTO %s%s SELECT %s";

                    $keys = ($this->keys == "*" || is_null($this->keys)) ? null : "(".$this->keys.")";

                    array_walk($this->values_array, function(&$value, $key) {

                        $value = "(".$value.")";

                    });

                    $values = implode(' FROM DUAL UNION ALL SELECT ', $this->values_array)." FROM DUAL";

                    $query = sprintf($query_pattern, $this->table, " ".$keys, $values);

                    break;

            }

        }

        return $query;

    }

}
