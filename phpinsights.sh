#!/bin/sh
docker run -it --rm -v "$(pwd):/app" nunomaduro/phpinsights $*