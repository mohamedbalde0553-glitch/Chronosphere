package com.chronosphere.mobile.utils;

import androidx.lifecycle.MutableLiveData;

public class AuthEventBus {
    public static final MutableLiveData<Boolean> onUnauthorized = new MutableLiveData<>();
}
