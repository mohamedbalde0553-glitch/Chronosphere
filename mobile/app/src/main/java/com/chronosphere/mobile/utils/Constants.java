package com.chronosphere.mobile.utils;

public class Constants {
    // Émulateur Android : 10.0.2.2 pointe vers localhost de la machine hôte
    // Appareil physique sur le même réseau WiFi : remplacer par l'IP locale ex: 192.168.1.x
    public static final String BASE_URL = "http://10.0.2.2/Chronosphere/public/api/";

    public static final String PREF_FILE    = "chronosphere_prefs";
    public static final String KEY_TOKEN    = "auth_token";
    public static final String KEY_USER_ID  = "user_id";
    public static final String KEY_USER_NAME = "user_name";
    public static final String KEY_USER_ROLE = "user_role";
    public static final String KEY_EMP_ID   = "employee_id";

    public static final String ROLE_HR_MANAGER  = "hr_manager";
    public static final String ROLE_HR_EMPLOYEE = "hr_employee";
    public static final String ROLE_SUPER_ADMIN = "super_admin";
}
