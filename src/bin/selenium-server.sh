#!/bin/bash
#
# selenium-server.sh
#	start | stop selenium-server.sh

JAR=selenium-server-standalone-2.44.0.jar
NAME=selenium-server

# special case - where is the JAR file?
DATA_DIR="@@DATA_DIR@@"
if [[ $DATA_DIR == "@""@DATA_DIR@@" ]] ; then
	# we are running out of a vendor folder
	if [[ -f `dirname $0`/$JAR ]] ; then
		DATA_DIR="`dirname $0`"
	else
		DATA_DIR="`dirname $0`/../data/webdriver"
	fi
else
	# PEAR makes DATA_DIR point at a top-level, not at
	# something that's package-specific
	DATA_DIR="$DATA_DIR/webdriver"
fi

# special case - where are our webdrivers?
BIN_DIR="@@BIN_DIR@@"
if [[ $BIN_DIR == "@""@BIN_DIR@@" ]] ; then
	# we are running out of a vendor folder
	BIN_DIR="`dirname $0`/../bin"
fi

# the binary we are going to run
chromedriver="chromedriver"
if [[ `uname` == 'Darwin' ]] ; then
	firefox="/Applications/Firefox.app/Contents/MacOS/firefox-bin"
else
	firefox="`which firefox`"
fi

# the params to pass to the
PARAMS="-Dwebdriver.chrome.driver=$BIN_DIR/$chromedriver -Dwebdriver.firefox.bin=$firefox"

function die() {
	echo "*** error: $@"
	exit 1
}

# make sure we have Java installed
if ! which java > /dev/null 2>&1 ; then
	die "java not found. please install and then try again"
fi

# make sure we have our JAR installed
if [[ ! -e $DATA_DIR/$JAR ]] ; then
	die "$DATA_DIR/$JAR not found. do you need to download it?"
fi

# make sure we have our chromedriver installed
if [[ ! -e $BIN_DIR/chromedriver ]] ; then
	die "$BIN_DIR/chromedriver not found. do you need to download it?"
fi

function start() {
	if ! is_running ; then
		# start the process
		echo "Starting $NAME in a screen"
		screen -d -m -S $NAME java -jar "$DATA_DIR/$JAR" $PARAMS

		# did it start?
		sleep 1
		is_running
	fi
}

function stop() {
	local pid=`get_pid`

	if [[ -z $pid ]] ; then
		echo "$NAME was not running"
		return 0
	fi

	# stop the children first
	kill_children $pid

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

function kill_children() {
	local ppid=$1

	# get the pid of all the child processes
	for x in `ps -ef | awk '$3 == '${ppid}' { print $2 }'` ; do
		kill $x
	done
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