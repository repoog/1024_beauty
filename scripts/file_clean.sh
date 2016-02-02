#!/bin/sh

option=$1

# image file path
image_path="../www/weixin/images"

case "$option" in
	"-C" | "-c" )
		rm -f $image_path/*
	;;

	"-D" | "-d" )
		# list image file which size is more than 500k
		huge_list=$(find $image_path -size +500k)

		# delete huge image file
		for i in $huge_list
		do
			echo "Deleting file: $i"
			rm -f $i
		done
	;;

	* )
		echo "USAGE: file_clean.sh [ARGUMENT]"
		echo "	-C		Clear all files in image path."
		echo "	-D		Delete all files which size is more than 500K for reducing uploading and downloading time."
esac
