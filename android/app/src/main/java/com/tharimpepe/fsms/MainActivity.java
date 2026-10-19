package com.tharimpepe.fsms;

import com.getcapacitor.BridgeActivity;

public class MainActivity extends BridgeActivity {
    @Override
    protected void onCreate(android.os.Bundle savedInstanceState) {
        // Register the native file writer so report exports reach Android Downloads.
        registerPlugin(ReportExportPlugin.class);
        super.onCreate(savedInstanceState);
    }
}
