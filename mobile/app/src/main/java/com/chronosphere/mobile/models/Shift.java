package com.chronosphere.mobile.models;

import com.google.gson.annotations.SerializedName;

public class Shift {
    public int    id;

    @SerializedName("start_at")
    public String startAt;

    @SerializedName("end_at")
    public String endAt;

    public String status;
    public String notes;

    @SerializedName("worked_minutes")
    public int workedMinutes;

    @SerializedName("overtime_minutes")
    public int overtimeMinutes;
}
