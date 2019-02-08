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

/*!
** This class is used to create a set of serch terms that is passed to the 
** IMu server.
**
** A `Terms` object can be passed to the **findTerms( )** 
** [$<link>(:module:findTerms)] method of either a `IMuModule`
** [$<link>(:module:module)] or `IMuModules` object.
**
** @usage
**  require_once IMu::$lib . '/Terms.php';
** @end
**
** @since 1.0
*/
class IMuTerms
{
	/* Constructor */
	/*!
	** Creates a new `IMuTerms` object with the given ``kind``.
	** 
	** @param $kind string
	**   The `boolean` operator for search terms.
	*/
	public function
	__construct($kind = 'and')
	{
		$kind = strtolower($kind);
		if ($kind != 'and' && $kind != 'or')
		{
			throw new Exception("Illegal kind: $kind");
		}
		$this->kind = $kind;
		$this->list = array();
	}

	/* Properties */
	/*!
	** @property kind string
	**   Gets the 'kind' property.
	*/
	public function
	getKind()
	{
		return($this->kind);
	}

	/*!
	** @property list mixed
	**   Gets the 'list' property.
	*/
	public function
	getList()
	{
		return($this->list);
	}

	/* Methods */
	/*!
	** Adds a new term to the list.
	** 
	** Omitting ``$op`` is the preferred method for adding terms in many cases 
	** as it allows the server to choose the most suitable operator.
	**
	** @param $name string
	**   The name of a column or search alias.
	**
	** @param $value string
	**   The value to match
	**
	** @param $op string
	**   An operator (such as ``contains``, ``=``, ``<``, etc.) for the
	**   server to apply when searching.
	*/
	public function
	add($name, $value, $op = null)
	{
		$term = array($name, $value, $op);
		$this->list[] = $term;
	}

	/*!
	** Adds an initially empty nested set of terms to the list.
	**
	** @param $kind string
	**   The boolean operator used for search terms added to the 
	**   returned `IMuTerms` object.
	** 
	** @returns IMuTerms
	**   The newly added `IMuTerms` object.
	*/
	public function
	addTerms($kind)
	{
		$child = new IMuTerms($kind);
		$this->list[] = $child;
		return($child);
	}

	/*!
	** Adds an initially empty nested set of ``AND`` terms to the list.
	**
	** Equivalent to:
	** @code
	**   addTerms('and');
	**
	** @returns IMuTerms
	**   The newly added `IMuTerms` object.
	*/
	public function
	addAnd()
	{
		return($this->addTerms('and'));
	}

	/*!
	** Adds an initially empty nested set of ``OR`` terms to the list.
	**
	** Equivalent to:
	** @code
	**   addTerms('or');
	**
	** @returns IMuTerms
	**   The newly added `IMuTerms` object.
	*/
	public function
	addOr()
	{
		return($this->addTerms('or'));
	}

	/*!
	** Returns the `IMuTerms` object as an array.
	**
	** @returns IMuTerms[]
	**   The `IMuTerms` object as an array.
	*/
	public function
	toArray()
	{
		$result = array();
		$result[0] = $this->kind;

		$list = array();
		for ($i = 0; $i < count($this->list); $i++)
		{
			$term = $this->list[$i];
			if ($term instanceof IMuTerms)
			{
				$term = $term->toArray();
			}
			$list[$i] = $term;
		}
		$result[1] = $list;

		return($result);
	}

	/*!
	** Returns a `string` representation of the `IMuTerms` object.
	**
	** @returns string
	**   A `string` representation of the `IMuTerms` object.
	*/
	public function
	__toString()
	{
		$result = '[';
		$result .= $this->kind;
		$result .= ', [';
		for ($i = 0; $i < count($this->list); $i++)
		{
			if ($i > 0)
			{
				$result .= ', ';
			}
			$term = $this->list[$i];
			if ($term instanceof IMuTerms)
			{
				$term = $term->__toString();
			}
			else
			{
				$term = '[' . implode(', ', $term) . ']';
			}
			$result .= $term;
		}
		$result .= ']]';
		return($result);
	}

	public function
	toString()
	{
		return($this->__toString());
	}

	private $kind = null;
	private $list = null;
}

?>
