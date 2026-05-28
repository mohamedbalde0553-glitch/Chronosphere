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

    @SerializedName("duration_minutes")
    public int durationMinutes;
}
