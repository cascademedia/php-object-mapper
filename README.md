PHP Object Mapper
=================
The php-object-mapper library provides the ability to transform data from one type into another type.

[![Build Status](https://scrutinizer-ci.com/g/cascademedia/php-object-mapper/badges/build.png?b=master)](https://scrutinizer-ci.com/g/cascademedia/php-object-mapper/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/cascademedia/php-object-mapper/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/cascademedia/php-object-mapper/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cascademedia/php-object-mapper/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/cascademedia/php-object-mapper/?branch=master)

Installation
============
To install php-object-mapper, simply require the library by executing the following composer command.

```
$ composer require cascademedia/php-object-mapper @stable
```

Alternatively, you can clone/download this repository and install the package manually.

Basic Usage
===========
TODO.

```php
class MyContext implements ContextInterface
{
    public function getMap()
    {
        return (new Map())
            ->from(Map::REF_ARRAY)
            ->to(Map::REF_ARRAY)
            ->add('first_name', 'fname')
            ->add('last_name', 'lname')
        ;
    }
}

$originalDestination = [
    'first_name' => 'TBD',
    'last_name' => 'TBD'
];

$source = [
    'fname' => 'Test First',
    'lname' => 'Test Last'
];

$mapper = new Mapper();
$context = new MyContext();

$updatedDestination = $mapper->map($originalDestination, $source, $context);
var_dump($updatedDestination);
/*
array(2) {
  'first_name' =>
  string(10) "Test First"
  'last_name' =>
  string(9) "Test Last"
}
*/
```

Contexts
========
Contexts are classes that contain all of the rules used for an individual ```Mapper::map()``` operation. When using the
mapper, it is required to contain mapping rules within a context.

To create a context, create a class that implements ```ContextInterface``` and use ```getMap()``` to return a map
containing all mapping rules desired.

```php
class MyContext implements ContextInterface
{
    public function getMap()
    {
        return (new Map())
            ->from(Map::REF_ARRAY)
            ->to(Map::REF_ARRAY)
            ->add('first_name', 'fname')
            ->add('last_name', 'lname')
        ;
    }
}

$source = [
    'fname' => 'Test First',
    'lname' => 'Test Last'
];

$mapper = new Mapper();

$result = $mapper->map([], $source, new MyContext());
var_dump($result);
/*
array(2) {
  'first_name' =>
  string(10) "Test First"
  'last_name' =>
  string(9) "Test Last"
}
*/
```

References
==========
References are classes used by the mapper to to retrieve or store field data in an object or array. The mapper currently
supports array, object mutator, and object property references.

Reference classes are not typically used directly unless you are manually creating field mappings.

Array References
----------------
The ```ArrayReference``` class tells the mapper that you wish to access data contained within the top level of an
array.

The ```getValue()``` method allows users to retrieve data from any given array.
```php
$reference = new ArrayReference('first_name');

$data = [
    'first_name' => 'Test First',
    'last_name' => 'Test Last'
];

var_dump($reference->getValue($data));
// string(10) "Test First"
```

The ```setValue()``` method allows users to put data into any given array. Note that this method returns a copy of the
modified array and does not modify the original array passed into it.
```php
var_dump($reference->setValue($data, 'Another Test First'));
/*
array(2) {
  'first_name' =>
  string(18) "Another Test First"
  'last_name' =>
  string(9) "Test Last"
}
*/
```

Mutator References
------------------
The ```MutatorReference``` class tells the mapper that you wish to access data returned from a class' method call. By
default, this reference will attempt to use getters and setters of the named field. For example, referencing a
field named ```test``` will call ```getTest()``` and ```setTest()``` respectively, but can be configured to call other
methods if necessary.

Note that only public methods can be accessed. Accessing private and protected methods will result in a
```ReflectionException``` being thrown.

The ```getValue()``` method will call the configured getter method for the given object and return its result.
```php
class User
{
    private $firstName;

    private $lastName;

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }
}

$reference = new MutatorReference('first_name');

$user = (new User())
    ->setFirstName('Test First')
    ->setLastName('Test Last')
;

var_dump($reference->getValue($user));
// string(10) "Test First"
```

The ```setValue()``` method will call the configured setter method for the given object.
```php
var_dump($reference->setValue($user, 'Another Test First'));
/*
class User#3 (2) {
  private $firstName =>
  string(18) "Another Test First"
  private $lastName =>
  string(9) "Test Last"
}
*/
```

If the default getters and setters are not satisfactory, you can change the methods that are called via the reference's
constructor.
```php
$reference = new MutatorReference('first_name', 'retrieveFirstName', 'addFirstName');
```

Property References
-------------------
The ```PropertyReference``` class tells the mapper that you wish to access data contained within a class' public
property. Note that only public properties can be accessed. Accessing private and protected properties will result in
a ```ReflectionException``` being thrown.

The ```getValue()``` method will return the data contained in the referenced object property.
```php
class User
{
    public $firstName;

    public $lastName;
}

$reference = new PropertyReference('firstName');

$user = new User();
$user->firstName = 'Test First';
$user->lastName = 'Test Last';

var_dump($reference->getValue($user));
// string(10) "Test First"
```

The ```setValue()``` method will put data into the referenced object property.
```php
var_dump($reference->setValue($user, 'Another Test First'));
/*
class User#3 (2) {
  public $firstName =>
  string(18) "Another Test First"
  public $lastName =>
  string(9) "Test Last"
}
*/
```

Mappings
========
Mapping classes are the workhorse of the mapper library. They are the classes that map individual pieces of data using
references to determine where the data comes from and where it goes.

Mapping
-------
The ```Mapping``` class is the most straightforward of all mappings. It simply takes the source and maps it directly to
the configured destination. When constructing the ```Mapping``` class, the destination reference comes first followed
by the source reference.

```php
$source = [
    'fname' => 'First',
    'lname' => 'Last'
];

$mapping = new Mapping(new ArrayReference('first_name'), new ArrayReference('fname'));

$result = $mapping->map([], $source);
var_dump($result);
/*
array(1) {
  'first_name' =>
  string(5) "First"
}
*/
```

Embedded Mapping
----------------
TODO.

Resolver Mapping
----------------
TODO.

Value Resolvers
===============
A value resolver is an object that will take the entire source object and return an individual value from it. Using a
value resolver allows for arbitrary logic in order to map a value.

```php
class FullNameResolver implements ValueResolverInterface
{
    public function resolve($source)
    {
        return $source['fname'] . ' ' . $source['lname'];
    }
}

$source = [
    'fname' => 'First',
    'lname' => 'Last'
];

$resolver = new FullNameResolver();
$result = $resolver->resolve($source);
var_dump($result);
// string(10) "First Last"
```

To use a value resolver inside of a map, simply add it using the ```Map::addResolver()``` method.
```php
(new Map())
    ->addResolver('full_name', new FullNameResolver())
;
```

Want to contribute?
===================
TODO.

License
=======
This library is MIT licensed, meaning it is free for anyone to use and modify.

```
The MIT License (MIT)

Copyright (c) 2015 Cascade Media, LLC.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```
