package com.chronosphere.mobile.ui.employees;

import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.lifecycle.ViewModelProvider;
import androidx.navigation.Navigation;
import androidx.recyclerview.widget.LinearLayoutManager;

import com.chronosphere.mobile.R;
import com.chronosphere.mobile.databinding.FragmentEmployeeListBinding;
import com.chronosphere.mobile.viewmodels.EmployeeViewModel;

public class EmployeeListFragment extends Fragment {

    private FragmentEmployeeListBinding binding;
    private EmployeeViewModel viewModel;
    private EmployeeAdapter   adapter;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle saved) {
        binding = FragmentEmployeeListBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle saved) {
        super.onViewCreated(view, saved);

        adapter   = new EmployeeAdapter();
        viewModel = new ViewModelProvider(this).get(EmployeeViewModel.class);
        viewModel.init(requireContext());

        binding.recyclerView.setLayoutManager(new LinearLayoutManager(requireContext()));
        binding.recyclerView.setAdapter(adapter);

        adapter.setListener(employee -> {
            Bundle args = new Bundle();
            args.putInt("employee_id", employee.id);
            args.putString("employee_name", employee.name);
            Navigation.findNavController(view).navigate(R.id.action_list_to_detail, args);
        });

        viewModel.employees.observe(getViewLifecycleOwner(), list -> {
            if (list != null) adapter.setData(list);
        });

        viewModel.isLoading.observe(getViewLifecycleOwner(), loading -> {
            binding.progressBar.setVisibility(loading ? View.VISIBLE : View.GONE);
        });

        binding.swipeRefresh.setOnRefreshListener(() -> {
            viewModel.loadEmployees(null);
            binding.swipeRefresh.setRefreshing(false);
        });

        binding.etSearch.addTextChangedListener(new TextWatcher() {
            @Override public void beforeTextChanged(CharSequence s, int st, int c, int a) {}
            @Override public void onTextChanged(CharSequence s, int st, int b, int c) {
                viewModel.loadEmployees(s.toString().trim());
            }
            @Override public void afterTextChanged(Editable s) {}
        });

        viewModel.loadEmployees(null);
    }
}
