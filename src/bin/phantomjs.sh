#!/bin/bash
#
# phantomjs.sh
#	start | stop phantomjs

NAME=phantomjs
BIN=phantomjs

function die() {
	echo "*** error: $@"
	exit 1
}

function start() {
	if ! is_running ; then
		# start the process
		echo "Starting $NAME in a screen"
		screen -d -m -S $NAME $BIN --webdriver=8910 --webdriver-selenium-grid-hub=http://127.0.0.1:4444

		# did it start?
		sleep 2
		is_running
	fi
}

function stop() {
	local pid=`get_pid`

	if [[ -z $pid ]] ; then
		echo "$NAME was not running"
		return 0
	fi

	kill $pid
	pid=`get_pid`
	if [[ -n $pid ]] ; then
		sleep 2
		pid=`get_pid`
	fi

	if [[ -n $pid ]] ; then
		kill -9 $pid
		pid=`get_pid`
	fi

	if [[ -n $pid ]] ; then
		echo "$NAME is running as pid $pid, and has ignored attempts to terminate"
		return 1
	fi

	echo "$NAME has been stopped"
}
function restart() {
	local pid=`get_pid`

	if [[ -n $pid ]] ; then
		stop
	fi

	start
}

function is_running() {
	local pid=`get_pid`

	if [[ -n $pid ]] ; then
		echo "$NAME is running as pid $pid"
		return 0
	fi

	echo "$NAME is not running"
	return 1
}

function monitor() {
	local pid=`get_pid`

	if [[ -z $pid ]] ; then
		echo "$NAME is not running"
		exit 1
	fi

	screen -rd $NAME
}

function usage() {
	echo "usage: $NAME.sh <start|stop|restart|status|monitor>"
}

function get_pid() {
	# get the pid of our daemon
	local pid=`ps -ef | grep "$NAME" | grep [S]CREEN | awk {' print $2 '}`

	if [[ -n $pid ]] ; then
		echo "$pid"
	fi
}

case "$1" in
	"status")
		is_running
		;;
	"stop")
		stop
		;;
	"restart")
		restart
		;;
	"start")
		start
		;;
	"monitor")
		monitor
		;;
	*)
		usage
		;;
esac