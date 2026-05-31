package com.chronosphere.mobile.ui.leave;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.lifecycle.ViewModelProvider;
import androidx.recyclerview.widget.LinearLayoutManager;

import com.chronosphere.mobile.databinding.FragmentLeaveBinding;
import com.chronosphere.mobile.utils.TokenManager;
import com.chronosphere.mobile.viewmodels.EmployeeViewModel;

public class LeaveFragment extends Fragment {

    private FragmentLeaveBinding binding;
    private EmployeeViewModel    viewModel;
    private TokenManager         tokenManager;

    @Override
    public View onCreateView(@NonNull LayoutInflater i, ViewGroup c, Bundle s) {
        binding = FragmentLeaveBinding.inflate(i, c, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle saved) {
        super.onViewCreated(view, saved);

        tokenManager = new TokenManager(requireContext());
        viewModel    = new ViewModelProvider(this).get(EmployeeViewModel.class);
        viewModel.init(requireContext());

        boolean isManager  = tokenManager.isManager();
        int     employeeId = tokenManager.getEmployeeId();

        LeaveAdapter adapter = new LeaveAdapter();
        binding.recyclerView.setLayoutManager(new LinearLayoutManager(requireContext()));
        binding.recyclerView.setAdapter(adapter);

        viewModel.leaves.observe(getViewLifecycleOwner(), list -> {
            if (list != null) {
                adapter.setData(requireContext(), list, isManager, employeeId,
                        new LeaveAdapter.ActionListener() {
                            @Override public void onApprove(int leaveId) { viewModel.approveLeave(leaveId); }
                            @Override public void onReject(int leaveId)  { viewModel.rejectLeave(leaveId, "Refusé par le manager"); }
                            @Override public void onCancel(int leaveId)  { viewModel.cancelLeave(employeeId, leaveId); }
                        });
                binding.tvEmpty.setVisibility(list.isEmpty() ? View.VISIBLE : View.GONE);
            }
        });

        viewModel.isLoading.observe(getViewLifecycleOwner(), loading ->
                binding.progressBar.setVisibility(loading ? View.VISIBLE : View.GONE));

        viewModel.actionResult.observe(getViewLifecycleOwner(), msg -> {
            if (msg != null && !msg.isEmpty()) {
                Toast.makeText(requireContext(), msg, Toast.LENGTH_SHORT).show();
            }
        });

        // FAB visible uniquement pour les employés
        if (isManager) {
            binding.fabNewLeave.setVisibility(View.GONE);
        } else {
            binding.fabNewLeave.setOnClickListener(v ->
                    new NewLeaveDialogFragment(employeeId, viewModel)
                            .show(getParentFragmentManager(), "new_leave"));
        }

        binding.swipeRefresh.setOnRefreshListener(() -> {
            if (isManager) {
                viewModel.loadManagerLeaves();
            } else {
                viewModel.loadLeaves(employeeId);
            }
        });

        // Observe isLoading pour masquer le swipeRefresh correctement
        viewModel.isLoading.observe(getViewLifecycleOwner(), loading -> {
            if (!loading) binding.swipeRefresh.setRefreshing(false);
        });

        // Chargement initial
        if (isManager) {
            viewModel.loadManagerLeaves();
        } else {
            viewModel.loadLeaves(employeeId);
        }
    }
}
