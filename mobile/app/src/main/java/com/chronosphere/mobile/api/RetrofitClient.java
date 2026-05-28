package com.chronosphere.mobile.api;

import com.chronosphere.mobile.utils.Constants;
import com.chronosphere.mobile.utils.TokenManager;

import okhttp3.OkHttpClient;
import okhttp3.logging.HttpLoggingInterceptor;
import retrofit2.Retrofit;
import retrofit2.converter.gson.GsonConverterFactory;

public class RetrofitClient {

    private static RetrofitClient instance;
    private final ApiService apiService;

    private RetrofitClient(TokenManager tokenManager) {
        HttpLoggingInterceptor logging = new HttpLoggingInterceptor();
        logging.setLevel(HttpLoggingInterceptor.Level.BODY);

        OkHttpClient client = new OkHttpClient.Builder()
                .addInterceptor(new AuthInterceptor(tokenManager))
                .addInterceptor(logging)
                .build();

        Retrofit retrofit = new Retrofit.Builder()
                .baseUrl(Constants.BASE_URL)
                .client(client)
                .addConverterFactory(GsonConverterFactory.create())
                .build();

        apiService = retrofit.create(ApiService.class);
    }

    public static synchronized RetrofitClient getInstance(TokenManager tokenManager) {
        if (instance == null) {
            instance = new RetrofitClient(tokenManager);
        }
        return instance;
    }

    public static void reset() {
        instance = null;
    }

    public ApiService getApi() {
        return apiService;
    }
}
