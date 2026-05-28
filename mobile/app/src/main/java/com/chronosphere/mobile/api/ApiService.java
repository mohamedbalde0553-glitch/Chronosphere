package com.chronosphere.mobile.api;

import com.chronosphere.mobile.models.Employee;
import com.chronosphere.mobile.models.EmployeeListResponse;
import com.chronosphere.mobile.models.LeaveRequest;
import com.chronosphere.mobile.models.LeaveRequestListResponse;
import com.chronosphere.mobile.models.LoginRequest;
import com.chronosphere.mobile.models.LoginResponse;
import com.chronosphere.mobile.models.MessageResponse;
import com.chronosphere.mobile.models.Shift;
import com.chronosphere.mobile.models.ShiftListResponse;

import java.util.Map;

import retrofit2.Call;
import retrofit2.http.Body;
import retrofit2.http.GET;
import retrofit2.http.POST;
import retrofit2.http.PUT;
import retrofit2.http.Path;
import retrofit2.http.Query;

public interface ApiService {

    // Auth
    @POST("auth/login")
    Call<LoginResponse> login(@Body LoginRequest request);

    @POST("auth/logout")
    Call<MessageResponse> logout();

    // Employés
    @GET("employees")
    Call<EmployeeListResponse> getEmployees(
            @Query("page") int page,
            @Query("per_page") int perPage,
            @Query("search") String search,
            @Query("department_id") Integer departmentId
    );

    @GET("employees/{id}")
    Call<EmployeeResponse> getEmployee(@Path("id") int id);

    // Shifts d'un employé
    @GET("employees/{id}/shifts")
    Call<ShiftListResponse> getEmployeeShifts(
            @Path("id") int employeeId,
            @Query("start") String start,
            @Query("end") String end
    );

    // Congés d'un employé
    @GET("employees/{id}/leave-requests")
    Call<LeaveRequestListResponse> getLeaveRequests(@Path("id") int employeeId);

    // Créer une demande de congé
    @POST("employees/{id}/leave-requests")
    Call<LeaveRequestResponse> createLeaveRequest(
            @Path("id") int employeeId,
            @Body Map<String, Object> body
    );

    // Approuver / Refuser (manager)
    @PUT("leave-requests/{id}/approve")
    Call<LeaveRequestResponse> approveLeave(@Path("id") int leaveId);

    @PUT("leave-requests/{id}/reject")
    Call<LeaveRequestResponse> rejectLeave(
            @Path("id") int leaveId,
            @Body Map<String, String> body
    );
}
