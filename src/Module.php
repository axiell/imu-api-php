<?php
/* KE Software Open Source Licence
** 
** Notice: Copyright (c) 2011-2013 KE SOFTWARE PTY LTD (ACN 006 213 298)
** (the "Owner"). All rights reserved.
** 
** Licence: Permission is hereby granted, free of charge, to any person
** obtaining a copy of this software and associated documentation files
** (the "Software"), to deal with the Software without restriction,
** including without limitation the rights to use, copy, modify, merge,
** publish, distribute, sublicense, and/or sell copies of the Software,
** and to permit persons to whom the Software is furnished to do so,
** subject to the following conditions.
** 
** Conditions: The Software is licensed on condition that:
** 
** (1) Redistributions of source code must retain the above Notice,
**     these Conditions and the following Limitations.
** 
** (2) Redistributions in binary form must reproduce the above Notice,
**     these Conditions and the following Limitations in the
**     documentation and/or other materials provided with the distribution.
** 
** (3) Neither the names of the Owner, nor the names of its contributors
**     may be used to endorse or promote products derived from this
**     Software without specific prior written permission.
** 
** Limitations: Any person exercising any of the permissions in the
** relevant licence will be taken to have accepted the following as
** legally binding terms severally with the Owner and any other
** copyright owners (collectively "Participants"):
** 
** TO THE EXTENT PERMITTED BY LAW, THE SOFTWARE IS PROVIDED "AS IS",
** WITHOUT ANY REPRESENTATION, WARRANTY OR CONDITION OF ANY KIND, EXPRESS
** OR IMPLIED, INCLUDING (WITHOUT LIMITATION) AS TO MERCHANTABILITY,
** FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. TO THE EXTENT
** PERMITTED BY LAW, IN NO EVENT SHALL ANY PARTICIPANT BE LIABLE FOR ANY
** CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
** TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
** SOFTWARE OR THE USE OR OTHER DEALINGS WITH THE SOFTWARE.
** 
** WHERE BY LAW A LIABILITY (ON ANY BASIS) OF ANY PARTICIPANT IN RELATION
** TO THE SOFTWARE CANNOT BE EXCLUDED, THEN TO THE EXTENT PERMITTED BY
** LAW THAT LIABILITY IS LIMITED AT THE OPTION OF THE PARTICIPANT TO THE
** REPLACEMENT, REPAIR OR RESUPPLY OF THE RELEVANT GOODS OR SERVICES
** (INCLUDING BUT NOT LIMITED TO SOFTWARE) OR THE PAYMENT OF THE COST OF SAME.
*/
require_once dirname(__FILE__) . '/IMu.php';
require_once IMu::$api . '/Handler.php';

/*!
** Provides access to an EMu module.
** 
** @extends IMuHandler
**
** @usage
**   require_once IMu::$lib . '/Module.php';
** @end
**
** @since 1.0
*/
class IMuModule extends IMuHandler
{
	/* Constructors */
	/*!
	** Creates an object which can be used to access an EMu module.
	**
	** If the ``$session`` parameter is ``null`` a new session is created
	** automatically using the `IMuSession` class's **defaultHost** 
	** [$<link>(:session:defaultHost)] and **defaultPort** 
	** [$<link>(:session:defaultPort)] values.
	**
	** @param $table string
	**   Name of the EMu module to be accessed.
	**
	** @param $session IMuSession
	**   A `Session` object to be used to communicate with the IMu server.
	*/
	public function
	__construct($table, $session = null)
	{
		parent::__construct($session);

		$this->_name = 'Module';
		$this->_create = $table;

		$this->_table = $table;
	}

	/* Properties */
	/*!
	** @property table string
	**   The name of the table associated with the `IMuModule` object 
	**   [$<link>(:module:module)].
	*/
	public function
	getTable()
	{
		return $this->_table;
	}

	public function
	__get($name)
	{
		switch ($name)
		{
		  case 'table':
		  	return $this->getTable();
		  default:
		  	return parent::__get($name);
		}
	}

	/* Methods */
	/*!
	** Associates a set of columns with a logical name in the server.
	**
	** The name can be used instead of a column list when retrieving data
	** using **fetch( )** [$<link>(:module:module)].
	**
	** @param $name string
	**   The logical name to associate with the set of columns.
	**
	** @param $columns mixed
	**   A `string` or an array of `string`\s containing the names of the 
	**   columns to be used when ``$name`` is passed to **fetch( )**
	**   [$<link>(:module:fetch)]. 
	**   Each string can contain one or more column names, separated by a 
	**   ``semi-colon`` or a ``comma``.
	**
	** @returns int
	**   The number of sets (including this one) registered in the server.
	**
	** @throws IMuException
	**   A server-side error occurred.
	*/
	public function
	addFetchSet($name, $columns)
	{
		$args = array();
		$args['name'] = $name;
		$args['columns'] = $columns;
		return $this->call('addFetchSet', $args) + 0;
	}

	/*!
	** Associates several sets of columns with logical names in the server.
	**
	** This is the equivalent of calling **addFetchSet( )** 
	** [$<link>(:module:addFetchSet)] for each entry in the map but is more 
	** efficient.
	**
	** @param $sets array
	**   An associative array containing mappings between names and sets of 
	**   columns.
	**
	** @returns int
	**   The number of sets (including these ones) registered in the server.
	**
	** @throws IMuException
	**   A server-side error occurred.
	*/
	public function
	addFetchSets($sets)
	{
		return $this->call('addFetchSets', $sets) + 0;
	}

	/*!
	** Associates a set of columns with a logical name in the server.
	**
	** The name can be used when specifying search terms to be passed to
	** **findTerms( )** [$<link>(:module:findTerms)].
	** The search becomes the equivalent of an ``OR`` search involving the 
	** columns.
	**
	** @param $name string
	**   The logical name to associate with the set of columns.
	**
	** @param $columns mixed
	**   A `string` or an array of `string`\s containing the names of the 
	**   columns to be used when ``$name`` is passed to **findTerms( )**
	**   [$<link>(:module:findTerms)].

	**   Each `string` can contain one or more column names, separated by a 
	**   ``semi-colon`` or a ``comma``.
	**
	** @returns int
	**   The number of aliases (including this one) registered in the server.
	**
	** @throws IMuException
	**   A server-side error occurred.
	*/
	public function
	addSearchAlias($name, $columns)
	{
		$args = array();
		$args['name'] = $name;
		$args['columns'] = $columns;
		return $this->call('addSearchAlias', $args) + 0;
	}

	/*!
	** Associates several sets of columns with a logical names in the server.
	**
	** This is the equivalent of calling
	** **addSearchAlias( )** [$<link>(:module:addSearchAlias)] for each
	** entry in the map but is more efficient.
	**
	** @param $aliases array
	**   An associative array containing a set of mappings between a name and a
	**   set of columns.
	**
	** @returns int
	**   The number of sets (including these ones) registered in the server.
	**
	** @throws IMuException
	**   A server-side error occurred.
	*/ 
	public function
	addSearchAliases($aliases)
	{
		return $this->call('addSearchAliases', $aliases) + 0;
	}

	/*!
	** Associates several sets of columns with a logical names in the server.
	**
	** The name can be used instead of a sort key list when sorting the current
	** result set using **sort( )** [$<link>(:module:sort)].
	**
	** @param $name string
	**   The logical name to associate with the set of columns.
	**
	** @param $keys mixed
	**   A `string` or an array of `string`\s containing the names of the keys
	**   to be used when ``$name`` is passed to **sort( )** 
	**   [$<link>(:module:sort)].
	**   Each `string` can contain one or more keys, separated by a 
	**   ``semi-colon`` or a ``comma``.
	**
	** @returns int
	**   The number of sets (including this one) registered in the server.
	**
	** @throws IMuException
	**   A server-side error occurred.
	*/
	public function
	addSortSet($name, $columns)
	{
		$args = array();
		$args['name'] = $name;
		$args['columns'] = $columns;
		return $this->call('addSortSet', $args) + 0;
	}

	/*!
	** Associates serveral sets of sort keys with logical names in the server.
	** 
	** This is the equivalent of calling **addSortSet( )**
	** [$<link>(:module:addSortSet)] for each entry in the list but is more
	** efficient.
	**
	** @param $sets array
	**   An associative array containing a set of mappings between a name and a
	**   set of keys.
	**
	** @returns int
	**   The number sets (including these ones) registered in the server.
	**
	** @throws IMuException
	*/
	public function
	addSortSets($sets)
	{
		return $this->call('addSortSets', $sets) + 0;
	}

	/*!
	** Fetches ``count`` records from the position described by a combination of
	** ``flag`` and ``offset``.
	**
	** @param $flag string
	**   The position to start fetching records from. 
	**   Must be one of: 
	**     ``start`` 
	**     ``current``
	**     ``end``
	**
	** @param $offset int
	**   The position relative to ``$flag`` to start fetching from.
	**
	** @param $count int
	**   The number of records to fetch. 
	**   A ``$count`` of ``0`` is permitted to change the location of the 
	**   current record without returning any results. 
	**   A ``$count`` of less than ``0`` causes all the remaining records in the
	**   result set to be returned.
	**
	** @param $columns mixed
	**   A `string` or an array of `string`\s containing the names of the 
	**   columns to be returned for each record or the name of a column set which
	**   has been registered previously using **addFetchSet( )** 
	**   [$<link>(:module:addFetchSet)].
	**   Each string can contain one or more column, names, separated by a 
	**   ``semi-colon`` or a ``comma``.
	** 
	**   If this parameter is not supplied, no column data is returned. The
	**   results will still include the pseudo-column ``rownum`` for each
	**   fetched record.
	**
	** @returns IMuModuleFetchResult
	**   An `IMuModuleFetchResult` object.
	**
	** @throws IMuException
	**   If a server-side error occured.
	*/
	public function
	fetch($flag, $offset, $count, $columns = null)
	{
		$args = array();
		$args['flag'] = $flag;
		$args['offset'] = $offset;
		$args['count'] = $count;
		if ($columns !== null)
			$args['columns'] = $columns;
		return $this->makeResult($this->call('fetch', $args));
	}

	/*!
	** Fetches a hierarchy of records around the record with the key
	** value of ``$key``.
	**
	** @param $key int
	**   The key of the record to start building the hierarchy from.
	**
	** @param $parent string
	**   The name of the column which contains the parent attachment.
	*/
	public function
	fetchHierarchy($key, $parent, $options = null)
	{
		$args = array();
		$args['key'] = $key;
		$args['parent'] = $parent;
		if ($options !== null)
			$args['options'] = $options;
		$data = $this->call('fetchHierarchy', $args);
		return $data;
	}

	/*!
	** Searches for a record with the key value ``$key``.
	**
	** @param $key int
	**   The key of the record being searched for.
	**
	** @returns int
	**   The number of records found. 
	**   This will be either ``1`` if the record was found or ``0`` if not found.
	**
	** @throws IMuException
	**   If a server-side error occured.
	*/
	public function
	findKey($key)
	{
		return $this->call('findKey', $key) + 0;
	}
	
	/*!
	** Searches for records with key values in the array ``$keys``.
	**
	** @param $keys array
	**   The list of keys being searched for.
	**
	** @returns int
	**   The number of records found.
	**
	** @throws IMuException
	**   If a server-side error occured.
	*/
	public function
	findKeys($keys)
	{
		return $this->call('findKeys', $keys) + 0;
	}

	/*!
	** Searches for records which match the search terms specified in ``$terms``.
	**
	** @param $terms mixed
	**   The search terms.
	**
	** @param $options hash
	**   A set of options to control the behaviour of the search.
	**
	** @returns int
	**   An estimate of the number of records found.
	**
	** @throws IMuException
	**   If a server-side error occurred.
	*/
	public function
	findTerms($terms, $options = null)
	{
		$class = 'IMuTerms';
		if ($terms instanceof $class)
		{
			$terms = $terms->toArray();
		}
		$args = array();
		$args['terms'] = $terms;
		if ($options !== null)
			$args['options'] = $options;
		return $this->call('findTerms', $args) + 0;
	}

	/*!
	** Searches for records which match the TexQL ``WHERE`` clause.
	**
	** @param $where string
	**   The TexQL ``WHERE`` clause to use.
	**
	** @returns int
	**   An estimate of the number of records found.
	**
	** @throws IMuException
	**   If a server-side error occurred.
	*/
	public function
	findWhere($where)
	{
		return $this->call('findWhere', $where) + 0;
	}
	
	public function
	insert($values, $columns = null)
	{
		$args = array();
		$args['values'] = $values;
		if ($columns !== null)
			$args['columns'] = $columns;
		return $this->call('insert', $args);
	}

	public function
	remove($flag, $offset, $count = null)
	{
		$args = array();
		$args['flag'] = $flag;
		$args['offset'] = $offset;
		if ($count !== null)
			$args['count'] = $count;
		return $this->call('remove', $args) + 0;
	}

	/*!
	** Restores a set of records from a file on the server machine which 
	** contains a list of keys, one per line.
	**
	** @param $file string
	**   The file on the server machine containing the keys.
	**
	** @returns int
	**   The number of records found.
	**
	** @throws IMuException
	**   If a server-side error occured.
	*/
	public function
	restoreFromFile($file)
	{
		$args = array();
		$args['file'] = $file;
		return $this->call('restoreFromFile', $args) + 0;
	}

	/*!
	** Restores a set of records from a temporary file on the server machine 
	** which contains a list of keys, one per line. 
	**
	** Operates the same way as **restoreFromFile( )** 
	** [$<link>(:module:restoreFromFile)] except that the ``file`` parameter is
	** relative to the server's temporary directory.
	**
	** @param $file string
	**   The file on the server machine containing the keys.
	**
	** @returns int
	**   The number of records found.
	**
	** @throws IMuException
	**   If a server-side error occured.
	*/
	public function
	restoreFromTemp($file)
	{
		$args = array();
		$args['file'] = $file;
		return $this->call('restoreFromTemp', $args) + 0;
	}

	/*!
	** Sorts the current result set by the sort keys in ``$keys``. 
	** Each sort key is a column name optionally preceded by a ``+`` (for an 
	** ascending sort) or a ``-`` (for descending sort).
	**
	** @param $keys mixed
	**   A `string` or array of `string`\s containing the list of sort keys.
	**   Each string can contain one or more keys, separated by a ``semi-colon``
	**   or a ``comma``.
	**
	** @param $flags mixed
	**   A `string` or array of `string`\s containing a set of flags specifying
	**   the behaviour of the sort. 
	**   Each string can contain one or more flags, separated by a 
	**   ``semi-colon`` or a ``comma``.
	**
	** @returns ModuleSortResult
	**   An array containing the report information if the report flag has been
	**   specified. Otherwise the result will be ``null``.
	**
	** @throws IMuException
	**   If a server-side error occurred.
	*/
	public function
	sort($columns, $flags = null)
	{
		$args = array();
		$args['columns'] = $columns;
		if ($flags !== null)
			$args['flags'] = $flags;
		return $this->call('sort', $args);
	}

	public function
	setMatchLimit($limit)
	{
		return $this->call('setMatchLimit', $limit);
	}

	public function
	update($flag, $offset, $count, $values, $columns = null)
	{
		$args = array();
		$args['flag'] = $flag;
		$args['offset'] = $offset;
		$args['count'] = $count;
		$args['values'] = $values;
		if ($columns !== null)
			$args['columns'] = $columns;
		return $this->makeResult($this->call('update', $args));
	}

	public function
	updateMany($list,$values,$columns)
	{
		$args = array();
		$args['list'] = $list;
		$args['values'] = $values;
		$args['columns'] = $columns;

		return $this->makeResult($this->call('updateMany', $args));
	}

	protected $_table;

	protected function
	makeResult($data)
	{
		$result = new IMuModuleFetchResult;
		$result->hits = $data['hits'];
		$result->rows = $data['rows'];
		$result->count = count($result->rows);
		return $result;
	}
}

/*!
** Provides results from a call to the `IMuModule` **fetch( )**
** [$<link>(:module:fetch)] method.
** 
** @usage
**   require_once IMu::$lib . '/Module.php'
** @end
**
** @since 1.0
*/
class IMuModuleFetchResult
{
	/* Properties */
	/*!
	** @property $count int
	**   The number of records returned in the result.
	*/
	public $count;

	/*!
	** @property $hits int
	**   The best estimate of the size of the result set after the fetch method
	**   has completed. 
	**
	**   When the `Module` object [$<link>(:module:module)] generates a result
	**   set using **findTerms( )** [$<link>(:module:findTerms)] or 
	**   **findWhere( )** [$<link>(:module:findWhere)], the number of matches
	**   is occasionally an overestimate of the true number of matches. 
	**
	**   After the **fetch( )** [$<link(:module:fetch)] method has been called,
	**   the IMu server may have a better estimate of the true numeber of
	**   matches so it is inclued in the result.
	*/
	public $hits;

	/*!
	** @property $rows array
	**   The array of the records actually fetched. 
	**
	**   Each record is represented by an associative array with the keys being 
	**   the names of the columns requested in the **fetch( )**
	**   [$<link>(:module:fetch)] call.
	*/
	public $rows;
}
?>
