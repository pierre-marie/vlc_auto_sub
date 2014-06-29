#!/bin/sh
for i in `ps aux | grep vlc_sub | awk '{print $2}'`;
do
	if [ "$i" != "$1" ] ; then
    	kill -9 $i;
    fi
done
sleep 1;
for j in `ps aux | grep VLC | awk '{print $2}'`;
do
	if [ "$i" != "$1" ] ; then
	    kill -9 $j;
	fi
done
