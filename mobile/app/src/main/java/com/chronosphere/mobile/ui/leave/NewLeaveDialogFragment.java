package com.chronosphere.mobile.ui.leave;

import android.app.DatePickerDialog;
import android.app.Dialog;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.fragment.app.DialogFragment;

import com.chronosphere.mobile.databinding.DialogNewLeaveBinding;
import com.chronosphere.mobile.viewmodels.EmployeeViewModel;

import java.util.Calendar;
import java.util.HashMap;
import java.util.Map;

public class NewLeaveDialogFragment extends DialogFragment {

    private final int              employeeId;
    private final EmployeeViewModel viewModel;
    private DialogNewLeaveBinding  binding;

    private String startDate = "";
    private String endDate   = "";

    public NewLeaveDialogFragment(int employeeId, EmployeeViewModel viewModel) {
        this.employeeId = employeeId;
        this.viewModel  = viewModel;
    }

    @NonNull
    @Override
    public Dialog onCreateDialog(@Nullable Bundle saved) {
        binding = DialogNewLeaveBinding.inflate(LayoutInflater.from(requireContext()));

        String[] types   = {"Congé payé", "Maladie", "Sans solde", "Autre"};
        String[] typeVals = {"conge_paye", "maladie", "sans_solde", "autre"};

        binding.spinnerType.setAdapter(new ArrayAdapter<>(
                requireContext(), android.R.layout.simple_spinner_dropdown_item, types));

        binding.btnStartDate.setOnClickListener(v -> pickDate(true));
        binding.btnEndDate.setOnClickListener(v -> pickDate(false));

        return new AlertDialog.Builder(requireContext())
                .setTitle("Nouvelle demande de congé")
                .setView(binding.getRoot())
                .setPositiveButton("Envoyer", (d, w) -> {
                    if (startDate.isEmpty() || endDate.isEmpty()) {
                        Toast.makeText(requireContext(), "Sélectionnez les dates", Toast.LENGTH_SHORT).show();
                        return;
                    }
                    Map<String, Object> body = new HashMap<>();
                    body.put("leave_type", typeVals[binding.spinnerType.getSelectedItemPosition()]);
                    body.put("start_date", startDate);
                    body.put("end_date", endDate);
                    body.put("reason", binding.etReason.getText().toString().trim());
                    viewModel.createLeaveRequest(employeeId, body);
                })
                .setNegativeButton("Annuler", null)
                .create();
    }

    private void pickDate(boolean isStart) {
        Calendar cal = Calendar.getInstance();
        new DatePickerDialog(requireContext(), (view, y, m, d) -> {
            String date = String.format("%04d-%02d-%02d", y, m + 1, d);
            if (isStart) {
                startDate = date;
                binding.btnStartDate.setText("Début : " + date);
            } else {
                endDate = date;
                binding.btnEndDate.setText("Fin : " + date);
            }
        }, cal.get(Calendar.YEAR), cal.get(Calendar.MONTH), cal.get(Calendar.DAY_OF_MONTH)).show();
    }
}
