<?php namespace Comodojo\Database\QueryBuilder;

use \Comodojo\Exception\DatabaseException;
use \Exception;

/**
 * UPDATE query builder
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

class QueryUpdate {

    private $model = null;

    private $table = null;

    private $where = null;

    private $values_array = array();

    private $keys_array = array();

    public function __construct($model) {

        $this->model = $model;

    }

    final public function table($data) {

        $this->table = $data;

        return $this;

    }

    final public function where($data) {

        $this->where = $data;

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

        if ( is_null($this->table) || empty($this->keys_array) || empty($this->values_array) ) throw new DatabaseException('Invalid parameters for database->update', 1024);

        if ( sizeof($this->values_array) != 1 ) throw new DatabaseException('Cannot update multiple values at a time');

        if ( sizeof($this->keys_array) != sizeof($this->values_array[0]) ) throw new DatabaseException('Keys and values are of different sizes', 1025);

        $query_pattern = "UPDATE %s SET %s%s";

        $query_content_array = array();

        foreach ( $this->keys_array as $position => $key ) array_push($query_content_array, $key.'='.$this->values_array[0][$position]);

        $where = is_null($this->where) ? null : " ".$this->where;

        $query = sprintf($query_pattern, $this->table, implode(',', $query_content_array), $where);

        return $query;

    }

}
