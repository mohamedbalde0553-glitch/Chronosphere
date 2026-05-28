package com.chronosphere.mobile.models;

import com.google.gson.annotations.SerializedName;

public class Employee {
    public int    id;
    public String name;
    public String email;

    @SerializedName("employee_code")
    public String employeeCode;

    @SerializedName("phone")
    public String phone;

    @SerializedName("photo_url")
    public String photoUrl;

    public String status;

    @SerializedName("hire_date")
    public String hireDate;

    public Department department;
    public Position   position;

    public static class Department {
        public int    id;
        public String name;
    }

    public static class Position {
        public int    id;
        public String title;
    }
}
