package com.chronosphere.mobile.ui.main;

import android.content.Intent;
import android.os.Bundle;

import androidx.appcompat.app.AppCompatActivity;
import androidx.navigation.NavController;
import androidx.navigation.fragment.NavHostFragment;
import androidx.navigation.ui.NavigationUI;

import com.chronosphere.mobile.R;
import com.chronosphere.mobile.databinding.ActivityMainBinding;
import com.chronosphere.mobile.ui.login.LoginActivity;
import com.chronosphere.mobile.api.RetrofitClient;
import com.chronosphere.mobile.utils.AuthEventBus;
import com.chronosphere.mobile.utils.TokenManager;

public class MainActivity extends AppCompatActivity {

    private ActivityMainBinding binding;
    private TokenManager        tokenManager;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding      = ActivityMainBinding.inflate(getLayoutInflater());
        tokenManager = new TokenManager(this);
        setContentView(binding.getRoot());

        binding.tvUserName.setText(tokenManager.getUserName());
        binding.tvUserRole.setText(tokenManager.isManager() ? "Responsable RH" : "Employé");

        NavHostFragment navHost = (NavHostFragment) getSupportFragmentManager()
                .findFragmentById(R.id.nav_host_fragment);
        if (navHost != null) {
            NavController navController = navHost.getNavController();
            NavigationUI.setupWithNavController(binding.bottomNav, navController);
        }

        binding.btnLogout.setOnClickListener(v -> logout());

        // Redirection automatique vers Login si le token expire (401)
        AuthEventBus.onUnauthorized.observe(this, unauthorized -> {
            if (Boolean.TRUE.equals(unauthorized)) {
                AuthEventBus.onUnauthorized.setValue(null);
                RetrofitClient.reset();
                startActivity(new Intent(this, LoginActivity.class));
                finish();
            }
        });
    }

    private void logout() {
        RetrofitClient.getInstance(tokenManager)
                .getApi()
                .logout()
                .enqueue(new retrofit2.Callback<com.chronosphere.mobile.models.MessageResponse>() {
                    @Override
                    public void onResponse(retrofit2.Call<com.chronosphere.mobile.models.MessageResponse> call,
                                           retrofit2.Response<com.chronosphere.mobile.models.MessageResponse> response) {}
                    @Override
                    public void onFailure(retrofit2.Call<com.chronosphere.mobile.models.MessageResponse> call,
                                          Throwable t) {}
                });
        tokenManager.clear();
        RetrofitClient.reset();
        startActivity(new Intent(this, LoginActivity.class));
        finish();
    }
}
