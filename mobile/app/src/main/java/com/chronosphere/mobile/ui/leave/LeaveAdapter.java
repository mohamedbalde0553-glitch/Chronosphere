package com.chronosphere.mobile.ui.leave;

import android.content.Context;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.chronosphere.mobile.R;
import com.chronosphere.mobile.models.LeaveRequest;

import java.util.ArrayList;
import java.util.List;

public class LeaveAdapter extends RecyclerView.Adapter<LeaveAdapter.ViewHolder> {

    public interface ActionListener {
        void onApprove(int leaveId);
        void onReject(int leaveId);
        void onCancel(int leaveId);
    }

    private final List<LeaveRequest> items = new ArrayList<>();
    private boolean        isManager;
    private int            currentEmployeeId;
    private ActionListener listener;

    public void setData(Context ctx, List<LeaveRequest> data, boolean isManager,
                        int currentEmployeeId, ActionListener listener) {
        this.isManager         = isManager;
        this.currentEmployeeId = currentEmployeeId;
        this.listener          = listener;
        items.clear();
        items.addAll(data);
        notifyDataSetChanged();
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_leave, parent, false);
        return new ViewHolder(v);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder h, int pos) {
        LeaveRequest l = items.get(pos);

        h.type.setText(translateType(l.leaveType));
        h.dates.setText(l.startDate + "  →  " + l.endDate);
        h.status.setText(translateStatus(l.status));
        h.status.setTextColor(statusColor(l.status));

        // Nom de l'employé (visible pour le manager)
        if (isManager && l.employee != null && l.employee.name != null) {
            h.employeeName.setVisibility(View.VISIBLE);
            h.employeeName.setText(l.employee.name + " (" + l.employee.code + ")");
        } else {
            h.employeeName.setVisibility(View.GONE);
        }

        // Boutons manager : approuver / refuser (congés en attente seulement)
        if (isManager && "pending".equals(l.status)) {
            h.btnApprove.setVisibility(View.VISIBLE);
            h.btnReject.setVisibility(View.VISIBLE);
            h.btnCancel.setVisibility(View.GONE);
            h.btnApprove.setOnClickListener(v -> { if (listener != null) listener.onApprove(l.id); });
            h.btnReject.setOnClickListener(v -> { if (listener != null) listener.onReject(l.id); });
        } else if (!isManager && "pending".equals(l.status)) {
            // Bouton annuler pour l'employé (ses propres demandes)
            h.btnApprove.setVisibility(View.GONE);
            h.btnReject.setVisibility(View.GONE);
            h.btnCancel.setVisibility(View.VISIBLE);
            h.btnCancel.setOnClickListener(v -> { if (listener != null) listener.onCancel(l.id); });
        } else {
            h.btnApprove.setVisibility(View.GONE);
            h.btnReject.setVisibility(View.GONE);
            h.btnCancel.setVisibility(View.GONE);
        }
    }

    @Override
    public int getItemCount() { return items.size(); }

    private String translateType(String type) {
        if (type == null) return "";
        switch (type) {
            case "conge_paye":  return "Congé payé";
            case "maladie":     return "Maladie";
            case "sans_solde":  return "Sans solde";
            case "autre":       return "Autre";
            default:            return type;
        }
    }

    private String translateStatus(String status) {
        if (status == null) return "";
        switch (status) {
            case "pending":    return "En attente";
            case "approved":   return "Approuvé";
            case "rejected":   return "Refusé";
            case "cancelled":  return "Annulé";
            default:           return status;
        }
    }

    private int statusColor(String status) {
        if ("approved".equals(status))  return Color.parseColor("#16A34A");
        if ("rejected".equals(status))  return Color.parseColor("#DC2626");
        if ("cancelled".equals(status)) return Color.parseColor("#64748B");
        return Color.parseColor("#D97706");
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        TextView type, dates, status, employeeName;
        Button   btnApprove, btnReject, btnCancel;

        ViewHolder(View v) {
            super(v);
            type         = v.findViewById(R.id.tv_type);
            dates        = v.findViewById(R.id.tv_dates);
            status       = v.findViewById(R.id.tv_status);
            employeeName = v.findViewById(R.id.tv_employee_name);
            btnApprove   = v.findViewById(R.id.btn_approve);
            btnReject    = v.findViewById(R.id.btn_reject);
            btnCancel    = v.findViewById(R.id.btn_cancel);
        }
    }
}
