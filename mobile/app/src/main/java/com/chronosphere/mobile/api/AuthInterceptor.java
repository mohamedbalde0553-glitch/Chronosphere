package com.chronosphere.mobile.api;

import com.chronosphere.mobile.utils.AuthEventBus;
import com.chronosphere.mobile.utils.TokenManager;

import java.io.IOException;

import okhttp3.Interceptor;
import okhttp3.Request;
import okhttp3.Response;

public class AuthInterceptor implements Interceptor {

    private final TokenManager tokenManager;

    public AuthInterceptor(TokenManager tokenManager) {
        this.tokenManager = tokenManager;
    }

    @Override
    public Response intercept(Chain chain) throws IOException {
        String token = tokenManager.getToken();

        Request.Builder builder = chain.request().newBuilder()
                .header("Accept", "application/json");

        if (token != null) {
            builder.header("Authorization", "Bearer " + token);
        }

        Response response = chain.proceed(builder.build());

        if (response.code() == 401 && token != null) {
            tokenManager.clear();
            AuthEventBus.onUnauthorized.postValue(true);
        }

        return response;
    }
}
