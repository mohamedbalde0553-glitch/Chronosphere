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

        boolean isManager = tokenManager.isManager();

        LeaveAdapter adapter = new LeaveAdapter();
        binding.recyclerView.setLayoutManager(new LinearLayoutManager(requireContext()));
        binding.recyclerView.setAdapter(adapter);

        viewModel.leaves.observe(getViewLifecycleOwner(), list -> {
            if (list != null) {
                adapter.setData(requireContext(), list, isManager, new LeaveAdapter.ActionListener() {
                    @Override
                    public void onApprove(int leaveId) { viewModel.approveLeave(leaveId); }
                    @Override
                    public void onReject(int leaveId)  { viewModel.rejectLeave(leaveId, "Refusé par le manager"); }
                });
                binding.tvEmpty.setVisibility(list.isEmpty() ? View.VISIBLE : View.GONE);
            }
        });

        viewModel.actionResult.observe(getViewLifecycleOwner(), msg -> {
            if (msg != null && !msg.isEmpty()) {
                Toast.makeText(requireContext(), msg, Toast.LENGTH_SHORT).show();
            }
        });

        // Bouton nouvelle demande (employé seulement)
        if (isManager) {
            binding.fabNewLeave.setVisibility(View.GONE);
        } else {
            binding.fabNewLeave.setOnClickListener(v ->
                    new NewLeaveDialogFragment(tokenManager.getEmployeeId(), viewModel)
                            .show(getParentFragmentManager(), "new_leave"));
        }

        binding.swipeRefresh.setOnRefreshListener(() -> {
            viewModel.loadLeaves(tokenManager.getEmployeeId());
            binding.swipeRefresh.setRefreshing(false);
        });

        viewModel.loadLeaves(tokenManager.getEmployeeId());
    }
}
