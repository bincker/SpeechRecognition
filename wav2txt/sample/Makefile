all:sample

sample:sample.cpp 
	g++ $^ ../libs/libusc.so  -Wall -O2 -o $@ -L. 

clean:
	rm -f sample
	rm -f sample.o
