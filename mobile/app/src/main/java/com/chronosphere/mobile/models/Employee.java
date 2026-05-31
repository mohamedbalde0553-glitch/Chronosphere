package com.chronosphere.mobile.models;

import com.google.gson.annotations.SerializedName;

public class Employee {
    public int    id;

    @SerializedName("employee_code")
    public String employeeCode;

    @SerializedName("photo_url")
    public String photoUrl;

    public String status;

    @SerializedName("hire_date")
    public String hireDate;

    @SerializedName("contract_type")
    public String contractType;

    public User       user;
    public Department department;
    public Position   position;

    public String getDisplayName()  { return user != null && user.name  != null ? user.name  : ""; }
    public String getDisplayEmail() { return user != null && user.email != null ? user.email : ""; }
    public String getDisplayPhone() { return user != null && user.phone != null ? user.phone : ""; }

    public static class User {
        public int    id;
        public String name;
        public String email;
        public String phone;
        public String avatar;
    }

    public static class Department {
        public int    id;
        public String name;
    }

    public static class Position {
        public int    id;
        public String title;
    }
}
