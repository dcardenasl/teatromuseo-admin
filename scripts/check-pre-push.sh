#!/usr/bin/env sh

set -eu

echo 'Running Admin pre-push quality gate...'
composer quality
npm run lint:all
npm run test:js
echo 'Admin pre-push quality gate passed.'
