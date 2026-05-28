package com.chronosphere.mobile.models;

import com.google.gson.annotations.SerializedName;

public class LeaveRequest {
    public int    id;

    @SerializedName("leave_type")
    public String leaveType;

    @SerializedName("start_date")
    public String startDate;

    @SerializedName("end_date")
    public String endDate;

    public String reason;
    public String status;

    @SerializedName("rejection_reason")
    public String rejectionReason;
}
