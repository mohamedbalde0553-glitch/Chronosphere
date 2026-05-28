package com.chronosphere.mobile.ui.shifts;

import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.chronosphere.mobile.R;
import com.chronosphere.mobile.models.Shift;

import java.util.ArrayList;
import java.util.List;

public class ShiftAdapter extends RecyclerView.Adapter<ShiftAdapter.ViewHolder> {

    private final List<Shift> items = new ArrayList<>();

    public void setData(List<Shift> data) {
        items.clear();
        items.addAll(data);
        notifyDataSetChanged();
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_shift, parent, false);
        return new ViewHolder(v);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder h, int pos) {
        Shift s = items.get(pos);
        h.dates.setText(formatDate(s.startAt) + "  →  " + formatDate(s.endAt));
        h.duration.setText(s.durationMinutes / 60 + "h" + (s.durationMinutes % 60 != 0 ? s.durationMinutes % 60 : ""));
        h.status.setText(translateStatus(s.status));
        h.status.setTextColor(statusColor(s.status));
    }

    @Override
    public int getItemCount() { return items.size(); }

    private String formatDate(String iso) {
        if (iso == null) return "—";
        return iso.length() >= 16 ? iso.substring(0, 16).replace("T", " ") : iso;
    }

    private String translateStatus(String status) {
        if (status == null) return "";
        switch (status) {
            case "scheduled":  return "Planifié";
            case "completed":  return "Terminé";
            case "cancelled":  return "Annulé";
            default:           return status;
        }
    }

    private int statusColor(String status) {
        if ("completed".equals(status))  return Color.parseColor("#16A34A");
        if ("cancelled".equals(status))  return Color.parseColor("#DC2626");
        return Color.parseColor("#2563EB");
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        TextView dates, duration, status;
        ViewHolder(View v) {
            super(v);
            dates    = v.findViewById(R.id.tv_dates);
            duration = v.findViewById(R.id.tv_duration);
            status   = v.findViewById(R.id.tv_status);
        }
    }
}
