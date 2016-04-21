#ifndef _LIBUSC_H_
#define _LIBUSC_H_


// 语音编码格式 
#define AUDIO_FORMAT_PCM_16K         "pcm16k"
#define AUDIO_FORMAT_PCM_8K          "pcm8k"
#define AUDIO_FORMAT_AMRNB           "amrnb"

// 识别领域
#define RECOGNITION_FIELD_GENERAL    "general"
#define RECOGNITION_FIELD_POI        "poi"
#define RECOGNITION_FIELD_SONG       "song"
#define RECOGNITION_FIELD_MEDICAL    "medical"
#define RECOGNITION_FIELD_MOVIETV    "movietv"

// 状态开关
#define USC_ENABLE        "true"
#define USC_DISABLED      "false"

#define LANGUAGE_ENGLISH             "english"
#define LANGUAGE_CANTONESE           "cantonese"
#define LANGUAGE_CHINESE             "chinese"


enum {
    // 识别正常
    USC_OK                           = 0,

    // 有结果返回
    USC_RECOGNIZER_PARTIAL_RESULT    = 2,

    // 检测到语音开始
    USC_RECOGNIZER_SPEAK_BEGIN       = 100,

    // 检测到语音结束
    USC_RECOGNIZER_SPEAK_END         = 101,

    // 调用失败
    USC_FATAL_ERROR                  = -70001,

    // 识别句柄错误
    USC_NO_HANDLE_INPUT              = -70002,

    // 参数错误
    USC_INVALID_PARAMETERS           = -70003,

	// 语音数据格式错误
    USC_INVALID_INPUT_DATA           = -70004,

	// 授权无效
    USC_EXPIRED_LICENSE              = -80001
};


enum {
    // 参数为APP_KEY
    USC_OPT_APP_KEY                  = 9,

    // 参数为用户ID
    USC_OPT_USER_ID                  = 14,

    // 选择识别领域 
    USC_OPT_RECOGNITION_FIELD        = 18,

    USC_ASR_ENGINE_PARAMETER	     = 34,
	
    // 输入语音编码格式
    USC_OPT_INPUT_AUDIO_FORMAT       = 1001,

};

// 定义识别句柄
typedef long long USC_HANDLE;

#ifdef WIN32
	#ifdef LIBUSC_EXPORTS
		#ifdef JNI_EXPORTS
			#define USC_API
		#else
			#define USC_API extern "C" __declspec(dllexport)
		#endif
	#else
		#define USC_API extern "C" __declspec(dllimport)
	#endif
#else
	#define USC_API extern "C" __attribute__ ((visibility("default")))
#endif


// 创建公有云识别
USC_API int usc_create_service(USC_HANDLE* handle);

// 创建私有云识别
USC_API int usc_create_service_ext(USC_HANDLE* handle, const char* host, const unsigned short port);

// 设置VAD超时时间
USC_API void usc_vad_set_timeout(USC_HANDLE handle, int frontSil,int backSil);

// 启动
USC_API int usc_start_recognizer(USC_HANDLE handle);

// 停止
USC_API int usc_stop_recognizer(USC_HANDLE handle);

// 进行识别
USC_API int usc_feed_buffer(USC_HANDLE handle, const char* buffer, int len);

// 获得识别结果
USC_API const char* usc_get_result(USC_HANDLE handle);

// 释放识别
USC_API void usc_release_service(USC_HANDLE handle);

// 设置识别参数
USC_API int usc_set_option(USC_HANDLE handle, int option_id, const char* value);

// 获取SDK 版本号
USC_API const char* usc_get_version();

// 当前语音说话开始位置，单位为毫秒
USC_API int usc_get_result_begin_time(USC_HANDLE handle);

// 当前语音说话停止位置，单位为毫秒
USC_API int usc_get_result_end_time(USC_HANDLE handle);

// 获取识别相关参数
USC_API const char * usc_get_option(USC_HANDLE handle, int option_id);

USC_API void usc_clear_option(USC_HANDLE handle, int option_id);

#endif

