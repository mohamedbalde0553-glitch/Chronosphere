package com.chronosphere.mobile.ui.employees;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.lifecycle.ViewModelProvider;
import androidx.recyclerview.widget.LinearLayoutManager;

import com.bumptech.glide.Glide;
import com.chronosphere.mobile.R;
import com.chronosphere.mobile.databinding.FragmentEmployeeDetailBinding;
import com.chronosphere.mobile.ui.shifts.ShiftAdapter;
import com.chronosphere.mobile.ui.leave.LeaveAdapter;
import com.chronosphere.mobile.viewmodels.EmployeeViewModel;

public class EmployeeDetailFragment extends Fragment {

    private FragmentEmployeeDetailBinding binding;
    private EmployeeViewModel viewModel;

    @Override
    public View onCreateView(@NonNull LayoutInflater i, ViewGroup c, Bundle s) {
        binding = FragmentEmployeeDetailBinding.inflate(i, c, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle saved) {
        super.onViewCreated(view, saved);

        int  employeeId   = getArguments() != null ? getArguments().getInt("employee_id", -1) : -1;
        String empName    = getArguments() != null ? getArguments().getString("employee_name", "") : "";

        viewModel = new ViewModelProvider(this).get(EmployeeViewModel.class);
        viewModel.init(requireContext());

        ShiftAdapter shiftAdapter = new ShiftAdapter();
        LeaveAdapter leaveAdapter = new LeaveAdapter();

        binding.rvShifts.setLayoutManager(new LinearLayoutManager(requireContext()));
        binding.rvShifts.setAdapter(shiftAdapter);

        binding.rvLeaves.setLayoutManager(new LinearLayoutManager(requireContext()));
        binding.rvLeaves.setAdapter(leaveAdapter);

        viewModel.selectedEmployee.observe(getViewLifecycleOwner(), emp -> {
            if (emp == null) return;
            binding.tvName.setText(emp.name);
            binding.tvCode.setText(emp.employeeCode);
            binding.tvDept.setText(emp.department != null ? emp.department.name : "");
            binding.tvPos.setText(emp.position != null ? emp.position.title : "");
            binding.tvPhone.setText(emp.phone != null ? emp.phone : "—");
            binding.tvStatus.setText(emp.status);

            if (emp.photoUrl != null && !emp.photoUrl.isEmpty()) {
                Glide.with(this).load(emp.photoUrl).circleCrop().into(binding.ivAvatar);
            }
        });

        viewModel.shifts.observe(getViewLifecycleOwner(), list -> {
            if (list != null) shiftAdapter.setData(list);
        });

        viewModel.leaves.observe(getViewLifecycleOwner(), list -> {
            if (list != null) leaveAdapter.setData(requireContext(), list, false, null);
        });

        if (employeeId != -1) {
            viewModel.loadEmployee(employeeId);
            viewModel.loadShifts(employeeId);
            viewModel.loadLeaves(employeeId);
        }
    }
}
