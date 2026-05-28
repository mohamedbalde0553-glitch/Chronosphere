package com.chronosphere.mobile.viewmodels;

import android.content.Context;

import androidx.lifecycle.MutableLiveData;
import androidx.lifecycle.ViewModel;

import com.chronosphere.mobile.api.ApiService;
import com.chronosphere.mobile.api.RetrofitClient;
import com.chronosphere.mobile.models.Employee;
import com.chronosphere.mobile.models.EmployeeListResponse;
import com.chronosphere.mobile.models.EmployeeResponse;
import com.chronosphere.mobile.models.LeaveRequest;
import com.chronosphere.mobile.models.LeaveRequestListResponse;
import com.chronosphere.mobile.models.LeaveRequestResponse;
import com.chronosphere.mobile.models.Shift;
import com.chronosphere.mobile.models.ShiftListResponse;
import com.chronosphere.mobile.utils.TokenManager;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class EmployeeViewModel extends ViewModel {

    public MutableLiveData<List<Employee>>    employees        = new MutableLiveData<>();
    public MutableLiveData<Employee>          selectedEmployee = new MutableLiveData<>();
    public MutableLiveData<List<Shift>>       shifts           = new MutableLiveData<>();
    public MutableLiveData<List<LeaveRequest>> leaves          = new MutableLiveData<>();
    public MutableLiveData<Boolean>           isLoading        = new MutableLiveData<>(false);
    public MutableLiveData<String>            actionResult     = new MutableLiveData<>();

    private ApiService api;

    public void init(Context context) {
        TokenManager tm = new TokenManager(context);
        api = RetrofitClient.getInstance(tm).getApi();
    }

    public void loadEmployees(String search) {
        isLoading.setValue(true);
        api.getEmployees(1, 50, search, null).enqueue(new Callback<EmployeeListResponse>() {
            @Override
            public void onResponse(Call<EmployeeListResponse> c, Response<EmployeeListResponse> r) {
                isLoading.setValue(false);
                if (r.isSuccessful() && r.body() != null) employees.setValue(r.body().data);
            }
            @Override
            public void onFailure(Call<EmployeeListResponse> c, Throwable t) { isLoading.setValue(false); }
        });
    }

    public void loadEmployee(int id) {
        api.getEmployee(id).enqueue(new Callback<EmployeeResponse>() {
            @Override
            public void onResponse(Call<EmployeeResponse> c, Response<EmployeeResponse> r) {
                if (r.isSuccessful() && r.body() != null) selectedEmployee.setValue(r.body().data);
            }
            @Override
            public void onFailure(Call<EmployeeResponse> c, Throwable t) {}
        });
    }

    public void loadShifts(int employeeId) {
        isLoading.setValue(true);
        api.getEmployeeShifts(employeeId, null, null).enqueue(new Callback<ShiftListResponse>() {
            @Override
            public void onResponse(Call<ShiftListResponse> c, Response<ShiftListResponse> r) {
                isLoading.setValue(false);
                if (r.isSuccessful() && r.body() != null) shifts.setValue(r.body().data);
            }
            @Override
            public void onFailure(Call<ShiftListResponse> c, Throwable t) { isLoading.setValue(false); }
        });
    }

    public void loadLeaves(int employeeId) {
        isLoading.setValue(true);
        api.getLeaveRequests(employeeId).enqueue(new Callback<LeaveRequestListResponse>() {
            @Override
            public void onResponse(Call<LeaveRequestListResponse> c, Response<LeaveRequestListResponse> r) {
                isLoading.setValue(false);
                if (r.isSuccessful() && r.body() != null) leaves.setValue(r.body().data);
            }
            @Override
            public void onFailure(Call<LeaveRequestListResponse> c, Throwable t) { isLoading.setValue(false); }
        });
    }

    public void createLeaveRequest(int employeeId, Map<String, Object> body) {
        api.createLeaveRequest(employeeId, body).enqueue(new Callback<LeaveRequestResponse>() {
            @Override
            public void onResponse(Call<LeaveRequestResponse> c, Response<LeaveRequestResponse> r) {
                if (r.isSuccessful()) {
                    actionResult.setValue("Demande envoyée avec succès");
                    loadLeaves(employeeId);
                } else {
                    actionResult.setValue("Erreur lors de l'envoi");
                }
            }
            @Override
            public void onFailure(Call<LeaveRequestResponse> c, Throwable t) {
                actionResult.setValue("Erreur réseau");
            }
        });
    }

    public void approveLeave(int leaveId) {
        api.approveLeave(leaveId).enqueue(new Callback<LeaveRequestResponse>() {
            @Override
            public void onResponse(Call<LeaveRequestResponse> c, Response<LeaveRequestResponse> r) {
                actionResult.setValue(r.isSuccessful() ? "Congé approuvé" : "Erreur");
            }
            @Override
            public void onFailure(Call<LeaveRequestResponse> c, Throwable t) {
                actionResult.setValue("Erreur réseau");
            }
        });
    }

    public void rejectLeave(int leaveId, String reason) {
        Map<String, String> body = new HashMap<>();
        body.put("reason", reason);
        api.rejectLeave(leaveId, body).enqueue(new Callback<LeaveRequestResponse>() {
            @Override
            public void onResponse(Call<LeaveRequestResponse> c, Response<LeaveRequestResponse> r) {
                actionResult.setValue(r.isSuccessful() ? "Congé refusé" : "Erreur");
            }
            @Override
            public void onFailure(Call<LeaveRequestResponse> c, Throwable t) {
                actionResult.setValue("Erreur réseau");
            }
        });
    }
}
