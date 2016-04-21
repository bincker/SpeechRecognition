// sample.cpp : Defines the entry point for the console application.
//

#include <fcntl.h>
#include <string.h>
#include <stdio.h>
#include <stdlib.h>
#include <string>
#include "../libs/libusc.h"
#include "../libs/appKey.h"

int main(int argc, char* argv[])
{
	if( argc < 4) {
		fprintf(stderr,"Usage: sample ip port <file.pcm> <utf8_result.txt> [pcm16k|pcm8k] \n");
		return -1;
	}

	const char * host = argv[1];
	const short port = atoi(argv[2]);
	const char * pcmFileName = argv[3];
	const char * resultFileName = argv[4];
	std::string result = "";
	char buff[640];

	// 打开结果保存文件(UTF-8)
	FILE* fresult = fopen(resultFileName, "a+");
	if( fresult == NULL) {
		fprintf(stderr,"Cann't Create File %s\n", resultFileName);
		return -2;
	}
	
	// 打开语音文件(16K/8K 16Bit pcm)
	FILE* fpcm = fopen(pcmFileName, "rb");
	if( fpcm == NULL) {
		fprintf(stderr,"Cann't Open File %s\n", pcmFileName);
		return -3;
	}

	fprintf(stderr,pcmFileName);
	fprintf(fresult,pcmFileName);
	
	// 识别语音的格式
	//const char *audio_format = AUDIO_FORMAT_PCM_8K;
	const char *audio_format = AUDIO_FORMAT_PCM_16K;
	if(argc > 5) {
		audio_format = argv[5];
	}
	
	fprintf(stderr,"\t%s\t", audio_format);
	fprintf(fresult,"\t%s\t", audio_format);

	// 创建识别实例
	USC_HANDLE handle;
	int ret = usc_create_service_ext(&handle, host, port);
	if(ret != USC_OK) {
		fprintf(stderr,"usc_create_service_ext error %d\n", ret);
		goto close_files;
	}
	
	// 设置识别AppKey
	ret = usc_set_option(handle, USC_OPT_APP_KEY, USC_ASR_SDK_APP_KEY);
	
	// 设置输入语音的格式
	ret = usc_set_option(handle, USC_OPT_INPUT_AUDIO_FORMAT, audio_format);

	// 设置噪音过滤
	//ret = usc_set_option(handle, USC_OPT_NOISE_FILTER, USC_ENABLE);

	ret = usc_start_recognizer(handle);
	if(ret != USC_OK ) {
		fprintf(stderr,"usc_start_recognizer error %d\n", ret);
		goto usc_release;
	}
	
	while(true){

		int nRead = fread(buff,sizeof(char), sizeof(buff),fpcm);
		if(nRead <= 0) {
			break;
		}
		// 传入语音数据
		ret = usc_feed_buffer(handle, buff, nRead);

		if(ret == USC_RECOGNIZER_PARTIAL_RESULT ||
		   ret == USC_RECOGNIZER_SPEAK_END) {

			// 获取中间部分识别结果
			result.append(usc_get_result(handle));	
		}
		else if( ret < 0) {

			// 网络出现错误退出
			fprintf(stderr,"usc_feed_buffer error %d\n", ret);
			goto usc_release;
		}
	}	
	
	// 停止语音输入
	ret = usc_stop_recognizer(handle);
	if(ret == 0) {

		// 获取剩余识别结果
		result.append(usc_get_result(handle));
		fprintf(fresult, "%s\n",result.c_str()); 
		fprintf(stderr, "%s\n",result.c_str());
	}
	else {
		// 网络出现错误退出
		fprintf(stderr,"usc_stop_recognizer error %d\n", ret);
	}

usc_release:

	usc_release_service(handle);	
	
	if( ret != 0) {
		// 识别出现错误
		fprintf(fresult,"!!!!!!!!--------> Error %d <--------!!!!!!!!!!!\n", ret);
	}

close_files:

	fclose(fresult);
	fclose(fpcm);

	return ret;
}
