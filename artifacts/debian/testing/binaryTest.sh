#!/usr/bin/env bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo $DIR
function testPharIsNotCorrupted {
     ${DIR}/../bin/phpmetrics --version|grep "PhpMetrics"
}

testPharIsNotCorrupted > /dev/null && echo "." ||exit 1