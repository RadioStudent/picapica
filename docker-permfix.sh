#!/bin/bash

# Fix owner of cache and logs and bundles, so that docker can use them properly
files=(app vendor bin composer.json composer.lock web . .composer)

for i in ${files[@]}; do
    chgrp -R 33 $i
    chmod -R g+w $i
done
