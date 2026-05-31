package com.chronosphere.mobile.models;

import com.google.gson.annotations.SerializedName;

public class LeaveRequest {
    public int    id;

    @SerializedName("employee_id")
    public int employeeId;

    @SerializedName("type")
    public String leaveType;

    @SerializedName("start_date")
    public String startDate;

    @SerializedName("end_date")
    public String endDate;

    public String reason;
    public String status;

    @SerializedName("rejection_reason")
    public String rejectionReason;

    public EmployeeInfo employee;

    public static class EmployeeInfo {
        public int    id;
        public String name;
        public String code;
    }
}
