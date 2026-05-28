package com.chronosphere.mobile.viewmodels;

import androidx.lifecycle.MutableLiveData;
import androidx.lifecycle.ViewModel;

import com.chronosphere.mobile.api.ApiService;
import com.chronosphere.mobile.api.RetrofitClient;
import com.chronosphere.mobile.models.LoginRequest;
import com.chronosphere.mobile.models.LoginResponse;
import com.chronosphere.mobile.utils.TokenManager;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class LoginViewModel extends ViewModel {

    public MutableLiveData<Boolean> isLoading    = new MutableLiveData<>(false);
    public MutableLiveData<Boolean> loginSuccess = new MutableLiveData<>(false);
    public MutableLiveData<String>  errorMessage = new MutableLiveData<>();

    private TokenManager tokenManager;
    private ApiService   api;

    public void init(TokenManager tokenManager) {
        this.tokenManager = tokenManager;
        this.api          = RetrofitClient.getInstance(tokenManager).getApi();
    }

    public void login(String email, String password) {
        isLoading.setValue(true);
        api.login(new LoginRequest(email, password)).enqueue(new Callback<LoginResponse>() {
            @Override
            public void onResponse(Call<LoginResponse> call, Response<LoginResponse> response) {
                isLoading.setValue(false);
                if (response.isSuccessful() && response.body() != null) {
                    LoginResponse body = response.body();
                    tokenManager.saveToken(body.token);
                    if (body.user != null) {
                        tokenManager.saveUserInfo(
                                body.user.id,
                                body.user.name,
                                body.user.role != null ? body.user.role : "",
                                body.user.employeeId
                        );
                    }
                    loginSuccess.setValue(true);
                } else {
                    errorMessage.setValue("Email ou mot de passe incorrect");
                }
            }

            @Override
            public void onFailure(Call<LoginResponse> call, Throwable t) {
                isLoading.setValue(false);
                errorMessage.setValue("Erreur réseau : " + t.getMessage());
            }
        });
    }
}
