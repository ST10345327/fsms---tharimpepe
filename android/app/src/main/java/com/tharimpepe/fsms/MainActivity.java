package com.tharimpepe.fsms;

import android.webkit.WebSettings;
import android.webkit.WebView;
import com.getcapacitor.BridgeActivity;

public class MainActivity extends BridgeActivity {

    @Override
    protected void load() {
        super.load();
        enableMixedContent();
    }

    @Override
    public void onStart() {
        super.onStart();
        enableMixedContent();
    }

    private void enableMixedContent() {
        if (getBridge() == null) {
            return;
        }

        WebView webView = getBridge().getWebView();
        if (webView != null) {
            webView.getSettings().setMixedContentMode(WebSettings.MIXED_CONTENT_ALWAYS_ALLOW);
        }
    }
}
