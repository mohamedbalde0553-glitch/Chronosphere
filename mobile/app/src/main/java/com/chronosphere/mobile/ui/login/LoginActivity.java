package com.chronosphere.mobile.ui.login;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.lifecycle.ViewModelProvider;

import com.chronosphere.mobile.databinding.ActivityLoginBinding;
import com.chronosphere.mobile.ui.main.MainActivity;
import com.chronosphere.mobile.utils.TokenManager;
import com.chronosphere.mobile.viewmodels.LoginViewModel;

public class LoginActivity extends AppCompatActivity {

    private ActivityLoginBinding binding;
    private LoginViewModel       viewModel;
    private TokenManager         tokenManager;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding      = ActivityLoginBinding.inflate(getLayoutInflater());
        tokenManager = new TokenManager(this);
        setContentView(binding.getRoot());

        if (tokenManager.isLoggedIn()) {
            goToMain();
            return;
        }

        viewModel = new ViewModelProvider(this).get(LoginViewModel.class);
        viewModel.init(tokenManager);

        viewModel.isLoading.observe(this, loading -> {
            binding.btnLogin.setEnabled(!loading);
            binding.progressBar.setVisibility(loading ? View.VISIBLE : View.GONE);
        });

        viewModel.loginSuccess.observe(this, success -> {
            if (Boolean.TRUE.equals(success)) goToMain();
        });

        viewModel.errorMessage.observe(this, msg -> {
            if (msg != null && !msg.isEmpty()) {
                Toast.makeText(this, msg, Toast.LENGTH_LONG).show();
            }
        });

        binding.btnLogin.setOnClickListener(v -> {
            String email    = binding.etEmail.getText().toString().trim();
            String password = binding.etPassword.getText().toString().trim();

            if (email.isEmpty() || password.isEmpty()) {
                Toast.makeText(this, "Veuillez remplir tous les champs", Toast.LENGTH_SHORT).show();
                return;
            }

            viewModel.login(email, password);
        });
    }

    private void goToMain() {
        startActivity(new Intent(this, MainActivity.class));
        finish();
    }
}
