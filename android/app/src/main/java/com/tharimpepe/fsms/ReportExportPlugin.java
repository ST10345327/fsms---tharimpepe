package com.tharimpepe.fsms;

import android.Manifest;
import android.content.ContentValues;
import android.net.Uri;
import android.os.Build;
import android.os.Environment;
import android.provider.MediaStore;
import android.util.Base64;
import com.getcapacitor.JSObject;
import com.getcapacitor.Plugin;
import com.getcapacitor.PluginCall;
import com.getcapacitor.PluginMethod;
import com.getcapacitor.annotation.CapacitorPlugin;
import com.getcapacitor.annotation.Permission;
import com.getcapacitor.annotation.PermissionCallback;
import java.io.File;
import java.io.FileOutputStream;
import java.io.OutputStream;

@CapacitorPlugin(
    name = "ReportExport",
    permissions = {
        @Permission(alias = "storage", strings = { Manifest.permission.WRITE_EXTERNAL_STORAGE })
    }
)
public class ReportExportPlugin extends Plugin {

    @PluginMethod
    public void saveFile(PluginCall call) {
        // Android 9 and earlier require runtime storage permission for public Downloads.
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.Q && getPermissionState("storage") != com.getcapacitor.PermissionState.GRANTED) {
            requestPermissionForAlias("storage", call, "saveFileWithStoragePermission");
            return;
        }
        writeReportFile(call);
    }

    @PermissionCallback
    private void saveFileWithStoragePermission(PluginCall call) {
        if (getPermissionState("storage") != com.getcapacitor.PermissionState.GRANTED) {
            call.reject("Storage permission is required to save this report.");
            return;
        }
        writeReportFile(call);
    }

    private void writeReportFile(PluginCall call) {
        String fileName = call.getString("fileName");
        String mimeType = call.getString("mimeType", "application/octet-stream");
        String encodedData = call.getString("data");
        if (fileName == null || fileName.trim().isEmpty() || encodedData == null) {
            call.reject("A file name and file data are required.");
            return;
        }

        // Keep the requested name inside the reports folder and prevent path traversal.
        fileName = new File(fileName).getName().replaceAll("[^A-Za-z0-9._-]", "_");
        Uri fileUri = null;
        boolean pending = false;
        try {
            byte[] fileBytes = Base64.decode(encodedData, Base64.DEFAULT);
            OutputStream output;
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
                ContentValues values = new ContentValues();
                values.put(MediaStore.MediaColumns.DISPLAY_NAME, fileName);
                values.put(MediaStore.MediaColumns.MIME_TYPE, mimeType);
                values.put(MediaStore.MediaColumns.RELATIVE_PATH, Environment.DIRECTORY_DOWNLOADS + "/FSMS Reports");
                values.put(MediaStore.MediaColumns.IS_PENDING, 1);
                fileUri = getContext().getContentResolver().insert(MediaStore.Downloads.EXTERNAL_CONTENT_URI, values);
                if (fileUri == null) {
                    throw new IllegalStateException("Android could not create the report file.");
                }
                pending = true;
                output = getContext().getContentResolver().openOutputStream(fileUri);
            } else {
                File downloads = Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DOWNLOADS);
                File reportFolder = new File(downloads, "FSMS Reports");
                if (!reportFolder.exists() && !reportFolder.mkdirs()) {
                    throw new IllegalStateException("Could not create the FSMS Reports folder.");
                }
                File reportFile = new File(reportFolder, fileName);
                fileUri = Uri.fromFile(reportFile);
                output = new FileOutputStream(reportFile);
            }

            if (output == null) {
                throw new IllegalStateException("Android could not open the report file for writing.");
            }
            try (OutputStream stream = output) {
                stream.write(fileBytes);
                stream.flush();
            }

            if (pending && fileUri != null) {
                ContentValues ready = new ContentValues();
                ready.put(MediaStore.MediaColumns.IS_PENDING, 0);
                getContext().getContentResolver().update(fileUri, ready, null, null);
            }

            JSObject result = new JSObject();
            result.put("fileName", fileName);
            result.put("uri", fileUri.toString());
            result.put("location", "Downloads/FSMS Reports");
            call.resolve(result);
        } catch (Exception error) {
            if (pending && fileUri != null) {
                getContext().getContentResolver().delete(fileUri, null, null);
            }
            call.reject("Could not save the report: " + error.getMessage(), error);
        }
    }
}
