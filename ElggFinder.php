<?php
/**
 * Elgg helper to find entities in the database
 *
 * Usage :
 *   $entities = Finder('album')->
 *     createdBetween('4 days ago')->and('yesterday')->
 *     ownedByMe()->
 *     find(10);
 */
class ElggFinder
{
  protected
    $params = array(
//      'type'      => null,
//      'subtype'   => null,
//      'offset'    => null,
//      'limit'     => null,
//      'order_by'  => null,
//      'user_guid' => null,
      'joins' => array(),
    ),
    $and = null,
    $entities = null;

  /**
   *
   * @param string $subtype
   * @param string $type 
   */
  public function __construct($subtype = null, $type = null)
  {
    $this->params['subtype'] = $subtype;
    $this->params['type'] = is_null($type) ? 'object' : $type;
  }

  // DATE & TIME

  /**
   *
   * @param string $date
   * @return ElggFinder
   */
  public function createdBefore($date)
  {
    $this->params['created_time_upper'] = strtotime($date);
    return $this;
  }

  /**
   *
   * @param string $date
   * @return ElggFinder
   */
  public function modifiedBefore($date)
  {
    $this->params['modified_time_upper'] = strtotime($date);
    return $this;
  }

  /**
   *
   * @param string $date
   * @return ElggFinder
   */
  public function before($date)
  {
    return $this->createdBefore($date);
  }

  /**
   *
   * @param string $date
   * @return ElggFinder
   */
  public function createdAfter($date)
  {
    $this->params['created_time_lower'] = strtotime($date);
    return $this;
  }

  /**
   *
   * @param string $date
   * @return ElggFinder
   */
  public function modifiedAfter($date)
  {
    $this->params['modified_time_lower'] = strtotime($date);
    return $this;
  }

  /**
   *
   * @param string $date
   * @return ElggFinder
   */
  public function after($date)
  {
    return $this->createdAfter($date);
  }


  /**
   *
   * @param string $date
   * @return ElggFinder
   */
  public function createdBetween($date)
  {
    $this->params['created_time_lower'] = strtotime($date);
    $this->and = 'createdBefore';
    return $this;
  }

  /**
   *
   * @param string $date
   * @return ElggFinder
   */
  public function modifiedBetween($date)
  {
    $this->params['modified_time_lower'] = strtotime($date);
    $this->and = 'modifiedBefore';
    return $this;
  }

  /**
   *
   * @param string $date
   * @return ElggFinder
   */
  public function between($date)
  {
    $this->and = 'createdBefore';
    return $this->createdBetween($date);
  }

  // OWNER

  /**
   *
   * @param mixed Owner(s)
   * @return ElggFinder
   */
  public function ownedBy()
  {
    $this->owner_guids = '';
    foreach (func_get_args() as $arg)
    {
      $this->owner_guids .= ', '.$this->getUserGUID($arg);
    }
    $this->owner_guids = substr($this->owner_guids, 2);
    return $this;
  }

  /**
   *
   * @return ElggFinder
   */
  public function ownedByMe()
  {
    $this->owner_guids = $this->getUserGUID();
    return $this;
  }

  /**
   *
   * @return ElggFinder
   */
  public function ownedByUser($user)
  {
    $this->owner_guids = $this->getUserGUID($user);
    return $this;
  }

  /**
   *
   * @param mixed $user Owner
   * @return ElggFinder
   */
  public function ownedByFriendsOf($user)
  {
//    $friends = Finder('user')->
    $this->owner_guids = $this->getUserGUID($user);
    return $this;
  }

  // WHERE

  /**
   *
   * @param string $relationship
   * @param mixed $target guid_2 or array of guid_2
   * @return ElggFinder
   */
  public function rel($relationship, $target = null)
  {
    $this->joins[] = 'JOIN entity_relationships rel ON rel.guid_one = entities.guid';
    $this->where[] = 'rel.relationship = "'.$relationship.'"';
    if (isset($target))
    {
      if (is_array($target))
      {
        $this->wheres[] = 'rel.guid_two IN('.implode(', ', $target).')';
      }
      else
      {
        $this->wheres[] = 'rel.guid_two = '.$target;
      }
    }

    return $this;
  }

  // FIND


  /**
   * Find entities in the database
   *
   * Params :
   *   - types => NULL|STR entity type (SQL: type IN ('type1', 'type2') Joined with subtypes by AND...see below)
   *   - subtypes => NULL|STR entity subtype (SQL: subtype IN ('subtype1', 'subtype2))
   *   - type_subtype_pairs => NULL|ARR (array('type' => 'subtype')) (SQL: type = '$type' AND subtype = '$subtype') pairs
   *   - owner_guids => NULL|INT entity guid
   *   - container_guids => NULL|INT container_guid
   *   - site_guids => NULL (current_site)|INT site_guid
   *   - order_by => NULL (time_created desc)|STR SQL order by clause
   *   - limit => NULL (10)|INT SQL limit clause
   *   - offset => NULL (0)|INT SQL offset clause
   *   - created_time_lower => NULL|INT Created time lower boundary in epoch time
   *   - created_time_upper => NULL|INT Created time upper boundary in epoch time
   *   - modified_time_lower => NULL|INT Modified time lower boundary in epoch time
   *   - modified_time_upper => NULL|INT Modified time upper boundary in epoch time
   *   - count => TRUE|FALSE return a count instead of entities
   *   - wheres => array() Additional where clauses to AND together
   *   - joins => array() Additional joins
   *
   * @param int $limit
   * @param int $offset
   * @return Array of ElggEntity
   */
  public function find($limit = null, $offset = null)
  {
    if ($limit)
    {
      $this->params['limit'] = $limit;
    }
    if ($offset)
    {
      $this->params['offset'] = $limit;
    }

    return elgg_get_entities($this->params);
  }

  /**
   *
   * @return Array of ElggEntity
   */
  public function findAll()
  {
    return $this->find();
  }

  /**
   *
   * @return ElggEntity
   */
  public function findOne()
  {
    return $this->find(1);
  }

  /**
   *
   * @return ElggEntity
   */
  public function findByGUID($guid)
  {
    return get_entity($guid);
  }

  // ALTERATION (CREATE, UPDATE & DELETE)

  /**
   *
   * @param int $limit
   * @param int $offset
   * @return int
   */
  public function delete($limit = null, $offset = null)
  {
    $entities = $this->find($limit, $offset);
    $count = 0;
    foreach ($entities as $entity)
    {
      delete_entity($entity->guid);
      $count++;
    }

    return $count;
  }

  /**
   * Disable entities but don't delete them
   *
   * @param string $reason Reason of deletion
   * @param int $limit
   * @param int $offset
   * @return int
   */
  public function disable($reason = '', $limit = null, $offset = null)
  {
    $entities = $this->find($limit, $offset);
    $count = 0;
    foreach ($entities as $entity)
    {
      disable_entity($entity->guid, $reason);
      $count++;
    }

    return $count;
  }

  /**
   * Enable entities
   *
   * @param int $limit
   * @param int $offset
   * @return int
   */
  public function enable($limit = null, $offset = null)
  {
    $entities = $this->find($limit, $offset);
    $count = 0;
    foreach ($entities as $entity)
    {
      enable_entity($entity->guid);
      $count++;
    }

    return $count;
  }

  /**
   *
   * @param string $attribute
   * @param mixed $value
   * @return ElggFinder
   */
  public function set($attribute, $value)
  {
    if (is_null($this->entities))
    {
      $this->find();
    }

    foreach ($this->entities as $entity)
    {
      $entity->$attribute = $value;
    }

    return $this;
  }

  /**
   *
   * @return int
   */
  public function save()
  {
    $count = 0;
    foreach ($this->entities as $entity)
    {
      $entity->save();
      $count++;
    }

    return $count;
  }

  // UNLEASH THE MAGIC

  /**
   *
   * @param string $name
   * @param array $arguments
   * @return mixed
   */
  public function  __call($name, $arguments)
  {
    switch(true)
    {
      // set<Attribute>(value)
      case substr($name, 0, 3) == 'set':
        return call_user_method_array(substr($name, 3), $this, $arguments);
        break;
    }
  }

  /**
   *
   * @return ElggFinder
   */
  public function _and()
  {
    if (!is_null($this->and))
    {
      call_user_method_array($this->and, $this, func_get_args());
    }
    $this->and = null;

    return $this;
  }

  /**
   * Retrieve user GUID from various data (user GUID, user object, online user)
   * @param mixed $user User
   * @return int
   */
  protected function getUserGUID($user = null)
  {
    if (is_null($user))
    {
      return get_loggedin_userid();
    }
    if (is_string($user))
    {
      return get_user_by_username($user)->guid;
    }
    if (is_int($user))
    {
      return $user;
    }
    if (is_object($user))
    {
      return $user->guid;
    }

    return null;
  }
}

/**
 *
 * @param string $subtype
 * @return ElggFinder
 */
function Finder($subtype = null)
{
  if ($subtype && class_exists($finderClass = $subtype.'Finder'))
  {
    return new $finderClass();
  }
  elseif ($subtype)
  {
    return new ElggFinder($subtype);
  }

  return new ElggFinder();
}