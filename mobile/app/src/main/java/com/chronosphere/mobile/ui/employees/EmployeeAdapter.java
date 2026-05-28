package com.chronosphere.mobile.ui.employees;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.chronosphere.mobile.R;
import com.chronosphere.mobile.models.Employee;

import java.util.ArrayList;
import java.util.List;

public class EmployeeAdapter extends RecyclerView.Adapter<EmployeeAdapter.ViewHolder> {

    public interface OnClickListener {
        void onClick(Employee employee);
    }

    private final List<Employee> items = new ArrayList<>();
    private OnClickListener listener;

    public void setListener(OnClickListener listener) { this.listener = listener; }

    public void setData(List<Employee> data) {
        items.clear();
        items.addAll(data);
        notifyDataSetChanged();
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_employee, parent, false);
        return new ViewHolder(v);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder h, int pos) {
        Employee e = items.get(pos);
        h.name.setText(e.name);
        h.code.setText(e.employeeCode != null ? e.employeeCode : "");
        h.dept.setText(e.department != null ? e.department.name : "");
        h.position.setText(e.position != null ? e.position.title : "");

        if (e.photoUrl != null && !e.photoUrl.isEmpty()) {
            Glide.with(h.avatar.getContext()).load(e.photoUrl).circleCrop().into(h.avatar);
        } else {
            h.avatar.setImageResource(R.drawable.ic_person);
        }

        h.itemView.setOnClickListener(v -> { if (listener != null) listener.onClick(e); });
    }

    @Override
    public int getItemCount() { return items.size(); }

    static class ViewHolder extends RecyclerView.ViewHolder {
        ImageView avatar;
        TextView  name, code, dept, position;

        ViewHolder(View v) {
            super(v);
            avatar   = v.findViewById(R.id.iv_avatar);
            name     = v.findViewById(R.id.tv_name);
            code     = v.findViewById(R.id.tv_code);
            dept     = v.findViewById(R.id.tv_department);
            position = v.findViewById(R.id.tv_position);
        }
    }
}
