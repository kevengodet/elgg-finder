What is ElggFinder?
===================

**ElggFinder** is an Elgg helper in very early dev stage designed to find entities in a fluent way.

Example:

    $blogs = Finder("blog")->
    ownedByMyFriends()->
    createdBetween("7 days ago")->_and("yesterday")->
    find(10);

which means, of course: "find ten blogs created by my friends between one week ago and yesterday".

Functionnalities
================

Dates
-----

```php
createdBefore(date)
createdAfter(date)
createdBetween(date) [..] _and(date) // don't forget the _ because "and" without it in a reserved word in PHP
before(date)
after(date)
between(date) [..] _and(date)

modifiedBefore(date)
modifiedAfter(date)
modifiedBetween(date) [..] _and(date)
```

Owner
-----

    ownedBy(user, user, ...) // You can provide GUID or ElggUser instances
    ownedByMe() // Me = logged user
    ownedByFriendsOf(user, user, ...) // You can provide GUID or ElggUser instances

Relationship
------------

    rel(relation, pair) // In <relationship> with <pair> (optional)
    relRelation(pair) // Magic method

Actions
-------

    find(count, limit)
    findAll()
    findOne()
    findByGUID()
    disable()
    enable()
    delete()
    set(field, value)
    setField(value) // Magic method
    save()

Please test it and give a feedback (bugs and features requests).
Don't use it in production.
