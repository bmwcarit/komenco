Hacking
=======

In case you want to contribute to komenco, here is a step-by-step instruction on
how to setup your own hack space.

Installation
------------

komenco is using docker containers to run everything from production to
development, even the tests are executed in a docker container. In fact the
production and the development container are one and the same and differ only
in the version of the komenco source code.

### Prerequisites ###

The following software is required on your system:

* [docker](https://www.docker.com/)
* [docker-composer](https://docs.docker.com/compose/)

Many of the major distributions provide packages for docker, but as they are not
necessarily providing the latest versions of docker we recommend that you follow
the installation instructions on the docker website.

docker-compose can be currently installed via pip. Please see the
docker-compose page for up-to-date installation instructions for your system.

### Setup ###

After checking out this repository clone the docker configuration of the komenco
base containers and build them

    git clone https://github.com/bmwcarit/komenco-docker-base.git
    cd komenco-docker-base
    ./build.sh

Once the base containers are built you can clone the docker configuration of
komenco

    git clone --recursive https://github.com/bmwcarit/komenco-docker.git
    cd komenco-docker

Create the docker-compose configuration

    ./setup.sh [PATH TO YOUR KOMENCO SOURCE FOLDER]

And finally create the docker containers

    docker-composer build

### Configuration ###

The config folder holds a JSON file with the configuration. We are using the
environment variable `APP_ENVIRONMENT` to determine the name of the file.

For example with `APP_ENVIRONMENT` set to `dev` the name of the file is
`dev.json`. This allows us to switch between different configurations easily. If
the environment variable is not set the configuration found in `default.json`
will be used.

The minimal configuration needs an URL of the OpenID server for authentication

    {
        "openid_server_url":"https://%KOMENCO_SIMPLEID_IP%/simpleid",
    }

Running komenco
---------------

Start the containers.

    docker-compose up -d

To complete the setup run the test suite once

    docker-compose run test run

After the tests suite is successfully finished, you can either check the logs
or inspect the komenco container to find out the IP address.

    docker-composer logs
    docker inspect --format '{{ .NetworkSettings.IPAddress }}' komencodocker_komenco_1

Now you can access komenco at

    http://<IP ADDRESS OF KOMENCO CONTAINER>/komenco

As your source folder is mounted to the container all your changes are instantly
available and can be tested. To modify the source you can use whatever IDE you
like best and simply create a project from your local source.

To run the komenco tests suite you can simply call (as during the setup step)

    docker-compose run test run

The suite contains unit as well as user interface tests that use a selenium
server that is also running inside a docker container.

We are using codeception to drive the whole test suite and you can thus also add
codeception options to the run command to execute specific tests or raise the
debug level:

    docker-compose run test <CODECEPTION COMMAND> <CODECEPTION OPTIONS>

Patch Style
-----------

We basically only have two rules on patches. A patch ...

* ... contains a small and atomic change
* ... has an good commit message

*Small and atomic commits*

Please keep patches small and concise. This helps making reviews fast and easy
and with the addition of good commit messages we create up-to-date documentation
on the fly.

*Good commit messages*

We are using the 50/80 rule on commit messages.

Meaning that a *commit message* has a subject line with a maximum of 50
characters. Followed by a blank line is a detailed description where each line
is 80 characters long at max.

The *subject line* contains of a prefix and a short summary in passive present
tense language. The prefix can be inferred by the following rules:

* Changes on top level files have the prefix *komenco*
* Changes in the src/komenco folder have a prefix that equals the name of the
  subfolder
* Changes in the res folder have a prefix that equals the name of the
  subfolder
* All other changes have prefix that equals the name of the top level folder

For example a change in the views folder:

> views: Add name to users tables

The *description* explains the content of the commit. This usually contains

* the problem or misbehavior
* the solution
* additional information

Please do not assume that the code is self-documenting, no matter how good it
is. If you need inspiration just browse the git log for examples.

Coding Style
------------

We use several different languages in this project and we do not have
sophisticated rules for managing them. Instead we encourage you to apply some
common sense when it comes to naming classes, methods and variables.

Additionally we have some general formatting rules. The main goal of these rules
is to increase readability of the overall code.

* Use unix line endings (CR, \n).
* For indentions use tabs in the size of 4 spaces instead of only spaces.
* Remove unnecessary white spaces. You can spot them in gerrit and also in git
  diff with the following setting
  (git config --global color.diff.whitespace "red reverse")
* Wrap lines and strings after 80 characters. This rule is relaxed only if
  readability is significantly decreased.
* Always use curly braces - even for single line if statements. Keep the opening
  brace in the same line as the statement.