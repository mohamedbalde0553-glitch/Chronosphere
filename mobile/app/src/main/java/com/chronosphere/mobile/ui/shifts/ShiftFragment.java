package com.chronosphere.mobile.ui.shifts;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.lifecycle.ViewModelProvider;
import androidx.recyclerview.widget.LinearLayoutManager;

import com.chronosphere.mobile.databinding.FragmentShiftBinding;
import com.chronosphere.mobile.utils.TokenManager;
import com.chronosphere.mobile.viewmodels.EmployeeViewModel;

public class ShiftFragment extends Fragment {

    private FragmentShiftBinding binding;
    private EmployeeViewModel    viewModel;
    private int                  employeeId;

    @Override
    public View onCreateView(@NonNull LayoutInflater i, ViewGroup c, Bundle s) {
        binding = FragmentShiftBinding.inflate(i, c, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle saved) {
        super.onViewCreated(view, saved);

        TokenManager tm = new TokenManager(requireContext());
        employeeId = tm.getEmployeeId();

        viewModel = new ViewModelProvider(this).get(EmployeeViewModel.class);
        viewModel.init(requireContext());

        ShiftAdapter adapter = new ShiftAdapter();
        binding.recyclerView.setLayoutManager(new LinearLayoutManager(requireContext()));
        binding.recyclerView.setAdapter(adapter);

        viewModel.shifts.observe(getViewLifecycleOwner(), list -> {
            if (list != null) {
                adapter.setData(list);
                binding.tvEmpty.setVisibility(list.isEmpty() ? View.VISIBLE : View.GONE);
            }
        });

        // Masquer le spinner de refresh quand le chargement est terminé
        viewModel.isLoading.observe(getViewLifecycleOwner(), loading -> {
            binding.progressBar.setVisibility(loading ? View.VISIBLE : View.GONE);
            if (!loading) binding.swipeRefresh.setRefreshing(false);
        });

        binding.swipeRefresh.setOnRefreshListener(() -> {
            if (employeeId != -1) viewModel.loadShifts(employeeId);
        });

        if (employeeId != -1) {
            viewModel.loadShifts(employeeId);
        }
    }
}
