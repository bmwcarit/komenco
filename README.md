komenco
=======

A ready-to-rock web application foundation. No need for gory setup details -
start writing beautiful applications instantly!

It is an [silex](http://silex.sensiolabs.org/) application packed with
numerous pre-configured services:

* OpenID based login, supporting ([SimpleID](http://simpleid.koinic.net/) and
  [CrowdID](https://confluence.atlassian.com/display/CROWD/CrowdID+User+Guide))
* [Twitter Bootstrap](http://getbootstrap.com/) powered user interface
* [Atlassian JIRA](https://www.atlassian.com/software/jira) integration
* ORM enabled database with [Propel](http://propelorm.org/)
* Automatic resource management with
  [Assetic](https://github.com/kriswallsmith/assetic)
* Ready for behavior driven testing with [Codeception](http://codeception.com/)

Installation
------------

The most easiest way to run komenco in a self-contained way is by using the
[docker containers](https://github.com/bmwcarit/komenco-docker).

This will fire up an empty komenco application, which is not very exciting but
will give you an idea about how it will look like. To use komenco for
your own application see the usage section.

### Clone the docker configuration and create the containers ###

    git clone https://github.com/bmwcarit/komenco-docker-base.git
    cd komenco-docker-base
    ./build.sh
    cd ..
    git clone --recursive https://github.com/bmwcarit/komenco-docker.git
    cd komenco-docker
    ./setup.sh [PATH TO YOUR KOMENCO SOURCE FOLDER]
    docker-composer build

### Start komenco and finish the setup ###

    docker-compose up -d
    docker-compose run test run

Get the IP Address of the komenco container

    docker inspect -format '{{ .NetworkSettings.IPAddress }}' komencodocker_komenco_1

Browse to ``http://<IP ADDRESS OF KOMENCO CONTAINER>/komenco`` to access
komenco.

Usage
-----

See the documentation for details on how to use komenco to create your own
application.

License
-------

The komenco is licensed under the MIT license (this includes provided
artefacts, such as images).
