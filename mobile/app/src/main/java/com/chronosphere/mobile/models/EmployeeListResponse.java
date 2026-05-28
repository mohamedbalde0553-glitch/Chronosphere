package com.chronosphere.mobile.models;

import java.util.List;

public class EmployeeListResponse {
    public List<Employee> data;
    public Meta           meta;

    public static class Meta {
        public int current_page;
        public int last_page;
        public int total;
    }
}
