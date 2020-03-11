#!/bin/bash

while sleep 1; do APP_DEBUG=1 bin/console dev:telegram:updates; done