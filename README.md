CLICKEAT TEST - POPPY'S ADVENTURE
==================================

Here is my project for the technical test of Click Lab.

Technical stack
---------------
* PHP 7.4
* Apache 2.4
* Symfony 5.2
* MongoDB 4.4
* DoctrineMongoDBBundle 4.3

Installation
------------

Simply run the docker-compose file:

``docker-composer up``

Usage
-----

Go to http://localhost
This page presents you a short documentation of all API endpoints with parameters and example.
To use an API endpoint, simply type the URL in your browser.

Run tests
---------

Open a terminal in the container that runs docker image 'php' and run the following command:

``php bin/phpunit``

Troubleshooting
---------------

On Linux, it will work if Docker version >= 20.10.
If you have another version, either change host.docker.internal for DB host in file .env 
(and .env.test.local to run tests) by setting it to "mongo", or change these lines of the docker-compose file:

```yaml
extra_hosts:
    - "host.docker.internal:host-gateway"
```

by this:

```yaml
network_mode: host
```
