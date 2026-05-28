package com.chronosphere.mobile.models;

import com.google.gson.annotations.SerializedName;

public class LoginResponse {
    public String token;
    public User   user;
    public String message;

    public static class User {
        public int    id;
        public String name;
        public String email;
        public String role;

        @SerializedName("employee_id")
        public int employeeId;
    }
}
