#!/bin/sh
set -e

if [ -z "${1:-}" ]; then
    echo "Zyphor: ✖ Missing package or app name" >&2
    exit 1
fi

zy pkg search "$1"