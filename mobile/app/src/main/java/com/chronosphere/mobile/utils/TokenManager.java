package com.chronosphere.mobile.utils;

import android.content.Context;
import android.content.SharedPreferences;

import androidx.security.crypto.EncryptedSharedPreferences;
import androidx.security.crypto.MasterKey;

import java.io.IOException;
import java.security.GeneralSecurityException;

public class TokenManager {

    private final SharedPreferences prefs;

    public TokenManager(Context context) {
        SharedPreferences temp;
        try {
            MasterKey masterKey = new MasterKey.Builder(context)
                    .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
                    .build();
            temp = EncryptedSharedPreferences.create(
                    context,
                    Constants.PREF_FILE,
                    masterKey,
                    EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
                    EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
            );
        } catch (GeneralSecurityException | IOException e) {
            temp = context.getSharedPreferences(Constants.PREF_FILE, Context.MODE_PRIVATE);
        }
        prefs = temp;
    }

    public void saveToken(String token) {
        prefs.edit().putString(Constants.KEY_TOKEN, token).apply();
    }

    public String getToken() {
        return prefs.getString(Constants.KEY_TOKEN, null);
    }

    public void saveUserInfo(int userId, String name, String role, int employeeId) {
        prefs.edit()
                .putInt(Constants.KEY_USER_ID, userId)
                .putString(Constants.KEY_USER_NAME, name)
                .putString(Constants.KEY_USER_ROLE, role)
                .putInt(Constants.KEY_EMP_ID, employeeId)
                .apply();
    }

    public String getUserName()  { return prefs.getString(Constants.KEY_USER_NAME, ""); }
    public String getUserRole()  { return prefs.getString(Constants.KEY_USER_ROLE, ""); }
    public int    getEmployeeId(){ return prefs.getInt(Constants.KEY_EMP_ID, -1); }

    public boolean isLoggedIn()  { return getToken() != null; }

    public void clear() {
        prefs.edit().clear().apply();
    }

    public boolean isManager() {
        String role = getUserRole();
        return Constants.ROLE_HR_MANAGER.equals(role) || Constants.ROLE_SUPER_ADMIN.equals(role);
    }
}
